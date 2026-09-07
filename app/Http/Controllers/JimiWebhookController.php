<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JimiWebhookController extends Controller
{
    public function receiveNotification(Request $request)
    {
        try {
            $msgType = $request->input('msgType');
            $data = json_decode($request->input('data'), true);

            // Log::channel('jimi_notifications')->info('JIMI Notification Received', [
            //     'msgType' => $msgType,
            //     'data' => $data
            // ]);

            if ($msgType === 'jimi.push.device.alarm') {
                $this->handleAlarmNotification($data);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification received'
            ], 200);

        } catch (\Exception $e) {
            Log::channel('jimi_notifications')->error('JIMI Webhook Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing notification'
            ], 500);
        }
    }

    private function handleAlarmNotification($data)
    {
        if (!in_array($data['alarmType'] ?? '', ['1006', '1007'])) {
            Log::channel('jimi_notifications')->info('Ignored non-geofence alarm', ['type' => $data['alarmType'] ?? 'missing']);
            return;
        }

        try {
            // Store in pending file first
            $this->storeNotificationToFile($data);

            // Try to process immediately
            $this->processNotification($data);

        } catch (\Exception $e) {
            Log::channel('jimi_notifications')->error('Failed to handle notification', [
                'imei' => $data['imei'] ?? 'missing',
                'error' => $e->getMessage()
            ]);
        }
    }

    private function storeNotificationToFile($data)
    {
        $filename = 'jimi_notifications_pending.json';

        // Get existing notifications
        $notifications = [];
        if (Storage::disk('local')->exists($filename)) {
            $content = Storage::disk('local')->get($filename);
            $notifications = json_decode($content, true) ?? [];
        }

        // Add new notification with timestamp and unique ID
        $notifications[] = [
            'id' => uniqid('jimi_', true),
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
            'processed' => false,
            'retry_count' => 0
        ];

        // Save back to file
        Storage::disk('local')->put($filename, json_encode($notifications, JSON_PRETTY_PRINT));

        Log::channel('jimi_notifications')->info('Notification stored to file', [
            'imei' => $data['imei']
        ]);
    }

    private function processNotification($data)
    {
        try {
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

            Log::channel('jimi_notifications')->info('SP OutputJson: ' . $outputJson);

            $output = json_decode($outputJson, true);

            // Mark as processed in file
            $this->markNotificationAsProcessed($data['imei']);

            Log::channel('jimi_notifications')->info('Geofence alarm processed by SP', [
                'imei' => $data['imei'],
                'type' => $data['alarmType'],
                'name' => $data['alarmName'],
                'sp_result' => $output
            ]);

        } catch (\Exception $e) {
            Log::channel('jimi_notifications')->error('Failed to process notification', [
                'imei' => $data['imei'] ?? 'missing',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function markNotificationAsProcessed($imei)
    {
        $filename = 'jimi_notifications_pending.json';

        if (!Storage::disk('local')->exists($filename)) {
            return;
        }

        $content = Storage::disk('local')->get($filename);
        $notifications = json_decode($content, true) ?? [];

        // Mark matching notification as processed
        foreach ($notifications as &$notification) {
            if (!$notification['processed'] && ($notification['data']['imei'] ?? '') === $imei) {
                $notification['processed'] = true;
                $notification['processed_at'] = now()->toIso8601String();
                break;
            }
        }

        Storage::disk('local')->put($filename, json_encode($notifications, JSON_PRETTY_PRINT));
    }
}
