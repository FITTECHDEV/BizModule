<?php

namespace App\Http\Controllers;

use App\Services\JimiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JimiTestController extends Controller
{
    private $jimiService;

    public function __construct(JimiService $jimiService)
    {
        $this->jimiService = $jimiService;
    }

    /**
     * Test Jimi API connection
     */
    public function testConnection()
    {
        Log::info('JimiTestController@testConnection called');

        $result = $this->jimiService->testConnection();

        Log::info('Jimi API testConnection response', [
            'response' => $result
        ]);

        return response()->json($result);
    }

    /**
     * Get all vehicles with online status
     */
    public function getVehicles()
    {
        Log::info('JimiTestController@getVehicles called');

        $vehicles = $this->jimiService->getVehicleList();

        Log::info('Vehicle list retrieved', [
            'count' => count($vehicles)
        ]);

        return response()->json([
            'success' => true,
            'count' => count($vehicles),
            'data' => $vehicles
        ]);
    }

    /**
     * Get current location for specific vehicle
     */
    public function getCurrentLocation($imei)
    {
        Log::info('JimiTestController@getCurrentLocation called', [
            'imei' => $imei
        ]);

        $location = $this->jimiService->getCurrentLocation($imei);

        if ($location) {
            Log::info('Current location found', [
                'imei' => $imei,
                'location' => $location
            ]);

            return response()->json([
                'success' => true,
                'data' => $location
            ]);
        }

        Log::warning('Vehicle not found or offline', [
            'imei' => $imei
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Vehicle not found or offline'
        ], 404);
    }

    /**
     * Get mileage for a trip
     */
    public function getMileage(Request $request)
    {
        Log::info('JimiTestController@getMileage called', [
            'input' => $request->all()
        ]);

        $request->validate([
            'imei' => 'required|string',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date_format:Y-m-d H:i:s'
        ]);

        $result = $this->jimiService->getMileageData(
            $request->imei,
            $request->start_time,
            $request->end_time
        );

        Log::info('Mileage data response', [
            'imei' => $request->imei,
            'result' => $result
        ]);

        return response()->json($result);
    }

   /**
 * Get GPS track data - CORRECTED FORMAT
 */
public function getTrackData(Request $request)
{
    Log::info('JimiTestController@getTrackData called', [
        'input' => $request->all()
    ]);

    $request->validate([
        'imei' => 'required|string',
        'start_time' => 'required|date_format:Y-m-d H:i:s',
        'end_time' => 'required|date_format:Y-m-d H:i:s'
    ]);

    $imei = $request->imei;
    $startTime = $request->start_time;
    $endTime = $request->end_time;

    try {
        $trackData = $this->jimiService->getTrackData($imei, $startTime, $endTime);

        if (empty($trackData)) {
            return response()->json([
                'success' => false,
                'message' => 'No track data available for the specified period'
            ]);
        }

        // Calculate total mileage from track data using Haversine formula
        $totalMileage = 0;
        for ($i = 0; $i < count($trackData) - 1; $i++) {
            $lat1 = $trackData[$i]['lat'];
            $lng1 = $trackData[$i]['lng'];
            $lat2 = $trackData[$i + 1]['lat'];
            $lng2 = $trackData[$i + 1]['lng'];

            $earthRadius = 6371000; // meters
            $dLat = deg2rad($lat2 - $lat1);
            $dLng = deg2rad($lng2 - $lng1);

            $a = sin($dLat/2) * sin($dLat/2) +
                 cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
                 sin($dLng/2) * sin($dLng/2);

            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            $distance = $earthRadius * $c;

            $totalMileage += $distance;
        }

        // Format track points for SP - EXACT FORMAT
        $trackPointsFormatted = [];
        foreach ($trackData as $point) {
            $speed = (float) ($point['speed'] ?? 0);

            $trackPointsFormatted[] = [
                'lat' => (float) $point['lat'],
                'lng' => (float) $point['lng'],
                'gpsTime' => $point['gps_time'],
                'direction' => (int) ($point['direction'] ?? 0),
                'gpsSpeed' => $speed,
                'posType' => 1,
                'satellite' => 0,
                'ignition' => $speed > 0 ? 'ON' : 'OFF',
                'accStatus' => $speed > 0 ? 'ON' : 'OFF',
                'confidence' => null,
            ];
        }

        Log::info('Track data retrieved and formatted', [
            'imei' => $imei,
            'count' => count($trackPointsFormatted),
            'total_mileage_km' => round($totalMileage / 1000, 2)
        ]);

        // Return in CORRECT FORMAT matching mileage structure
        return response()->json([
            'success' => true,
            'imei' => $imei,
            'period' => "$startTime to $endTime",
            'count' => count($trackPointsFormatted),
            'result' => $trackPointsFormatted,
            'data' => [
                'mileage' => round($totalMileage / 1000, 2) // in kilometers
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Track data error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Test page view
     */
    public function testPage()
    {
        Log::info('JimiTestController@testPage called');
        return view('jimi.test');
    }

    /**
     * Test complete job formatting - shows exact data format for SP
     * Works with both GET (with query params) and POST (with body)
     */
    public function testCompleteJobFormatting(Request $request)
    {
        Log::info('JimiTestController@testCompleteJobFormatting called', [
            'input' => $request->all()
        ]);

        // Validate - works for both GET and POST
        $request->validate([
            'imei' => 'required|string',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date_format:Y-m-d H:i:s'
        ]);

        $imei = $request->input('imei');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        // Get raw data from Jimi API
        $mileageResult = $this->jimiService->getMileageData($imei, $startTime, $endTime);
        $trackResult = $this->jimiService->getTrackData($imei, $startTime, $endTime);

        // Calculate statistics
        $totalDistance = $mileageResult['mileage'] ?? 0;
        $totalDuration = $mileageResult['duration'] ?? 0;
        $gpsPointsCount = count($trackResult);

        $avgSpeed = 0;
        $maxSpeed = 0;
        if ($gpsPointsCount > 0) {
            $speeds = array_column($trackResult, 'speed');
            $maxSpeed = max($speeds);
            $movingSpeeds = array_filter($speeds, fn($speed) => $speed > 0);
            if (!empty($movingSpeeds)) {
                $avgSpeed = round(array_sum($movingSpeeds) / count($movingSpeeds), 2);
            }
        }

        // Format mileage data for SP (EXACT FORMAT)
        $tripsFormatted = [];
        foreach ($mileageResult['trips'] ?? [] as $trip) {
            $tripsFormatted[] = [
                'avgSpeed' => (string) $trip['avg_speed'],
                'distance' => (string) $trip['distance'],
                'endLat' => $trip['end_lat'],
                'endLng' => $trip['end_lng'],
                'endTime' => $trip['end_time'],
                'imei' => $imei,
                'runTimeSecond' => (string) $trip['duration'],
                'startLat' => $trip['start_lat'],
                'startLng' => $trip['start_lng'],
                'startTime' => $trip['start_time'],
                'startMileage' => $trip['start_mileage'] ?? 0,
                'endMileage' => $trip['end_mileage'] ?? 0,
            ];
        }

        $tripMileage = [
            'result' => $tripsFormatted,
            'data' => [
                [
                    'imei' => $imei,
                    'totalMileage' => isset($mileageResult['total_mileage'])
                        ? (int) $mileageResult['total_mileage']
                        : (int) round($totalDistance),
                ],
            ],
        ];

        // Format track data for SP (EXACT FORMAT)
        $trackPointsFormatted = [];
        foreach ($trackResult as $point) {
            $speed = (float) ($point['speed'] ?? 0.0);
            $ignitionStatus = ($speed > 0) ? 'ON' : 'OFF';

            $trackPointsFormatted[] = [
                'lat' => (float) $point['lat'],
                'lng' => (float) $point['lng'],
                'gpsTime' => $point['gps_time'],
                'direction' => (int) ($point['direction'] ?? 0),
                'gpsSpeed' => $speed,
                'posType' => 1,
                'satellite' => 0,
                'ignition' => $ignitionStatus,
                'accStatus' => $ignitionStatus,
                'confidence' => null,
            ];
        }

        $tripTrack = [
            'result' => $trackPointsFormatted,
            'data' => [
                'mileage' => (float) ($totalDistance ?? 0),
            ],
        ];

        // Return comprehensive test data
        return response()->json([
            'success' => true,
            'test_info' => [
                'imei' => $imei,
                'period' => "$startTime to $endTime",
                'total_distance_m' => $totalDistance,
                'total_distance_km' => round($totalDistance / 1000, 2),
                'total_duration_sec' => $totalDuration,
                'duration_hours' => round($totalDuration / 3600, 2),
                'avg_speed' => $avgSpeed,
                'max_speed' => $maxSpeed,
                'gps_points' => $gpsPointsCount,
                'trip_segments' => count($tripsFormatted),
            ],
            'raw_from_jimi' => [
                'mileage_api_response' => [
                    'sample_trip' => $mileageResult['trips'][0] ?? null,
                    'has_start_mileage' => isset($mileageResult['trips'][0]['start_mileage']),
                    'has_end_mileage' => isset($mileageResult['trips'][0]['end_mileage']),
                ],
                'track_api_response' => [
                    'sample_point' => $trackResult[0] ?? null,
                    'first_5_points' => array_slice($trackResult, 0, 5),
                ],
            ],
            'formatted_for_sp' => [
                'tripMileage_sample' => [
                    'first_segment' => $tripsFormatted[0] ?? null,
                    'last_segment' => end($tripsFormatted) ?: null,
                ],
                'tripTrack_sample' => [
                    'first_5_points' => array_slice($trackPointsFormatted, 0, 5),
                ],
            ],
            'validation_checks' => [
                'mileage_has_cumulative_values' => isset($tripsFormatted[0]['startMileage']) && $tripsFormatted[0]['startMileage'] > 0,
                'track_has_float_coords' => isset($trackPointsFormatted[0]) && is_float($trackPointsFormatted[0]['lat']),
                'track_has_correct_ignition' => isset($trackPointsFormatted[0]) && in_array($trackPointsFormatted[0]['ignition'], ['ON', 'OFF']),
                'track_confidence_is_null' => isset($trackPointsFormatted[0]) && $trackPointsFormatted[0]['confidence'] === null,
            ],
        ]);
    }




}
