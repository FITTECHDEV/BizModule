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
     * Get GPS track data
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

        $trackData = $this->jimiService->getTrackData(
            $request->imei,
            $request->start_time,
            $request->end_time
        );

        Log::info('Track data retrieved', [
            'imei' => $request->imei,
            'count' => count($trackData)
        ]);

        return response()->json([
            'success' => true,
            'count' => count($trackData),
            'data' => $trackData
        ]);
    }

    /**
     * Test page view
     */
    public function testPage()
    {
        Log::info('JimiTestController@testPage called');
        return view('jimi.test');
    }
}
