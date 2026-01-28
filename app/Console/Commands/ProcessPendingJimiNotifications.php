<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPendingJimiNotifications extends Command
{
    protected $signature = 'jimi:process-pending';
    protected $description = 'Process pending JIMI notifications from storage';

    public function handle()
    {
        $filename = 'jimi_notifications_pending.json';

        if (!Storage::disk('local')->exists($filename)) {
            $this->info('No pending notifications found');
            return;
        }

        $content = Storage::disk('local')->get($filename);
        $notifications = json_decode($content, true) ?? [];

        $pending = array_filter($notifications, function($n) {
            return !$n['processed'] && $n['retry_count'] < 5;
        });

        $this->info('Found ' . count($pending) . ' pending notifications');

        foreach ($pending as $key => &$notification) {
            try {
                $this->processNotification($notification['data']);

                $notification['processed'] = true;
                $notification['processed_at'] = now()->toIso8601String();

                $this->info("✓ Processed: {$notification['data']['imei']}");

            } catch (\Exception $e) {
                $notification['retry_count']++;
                $notification['last_error'] = $e->getMessage();
                $notification['last_retry'] = now()->toIso8601String();

                $this->error("✗ Failed: {$notification['data']['imei']} - {$e->getMessage()}");
            }
        }

        // Save updated notifications
        Storage::disk('local')->put($filename, json_encode(array_values($notifications), JSON_PRETTY_PRINT));

        $this->info('Processing complete');
    }

    private function processNotification($data)
    {
        $notificationJson = json_encode([
            'data' => [
                'imei' => $data['imei'] ?? null,
                'deviceName' => $data['deviceName'] ?? null,
                'alarmType' => $data['alarmType'],
                'fenceAlertType' => $data['fenceAlertType'] ?? null,
                'alarmName' => $data['alarmName'] ?? null,
                'fenceId' => $data['fenceId'] ?? null,
                'lat' => $data['lat'] ?? null,
                'lng' => $data['lng'] ?? null,
                'alarmTime' => $data['alarmTime']
            ]
        ]);

        $pdo = DB::connection('sqlsrv')->getPdo();

        $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(MAX) = NULL;

            EXEC dbo.sp_Trip_NotificationUpdate
                @NotificationJson = :notificationJson,
                @OutputJson = @OutputJson OUTPUT;

            SELECT ISNULL(@OutputJson, '') AS OutputJson;
        ");

        $stmt->bindParam(':notificationJson', $notificationJson, \PDO::PARAM_STR);
        $stmt->execute();

        $outputJson = '';
        do {
            if ($stmt->columnCount() > 0) {
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    if (isset($row['OutputJson'])) {
                        $outputJson = $row['OutputJson'];
                        break 2;
                    }
                }
            }
        } while ($stmt->nextRowset());

        Log::channel('jimi_notifications')->info('Processed from file', [
            'imei' => $data['imei'],
            'output' => $outputJson
        ]);
    }
}
