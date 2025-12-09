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
     * ✅ NEW: Get access token (with automatic refresh support)
     */
    public function getAccessToken()
    {
        // Check if we have a valid cached access token
        $cachedToken = Cache::get('jimi_access_token');

        if ($cachedToken) {
            Log::info('Jimi: Using cached access token');
            return $cachedToken;
        }

        // Check if we have a refresh token
        $refreshToken = Cache::get('jimi_refresh_token');

        if ($refreshToken) {
            Log::info('Jimi: Attempting to refresh access token');
            $newToken = $this->refreshAccessToken($refreshToken);

            if ($newToken) {
                return $newToken;
            }

            Log::warning('Jimi: Refresh token failed, getting new token');
        }

        // Get a fresh token (both access and refresh)
        return $this->getFreshAccessToken();
    }

    /**
     * ✅ NEW: Get fresh access token (initial authentication)
     */
    private function getFreshAccessToken()
    {
        try {
            $timestamp = gmdate('Y-m-d H:i:s');

            $params = [
                'method' => 'jimi.oauth.token.get',
                'timestamp' => $timestamp,
                'app_key' => $this->appKey,
                'sign_method' => 'md5',
                'v' => '1.0',
                'format' => 'json',
                'user_id' => $this->account,
                'user_pwd_md5' => $this->passwordMd5,
                'expires_in' => '7200'  // Request 2 hour expiry
            ];

            $signature = $this->generateJimiSignature($params);
            $params['sign'] = $signature;

            Log::info('Jimi: Requesting fresh access token');

            $response = Http::timeout(10)->asForm()->post($this->baseUrl, $params);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['result']['accessToken'])) {
                    $accessToken = $data['result']['accessToken'];
                    $refreshToken = $data['result']['refreshToken'] ?? null;
                    $expiresIn = $data['result']['expiresIn'] ?? 7000;

                    // ✅ Cache access token (slightly less than expiry time)
                    $cacheDuration = max(60, $expiresIn - 200); // At least 1 minute, subtract 200s for safety
                    Cache::put('jimi_access_token', $accessToken, $cacheDuration);

                    Log::info('Jimi: Fresh access token obtained', [
                        'expires_in' => $expiresIn,
                        'cache_duration' => $cacheDuration,
                        'has_refresh_token' => !empty($refreshToken)
                    ]);

                    // ✅ Cache refresh token if provided (lasts longer, typically 30 days)
                    if ($refreshToken) {
                        Cache::put('jimi_refresh_token', $refreshToken, now()->addDays(29));
                        Log::info('Jimi: Refresh token cached for 29 days');
                    }

                    return $accessToken;
                }
            }

            Log::error('Jimi: Failed to get fresh token', [
                'response' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Jimi: Token error - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ NEW: Refresh access token using refresh token
     */
    private function refreshAccessToken($refreshToken)
    {
        try {
            $timestamp = gmdate('Y-m-d H:i:s');

            $params = [
                'method' => 'jimi.oauth.token.refresh',
                'timestamp' => $timestamp,
                'app_key' => $this->appKey,
                'sign_method' => 'md5',
                'v' => '1.0',
                'format' => 'json',
                'access_token' => Cache::get('jimi_access_token', ''), // Old token
                'refresh_token' => $refreshToken,
                'expires_in' => '7200'
            ];

            $signature = $this->generateJimiSignature($params);
            $params['sign'] = $signature;

            Log::info('Jimi: Refreshing access token');

            $response = Http::timeout(10)->asForm()->post($this->baseUrl, $params);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['result']['accessToken'])) {
                    $accessToken = $data['result']['accessToken'];
                    $newRefreshToken = $data['result']['refreshToken'] ?? null;
                    $expiresIn = $data['result']['expiresIn'] ?? 7000;

                    // ✅ Cache new access token
                    $cacheDuration = max(60, $expiresIn - 200);
                    Cache::put('jimi_access_token', $accessToken, $cacheDuration);

                    Log::info('Jimi: Access token refreshed successfully', [
                        'expires_in' => $expiresIn,
                        'cache_duration' => $cacheDuration
                    ]);

                    // ✅ Update refresh token if a new one is provided
                    if ($newRefreshToken) {
                        Cache::put('jimi_refresh_token', $newRefreshToken, now()->addDays(29));
                        Log::info('Jimi: New refresh token cached');
                    }

                    return $accessToken;
                }
            }

            Log::error('Jimi: Failed to refresh token', [
                'response' => $response->body()
            ]);

            // Clear invalid refresh token
            Cache::forget('jimi_refresh_token');

            return null;

        } catch (\Exception $e) {
            Log::error('Jimi: Token refresh error - ' . $e->getMessage());
            Cache::forget('jimi_refresh_token');
            return null;
        }
    }

    /**
     * Generate Jimi signature
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
     * ✅ Make API request with automatic token refresh
     */
    private function makeJimiRequest($methodParams, $retryCount = 0)
    {
        // Maximum 2 retries (initial attempt + 2 retries)
        if ($retryCount > 2) {
            Log::error('Jimi: Max retry attempts reached');
            return [
                'code' => 0,
                'message' => 'Max retry attempts reached',
                'result' => []
            ];
        }

        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error('Jimi: No access token available');
            return [
                'code' => 0,
                'message' => 'Failed to obtain access token',
                'result' => []
            ];
        }

        // Add timestamp if not present
        if (!isset($methodParams['timestamp'])) {
            $methodParams['timestamp'] = gmdate('Y-m-d H:i:s');
        }

        // Add access token
        $methodParams['access_token'] = $accessToken;

        // Generate signature
        $signature = $this->generateJimiSignature($methodParams);
        $methodParams['sign'] = $signature;

        Log::info('Jimi: Making API request', [
            'method' => $methodParams['method'] ?? 'unknown',
            'retry_count' => $retryCount,
            'token_prefix' => substr($accessToken, 0, 10) . '...'
        ]);

        $response = Http::timeout(15)->asForm()->post($this->baseUrl, $methodParams);

        if ($response->successful()) {
            $data = $response->json();

            // ✅ CHECK FOR TOKEN ERROR (code 1004)
            if (isset($data['code']) && $data['code'] === 1004) {
                Log::warning('Jimi: Token error (1004), attempting recovery', [
                    'method' => $methodParams['method'] ?? 'unknown',
                    'retry_count' => $retryCount
                ]);

                // Clear both tokens to force fresh authentication
                Cache::forget('jimi_access_token');
                Cache::forget('jimi_refresh_token');

                // Remove tokens from params for retry
                unset($methodParams['access_token']);
                unset($methodParams['sign']);

                // Retry with fresh token
                return $this->makeJimiRequest($methodParams, $retryCount + 1);
            }

            return $data;
        }

        Log::error('Jimi: API request failed', [
            'status' => $response->status(),
            'response' => $response->body()
        ]);

        return [
            'code' => 0,
            'message' => 'API request failed',
            'result' => []
        ];
    }

    /**
     * Get vehicle list
     */
    public function getVehicleList()
    {
        try {
            $params = [
                'method' => 'jimi.user.device.location.list',
                'app_key' => $this->appKey,
                'target' => $this->account,
                'map_type' => 'GOOGLE',
                'sign_method' => 'md5',
                'v' => '1.0',
                'format' => 'json'
            ];

            Log::info('Jimi: Requesting vehicle list');

            $data = $this->makeJimiRequest($params);

            Log::info('Jimi: Vehicle list response', [
                'code' => $data['code'] ?? null,
                'count' => isset($data['result']) ? count($data['result']) : 0
            ]);

            if (isset($data['result']) && is_array($data['result'])) {
                return array_map(function($vehicle) {
                    return [
                        'imei' => $vehicle['imei'] ?? null,
                        'device_name' => $vehicle['deviceName'] ?? 'Unknown',
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
            $params = [
                'method' => 'jimi.device.track.mileage',
                'app_key' => $this->appKey,
                'imeis' => $imei,
                'begin_time' => $startTime,
                'end_time' => $endTime,
                'sign_method' => 'md5',
                'v' => '1.0',
                'format' => 'json'
            ];

            Log::info('Jimi: Requesting mileage', [
                'imei' => $imei,
                'period' => "$startTime to $endTime"
            ]);

            $data = $this->makeJimiRequest($params);

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
                'mileage' => $totalDistance,
                'mileage_km' => round($totalDistance / 1000, 2),
                'duration' => $totalDuration,
                'duration_minutes' => round($totalDuration / 60, 2),
                'duration_hours' => round($totalDuration / 3600, 2),
                'trips_count' => count($trips),
                'trips' => $trips
            ];

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
            $params = [
                'method' => 'jimi.device.track.list',
                'app_key' => $this->appKey,
                'imei' => $imei,
                'begin_time' => $startTime,
                'end_time' => $endTime,
                'map_type' => 'GOOGLE',
                'sign_method' => 'md5',
                'v' => '1.0',
                'format' => 'json'
            ];

            $data = $this->makeJimiRequest($params);

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
     * Map IMEI to vehicle number (CORRECTED)
     */
    private function getVehicleNoFromIMEI($imei)
    {
        $map = [
            '865784055081171' => 'KDN 124Z',
            '865784055077419' => 'KDN 209Y',
            '865784055074523' => 'KDN 331Y',
            '865784055073301' => 'KDN 345Y',
            '865784055081189' => 'KDN 346Y',
            '865784055076759' => 'KDN 347Y',
            '865784055073996' => 'KDN 348Y',
            '865784055074077' => 'KDN 349Y',
            '865784055073303' => 'KDN 351Y',
            '865784055082542' => 'KDN 352Y',
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
