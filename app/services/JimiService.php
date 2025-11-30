<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class JimiService
{
    private $baseUrl;
    private $appKey;
    private $appSecret;
    private $account;
    private $passwordMd5;

    public function __construct()
    {
        $this->baseUrl = config('jimi.base_url', 'https://eu-open.tracksolidpro.com/route/rest');
        $this->appKey = config('jimi.app_key');
        $this->appSecret = config('jimi.app_secret');
        $this->account = config('jimi.account', 'freightintime');
        $this->passwordMd5 = config('jimi.password_md5', '426f77e303c0cf9c6a72f145d32568f7');
    }

    /**
     * Get access token from Jimi API
     */
    public function getAccessToken()
    {
        return Cache::remember('jimi_access_token', 3600, function () {
            try {
                // Generate timestamp in UTC (Y-m-d H:i:s format)
                $timestamp = gmdate('Y-m-d H:i:s');

                // Build params
                $params = [
                    'method' => 'jimi.oauth.token.get',
                    'timestamp' => $timestamp,
                    'app_key' => $this->appKey,
                    'sign_method' => 'md5',
                    'v' => '1.0',
                    'format' => 'json',
                    'user_id' => $this->account,
                    'user_pwd_md5' => $this->passwordMd5,  // Use property from config
                    'expires_in' => '7200'
                ];

                // Generate signature
                $signature = $this->generateJimiSignature($params);
                $params['sign'] = $signature;

                Log::info('Jimi: Token request', [
                    'timestamp' => $timestamp,
                    'signature' => $signature
                ]);

                // Send request
                $response = Http::timeout(10)->asForm()->post($this->baseUrl, $params);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['result']['accessToken'])) {
                        Log::info('Jimi: Access token obtained');
                        return $data['result']['accessToken'];
                    }
                }

                Log::error('Jimi: Failed to get token', ['response' => $response->body()]);
                return null;

            } catch (\Exception $e) {
                Log::error('Jimi: Token error - ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Generate Jimi signature
     * Format: MD5(appSecret + key1value1key2value2... + appSecret)
     */
    private function generateJimiSignature($params)
    {
        unset($params['sign']);
        ksort($params);

        $signString = $this->appSecret;
        foreach ($params as $key => $value) {
            $signString .= $key . $value;
        }
        $signString .= $this->appSecret;

        return strtoupper(md5($signString));
    }

    /**
     * Get vehicle list with online status
     */
    public function getVehicleList()
    {
        try {
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                Log::error('Jimi: No access token for vehicle list');
                return [];
            }

            $timestamp = gmdate('Y-m-d H:i:s');

            $params = [
                'method' => 'jimi.user.device.location.list',
                'timestamp' => $timestamp,
                'app_key' => $this->appKey,
                'access_token' => $accessToken,
                'target' => $this->account,  // Use 'target' with account name
                'map_type' => 'GOOGLE',
                'sign_method' => 'md5',
                'v' => '1.0',
                'format' => 'json'
            ];

            $signature = $this->generateJimiSignature($params);
            $params['sign'] = $signature;

            Log::info('Jimi: Requesting vehicle list');

            $response = Http::timeout(15)->asForm()->post($this->baseUrl, $params);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Jimi: Vehicle list response', [
                    'code' => $data['code'] ?? null,
                    'count' => isset($data['result']) ? count($data['result']) : 0
                ]);

                if (isset($data['result']) && is_array($data['result'])) {
                    return array_map(function($vehicle) {
                        return [
                            'imei' => $vehicle['imei'] ?? null,
                            'device_name' => $vehicle['deviceName'] ?? 'Unknown',  // Note: 'deviceName' not 'name'
                            'vehicle_no' => $this->getVehicleNoFromIMEI($vehicle['imei'] ?? ''),
                            'lat' => $vehicle['lat'] ?? null,
                            'lng' => $vehicle['lng'] ?? null,
                            'speed' => $vehicle['speed'] ?? 0,
                            'direction' => $vehicle['direction'] ?? 0,
                            'gps_time' => $vehicle['gpsTime'] ?? null,
                            'is_online' => isset($vehicle['status']) ? $vehicle['status'] == '1' : false,
                            'acc_status' => $vehicle['accStatus'] ?? '0'
                        ];
                    }, $data['result']);
                }
            }

            Log::error('Jimi: Failed to get vehicles', [
                'response' => $response->body()
            ]);

            return [];

        } catch (\Exception $e) {
            Log::error('Jimi: Get vehicles error - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get mileage data
     */
    public function getMileageData($imei, $startTime, $endTime)
    {
        try {
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                return ['success' => false, 'message' => 'No access token'];
            }

            $timestamp = gmdate('Y-m-d H:i:s');

            $params = [
                'method' => 'jimi.device.track.mileage',
                'timestamp' => $timestamp,
                'app_key' => $this->appKey,
                'access_token' => $accessToken,
                'imeis' => $imei,
                'begin_time' => $startTime,
                'end_time' => $endTime,
                'sign_method' => 'md5',
                'v' => '1.0',
                'format' => 'json'
            ];

            $signature = $this->generateJimiSignature($params);
            $params['sign'] = $signature;

            Log::info('Jimi: Requesting mileage', [
                'imei' => $imei,
                'period' => "$startTime to $endTime"
            ]);

            $response = Http::timeout(15)->asForm()->post($this->baseUrl, $params);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Jimi: Mileage response', [
                    'code' => $data['code'] ?? null,
                    'has_result' => isset($data['result']),
                    'has_data' => isset($data['data'])
                ]);

                // Get total mileage from 'data' array
                $totalMileage = 0;
                if (isset($data['data'][0]['totalMileage'])) {
                    $totalMileage = (float)$data['data'][0]['totalMileage'];
                }

                // Get trip segments from 'result' array
                $trips = [];
                $totalDistance = 0;
                $totalDuration = 0;

                if (isset($data['result']) && is_array($data['result'])) {
                    foreach ($data['result'] as $trip) {
                        $distance = (float)($trip['distance'] ?? 0);
                        $duration = (int)($trip['runTimeSecond'] ?? 0);

                        $totalDistance += $distance;
                        $totalDuration += $duration;

                        $trips[] = [
                            'start_time' => $trip['startTime'] ?? null,
                            'end_time' => $trip['endTime'] ?? null,
                            'distance' => $distance,
                            'distance_km' => round($distance / 1000, 2),
                            'duration' => $duration,
                            'duration_minutes' => round($duration / 60, 2),
                            'avg_speed' => $trip['avgSpeed'] ?? 0,
                            'start_lat' => $trip['startLat'] ?? null,
                            'start_lng' => $trip['startLng'] ?? null,
                            'end_lat' => $trip['endLat'] ?? null,
                            'end_lng' => $trip['endLng'] ?? null
                        ];
                    }
                }

                return [
                    'success' => true,
                    'imei' => $imei,
                    'total_mileage' => $totalMileage,
                    'total_mileage_km' => round($totalMileage / 1000, 2),
                    'mileage' => $totalDistance,  // Total distance in period (meters)
                    'mileage_km' => round($totalDistance / 1000, 2),
                    'duration' => $totalDuration,  // Total duration (seconds)
                    'duration_minutes' => round($totalDuration / 60, 2),
                    'duration_hours' => round($totalDuration / 3600, 2),
                    'trips_count' => count($trips),
                    'trips' => $trips
                ];
            }

            Log::error('Jimi: Failed to get mileage', [
                'response' => $response->body()
            ]);

            return ['success' => false, 'message' => 'Failed to get mileage'];

        } catch (\Exception $e) {
            Log::error('Jimi: Mileage error - ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get GPS track data
     */
    public function getTrackData($imei, $startTime, $endTime)
    {
        try {
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                return [];
            }

            $timestamp = gmdate('Y-m-d H:i:s');

            $params = [
                'method' => 'jimi.device.track.list',
                'timestamp' => $timestamp,
                'app_key' => $this->appKey,
                'access_token' => $accessToken,
                'imei' => $imei,  // Singular 'imei', not 'imeis'
                'begin_time' => $startTime,
                'end_time' => $endTime,
                'map_type' => 'GOOGLE',
                'sign_method' => 'md5',
                'v' => '1.0',
                'format' => 'json'
            ];

            $signature = $this->generateJimiSignature($params);
            $params['sign'] = $signature;

            $response = Http::timeout(20)->asForm()->post($this->baseUrl, $params);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['result']) && is_array($data['result'])) {
                    return array_map(function($point) {
                        return [
                            'lat' => $point['lat'] ?? null,
                            'lng' => $point['lng'] ?? null,
                            'speed' => $point['speed'] ?? 0,
                            'direction' => $point['direction'] ?? 0,
                            'gps_time' => $point['gpsTime'] ?? null,
                            'altitude' => $point['altitude'] ?? 0
                        ];
                    }, $data['result']);
                }
            }

            return [];

        } catch (\Exception $e) {
            Log::error('Jimi: Track data error - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get current location
     */
    public function getCurrentLocation($imei)
    {
        try {
            $vehicles = $this->getVehicleList();
            $vehicle = collect($vehicles)->firstWhere('imei', $imei);

            if ($vehicle) {
                return [
                    'imei' => $vehicle['imei'],
                    'lat' => $vehicle['lat'],
                    'lng' => $vehicle['lng'],
                    'speed' => $vehicle['speed'],
                    'direction' => $vehicle['direction'],
                    'gps_time' => $vehicle['gps_time'],
                    'is_online' => $vehicle['is_online']
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Jimi: Get location error - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Map IMEI to vehicle number
     */
    private function getVehicleNoFromIMEI($imei)
    {
        $map = [
            '865784055073301' => 'KDN 345Y',
            '865784055073302' => 'KDN 352Y',
            '865784055073303' => 'KDN 351Y',
            '865784055073304' => 'KDN 349Y',
            '865784055073305' => 'KDN 124Z',
            '865784055073306' => 'KDN 346Y',
            '865784055073307' => 'KDN 209Y',
            '865784055073308' => 'KDN 331Y',
            '865784055073309' => 'KDN 347Y',
            '865784055073310' => 'KDN 348Y'
        ];

        return $map[$imei] ?? 'Unknown';
    }

    /**
     * Test connection
     */
    public function testConnection()
    {
        try {
            $token = $this->getAccessToken();

            if ($token) {
                $vehicles = $this->getVehicleList();

                return [
                    'success' => true,
                    'message' => 'Connected to Jimi API successfully',
                    'token' => substr($token, 0, 20) . '...',
                    'vehicles_count' => count($vehicles)
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to get access token'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
