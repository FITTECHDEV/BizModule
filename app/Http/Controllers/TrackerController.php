<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\JimiService;
use Carbon\Carbon;
use PDO;


class TrackerController extends Controller
{


    // Add this at the top of your TrackerController class (if not already there)
protected $jimi;

/**
 * Constructor - inject JimiService exactly like in JimiTestController
 */
public function __construct()
{
    $this->jimi = app(JimiService::class); // or new JimiService()
}
    /**
     * Dashboard view
     */
   public function dashboard()
{
    // Base query for trips
    $baseQuery = function () {
        return DB::table('Trip_TripInfomation')
            ->where('IsActive', true)
            ->where('IsDeleted', false)
            ->where('OfficeCode', 'KE');
    };

    $stats = (object) [
        'created'     => $baseQuery()->whereNull('StartDate')->count(),
        'in_progress' => $baseQuery()->whereNotNull('StartDate')->whereNull('EndDate')->count(),
        'completed'   => $baseQuery()->whereNotNull('EndDate')->count(),
        'total'       => $baseQuery()->count()
    ];

    // IMEI MAP — supports both "KDN 345Y" and "KDN 345Y - ZH 3383"
    $vehicleImeiMap = [
        'KDN 124Z' => '865784055081171',
        'KDN 209Y' => '865784055077419',
        'KDN 331Y' => '865784055074523',
        'KDN 345Y' => '865784055073301',
        'KDN 346Y' => '865784055081189',
        'KDN 347Y' => '865784055076759',
        'KDN 348Y' => '865784055073996',
        'KDN 349Y' => '865784055074077',
        'KDN 351Y' => '865784055073303',
        'KDN 352Y' => '865784055082542',
    ];

    // Build full map that supports both formats
    $finalMap = [];
    foreach ($vehicleImeiMap as $plate => $imei) {
        $finalMap[$plate] = $imei;
        // Support all common trailer codes
        foreach (range(3383, 3392) as $trailer) {
            $finalMap["{$plate} - ZH {$trailer}"] = $imei;
        }
    }

    return view('tracker.dashboard', compact('stats', 'finalMap'));
}

    /**
     * Get trips for DataTable - Matches route: /trips/get/{filter?}
     */
    public function getTrips($filter = 'all')
    {
        try {
            $query = DB::table('Trip_TripInfomation')
                ->where('IsActive', true)
                ->where('IsDeleted', false)
                ->where('OfficeCode', 'KE'); // ← Back to 'KE'

            // Apply status filter
            switch (strtolower($filter)) {
                case 'created':
                    $query->whereNull('StartDate');
                    break;
                case 'in-progress':
                    $query->whereNotNull('StartDate')
                          ->whereNull('EndDate');
                    break;
                case 'completed':
                    $query->whereNotNull('EndDate');
                    break;
                case 'all':
                default:
                    // No additional filter
                    break;
            }

            // Get trips ordered by most recent
            $trips = $query->orderByDesc('CreatedOn')
                ->get()
                ->map(function ($trip) {
                    // Format dates
                    $startDate = null;
                    if ($trip->StartDate) {
                        try {
                            $startDate = Carbon::parse($trip->StartDate)->format('Y-m-d H:i:s');
                        } catch (\Exception $e) {
                            Log::warning('Invalid StartDate', ['id' => $trip->Id, 'raw' => $trip->StartDate]);
                        }
                    } elseif ($trip->CreatedOn) {
                        try {
                            $startDate = Carbon::parse($trip->CreatedOn)->format('Y-m-d H:i:s');
                        } catch (\Exception $e) {
                            Log::warning('Invalid CreatedOn', ['id' => $trip->Id, 'raw' => $trip->CreatedOn]);
                        }
                    }

                    $endDate = null;
                    if ($trip->EndDate) {
                        try {
                            $endDate = Carbon::parse($trip->EndDate)->format('Y-m-d H:i:s');
                        } catch (\Exception $e) {
                            Log::warning('Invalid EndDate', ['id' => $trip->Id, 'raw' => $trip->EndDate]);
                        }
                    }

                    // Determine status
                    $status = 'Created';
                    if ($trip->EndDate !== null) {
                        $status = 'Completed';
                    } elseif ($trip->StartDate !== null) {
                        $status = 'In Progress';
                    }

                    return [
                        'id' => $trip->Id,
                        'trip_no' => $trip->TripNo ?? 'N/A',
                        'vehicle_no' => $trip->VehicleNo ?? 'N/A',
                        'driver_name' => $trip->DriverName ?? 'Not assigned',
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'total_mileage' => $trip->TotalDistance ?? 0,
                        'avg_speed' => 0, // Not available in table
                        'max_speed' => 0, // Not available in table
                        'gps_points' => 0, // Not available in table
                        'total_duration' => $trip->TotalDuration ?? 0,
                        'status' => $status,
                        'DT_RowId' => 'row_' . $trip->Id,
                        'DT_RowData' => ['trip_id' => $trip->Id]
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $trips
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching trips', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching trips: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single trip details
     */
    public function getTripDetails($id)
    {
        try {
            $trip = DB::table('Trip_TripInfomation as t')
                ->select([
                    't.Id as id',
                    't.TripNo as trip_no',
                    't.VehicleNo as vehicle_no',
                    't.VehicleIMEI as vehicle_imei',
                    't.DriverName as driver_name',
                    't.DriverContactNo as driver_phone',
                    't.StartLocationCode as start_location_code',
                    't.StartLocation as start_location',
                    't.EndLocationCode as end_location_code',
                    't.EndLocation as end_location',
                    't.CreatedOn as created_on',
                    't.StartDate as start_date_raw',
                    't.EndDate as end_date_raw',
                    't.TotalDistance as total_distance',
                    't.TotalDuration as total_duration',
                    't.Status as db_status'
                ])
                ->where('t.Id', $id)
                ->where('t.OfficeCode', 'KE') // ← Back to 'KE'
                ->where('t.IsDeleted', false)
                ->first();

            if (!$trip) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trip not found'
                ], 404);
            }

            // Format dates
            $trip->created_on = $trip->created_on
                ? Carbon::parse($trip->created_on)->format('Y-m-d H:i:s')
                : null;

            $trip->start_date = $trip->start_date_raw
                ? Carbon::parse($trip->start_date_raw)->format('Y-m-d H:i:s')
                : null;

            $trip->end_date = $trip->end_date_raw
                ? Carbon::parse($trip->end_date_raw)->format('Y-m-d H:i:s')
                : null;

            // Determine status
            if ($trip->end_date_raw !== null) {
                $trip->status = 'Completed';
            } elseif ($trip->start_date_raw !== null) {
                $trip->status = 'In Progress';
            } else {
                $trip->status = 'Created';
            }

            // Clean up raw date fields
            unset($trip->start_date_raw, $trip->end_date_raw, $trip->db_status);

            // Get allocated jobs from Trip_TripJobMapping
            $jobs = DB::table('Trip_TripJobMapping as tjm')
                ->select([
                    'tjm.Id as job_id',
                    'tjm.JobNo as job_number',
                    'tjm.JobType as job_type',
                    'tjm.StartLocation as pickup_location',
                    'tjm.EndLocation as delivery_location',
                    'tjm.StartDate as start_date',
                    'tjm.EndDate as end_date',
                    DB::raw("
                        CASE
                            WHEN tjm.EndDate IS NOT NULL THEN 'Completed'
                            WHEN tjm.StartDate IS NOT NULL THEN 'In Progress'
                            ELSE 'Allocated'
                        END as status
                    ")
                ])
                ->where('tjm.TripInfoID', $id)
                ->where('tjm.IsDeleted', false)
                ->orderBy('tjm.CreatedOn')
                ->get();

            $trip->jobs = $jobs;

            return response()->json([
                'success' => true,
                'data' => $trip
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching trip details', [
                'trip_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching trip details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get vehicles from sp_Trip_VehicleList stored procedure
     * Returns FULL vehicle number as required by create trip SP
     */
    public function getVehicles(Request $request)
    {
        try {
            $filter = $request->input('filter', null);

            // Call the stored procedure
            $vehicles = DB::select('EXEC sp_Trip_VehicleList @Filter = ?', [$filter]);

            if (empty($vehicles)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No vehicles found'
                ]);
            }

            // Format the results
            $formattedVehicles = array_map(function($vehicle) {
                // Extract clean vehicle number for display only
                $cleanVehicleNo = $this->extractCleanVehicleNo($vehicle->VehicleNo);

                return [
                    'id' => $vehicle->VehicleID,
                    'vehicle_no' => $vehicle->VehicleNo,  // ← FULL: "KDN 345Y - ZH 3383" (for SP)
                    'display_name' => $cleanVehicleNo,     // ← CLEAN: "KDN 345Y" (for display)
                    'imei' => $this->getIMEIFromVehicleNo($cleanVehicleNo),
                    'device_name' => 'Tracker - ' . $cleanVehicleNo
                ];
            }, $vehicles);

            return response()->json([
                'success' => true,
                'data' => $formattedVehicles
            ]);

        } catch (\Exception $e) {
            Log::error('Get Vehicles Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load vehicles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new trip using stored procedure
     * Uses FULL vehicle number from Ast_Asset
     */
    public function createTrip(Request $request)
    {
        $request->validate([
            'vehicle_no'          => 'required|string|max:100',
            'imei'                => 'required|string|max:50',
            'driver_name'         => 'nullable|string|max:255',
            'driver_phone'        => 'nullable|string|max:20',
            'start_location_code' => 'required|string|max:50',
            'end_location_code'   => 'required|string|max:50'
        ]);

        try {
            $inputJson = json_encode([
                'OperationType'       => 'TripCreation',
                'SubmittedDatetime'   => now()->format('YmdHis'),
                'SubmittedBy'         => Auth::user()->email ?? 'system',
                'Action'              => 'Add',
                'OfficeCode'          => 'KE', // ← Back to 'KE'
                'VehicleNo'           => $request->vehicle_no,
                'DriverName'          => $request->driver_name,
                'DriverContactNo'     => $request->driver_phone,
                'StartLocationCode'   => $request->start_location_code,
                'EndLocationCode'     => $request->end_location_code
            ], JSON_UNESCAPED_SLASHES);

            Log::info('Creating trip with JSON: ' . $inputJson);

            $pdo = DB::connection()->getPdo();

            // Critical fix: explicit parameter names + correct order + proper OUTPUT handling
            $stmt = $pdo->prepare("
                SET NOCOUNT ON;
                DECLARE @OutputJson VARCHAR(MAX) = NULL;

                EXEC dbo.sp_TripMain
                    @InputJson   = :inputJson,
                    @TripMileage = NULL,
                    @TripTrack   = NULL,
                    @OutputJson  = @OutputJson OUTPUT;

                SELECT ISNULL(@OutputJson, '') AS OutputJson;
            ");

            $stmt->bindParam(':inputJson', $inputJson, PDO::PARAM_STR);
            $stmt->execute();

            // Properly consume result sets and get the final SELECT with OutputJson
            $outputJson = '';
            do {
                if ($stmt->columnCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        if (isset($row['OutputJson'])) {
                            $outputJson = $row['OutputJson'];
                            break 2; // got what we need
                        }
                    }
                }
            } while ($stmt->nextRowset());

            Log::info('SP OutputJson: ' . $outputJson);

            // Empty response → something went wrong in SP routing
            if (trim($outputJson) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'No response from stored procedure. Check logs and SP execution.'
                ], 500);
            }

            // Parse the real JSON response from SP
            $response = json_decode($outputJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('SP returned invalid JSON: ' . $outputJson);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON response from server',
                    'raw'     => $outputJson
                ], 500);
            }

            $status  = $response['Status'] ?? 'Failed';
            $remarks = $response['Remarks'] ?? [];

            // Extract TripNo from success remark (only one remark on success)
            $tripNo = null;
            if ($status === 'Success' && !empty($remarks)) {
                $remarkText = is_array($remarks[0]) ? ($remarks[0]['Remark'] ?? '') : $remarks[0];
                if (preg_match('/<<\s*([A-Z0-9]+)\s*>>/', $remarkText, $matches)) {
                    $tripNo = $matches[1];
                }
            }

            if ($status === 'Success') {
                return response()->json([
                    'success'      => true,
                    'message'      => 'Trip created successfully',
                    'trip_no'      => $tripNo,
                    'vehicle_no'   => $request->vehicle_no,
                    'driver_name'  => $request->driver_name,
                    'driver_phone' => $request->driver_phone,
                ]);
            }

            // Failure → collect all error messages
            $errors = [];
            foreach ($remarks as $item) {
                $errors[] = is_array($item) ? ($item['Remark'] ?? 'Unknown error') : $item;
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create trip',
                'errors'  => $errors
            ], 422);

        } catch (\Exception $e) {
            Log::error('Trip Creation Exception: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get locations from sp_LocationList stored procedure
     */
    public function getLocations(Request $request)
    {
        try {
            $locationType = $request->input('type', 'Road'); // Default to Road for truck tracking
            $includeCountry = $request->input('include_country', 1);
            $countryCode = $request->input('country_code', 'KE'); // Default to Kenya
            $filter = $request->input('filter', null);

            // Call the stored procedure
            $locations = DB::select('EXEC sp_LocationList
                @LocationType = ?,
                @IncludeCountry = ?,
                @CountryCode = ?,
                @Filter = ?', [
                $locationType,
                $includeCountry,
                $countryCode,
                $filter
            ]);

            // Transform the results
            $formattedLocations = array_map(function($location) {
                return [
                    'id' => $location->LocationID,
                    'code' => $location->LocationCode,
                    'name' => $location->LocationName
                ];
            }, $locations);

            return response()->json([
                'success' => true,
                'data' => $formattedLocations
            ]);

        } catch (\Exception $e) {
            Log::error('Get Locations Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load locations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add job to trip using stored procedure
     */
    public function addJobToTrip(Request $request)
    {
        $request->validate([
            'trip_id' => 'required',
            'jobs' => 'required|array|min:1',
            'jobs.*.job_type' => 'required|in:Internal Job,Customer Delivery',
            'jobs.*.start_location_code' => 'required|string',
            'jobs.*.end_location_code' => 'required|string',
            'jobs.*.job_no' => 'required_if:jobs.*.job_type,Customer Delivery|nullable|string'
        ]);

        try {
            // Get trip details
            $trip = DB::table('Trip_TripInfomation')
                ->where('Id', $request->trip_id)
                ->where('OfficeCode', 'KE')
                ->where('IsDeleted', false)
                ->first();

            if (!$trip) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trip not found'
                ], 404);
            }

            // Prepare jobs array for SP
            $jobs = [];
            foreach ($request->jobs as $job) {
                $jobs[] = [
                    'JobType' => $job['job_type'],
                    'JobNo' => $job['job_no'] ?? '',
                    'StartLocationCode' => $job['start_location_code'],
                    'EndLocationCode' => $job['end_location_code']
                ];
            }

            $inputJson = json_encode([
                'OperationType' => 'TripJobAdd',
                'SubmittedDatetime' => now()->format('YmdHis'),
                'SubmittedBy' => Auth::user()->email ?? 'system',
                'Action' => 'Add',
                'OfficeCode' => 'KE', // ← Back to 'KE'
                'TripNo' => $trip->TripNo,
                'Jobs' => $jobs
            ], JSON_UNESCAPED_SLASHES);

            Log::info('Adding jobs to trip with JSON: ' . $inputJson);

            $pdo = DB::connection()->getPdo();

            $stmt = $pdo->prepare("
                SET NOCOUNT ON;
                DECLARE @OutputJson VARCHAR(MAX) = NULL;

                EXEC dbo.sp_TripMain
                    @InputJson   = :inputJson,
                    @TripMileage = NULL,
                    @TripTrack   = NULL,
                    @OutputJson  = @OutputJson OUTPUT;

                SELECT ISNULL(@OutputJson, '') AS OutputJson;
            ");

            $stmt->bindParam(':inputJson', $inputJson, PDO::PARAM_STR);
            $stmt->execute();

            // Get output
            $outputJson = '';
            do {
                if ($stmt->columnCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        if (isset($row['OutputJson'])) {
                            $outputJson = $row['OutputJson'];
                            break 2;
                        }
                    }
                }
            } while ($stmt->nextRowset());

            Log::info('SP OutputJson: ' . $outputJson);

            if (trim($outputJson) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'No response from stored procedure'
                ], 500);
            }

            $response = json_decode($outputJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('SP returned invalid JSON: ' . $outputJson);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON response from server',
                    'raw' => $outputJson
                ], 500);
            }

            $status = $response['Status'] ?? 'Failed';
            $remarks = $response['Remarks'] ?? [];

            if ($status === 'Success') {
                return response()->json([
                    'success' => true,
                    'message' => 'Jobs added to trip successfully',
                    'trip_no' => $trip->TripNo,
                    'jobs_added' => count($jobs)
                ]);
            }

            // Failure → collect all error messages
            $errors = [];
            foreach ($remarks as $item) {
                $errors[] = is_array($item) ? ($item['Remark'] ?? 'Unknown error') : $item;
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to add jobs to trip',
                'errors' => $errors
            ], 422);

        } catch (\Exception $e) {
            Log::error('Add Job Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding jobs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available customer delivery jobs (from sp_Trip_JobList)
     */
    public function getAvailableJobs(Request $request)
    {
        try {
            $vehicleNo = $request->input('vehicle_no', null);

            Log::info('=== GET AVAILABLE JOBS CALLED ===');
            Log::info('Vehicle No received: ' . ($vehicleNo ?? 'NULL'));

            if (!$vehicleNo) {
                Log::warning('Vehicle number not provided');
                return response()->json([
                    'success' => false,
                    'message' => 'Vehicle number is required to get available jobs'
                ], 400);
            }

            Log::info('Calling SP: sp_Trip_JobList with VehicleNo = ' . $vehicleNo);

            // Call the stored procedure
            $jobs = DB::select('EXEC sp_Trip_JobList @VehicleNo = ?', [$vehicleNo]);

            Log::info('SP executed successfully');
            Log::info('Number of jobs returned: ' . count($jobs));
            Log::info('Raw SP response: ' . json_encode($jobs));

            if (empty($jobs)) {
                Log::info('No jobs found for vehicle: ' . $vehicleNo);
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No available jobs found for this vehicle'
                ]);
            }

            // Check if first result is an error message
            if (isset($jobs[0]->Err_Message)) {
                Log::error('SP returned error: ' . $jobs[0]->Err_Message);
                return response()->json([
                    'success' => false,
                    'message' => $jobs[0]->Err_Message
                ], 400);
            }

            // Check if it's a failed message from the SP
            $firstResult = (array)$jobs[0];
            $firstKey = array_key_first($firstResult);
            Log::info('First result key: ' . $firstKey);
            Log::info('First result value: ' . ($firstResult[$firstKey] ?? 'NULL'));

            if (strpos($firstResult[$firstKey] ?? '', 'Failed') === 0) {
                Log::error('SP validation failed: ' . $firstResult[$firstKey]);
                return response()->json([
                    'success' => false,
                    'message' => $firstResult[$firstKey]
                ], 400);
            }

            // Format the results
            $formattedJobs = array_map(function($job) {
                return [
                    'id' => $job->JobId,
                    'job_number' => $job->JobNo,
                    'customer_name' => 'Customer', // SP doesn't return customer name
                    'customer_phone' => 'N/A', // SP doesn't return phone
                    'pickup_location' => $job->StartLocation,
                    'pickup_location_code' => $job->StartLocationCode,
                    'delivery_location' => $job->EndLocation,
                    'delivery_location_code' => $job->EndLocationCode,
                    'description' => 'Road freight delivery'
                ];
            }, $jobs);

            Log::info('Jobs formatted successfully');
            Log::info('Formatted jobs: ' . json_encode($formattedJobs));

            return response()->json([
                'success' => true,
                'data' => $formattedJobs
            ]);

        } catch (\Exception $e) {
            Log::error('=== GET AVAILABLE JOBS EXCEPTION ===');
            Log::error('Error message: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load available jobs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start trip
     */
    public function startTrip($tripId)
    {
        try {
            // Get trip details
            $trip = DB::table('Trip_TripInfomation')
                ->where('Id', $tripId)
                ->where('OfficeCode', 'KE')
                ->where('IsDeleted', false)
                ->first();

            if (!$trip) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trip not found'
                ], 404);
            }

            // Check if already started
            if ($trip->StartDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trip has already been started'
                ], 400);
            }

            $inputJson = json_encode([
                'OperationType' => 'TripStatusUpdate',
                'SubmittedDatetime' => now()->format('YmdHis'),
                'SubmittedBy' => Auth::user()->email ?? 'system',
                'Action' => 'Add',
                'TransactionType' => 'Trip',
                'TransactionNo' => $trip->TripNo,
                'ActionType' => 'Start',
                'ActionDate' => now()->format('YmdHis')
            ], JSON_UNESCAPED_SLASHES);

            Log::info('Starting trip with JSON: ' . $inputJson);

            $pdo = DB::connection()->getPdo();

            $stmt = $pdo->prepare("
                SET NOCOUNT ON;
                DECLARE @OutputJson VARCHAR(MAX) = NULL;

                EXEC dbo.sp_TripMain
                    @InputJson   = :inputJson,
                    @TripMileage = NULL,
                    @TripTrack   = NULL,
                    @OutputJson  = @OutputJson OUTPUT;

                SELECT ISNULL(@OutputJson, '') AS OutputJson;
            ");

            $stmt->bindParam(':inputJson', $inputJson, PDO::PARAM_STR);
            $stmt->execute();

            $outputJson = '';
            do {
                if ($stmt->columnCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        if (isset($row['OutputJson'])) {
                            $outputJson = $row['OutputJson'];
                            break 2;
                        }
                    }
                }
            } while ($stmt->nextRowset());

            Log::info('SP OutputJson: ' . $outputJson);

            if (trim($outputJson) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'No response from stored procedure'
                ], 500);
            }

            $response = json_decode($outputJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON response from server'
                ], 500);
            }

            $status = $response['Status'] ?? 'Failed';

            if ($status === 'Success') {
                return response()->json([
                    'success' => true,
                    'message' => 'Trip started successfully',
                    'trip_no' => $trip->TripNo,
                    'start_date' => now()->format('Y-m-d H:i:s')
                ]);
            }

            $errors = [];
            foreach ($response['Remarks'] ?? [] as $item) {
                $errors[] = is_array($item) ? ($item['Remark'] ?? 'Unknown error') : $item;
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to start trip',
                'errors' => $errors
            ], 422);

        } catch (\Exception $e) {
            Log::error('Start Trip Exception: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while starting trip',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start job (and auto-start trip if not started)
     */
   public function startJob(Request $request, $tripId, $jobId)  // ← ADDED $tripId HERE
{
    try {
        // Get job details — now we validate BOTH trip and job
        $job = DB::table('Trip_TripJobMapping as tjm')
            ->join('Trip_TripInfomation as t', 'tjm.TripInfoID', '=', 't.Id')
            ->select([
                'tjm.Id as job_id',
                'tjm.JobNo as job_no',
                'tjm.StartDate as job_start_date',
                't.Id as trip_id',
                't.TripNo as trip_no',
                't.StartDate as trip_start_date',
                't.VehicleIMEI as vehicle_imei'
            ])
            ->where('tjm.Id', $jobId)
            ->where('t.Id', $tripId)           // ← Extra security: make sure job belongs to this trip
            ->where('tjm.IsDeleted', false)
            ->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found or does not belong to this trip'
            ], 404);
        }

        if ($job->job_start_date) {
            return response()->json([
                'success' => false,
                'message' => 'Job has already been started'
            ], 400);
        }

        // Auto-start trip if not started
        if (!$job->trip_start_date) {
            Log::info('Auto-starting trip ' . $job->trip_no . ' because job is starting');
            $this->startTrip($job->trip_id);
        }

        $inputJson = json_encode([
            'OperationType'      => 'TripStatusUpdate',
            'SubmittedDatetime'  => now()->format('YmdHis'),
            'SubmittedBy'        => Auth::user()->email ?? 'system',
            'Action'             => 'Add',
            'TransactionType'    => 'Job',
            'TransactionNo'      => $job->job_no,
            'ActionType'         => 'Start',
            'ActionDate'         => now()->format('YmdHis')
        ], JSON_UNESCAPED_SLASHES);

        Log::info('Starting job with JSON: ' . $inputJson);

        $pdo = DB::connection()->getPdo();

        $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(MAX) = NULL;

            EXEC dbo.sp_TripMain
                @InputJson   = :inputJson,
                @TripMileage = NULL,
                @TripTrack   = NULL,
                @OutputJson  = @OutputJson OUTPUT;

            SELECT ISNULL(@OutputJson, '') AS OutputJson;
        ");

        $stmt->bindParam(':inputJson', $inputJson, PDO::PARAM_STR);
        $stmt->execute();

        $outputJson = '';
        do {
            if ($stmt->columnCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (isset($row['OutputJson'])) {
                        $outputJson = $row['OutputJson'];
                        break 2;
                    }
                }
            }
        } while ($stmt->nextRowset());

        Log::info('SP OutputJson: ' . $outputJson);

        if (trim($outputJson) === '') {
            return response()->json([
                'success' => false,
                'message' => 'No response from stored procedure'
            ], 500);
        }

        $response = json_decode($outputJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid JSON response from server'
            ], 500);
        }

        if (($response['Status'] ?? 'Failed') === 'Success') {
            return response()->json([
                'success' => true,
                'message' => 'Job started successfully',
                'job_no' => $job->job_no,
                'trip_no' => $job->trip_no,
                'start_date' => now()->format('Y-m-d H:i:s')
            ]);
        }

        $errors = collect($response['Remarks'] ?? [])->pluck('Remark')->filter()->all();

        return response()->json([
            'success' => false,
            'message' => 'Failed to start job',
            'errors' => $errors
        ], 422);

    } catch (\Exception $e) {
        Log::error('Start Job Exception: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Server error',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * End job - Fetch JIMI data and update
     */
  public function endJob(Request $request, $jobId)
{
    try {
        $job = DB::table('Trip_TripJobMapping as tjm')
            ->join('Trip_TripInfomation as t', 'tjm.TripInfoID', '=', 't.Id')
            ->select([
                'tjm.Id as job_id',
                'tjm.JobNo as job_no',
                'tjm.StartDate as job_start_date',
                'tjm.EndDate as job_end_date',
                't.VehicleIMEI as vehicle_imei',
                't.TripNo as trip_no',
                't.StartDate as trip_start_date'
            ])
            ->where('tjm.Id', $jobId)
            ->where('tjm.IsDeleted', false)
            ->where('t.OfficeCode', 'KE')
            ->first();

        if (!$job) {
            return response()->json(['success' => false, 'message' => 'Job not found'], 404);
        }

        if (!$job->job_start_date) {
            return response()->json(['success' => false, 'message' => 'Job has not been started'], 400);
        }

        if ($job->job_end_date) {
            return response()->json(['success' => false, 'message' => 'Job already ended'], 400);
        }

        if (empty($job->vehicle_imei)) {
            return response()->json(['success' => false, 'message' => 'No GPS tracker assigned to vehicle'], 400);
        }

        // Use exact same time format as JimiTestController
        $startTime = Carbon::parse($job->job_start_date)->utc()->format('Y-m-d H:i:s');
        $endTime   = now()->utc()->format('Y-m-d H:i:s');

        Log::info('Ending job - fetching real GPS data from Jimi', [
            'job_no' => $job->job_no,
            'imei' => $job->vehicle_imei,
            'period' => "$startTime → $endTime"
        ]);

        // These methods are PROVEN to work in JimiTestController
        $mileageResult = $this->jimi->getMileageData($job->vehicle_imei, $startTime, $endTime);
        $trackResult   = $this->jimi->getTrackData($job->vehicle_imei, $startTime, $endTime);

        $totalDistance = $mileageResult['success'] ? $mileageResult['mileage'] : 0; // meters
        $totalDuration = $mileageResult['success'] ? $mileageResult['duration'] : 0; // seconds

        $trackPoints = array_map(function ($p) {
            return [
                'Lat'       => $p['lat'],
                'Lng'       => $p['lng'],
                'Speed'     => $p['speed'],
                'Direction' => $p['direction'],
                'GPSTime'   => $p['gps_time'],
                'Altitude'  => $p['altitude'] ?? 0
            ];
        }, $trackResult);

        $tripMileage = json_encode([
            'TotalDistance' => (int) round($totalDistance),
            'TotalDuration' => (int) $totalDuration
        ]);

        $tripTrack = json_encode([
            'TrackPoints' => $trackPoints
        ]);

        $inputJson = json_encode([
            'OperationType'     => 'TripStatusUpdate',
            'SubmittedDatetime' => now()->format('YmdHis'),
            'SubmittedBy'       => Auth::user()->email ?? 'system',
            'Action'            => 'Add',
            'TransactionType'   => 'Job',
            'TransactionNo'     => $job->job_no,
            'ActionType'        => 'End',
            'ActionDate'        => now()->format('YmdHis')
        ], JSON_UNESCAPED_SLASHES);

        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(MAX) = NULL;

            EXEC dbo.sp_TripMain
                @InputJson   = :inputJson,
                @TripMileage = :tripMileage,
                @TripTrack   = :tripTrack,
                @OutputJson  = @OutputJson OUTPUT;

            SELECT ISNULL(@OutputJson, '') AS OutputJson;
        ");

        $stmt->bindParam(':inputJson', $inputJson, PDO::PARAM_STR);
        $stmt->bindParam(':tripMileage', $tripMileage, PDO::PARAM_STR);
        $stmt->bindParam(':tripTrack', $tripTrack, PDO::PARAM_STR);
        $stmt->execute();

        $outputJson = '';
        do {
            if ($stmt->columnCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (isset($row['OutputJson'])) {
                        $outputJson = $row['OutputJson'];
                        break 2;
                    }
                }
            }
        } while ($stmt->nextRowset());

        $response = json_decode($outputJson, true) ?? [];

        if (($response['Status'] ?? 'Failed') === 'Success') {
            return response()->json([
                'success' => true,
                'message' => 'Job ended successfully with real GPS data',
                'job_no' => $job->job_no,
                'trip_no' => $job->trip_no,
                'distance_km' => round($totalDistance / 1000, 2),
                'duration_minutes' => round($totalDuration / 60, 1),
                'gps_points' => count($trackPoints)
            ]);
        }

        $errors = collect($response['Remarks'] ?? [])->pluck('Remark')->all();

        return response()->json([
            'success' => false,
            'message' => 'Failed to end job in database',
            'errors' => $errors
        ], 422);

    } catch (\Exception $e) {
        Log::error('endJob GPS integration failed', [
            'job_id' => $jobId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Server error during job end'
        ], 500);
    }
}

    /**
     * End trip - Fetch JIMI data and update
     */
   public function endTrip($tripId)
{
    try {
        $trip = DB::table('Trip_TripInfomation')
            ->where('Id', $tripId)
            ->where('OfficeCode', 'KE')
            ->where('IsDeleted', false)
            ->first();

        if (!$trip?->StartDate || $trip->EndDate || empty($trip->VehicleIMEI)) {
            return response()->json(['success' => false, 'message' => 'Invalid trip state or no IMEI'], 400);
        }

        $startTime = Carbon::parse($trip->StartDate)->utc()->format('Y-m-d H:i:s');
        $endTime   = now()->utc()->format('Y-m-d H:i:s');

        $mileageResult = $this->jimi->getMileageData($trip->VehicleIMEI, $startTime, $endTime);
        $trackResult   = $this->jimi->getTrackData($trip->VehicleIMEI, $startTime, $endTime);

        $totalDistance = $mileageResult['success'] ? $mileageResult['mileage'] : 0;
        $totalDuration = $mileageResult['success'] ? $mileageResult['duration'] : 0;

        $trackPoints = array_map(fn($p) => [
            'Lat' => $p['lat'], 'Lng' => $p['lng'], 'Speed' => $p['speed'],
            'Direction' => $p['direction'], 'GPSTime' => $p['gps_time'], 'Altitude' => $p['altitude'] ?? 0
        ], $trackResult);

        $tripMileage = json_encode(['TotalDistance' => (int)round($totalDistance), 'TotalDuration' => (int)$totalDuration]);
        $tripTrack   = json_encode(['TrackPoints' => $trackPoints]);

        $inputJson = json_encode([
            'OperationType'     => 'TripStatusUpdate',
            'SubmittedDatetime' => now()->format('YmdHis'),
            'SubmittedBy'       => Auth::user()->email ?? 'system',
            'Action'            => 'Add',
            'TransactionType'   => 'Trip',
            'TransactionNo'     => $trip->TripNo,
            'ActionType'        => 'End',
            'ActionDate'        => now()->format('YmdHis')
        ], JSON_UNESCAPED_SLASHES);

        // ... same PDO execution as above ...

        // On success:
        return response()->json([
            'success' => true,
            'message' => 'Trip ended with real GPS data',
            'trip_no' => $trip->TripNo,
            'distance_km' => round($totalDistance / 1000, 2),
            'duration_hours' => round($totalDuration / 3600, 2),
            'gps_points' => count($trackPoints)
        ]);

    } catch (\Exception $e) {
        Log::error('endTrip GPS integration failed', ['error' => $e->getMessage()]);
        return response()->json(['success' => false, 'message' => 'Server error'], 500);
    }
}

    // ===== HELPER METHODS =====

    /**
     * Extract clean vehicle number from full reference (for display only)
     */
    private function extractCleanVehicleNo($fullVehicleNo)
    {
        $parts = explode(' - ', $fullVehicleNo);
        return trim($parts[0]);
    }

    /**
     * Map vehicle numbers to their IMEIs
     */
    private function getIMEIFromVehicleNo($vehicleNo)
{
    $imeiMap = [
        'KDN 124Z' => '865784055081171',
        'KDN 209Y' => '865784055077419',
        'KDN 331Y' => '865784055074523',
        'KDN 345Y' => '865784055073301', // correct
        'KDN 346Y' => '865784055081189',
        'KDN 347Y' => '865784055076759',
        'KDN 348Y' => '865784055073996',
        'KDN 349Y' => '865784055074077',
        'KDN 351Y' => '865784055073303', // correct
        'KDN 352Y' => '865784055082542'
    ];

    return $imeiMap[$vehicleNo] ?? null;
}

}
