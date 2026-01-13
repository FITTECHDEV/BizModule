<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
                $this->handleAlarmNotification($data);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification received'
            ], 200);

        } catch (\Exception $e) {
            Log::error('JIMI Webhook Error: ' . $e->getMessage(), [
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing notification'
            ], 500);
        }
    }

    private function handleAlarmNotification($data)
    {
        // Log the alarm details
        Log::info('Geofence Alarm Triggered', [
            'imei' => $data['imei'],
            'device_name' => $data['deviceName'],
            'alarm_type' => $data['alarmType'],
            'alarm_name' => $data['alarmName'],
            'latitude' => $data['lat'],
            'longitude' => $data['lng'],
            'alarm_time' => $data['alarmTime']
        ]);

        // Add your custom logic here:
        // - Update trip status
        // - Send notifications
        // - Store in existing tables
        // etc.
    }
}