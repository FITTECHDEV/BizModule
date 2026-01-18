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
                $this->handleAlarmNotification($data);
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

    // private function handleAlarmNotification($data)
    // {
    //     Log::info('Alarm Triggered', [
    //         'imei' => $data['imei'],
    //         'device_name' => $data['deviceName'],
    //         'alarm_type' => $data['alarmType'],
    //         'alarm_name' => $data['alarmName'],
    //         'latitude' => $data['lat'],
    //         'longitude' => $data['lng'],
    //         'alarm_time' => $data['alarmTime']
    //     ]);

    //     try {
    //         // Find active trip and job for this IMEI
    //         $tripJob = $this->findActiveTripAndJob($data['imei']);

    //         // Store ALL alarms in Trip_TripNotification table
    //         DB::connection('sqlsrv')->table('Trip_TripNotification')->insert([
    //             'Id' => Uuid::uuid4()->toString(),
    //             'IMEI' => $data['imei'],
    //             'DeviceName' => $data['deviceName'],
    //             'AlarmType' => $data['alarmType'],
    //             'AlarmName' => $data['alarmName'],
    //             'Latitude' => $data['lat'],
    //             'Longitude' => $data['lng'],
    //             'AlarmTime' => $data['alarmTime'],
    //             'TripInfoID' => $tripJob['trip_id'],
    //             'JobID' => $tripJob['job_id'],
    //             'Status' => 'New',
    //             'IsActive' => 1,
    //             'IsDeleted' => 0
    //         ]);

    //         Log::info('✅ Alarm Stored in Database', [
    //             'imei' => $data['imei'],
    //             'vehicle' => $data['deviceName'],
    //             'alarm_type' => $data['alarmType'],
    //             'alarm_name' => $data['alarmName'],
    //             'trip_id' => $tripJob['trip_id'],
    //             'job_id' => $tripJob['job_id']
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('❌ Failed to store alarm in database: ' . $e->getMessage(), [
    //             'imei' => $data['imei'],
    //             'alarm_name' => $data['alarmName'],
    //             'error' => $e->getMessage()
    //         ]);
    //     }
    // }
    private function handleAlarmNotification($data)
{
    Log::info('Alarm Triggered', [
        'imei' => $data['imei'],
        'device_name' => $data['deviceName'],
        'alarm_type' => $data['alarmType'],
        'alarm_name' => $data['alarmName'],
        'latitude' => $data['lat'],
        'longitude' => $data['lng'],
        'alarm_time' => $data['alarmTime']
    ]);

    // ❌ COMMENTED OUT - Table not in production yet
    // try {
    //     DB::connection('sqlsrv')->table('Trip_TripNotification')->insert([...]);
    //     Log::info('✅ Alarm Stored in Database');
    // } catch (\Exception $e) {
    //     Log::error('❌ Failed to store alarm');
    // }

    // ✅ FOR NOW: Just logging is enough
    Log::info('✅ Notification logged (DB storage disabled until table is in production)');
}

    private function findActiveTripAndJob($imei)
    {
        try {
            // Find active trip for this IMEI from Trip_TripInformation
            $trip = DB::connection('sqlsrv')
                ->table('Trip_TripInformation')
                ->where('IMEI', $imei)
                ->where('Status', 'In Transit')
                ->whereNull('EndDate')
                ->orderBy('StartDate', 'desc')
                ->first();

            if (!$trip) {
                Log::info('No active trip found for IMEI', ['imei' => $imei]);
                return ['trip_id' => null, 'job_id' => null];
            }

            // Find active job within this trip from Trip_Jobs
            $job = DB::connection('sqlsrv')
                ->table('Trip_Jobs')
                ->where('TripInfoID', $trip->Id)
                ->where('Status', 'In Transit')
                ->whereNull('EndDate')
                ->orderBy('StartDate', 'desc')
                ->first();

            Log::info('Active trip/job found', [
                'imei' => $imei,
                'trip_id' => $trip->Id,
                'trip_no' => $trip->TripNo ?? 'N/A',
                'job_id' => $job->Id ?? null,
                'job_no' => $job->JobNumber ?? 'N/A'
            ]);

            return [
                'trip_id' => $trip->Id,
                'job_id' => $job->Id ?? null
            ];

        } catch (\Exception $e) {
            Log::error('Error finding active trip/job: ' . $e->getMessage(), [
                'imei' => $imei,
                'error' => $e->getMessage()
            ]);
            return ['trip_id' => null, 'job_id' => null];
        }
    }
}
