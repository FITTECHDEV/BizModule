<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class JimiWebhookController extends Controller
{
    public function receiveNotification(Request $request)
    {
        try {
            $msgType = $request->input('msgType');
            $data = json_decode($request->input('data'), true);

            Log::info('JIMI Notification Received', [
                'msgType' => $msgType,
                'data' => $data
            ]);

            if ($msgType === 'jimi.push.device.alarm') {
                $this->handleAlarmNotification($request->all());
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification received'
            ], 200);

        } catch (\Exception $e) {
            Log::error('JIMI Webhook Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing notification'
            ], 500);
        }
    }

   private function handleAlarmNotification($fullRequest)
{
    $data = json_decode($fullRequest['data'], true);

    Log::info('Alarm Triggered', [
        'imei' => $data['imei'],
        'device_name' => $data['deviceName'],
        'alarm_type' => $data['alarmType'],
        'alarm_name' => $data['alarmName'],
        'latitude' => $data['lat'],
        'longitude' => $data['lng'],
        'alarm_time' => $data['alarmTime']
    ]);

    try {
        // ✅ FIX: Build the EXACT same JSON structure as SSMS test
        $notificationJson = json_encode([
            'msgType' => $fullRequest['msgType'],
            'data' => $data  // This is already decoded, so pass as object
        ]);

        // Log what we're sending to SP (for debugging)
        Log::info('📤 Sending to SP', [
            'json' => $notificationJson
        ]);

        // Execute SP with SET NOCOUNT ON
        $result = DB::connection('sqlsrv')->select(
            'SET NOCOUNT ON;
            DECLARE @Output varchar(MAX);
            EXEC sp_Trip_LocationUpdate @NotificationJson = ?, @OutputJson = @Output OUTPUT;
            SELECT @Output as OutputJson',
            [$notificationJson]
        );

        $outputJson = $result[0]->OutputJson ?? null;

        if ($outputJson) {
            $output = json_decode($outputJson, true);

            Log::info('📝 SP Output', [
                'output' => $output
            ]);

            // Check if successful
            if (is_array($output) && count($output) > 0) {
                $hasSuccess = false;
                $hasFailed = false;

                foreach ($output as $item) {
                    if (isset($item['Status'])) {
                        if ($item['Status'] === 'Success') {
                            $hasSuccess = true;
                        } elseif ($item['Status'] === 'Failed') {
                            $hasFailed = true;
                        }
                    }
                }

                if ($hasSuccess && !$hasFailed) {
                    Log::info('✅ Notification Processed Successfully', [
                        'remarks' => $output
                    ]);
                } elseif ($hasFailed) {
                    Log::warning('⚠️ SP encountered errors', [
                        'output' => $output
                    ]);
                } else {
                    Log::info('ℹ️ SP executed with mixed results', [
                        'output' => $output
                    ]);
                }
            }
        }

    } catch (\Exception $e) {
        Log::error('❌ Failed to execute stored procedure: ' . $e->getMessage(), [
            'imei' => $data['imei'],
            'alarm_name' => $data['alarmName'],
            'error' => $e->getMessage()
        ]);
    }
}
}
