<?php

namespace App\Http\Controllers;

use App\Services\JimiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            'Planned' => $baseQuery()->whereNull('StartDate')->count(),
            'in_progress' => $baseQuery()->whereNotNull('StartDate')->whereNull('EndDate')->count(),
            'completed' => $baseQuery()->whereNotNull('EndDate')->count(),
            'total' => $baseQuery()->count(),
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
            'KDN 351Y' => '865784055074028',
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
                ->where('OfficeCode', 'KE');

            // Apply status filter
            switch (strtolower($filter)) {
                case 'planned':
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
                    break;
            }

            $trips = $query->orderByRaw('
            CAST(SUBSTRING(TripNo, 4, LEN(TripNo) - 3) AS BIGINT) DESC
        ')
                ->get()
                ->map(function ($trip) {
                    // ✅ ADD +3 HOURS WHEN FORMATTING
                    $startDate = null;
                    if ($trip->StartDate) {
                        try {
                            // Add 3 hours to StartDate
                            $startDate = Carbon::parse($trip->StartDate)
                                ->addHours(3)
                                ->format('Y-m-d H:i:s');
                        } catch (\Exception $e) {
                            Log::warning('Invalid StartDate', ['id' => $trip->Id, 'raw' => $trip->StartDate]);
                        }
                    } elseif ($trip->CreatedOn) {
                        try {
                            // Add 3 hours to CreatedOn
                            $startDate = Carbon::parse($trip->CreatedOn)
                                ->addHours(3)
                                ->format('Y-m-d H:i:s');
                        } catch (\Exception $e) {
                            Log::warning('Invalid CreatedOn', ['id' => $trip->Id, 'raw' => $trip->CreatedOn]);
                        }
                    }

                    $endDate = null;
                    if ($trip->EndDate) {
                        try {
                            // Add 3 hours to EndDate
                            $endDate = Carbon::parse($trip->EndDate)
                                ->addHours(3)
                                ->format('Y-m-d H:i:s');
                        } catch (\Exception $e) {
                            Log::warning('Invalid EndDate', ['id' => $trip->Id, 'raw' => $trip->EndDate]);
                        }
                    }

                    $status = 'Planned';
                    if ($trip->EndDate !== null) {
                        $status = 'Completed';
                    } elseif ($trip->StartDate !== null) {
                        $status = 'In Transit';
                    }

                    return [
                        'id' => $trip->Id,
                        'trip_no' => $trip->TripNo ?? 'N/A',
                        'vehicle_no' => $trip->VehicleNo ?? 'N/A',
                        'driver_name' => $trip->DriverName ?? 'Not assigned',
                        'start_date' => $startDate,  // ✅ Has +3 hours
                        'end_date' => $endDate,      // ✅ Has +3 hours
                        'total_mileage' => $trip->TotalDistance ?? 0,
                        'avg_speed' => 0,
                        'max_speed' => 0,
                        'gps_points' => 0,
                        'total_duration' => $trip->TotalDuration ?? 0,
                        'status' => $status,
                        'DT_RowId' => 'row_'.$trip->Id,
                        'DT_RowData' => ['trip_id' => $trip->Id],
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $trips,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching trips', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching trips: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single trip details with job mileage
     * ✅ COMPLETE METHOD - Adds +3 hours in PHP (not SQL)
     */
    public function getTripDetails($id)
    {
        try {
            // Get trip information
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
                    't.Status as db_status',
                ])
                ->where('t.Id', $id)
                ->where('t.OfficeCode', 'KE')
                ->where('t.IsDeleted', false)
                ->first();

            if (! $trip) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trip not found',
                ], 404);
            }

            // ✅ Format trip dates WITH +3 HOURS
            $trip->created_on = $trip->created_on
                ? Carbon::parse($trip->created_on)->addHours(3)->format('Y-m-d H:i:s')
                : null;

            $trip->start_date = $trip->start_date_raw
                ? Carbon::parse($trip->start_date_raw)->addHours(3)->format('Y-m-d H:i:s')
                : null;

            $trip->end_date = $trip->end_date_raw
                ? Carbon::parse($trip->end_date_raw)->addHours(3)->format('Y-m-d H:i:s')
                : null;

            // Determine status
            if ($trip->end_date_raw !== null) {
                $trip->status = 'Completed';
            } elseif ($trip->start_date_raw !== null) {
                $trip->status = 'In Transit';
            } else {
                $trip->status = 'Planned';
            }

            // Clean up raw date fields
            unset($trip->start_date_raw, $trip->end_date_raw, $trip->db_status);

            // ✅ Get jobs WITH mileage - Simple SQL query
            $jobs = DB::table('Trip_TripJobMapping as tjm')
                ->leftJoin('Trip_TripMileage as mil', function ($join) {
                    $join->on('tjm.Id', '=', 'mil.TripJobInfoID');
                })
                ->select([
                    'tjm.Id as job_id',
                    'tjm.JobNo as job_number',
                    'tjm.JobType as job_type',
                    'tjm.StartLocation as pickup_location',
                    'tjm.EndLocation as delivery_location',
                    'tjm.StartDate as start_date_raw',  // Raw date from DB
                    'tjm.EndDate as end_date_raw',      // Raw date from DB
                    'tjm.CreatedOn as created_on',
                    DB::raw('COALESCE(SUM(mil.distance) / 1000.0, 0) as total_distance'),
                    DB::raw("
                    CASE
                        WHEN tjm.EndDate IS NOT NULL THEN 'Completed'
                        WHEN tjm.StartDate IS NOT NULL THEN 'In Transit'
                        ELSE 'Allocated'
                    END as status
                "),
                ])
                ->where('tjm.TripInfoID', $id)
                ->where('tjm.IsDeleted', false)
                ->groupBy([
                    'tjm.Id',
                    'tjm.JobNo',
                    'tjm.JobType',
                    'tjm.StartLocation',
                    'tjm.EndLocation',
                    'tjm.StartDate',
                    'tjm.EndDate',
                    'tjm.CreatedOn',
                ])
                ->orderBy('tjm.CreatedOn')
                ->get()
                ->map(function ($job) {
                    // ✅ ADD +3 HOURS TO DATES IN PHP
                    $job->start_date = $job->start_date_raw
                        ? Carbon::parse($job->start_date_raw)->addHours(3)->format('Y-m-d H:i:s')
                        : null;

                    $job->end_date = $job->end_date_raw
                        ? Carbon::parse($job->end_date_raw)->addHours(3)->format('Y-m-d H:i:s')
                        : null;

                    // Remove raw fields (clean up)
                    unset($job->start_date_raw, $job->end_date_raw);

                    return $job;
                });

            $trip->jobs = $jobs;

            return response()->json([
                'success' => true,
                'data' => $trip,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching trip details', [
                'trip_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching trip details: '.$e->getMessage(),
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
                    'message' => 'No vehicles found',
                ]);
            }

            // Format the results
            $formattedVehicles = array_map(function ($vehicle) {
                // Extract clean vehicle number for display only
                $cleanVehicleNo = $this->extractCleanVehicleNo($vehicle->VehicleNo);

                return [
                    'id' => $vehicle->VehicleID,
                    'vehicle_no' => $vehicle->VehicleNo,  // ← FULL: "KDN 345Y - ZH 3383" (for SP)
                    'display_name' => $cleanVehicleNo,     // ← CLEAN: "KDN 345Y" (for display)
                    'imei' => $this->getIMEIFromVehicleNo($cleanVehicleNo),
                    'device_name' => 'Tracker - '.$cleanVehicleNo,
                ];
            }, $vehicles);

            return response()->json([
                'success' => true,
                'data' => $formattedVehicles,
            ]);

        } catch (\Exception $e) {
            Log::error('Get Vehicles Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load vehicles',
                'error' => $e->getMessage(),
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
            'vehicle_no' => 'required|string|max:100',
            'imei' => 'required|string|max:50',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:20',
            'start_location_code' => 'required|string|max:50',
            'end_location_code' => 'required|string|max:50',
        ]);

        try {
            $inputJson = json_encode([
                'OperationType' => 'TripCreation',
                'SubmittedDatetime' => now()->format('YmdHis'),
                'SubmittedBy' => Auth::user()->email ?? 'system',
                'Action' => 'Add',
                'OfficeCode' => 'KE', // ← Back to 'KE'
                'VehicleNo' => $request->vehicle_no,
                'DriverName' => $request->driver_name,
                'DriverContactNo' => $request->driver_phone,
                'StartLocationCode' => $request->start_location_code,
                'EndLocationCode' => $request->end_location_code,
            ], JSON_UNESCAPED_SLASHES);

            Log::info('Creating trip with JSON: '.$inputJson);

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

            Log::info('SP OutputJson: '.$outputJson);

            // Empty response → something went wrong in SP routing
            if (trim($outputJson) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'No response from stored procedure. Check logs and SP execution.',
                ], 500);
            }

            // Parse the real JSON response from SP
            $response = json_decode($outputJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('SP returned invalid JSON: '.$outputJson);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON response from server',
                    'raw' => $outputJson,
                ], 500);
            }

            $status = $response['Status'] ?? 'Failed';
            $remarks = $response['Remarks'] ?? [];

            // Extract TripNo from success remark (only one remark on success)
            $tripNo = null;
            if ($status === 'Success' && ! empty($remarks)) {
                $remarkText = is_array($remarks[0]) ? ($remarks[0]['Remark'] ?? '') : $remarks[0];
                if (preg_match('/<<\s*([A-Z0-9]+)\s*>>/', $remarkText, $matches)) {
                    $tripNo = $matches[1];
                }
            }

            if ($status === 'Success') {
                return response()->json([
                    'success' => true,
                    'message' => 'Trip Planned successfully',
                    'trip_no' => $tripNo,
                    'vehicle_no' => $request->vehicle_no,
                    'driver_name' => $request->driver_name,
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
                'errors' => $errors,
            ], 422);

        } catch (\Exception $e) {
            Log::error('Trip Creation Exception: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get locations from sp_LocationList stored procedure
     */
  public function getLocations(Request $request)
{
    try {
        $locationType   = $request->input('type', 'Road');
        $includeCountry = (int) $request->input('include_country', 1);
        $filter         = $request->input('filter', null);

        // Only set country code if includeCountry = 1
        $countryCode = $includeCountry === 1
            ? $request->input('country_code', 'KE')
            : null;

        $pdo = DB::connection()->getPdo();

        $stmt = $pdo->prepare('
            SET NOCOUNT ON;
            EXEC sp_LocationList
                @LocationType = :locationType,
                @IncludeCountry = :includeCountry,
                @CountryCode = :countryCode,
                @Filter = :filter
        ');

        $stmt->bindParam(':locationType', $locationType, PDO::PARAM_STR);
        $stmt->bindParam(':includeCountry', $includeCountry, PDO::PARAM_INT);

        // Bind NULL properly when country is not included
        if ($includeCountry === 1) {
            $stmt->bindParam(':countryCode', $countryCode, PDO::PARAM_STR);
        } else {
            $stmt->bindValue(':countryCode', null, PDO::PARAM_NULL);
        }

        // Bind filter safely
        if ($filter !== null) {
            $stmt->bindParam(':filter', $filter, PDO::PARAM_STR);
        } else {
            $stmt->bindValue(':filter', null, PDO::PARAM_NULL);
        }

        $stmt->execute();

        $locations = $stmt->fetchAll(PDO::FETCH_OBJ);

        $formattedLocations = array_map(function ($location) {
            return [
                'id'   => $location->LocationID,
                'code' => $location->LocationCode,
                'name' => $location->LocationName,
            ];
        }, $locations);

        return response()->json([
            'success' => true,
            'data'    => $formattedLocations,
        ]);

    } catch (\Exception $e) {
        Log::error('Get Locations Error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Failed to load locations',
            'error'   => $e->getMessage(),
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
            'jobs.*.job_no' => 'required_if:jobs.*.job_type,Customer Delivery|nullable|string',
        ]);

        try {
            // Get trip details
            $trip = DB::table('Trip_TripInfomation')
                ->where('Id', $request->trip_id)
                ->where('OfficeCode', 'KE')
                ->where('IsDeleted', false)
                ->first();

            if (! $trip) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trip not found',
                ], 404);
            }

            // Prepare jobs array for SP
            $jobs = [];
            foreach ($request->jobs as $job) {
                $jobs[] = [
                    'JobType' => $job['job_type'],
                    'JobNo' => $job['job_no'] ?? '',
                    'StartLocationCode' => $job['start_location_code'],
                    'EndLocationCode' => $job['end_location_code'],
                ];
            }

            $inputJson = json_encode([
                'OperationType' => 'TripJobAdd',
                'SubmittedDatetime' => now()->format('YmdHis'),
                'SubmittedBy' => Auth::user()->email ?? 'system',
                'Action' => 'Add',
                'OfficeCode' => 'KE', // ← Back to 'KE'
                'TripNo' => $trip->TripNo,
                'Jobs' => $jobs,
            ], JSON_UNESCAPED_SLASHES);

            Log::info('Adding jobs to trip with JSON: '.$inputJson);

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

            Log::info('SP OutputJson: '.$outputJson);

            if (trim($outputJson) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'No response from stored procedure',
                ], 500);
            }

            $response = json_decode($outputJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('SP returned invalid JSON: '.$outputJson);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON response from server',
                    'raw' => $outputJson,
                ], 500);
            }

            $status = $response['Status'] ?? 'Failed';
            $remarks = $response['Remarks'] ?? [];

            if ($status === 'Success') {
                return response()->json([
                    'success' => true,
                    'message' => 'Jobs added to trip successfully',
                    'trip_no' => $trip->TripNo,
                    'jobs_added' => count($jobs),
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
                'errors' => $errors,
            ], 422);

        } catch (\Exception $e) {
            Log::error('Add Job Error: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding jobs',
                'error' => $e->getMessage(),
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
            Log::info('Vehicle No received: '.($vehicleNo ?? 'NULL'));

            if (! $vehicleNo) {
                Log::warning('Vehicle number not provided');

                return response()->json([
                    'success' => false,
                    'message' => 'Vehicle number is required to get available jobs',
                ], 400);
            }

            Log::info('Calling SP: sp_Trip_JobList with VehicleNo = '.$vehicleNo);

            // Call the stored procedure
            $jobs = DB::select('EXEC sp_Trip_JobList @VehicleNo = ?', [$vehicleNo]);

            Log::info('SP executed successfully');
            Log::info('Number of jobs returned: '.count($jobs));
            Log::info('Raw SP response: '.json_encode($jobs));

            if (empty($jobs)) {
                Log::info('No jobs found for vehicle: '.$vehicleNo);

                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No available jobs found for this vehicle',
                ]);
            }

            // Check if first result is an error message
            if (isset($jobs[0]->Err_Message)) {
                Log::error('SP returned error: '.$jobs[0]->Err_Message);

                return response()->json([
                    'success' => false,
                    'message' => $jobs[0]->Err_Message,
                ], 400);
            }

            // Check if it's a failed message from the SP
            $firstResult = (array) $jobs[0];
            $firstKey = array_key_first($firstResult);
            Log::info('First result key: '.$firstKey);
            Log::info('First result value: '.($firstResult[$firstKey] ?? 'NULL'));

            if (strpos($firstResult[$firstKey] ?? '', 'Failed') === 0) {
                Log::error('SP validation failed: '.$firstResult[$firstKey]);

                return response()->json([
                    'success' => false,
                    'message' => $firstResult[$firstKey],
                ], 400);
            }

            // Format the results
            $formattedJobs = array_map(function ($job) {
                return [
                    'id' => $job->JobId,
                    'job_number' => $job->JobNo,
                    'customer_name' => 'Customer', // SP doesn't return customer name
                    'customer_phone' => 'N/A', // SP doesn't return phone
                    'pickup_location' => $job->StartLocation,
                    'pickup_location_code' => $job->StartLocationCode,
                    'delivery_location' => $job->EndLocation,
                    'delivery_location_code' => $job->EndLocationCode,
                    'description' => 'Road freight delivery',
                ];
            }, $jobs);

            Log::info('Jobs formatted successfully');
            Log::info('Formatted jobs: '.json_encode($formattedJobs));

            return response()->json([
                'success' => true,
                'data' => $formattedJobs,
            ]);

        } catch (\Exception $e) {
            Log::error('=== GET AVAILABLE JOBS EXCEPTION ===');
            Log::error('Error message: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load available jobs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Start trip (supports both current time and custom date for auto-start)
     */
    public function startTrip($tripId, $customStartDate = null)
    {
        try {
            $trip = DB::table('Trip_TripInfomation')
                ->where('Id', $tripId)
                ->where('OfficeCode', 'KE')
                ->where('IsDeleted', false)
                ->first();

            if (! $trip) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trip not found',
                ], 404);
            }

            if ($trip->StartDate) {
                Log::info('Trip already started', ['trip_no' => $trip->TripNo]);

                return response()->json([
                    'success' => true,
                    'message' => 'Trip already started',
                ]);
            }

            // ✅ VALIDATION: Check for active trip on same vehicle
            $activeTrip = DB::table('Trip_TripInfomation')
                ->where('VehicleNo', $trip->VehicleNo)
                ->where('Id', '!=', $tripId)
                ->where('OfficeCode', 'KE')
                ->where('IsDeleted', false)
                ->whereNotNull('StartDate')
                ->whereNull('EndDate')
                ->first();

            if ($activeTrip) {
                Log::warning('Cannot start trip: Vehicle has active trip', [
                    'new_trip_no' => $trip->TripNo,
                    'active_trip_no' => $activeTrip->TripNo,
                    'vehicle_no' => $trip->VehicleNo,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Cannot start trip: This vehicle is currently on an active trip',
                    'active_trip_no' => $activeTrip->TripNo,
                    'vehicle_no' => $trip->VehicleNo,
                    'validation_type' => 'vehicle_active_trip',
                ], 400);
            }

            // ✅ SUBTRACT 3 HOURS - SQL Server is in UTC
            if ($customStartDate) {
                $actionDate = Carbon::parse($customStartDate, 'Africa/Nairobi')->subHours(3)->format('YmdHis');

                Log::info('Trip start with custom date', [
                    'trip_no' => $trip->TripNo,
                    'user_input_nairobi' => $customStartDate,
                    'nairobi_time' => Carbon::parse($customStartDate, 'Africa/Nairobi')->format('Y-m-d H:i:s'),
                    'sp_time_utc' => $actionDate,
                    'note' => 'SQL Server is in UTC, subtracting 3 hours',
                ]);
            } else {
                $actionDate = now()->subHours(3)->format('YmdHis');

                Log::info('Trip start with current time', [
                    'trip_no' => $trip->TripNo,
                    'server_now_nairobi' => now()->format('Y-m-d H:i:s'),
                    'sp_time_utc' => $actionDate,
                    'note' => 'SQL Server is in UTC, subtracting 3 hours',
                ]);
            }

            $inputJson = json_encode([
                'OperationType' => 'TripStatusUpdate',
                'SubmittedDatetime' => $actionDate,
                'SubmittedBy' => Auth::user()->email ?? 'system',
                'Action' => 'Add',
                'TransactionType' => 'Trip',
                'TransactionNo' => $trip->TripNo,
                'ActionType' => 'Start',
                'ActionDate' => $actionDate,
            ], JSON_UNESCAPED_SLASHES);

            Log::info('Starting trip with JSON: '.$inputJson);

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(MAX) = NULL;
            EXEC dbo.sp_TripMain
                @InputJson = :inputJson,
                @TripMileage = NULL,
                @TripTrack = NULL,
                @OutputJson = @OutputJson OUTPUT;
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

            Log::info('SP OutputJson: '.$outputJson);

            if (trim($outputJson) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'No response from stored procedure',
                ], 500);
            }

            $response = json_decode($outputJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON response from server',
                ], 500);
            }

            if (($response['Status'] ?? 'Failed') === 'Success') {
                return response()->json([
                    'success' => true,
                    'message' => 'Trip started successfully',
                    'trip_no' => $trip->TripNo,
                ]);
            }

            $errors = [];
            foreach ($response['Remarks'] ?? [] as $item) {
                $errors[] = is_array($item) ? ($item['Remark'] ?? 'Unknown error') : $item;
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to start trip',
                'errors' => $errors,
            ], 422);

        } catch (\Exception $e) {
            Log::error('Start Trip Exception: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while starting trip',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Start job (supports both current time and manual start)
     * WITH COMPLETE VALIDATION:
     * 1. Cannot start in the future
     * 2. Cannot start if vehicle is on another active job
     * 3. Cannot start before trip started
     * 4. Cannot start during another job's execution
     */
    public function startJob(Request $request, $tripId, $jobId)
    {
        try {
            $isManualStart = $request->has('start_date') && $request->has('start_time');

            if ($isManualStart) {
                $request->validate([
                    'start_date' => 'required|date|before_or_equal:now',
                    'start_time' => 'required|date_format:H:i',
                ]);

                $customStartDate = $request->start_date.' '.$request->start_time;

                if (Carbon::parse($customStartDate, 'Africa/Nairobi')->isFuture()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Start date cannot be in the future',
                    ], 400);
                }
            }

            $job = DB::table('Trip_TripJobMapping as tjm')
                ->join('Trip_TripInfomation as t', 'tjm.TripInfoID', '=', 't.Id')
                ->select([
                    'tjm.Id as job_id',
                    'tjm.JobNo as job_no',
                    'tjm.StartDate as job_start_date',
                    't.Id as trip_id',
                    't.TripNo as trip_no',
                    't.StartDate as trip_start_date',
                    't.VehicleIMEI as vehicle_imei',
                    't.VehicleNo as vehicle_no',
                ])
                ->where('tjm.Id', $jobId)
                ->where('t.Id', $tripId)
                ->where('tjm.IsDeleted', false)
                ->first();

            if (! $job) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job not found or does not belong to this trip',
                ], 404);
            }

            if ($job->job_start_date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job has already been started',
                ], 400);
            }

            // VALIDATION 2: Check if vehicle is on another active job
            $activeJob = DB::table('Trip_TripJobMapping as tjm')
                ->join('Trip_TripInfomation as t', 'tjm.TripInfoID', '=', 't.Id')
                ->select([
                    'tjm.JobNo as active_job_no',
                    't.TripNo as active_trip_no',
                    't.Id as active_trip_id',
                    'tjm.StartDate as job_start_date',
                ])
                ->where('t.VehicleNo', $job->vehicle_no)
                ->where('t.Id', '!=', $job->trip_id)
                ->where('t.OfficeCode', 'KE')
                ->where('t.IsDeleted', false)
                ->where('tjm.IsDeleted', false)
                ->whereNotNull('tjm.StartDate')
                ->whereNull('tjm.EndDate')
                ->first();

            if ($activeJob) {
                Log::warning('Cannot start job: Vehicle has active job on another trip', [
                    'current_trip_no' => $job->trip_no,
                    'current_job_no' => $job->job_no ?: 'Internal Job',
                    'active_trip_no' => $activeJob->active_trip_no,
                    'active_job_no' => $activeJob->active_job_no ?: 'Internal Job',
                    'vehicle_no' => $job->vehicle_no,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Cannot start job: This vehicle is currently on another job',
                    'active_trip_no' => $activeJob->active_trip_no,
                    'active_job_no' => $activeJob->active_job_no ?: 'Internal Job',
                    'active_job_started' => Carbon::parse($activeJob->job_start_date)->addHours(3)->format('Y-m-d H:i'),
                    'current_trip_no' => $job->trip_no,
                    'vehicle_no' => $job->vehicle_no,
                    'validation_type' => 'vehicle_active_job',
                ], 400);
            }

            // VALIDATION 3: Cannot start before trip started
            if ($isManualStart && $job->trip_start_date) {
                $manualStartDateTime = Carbon::parse($customStartDate, 'Africa/Nairobi');
                $tripStartDateTime = Carbon::parse($job->trip_start_date)->addHours(3); // DB is UTC, convert to Nairobi

                if ($manualStartDateTime->lessThan($tripStartDateTime)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Job start time cannot be before trip start time',
                        'trip_started_at' => $tripStartDateTime->format('Y-m-d H:i'),
                        'your_start_time' => $manualStartDateTime->format('Y-m-d H:i'),
                        'validation_type' => 'trip_start',
                    ], 400);
                }
            }

            // VALIDATION 4: Cannot start during another job's execution
            if ($isManualStart) {
                $manualStartDateTime = Carbon::parse($customStartDate, 'Africa/Nairobi');

                $overlappingJob = DB::table('Trip_TripJobMapping')
                    ->where('TripInfoID', $tripId)
                    ->where('IsDeleted', false)
                    ->where('Id', '!=', $jobId)
                    ->whereNotNull('StartDate')
                    ->whereNotNull('EndDate')
                    ->get()
                    ->first(function ($existingJob) use ($manualStartDateTime) {
                        // DB times are UTC, convert to Nairobi for comparison
                        $jobStart = Carbon::parse($existingJob->StartDate)->addHours(3);
                        $jobEnd = Carbon::parse($existingJob->EndDate)->addHours(3);

                        return $manualStartDateTime->greaterThanOrEqualTo($jobStart)
                            && $manualStartDateTime->lessThan($jobEnd);
                    });

                if ($overlappingJob) {
                    $jobStartTime = Carbon::parse($overlappingJob->StartDate)->addHours(3);
                    $jobEndTime = Carbon::parse($overlappingJob->EndDate)->addHours(3);

                    return response()->json([
                        'success' => false,
                        'message' => 'Job cannot start during another job\'s execution',
                        'conflicting_job' => $overlappingJob->JobNo ?: 'Internal Job',
                        'conflicting_job_start' => $jobStartTime->format('Y-m-d H:i'),
                        'conflicting_job_end' => $jobEndTime->format('Y-m-d H:i'),
                        'your_start_time' => $manualStartDateTime->format('Y-m-d H:i'),
                        'suggestion' => 'Job must start at or after '.$jobEndTime->format('Y-m-d H:i'),
                        'validation_type' => 'overlap',
                    ], 400);
                }

                $previousJob = DB::table('Trip_TripJobMapping')
                    ->where('TripInfoID', $tripId)
                    ->where('IsDeleted', false)
                    ->whereNotNull('StartDate')
                    ->whereNotNull('EndDate')
                    ->where('Id', '!=', $jobId)
                    ->where('EndDate', '<=', $manualStartDateTime->copy()->subHours(3)->format('Y-m-d H:i:s'))
                    ->orderByDesc('EndDate')
                    ->first();

                if ($previousJob) {
                    $previousJobEndDateTime = Carbon::parse($previousJob->EndDate)->addHours(3);

                    Log::info('Sequential job validation passed', [
                        'current_job' => $job->job_no,
                        'current_start' => $manualStartDateTime->format('Y-m-d H:i:s'),
                        'previous_job' => $previousJob->JobNo,
                        'previous_end' => $previousJobEndDateTime->format('Y-m-d H:i:s'),
                        'time_gap_minutes' => $previousJobEndDateTime->diffInMinutes($manualStartDateTime),
                    ]);
                }
            }

            // AUTO-START TRIP IF NOT STARTED
            if (! $job->trip_start_date) {
                Log::info('AUTO-STARTING TRIP because first job is starting', [
                    'trip_no' => $job->trip_no,
                    'trip_id' => $job->trip_id,
                    'is_manual' => $isManualStart,
                ]);

                $tripStartResult = $this->startTrip($job->trip_id, $isManualStart ? $customStartDate : null);

                if ($tripStartResult->getStatusCode() !== 200) {
                    $responseData = json_decode($tripStartResult->getContent(), true);

                    return response()->json($responseData, $tripStartResult->getStatusCode());
                }
            }

            // ✅ SUBTRACT 3 HOURS - SQL Server is in UTC
            if ($isManualStart) {
                $actionDate = Carbon::parse($customStartDate, 'Africa/Nairobi')->subHours(3)->format('YmdHis');

                Log::info('Manual start timezone conversion', [
                    'user_input_nairobi' => $customStartDate,
                    'nairobi_time' => Carbon::parse($customStartDate, 'Africa/Nairobi')->format('Y-m-d H:i:s'),
                    'sp_time_utc' => $actionDate,
                    'note' => 'SQL Server is in UTC, subtracting 3 hours',
                ]);
            } else {
                $actionDate = now()->subHours(3)->format('YmdHis');

                Log::info('Current start timezone', [
                    'server_now_nairobi' => now()->format('Y-m-d H:i:s'),
                    'sp_time_utc' => $actionDate,
                    'note' => 'SQL Server is in UTC, subtracting 3 hours',
                ]);
            }

            $inputJson = json_encode([
                'OperationType' => 'TripStatusUpdate',
                'SubmittedDatetime' => $actionDate,
                'SubmittedBy' => Auth::user()->email ?? 'system',
                'Action' => 'Add',
                'TransactionType' => 'Job',
                'TransactionNo' => $job->job_no ?: 'INTERNAL',
                'ActionType' => 'Start',
                'ActionDate' => $actionDate,
            ], JSON_UNESCAPED_SLASHES);

            Log::info('Starting job', [
                'job_no' => $job->job_no,
                'is_manual' => $isManualStart,
                'input_json' => $inputJson,
            ]);

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(MAX) = NULL;
            EXEC dbo.sp_TripMain
                @InputJson = :inputJson,
                @TripMileage = NULL,
                @TripTrack = NULL,
                @OutputJson = @OutputJson OUTPUT;
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

            Log::info('SP OutputJson: '.$outputJson);

            $response = json_decode($outputJson, true);

            if (($response['Status'] ?? 'Failed') === 'Success') {
                $message = $isManualStart ? 'Job started with backdated time!' : 'Job started successfully!';

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'trip_auto_started' => ! $job->trip_start_date,
                    'job_no' => $job->job_no ?: 'Internal Job',
                    'trip_no' => $job->trip_no,
                    'is_manual' => $isManualStart,
                    'start_date' => $isManualStart ? $customStartDate : now()->format('Y-m-d H:i:s'),
                ]);
            }

            $errors = collect($response['Remarks'] ?? [])->pluck('Remark')->filter()->all();

            return response()->json([
                'success' => false,
                'message' => 'Failed to start job',
                'errors' => $errors,
            ], 422);

        } catch (\Exception $e) {
            Log::error('Start Job Exception: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }
    /**
     * Complete job
     */

    /**
     * Complete job (supports both current time and backdating)
     * WITH VALIDATION: Cannot complete during another job's execution
     */
   /**
     * Complete job (supports both current time and backdating)
     * WITH VALIDATION: Cannot complete during another job's execution
     */
    public function completeJob(Request $request, $tripId, $jobId)
    {
        try {
            // Check if this is a backdated completion
            $isBackdated = $request->has('end_date') && $request->has('end_time');

            if ($isBackdated) {
                // Validate backdating inputs
                $request->validate([
                    'end_date' => 'required|date|before_or_equal:now',
                    'end_time' => 'required|date_format:H:i',
                ]);

                $customEndDate = $request->end_date.' '.$request->end_time;

                // Validate end date is not in the future
                if (Carbon::parse($customEndDate, 'Africa/Nairobi')->isFuture()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'End date cannot be in the future',
                    ], 400);
                }
            }

            // Accept both start and end dates for historical entries
            $isHistorical = $request->input('is_historical', false);
            $customStartDate = $request->input('custom_start_date', null);

            // Use backdated end date if provided, otherwise use custom_end_date (for historical) or current time
            if ($isBackdated) {
                $customEndDate = $request->end_date.' '.$request->end_time;
            } else {
                $customEndDate = $request->input('custom_end_date', null);
            }

            Log::info('=== COMPLETING JOB ===', [
                'trip_id' => $tripId,
                'job_id' => $jobId,
                'is_historical' => $isHistorical,
                'is_backdated' => $isBackdated,
                'custom_start_date' => $customStartDate,
                'custom_end_date' => $customEndDate,
            ]);

            // Get job details with trip info
            $job = DB::table('Trip_TripJobMapping as tjm')
                ->join('Trip_TripInfomation as t', 'tjm.TripInfoID', '=', 't.Id')
                ->select([
                    'tjm.Id as job_id',
                    'tjm.JobNo as job_no',
                    'tjm.StartDate as job_start_date',
                    'tjm.EndDate as job_end_date',
                    't.Id as trip_id',
                    't.TripNo as trip_no',
                    't.VehicleIMEI as vehicle_imei',
                    't.VehicleNo as vehicle_no',
                    't.StartDate as trip_start_date',
                ])
                ->where('tjm.Id', $jobId)
                ->where('t.Id', $tripId)
                ->where('tjm.IsDeleted', false)
                ->where('t.OfficeCode', 'KE')
                ->first();

            if (! $job) {
                Log::error('Job not found', ['job_id' => $jobId, 'trip_id' => $tripId]);

                return response()->json([
                    'success' => false,
                    'message' => 'Job not found or does not belong to this trip',
                ], 404);
            }

            // Get real IMEI from vehicle number
            $vehicleImeiMap = [
                'KDN 124Z' => '865784055081171',
                'KDN 209Y' => '865784055077419',
                'KDN 331Y' => '865784055074523',
                'KDN 345Y' => '865784055073301',
                'KDN 346Y' => '865784055081189',
                'KDN 347Y' => '865784055076759',
                'KDN 348Y' => '865784055073996',
                'KDN 349Y' => '865784055074077',
                'KDN 351Y' => '865784055074028',
                'KDN 352Y' => '865784055082542',
            ];

            $vehiclePlate = explode(' - ', $job->vehicle_imei)[0];
            $realImei = $vehicleImeiMap[$vehiclePlate] ?? null;

            if (empty($realImei)) {
                return response()->json([
                    'success' => false,
                    'message' => "No GPS tracker mapped for vehicle: {$vehiclePlate}",
                ], 400);
            }

            Log::info('Vehicle IMEI Resolution', [
                'db_vehicle_imei' => $job->vehicle_imei,
                'extracted_plate' => $vehiclePlate,
                'real_imei' => $realImei,
            ]);

            // Skip validation for historical entries
            if (! $isHistorical && ! $isBackdated) {
                if (! $job->job_start_date) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Job has not been started yet',
                    ], 400);
                }

                if ($job->job_end_date) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Job has already been completed',
                    ], 400);
                }
            }

            // ============================================================================
            // PREPARE DATES - FIXED TIMEZONE HANDLING
            // ============================================================================

            if ($isHistorical && $customStartDate && $customEndDate) {
                // Historical: User provides both start and end in Nairobi time
                $startTime = Carbon::parse($customStartDate, 'Africa/Nairobi')
                    ->setTimezone('UTC')
                    ->format('Y-m-d H:i:s');

                $endTime = Carbon::parse($customEndDate, 'Africa/Nairobi')
                    ->setTimezone('UTC')
                    ->format('Y-m-d H:i:s');

                $startActionDate = Carbon::parse($customStartDate, 'Africa/Nairobi')->format('YmdHis');
                $endActionDate = Carbon::parse($customEndDate, 'Africa/Nairobi')->format('YmdHis');

                Log::info('Historical job date configuration', [
                    'custom_start_nairobi' => $customStartDate,
                    'custom_end_nairobi' => $customEndDate,
                    'start_time_utc' => $startTime,
                    'end_time_utc' => $endTime,
                ]);

            } elseif ($isBackdated && $customEndDate) {
                // ✅ FIXED: DB already stores UTC time correctly - no adjustment needed
                $startTime = Carbon::parse($job->job_start_date, 'UTC')
                    ->format('Y-m-d H:i:s'); // Keep as UTC for Jimi API

                $endTime = Carbon::parse($customEndDate, 'Africa/Nairobi')
                    ->setTimezone('UTC')
                    ->format('Y-m-d H:i:s');

                $startActionDate = null;
                $endActionDate = Carbon::parse($customEndDate, 'Africa/Nairobi')->format('YmdHis');

                Log::info('Backdated job date configuration', [
                    'job_start_date_from_db_utc' => $job->job_start_date,
                    'start_time_for_jimi_utc' => $startTime,
                    'custom_end_nairobi' => $customEndDate,
                    'end_time_utc' => $endTime,
                    'note' => 'DB already has UTC, sending UTC to Jimi API',
                ]);

                // Validation: End date must be after start date
                // Convert both to Nairobi timezone for user-friendly comparison
                $proposedEndTime = Carbon::parse($customEndDate, 'Africa/Nairobi');
                $jobStartTime = Carbon::parse($job->job_start_date, 'UTC')
                    ->setTimezone('Africa/Nairobi'); // Convert UTC to Nairobi for comparison

                if ($proposedEndTime->lessThanOrEqualTo($jobStartTime)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'End date must be after job start date ('.
                            $jobStartTime->format('Y-m-d H:i').')',
                        'job_started_at' => $jobStartTime->format('Y-m-d H:i'),
                        'your_end_time' => $proposedEndTime->format('Y-m-d H:i'),
                    ], 400);
                }

            } else {
                // Current time completion - DB already has correct UTC time
                $startTime = Carbon::parse($job->job_start_date, 'UTC')
                    ->format('Y-m-d H:i:s'); // Keep as UTC for Jimi API

                $endTime = Carbon::parse(now(), 'Africa/Nairobi')
                    ->setTimezone('UTC')
                    ->format('Y-m-d H:i:s');

                $startActionDate = null;
                $endActionDate = now('Africa/Nairobi')->format('YmdHis');

                Log::info('Current time job completion date configuration', [
                    'job_start_date_from_db_utc' => $job->job_start_date,
                    'start_time_for_jimi_utc' => $startTime,
                    'end_time_utc' => $endTime,
                    'server_now_nairobi' => now('Africa/Nairobi')->format('Y-m-d H:i:s'),
                    'note' => 'DB has UTC, sending UTC to Jimi API',
                ]);
            }

            Log::info('Date Configuration Summary', [
                'is_historical' => $isHistorical,
                'is_backdated' => $isBackdated,
                'jimi_start_utc' => $startTime,
                'jimi_end_utc' => $endTime,
                'sp_start_nairobi' => $startActionDate,
                'sp_end_nairobi' => $endActionDate,
            ]);

            // VALIDATION: Overlap check for backdated/historical
            if ($isBackdated || $isHistorical) {
                $proposedEndDateTime = Carbon::parse($customEndDate, 'Africa/Nairobi');

                $overlappingJob = DB::table('Trip_TripJobMapping')
                    ->where('TripInfoID', $tripId)
                    ->where('IsDeleted', false)
                    ->where('Id', '!=', $jobId)
                    ->whereNotNull('StartDate')
                    ->whereNotNull('EndDate')
                    ->get()
                    ->first(function ($existingJob) use ($proposedEndDateTime) {
                        $jobStart = Carbon::parse($existingJob->StartDate)->addHours(3);
                        $jobEnd = Carbon::parse($existingJob->EndDate)->addHours(3);

                        return $proposedEndDateTime->greaterThan($jobStart)
                            && $proposedEndDateTime->lessThan($jobEnd);
                    });

                if ($overlappingJob) {
                    $jobStartTime = Carbon::parse($overlappingJob->StartDate)->addHours(3);
                    $jobEndTime = Carbon::parse($overlappingJob->EndDate)->addHours(3);

                    return response()->json([
                        'success' => false,
                        'message' => 'Job cannot be completed during another job\'s execution',
                        'conflicting_job' => $overlappingJob->JobNo ?: 'Internal Job',
                        'conflicting_job_start' => $jobStartTime->format('Y-m-d H:i'),
                        'conflicting_job_end' => $jobEndTime->format('Y-m-d H:i'),
                        'your_end_time' => $proposedEndDateTime->format('Y-m-d H:i'),
                        'suggestion' => 'Job must end at or before '.$jobStartTime->format('Y-m-d H:i').
                                       ' OR at or after '.$jobEndTime->format('Y-m-d H:i'),
                        'validation_type' => 'overlap_end',
                    ], 400);
                }

                Log::info('Job completion overlap validation passed', [
                    'job_no' => $job->job_no,
                    'proposed_end' => $proposedEndDateTime->format('Y-m-d H:i:s'),
                ]);
            }

            // Initialize variables
            $totalDistance = 0;
            $totalDuration = 0;
            $avgSpeed = 0;
            $maxSpeed = 0;
            $gpsPointsCount = 0;

            // Fetch GPS data from Jimi API
            $mileageResult = $this->jimi->getMileageData($realImei, $startTime, $endTime);
            $trackResult = $this->jimi->getTrackData($realImei, $startTime, $endTime);

            Log::info('Jimi Mileage Response', $mileageResult);
            Log::info('Jimi Track Data', ['points_count' => count($trackResult)]);

            if ($mileageResult['success'] ?? false) {
                $totalDistance = $mileageResult['mileage'] ?? 0;
                $totalDuration = $mileageResult['duration'] ?? 0;
            }

            $gpsPointsCount = count($trackResult);

            if ($gpsPointsCount > 0) {
                $speeds = array_column($trackResult, 'speed');
                $maxSpeed = max($speeds);

                $movingSpeeds = array_filter($speeds, function ($speed) {
                    return $speed > 0;
                });

                if (! empty($movingSpeeds)) {
                    $avgSpeed = round(array_sum($movingSpeeds) / count($movingSpeeds), 2);
                }
            }

            Log::info('Calculated Statistics', [
                'total_distance_m' => $totalDistance,
                'total_distance_km' => round($totalDistance / 1000, 2),
                'total_duration_sec' => $totalDuration,
                'avg_speed' => $avgSpeed,
                'max_speed' => $maxSpeed,
                'gps_points' => $gpsPointsCount,
            ]);

            // Format mileage data for SP
            $tripsFormatted = [];
            foreach ($mileageResult['trips'] ?? [] as $trip) {
                $tripsFormatted[] = [
                    'imei' => $realImei,
                    'startTime' => $trip['start_time'],
                    'endTime' => $trip['end_time'],
                    'startLat' => $trip['start_lat'],
                    'startLng' => $trip['start_lng'],
                    'endLat' => $trip['end_lat'],
                    'endLng' => $trip['end_lng'],
                    'distance' => $trip['distance'],
                    'avgSpeed' => $trip['avg_speed'],
                    'runTimeSecond' => $trip['duration'],
                    'startMileage' => 0,
                    'endMileage' => 0,
                ];
            }

            $tripMileage = json_encode([
                'result' => $tripsFormatted,
                'data' => [
                    [
                        'imei' => $realImei,
                        'totalMileage' => (int) round($totalDistance),
                    ],
                ],
            ], JSON_UNESCAPED_SLASHES);

            // Format track data for SP
            $trackPointsFormatted = [];
            foreach ($trackResult as $point) {
                $trackPointsFormatted[] = [
                    'lat' => $point['lat'],
                    'lng' => $point['lng'],
                    'gpsTime' => $point['gps_time'],
                    'direction' => $point['direction'],
                    'gpsSpeed' => $point['speed'],
                    'posType' => 0,
                    'satellite' => 0,
                    'ignition' => 'unknown',
                    'accStatus' => 'unknown',
                    'confidence' => 'high',
                ];
            }

            $tripTrack = json_encode([
                'result' => $trackPointsFormatted,
            ], JSON_UNESCAPED_SLASHES);

            Log::info('Formatted data for SP', [
                'trips_count' => count($tripsFormatted),
                'track_points_count' => count($trackPointsFormatted),
            ]);

            // STEP 1: Historical START (if needed)
            if ($isHistorical && $startActionDate) {

                // APPLY -3 HOURS TO ACTION DATE
                $adjustedStartActionDate = Carbon::parse($startActionDate)->subHours(3)->format('YmdHis');

                $startInputJson = json_encode([
                    'OperationType' => 'TripStatusUpdate',
                    'SubmittedDatetime' => $adjustedStartActionDate,
                    'SubmittedBy' => Auth::user()->email ?? 'system',
                    'Action' => 'Add',
                    'TransactionType' => 'Job',
                    'TransactionNo' => $job->job_no,
                    'ActionType' => 'Start',
                    'ActionDate' => $adjustedStartActionDate,
                ], JSON_UNESCAPED_SLASHES);

                Log::info('=== STEP 1: STARTING JOB WITH HISTORICAL DATE ===', [
                    'InputJson' => $startInputJson,
                    'JobNo' => $job->job_no,
                    'ActionDate' => $adjustedStartActionDate,
                    'SubmittedDatetime' => $adjustedStartActionDate,
                ]);

                $pdo = DB::connection()->getPdo();

                $stmt = $pdo->prepare("
                SET NOCOUNT ON;
                DECLARE @OutputJson VARCHAR(MAX) = NULL;
                EXEC dbo.sp_TripMain
                    @InputJson = :inputJson,
                    @TripMileage = NULL,
                    @TripTrack = NULL,
                    @OutputJson = @OutputJson OUTPUT;
                SELECT ISNULL(@OutputJson, '') AS OutputJson;
            ");

                $stmt->bindParam(':inputJson', $startInputJson, PDO::PARAM_STR);
                $stmt->execute();

                $startOutput = '';
                do {
                    if ($stmt->columnCount() > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            if (isset($row['OutputJson'])) {
                                $startOutput = $row['OutputJson'];
                                break 2;
                            }
                        }
                    }
                } while ($stmt->nextRowset());

                Log::info('Start Job Response', ['output' => $startOutput]);

                $startResponse = json_decode($startOutput, true);
                if (($startResponse['Status'] ?? 'Failed') === 'Failed') {
                    Log::error('Failed to start job', ['response' => $startResponse]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to start job: '.($startResponse['Remarks'][0]['Remark'] ?? 'Unknown error'),
                        'errors' => $startResponse['Remarks'] ?? [],
                    ], 422);
                }
            }

            // STEP 2: END job with GPS data
            // APPLY -3 HOURS TO END ACTION DATE
            $adjustedEndActionDate = Carbon::parse($endActionDate)->subHours(3)->format('YmdHis');

            $inputJson = json_encode([
                'OperationType' => 'TripStatusUpdate',
                'SubmittedDatetime' => $adjustedEndActionDate,
                'SubmittedBy' => Auth::user()->email ?? 'system',
                'Action' => 'Add',
                'TransactionType' => 'Job',
                'TransactionNo' => $job->job_no,
                'ActionType' => 'End',
                'ActionDate' => $adjustedEndActionDate,
            ], JSON_UNESCAPED_SLASHES);

            Log::info('=== STEP 2: ENDING JOB WITH GPS DATA ===', [
                'InputJson' => $inputJson,
                'JobNo' => $job->job_no,
                'ActionDate' => $adjustedEndActionDate,
                'SubmittedDatetime' => $adjustedEndActionDate,
                'TripMileage_sample' => substr($tripMileage, 0, 200).'...',
                'TripTrack_sample' => substr($tripTrack, 0, 200).'...',
            ]);

            $pdo = DB::connection()->getPdo();

            $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(MAX) = NULL;
            EXEC dbo.sp_TripMain
                @InputJson = :inputJson,
                @TripMileage = :tripMileage,
                @TripTrack = :tripTrack,
                @OutputJson = @OutputJson OUTPUT;
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

            Log::info('End Job Response', ['output' => $outputJson]);

            if (trim($outputJson) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'No response from stored procedure',
                ], 500);
            }

            $response = json_decode($outputJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON from SP', ['raw' => $outputJson]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid response from server',
                ], 500);
            }

            if (($response['Status'] ?? 'Failed') === 'Success') {
                $message = 'Job completed successfully';
                if ($isHistorical) {
                    $message = 'Historical job completed with GPS data';
                } elseif ($isBackdated) {
                    $message = 'Job completed with backdated time';
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'job_no' => $job->job_no,
                    'trip_no' => $job->trip_no,
                    'total_distance' => round($totalDistance / 1000, 2),
                    'avg_speed' => $avgSpeed,
                    'max_speed' => $maxSpeed,
                    'gps_points' => $gpsPointsCount,
                    'duration_minutes' => round($totalDuration / 60, 1),
                    'duration_hours' => round($totalDuration / 3600, 2),
                    'trip_segments' => count($tripsFormatted),
                    'is_backdated' => $isBackdated,
                ]);
            }

            $errors = [];
            foreach ($response['Remarks'] ?? [] as $item) {
                $errors[] = is_array($item) ? ($item['Remark'] ?? 'Unknown error') : $item;
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete job',
                'errors' => $errors,
            ], 422);

        } catch (\Exception $e) {
            Log::error('completeJob Exception', [
                'job_id' => $jobId,
                'trip_id' => $tripId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error while completing job',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * End trip - automatically uses last job's end date
     * FIXED: Adds +3 hours to job end date from DB before processing
     */
   /**
     * End trip - automatically uses last job's end date
     * FIXED: Uses UTC directly from DB without unnecessary timezone conversions
     */
    public function endTrip(Request $request, $tripId)
    {
        try {
            Log::info('=== ENDING TRIP ===', [
                'trip_id' => $tripId,
            ]);

            $trip = DB::table('Trip_TripInfomation')
                ->where('Id', $tripId)
                ->where('OfficeCode', 'KE')
                ->where('IsDeleted', false)
                ->first();

            if (! $trip) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trip not found',
                ], 404);
            }

            if (! $trip->StartDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trip has not been started yet',
                ], 400);
            }

            if ($trip->EndDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trip has already been ended',
                ], 400);
            }

            // Check all jobs are completed
            $incompleteJobs = DB::table('Trip_TripJobMapping')
                ->where('TripInfoID', $tripId)
                ->where('IsDeleted', false)
                ->whereNull('EndDate')
                ->get();

            if ($incompleteJobs->count() > 0) {
                $jobList = $incompleteJobs->map(function ($job) {
                    return $job->JobNo ?: 'Internal Job';
                })->implode(', ');

                return response()->json([
                    'success' => false,
                    'message' => 'All jobs must be completed before ending the trip',
                    'incomplete_jobs' => $jobList,
                ], 400);
            }

            // ============================================================================
            // GET LAST JOB'S END DATE - FIXED TIMEZONE HANDLING
            // ============================================================================

            $lastCompletedJob = DB::table('Trip_TripJobMapping')
                ->where('TripInfoID', $tripId)
                ->where('IsDeleted', false)
                ->whereNotNull('EndDate')
                ->orderByDesc('EndDate')
                ->first();

            if (! $lastCompletedJob || ! $lastCompletedJob->EndDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot end trip: No completed jobs found',
                ], 400);
            }

            // ✅ FIXED: Parse dates explicitly as UTC (that's what DB stores)
            $tripStartDateTime = Carbon::parse($trip->StartDate, 'UTC');
            $tripEndDateTime = Carbon::parse($lastCompletedJob->EndDate, 'UTC');

            Log::info('Using last job end date as trip end', [
                'trip_no' => $trip->TripNo,
                'last_job' => $lastCompletedJob->JobNo ?: 'Internal Job',
                'job_end_date_from_db_utc' => $lastCompletedJob->EndDate,
                'trip_start_date_utc' => $trip->StartDate,
                'note' => 'Both dates are UTC from DB - no conversion needed',
            ]);

            if (empty($trip->VehicleIMEI)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No GPS tracker assigned to vehicle',
                ], 400);
            }

            // Get vehicle IMEI mapping
            $vehicleImeiMap = [
                'KDN 124Z' => '865784055081171',
                'KDN 209Y' => '865784055077419',
                'KDN 331Y' => '865784055074523',
                'KDN 345Y' => '865784055073301',
                'KDN 346Y' => '865784055081189',
                'KDN 347Y' => '865784055076759',
                'KDN 348Y' => '865784055073996',
                'KDN 349Y' => '865784055074077',
                'KDN 351Y' => '865784055074028',
                'KDN 352Y' => '865784055082542',
            ];

            $vehiclePlate = explode(' - ', $trip->VehicleIMEI)[0];
            $realImei = $vehicleImeiMap[$vehiclePlate] ?? null;

            if (empty($realImei)) {
                return response()->json([
                    'success' => false,
                    'message' => "No GPS tracker mapped for vehicle: {$vehiclePlate}",
                ], 400);
            }

            // ✅ FIXED: Already UTC, no conversion needed for Jimi API
            $startTimeUTC = $tripStartDateTime->format('Y-m-d H:i:s');
            $endTimeUTC = $tripEndDateTime->format('Y-m-d H:i:s');

            Log::info('Fetching GPS data for trip', [
                'trip_no' => $trip->TripNo,
                'imei' => $realImei,
                'start_time_utc' => $startTimeUTC,
                'end_time_utc' => $endTimeUTC,
                'duration_hours' => $tripStartDateTime->diffInHours($tripEndDateTime),
                'note' => 'Sending UTC times directly to Jimi API',
            ]);

            // Fetch GPS data from Jimi API
            $mileageResult = $this->jimi->getMileageData($realImei, $startTimeUTC, $endTimeUTC);
            $trackResult = $this->jimi->getTrackData($realImei, $startTimeUTC, $endTimeUTC);

            Log::info('Jimi Mileage Response (Full Trip)', [
                'success' => $mileageResult['success'] ?? false,
                'mileage' => $mileageResult['mileage'] ?? 0,
                'duration' => $mileageResult['duration'] ?? 0,
                'trips_count' => count($mileageResult['trips'] ?? []),
            ]);

            Log::info('Jimi Track Data (Full Trip)', [
                'points_count' => count($trackResult),
            ]);

            $totalDistance = 0;
            $totalDuration = 0;

            if ($mileageResult['success'] ?? false) {
                $totalDistance = $mileageResult['mileage'] ?? 0;
                $totalDuration = $mileageResult['duration'] ?? 0;
            }

            $avgSpeed = 0;
            $maxSpeed = 0;
            $gpsPointsCount = count($trackResult);

            if ($gpsPointsCount > 0) {
                $speeds = array_column($trackResult, 'speed');
                $maxSpeed = max($speeds);
                $movingSpeeds = array_filter($speeds, fn ($speed) => $speed > 0);

                if (! empty($movingSpeeds)) {
                    $avgSpeed = round(array_sum($movingSpeeds) / count($movingSpeeds), 2);
                }
            }

            // Format mileage data for SP
            $tripsFormatted = [];
            foreach ($mileageResult['trips'] ?? [] as $tripData) {
                $tripsFormatted[] = [
                    'imei' => $realImei,
                    'startTime' => $tripData['start_time'],
                    'endTime' => $tripData['end_time'],
                    'startLat' => $tripData['start_lat'],
                    'startLng' => $tripData['start_lng'],
                    'endLat' => $tripData['end_lat'],
                    'endLng' => $tripData['end_lng'],
                    'distance' => $tripData['distance'],
                    'avgSpeed' => $tripData['avg_speed'],
                    'runTimeSecond' => $tripData['duration'],
                    'startMileage' => 0,
                    'endMileage' => 0,
                ];
            }

            $tripMileage = json_encode([
                'result' => $tripsFormatted,
                'data' => [[
                    'imei' => $realImei,
                    'totalMileage' => (int) round($totalDistance),
                ]],
            ], JSON_UNESCAPED_SLASHES);

            // Format track data for SP
            $trackPointsFormatted = array_map(fn ($point) => [
                'lat' => $point['lat'],
                'lng' => $point['lng'],
                'gpsTime' => $point['gps_time'],
                'direction' => $point['direction'],
                'gpsSpeed' => $point['speed'],
                'posType' => 0,
                'satellite' => 0,
                'ignition' => 'unknown',
                'accStatus' => 'unknown',
                'confidence' => 'high',
            ], $trackResult);

            $tripTrack = json_encode(['result' => $trackPointsFormatted], JSON_UNESCAPED_SLASHES);

            // ✅ FIXED: Convert UTC to YmdHis format for SP (DB expects UTC in this format)
            $actionDate = $tripEndDateTime->format('YmdHis');

            $inputJson = json_encode([
                'OperationType' => 'TripStatusUpdate',
                'SubmittedDatetime' => $actionDate,
                'SubmittedBy' => Auth::user()->email ?? 'system',
                'Action' => 'Add',
                'TransactionType' => 'Trip',
                'TransactionNo' => $trip->TripNo,
                'ActionType' => 'End',
                'ActionDate' => $actionDate,
            ], JSON_UNESCAPED_SLASHES);

            Log::info('Calling sp_TripMain to end trip', [
                'trip_no' => $trip->TripNo,
                'job_end_date_from_db_utc' => $lastCompletedJob->EndDate,
                'action_date_sent_to_sp' => $actionDate,
                'action_date_readable_utc' => $tripEndDateTime->format('Y-m-d H:i:s'),
                'action_date_readable_nairobi' => $tripEndDateTime->copy()->setTimezone('Africa/Nairobi')->format('Y-m-d H:i:s'),
                'explanation' => 'DB is UTC, sending UTC to SP in YmdHis format',
            ]);

            $pdo = DB::connection()->getPdo();

            $stmt = $pdo->prepare("
            SET NOCOUNT ON;
            DECLARE @OutputJson VARCHAR(MAX) = NULL;
            EXEC dbo.sp_TripMain
                @InputJson = :inputJson,
                @TripMileage = :tripMileage,
                @TripTrack = :tripTrack,
                @OutputJson = @OutputJson OUTPUT;
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

            Log::info('SP OutputJson (End Trip)', ['output' => $outputJson]);

            if (trim($outputJson) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'No response from stored procedure',
                ], 500);
            }

            $response = json_decode($outputJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON from SP', ['raw' => $outputJson]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid response from server',
                ], 500);
            }

            if (($response['Status'] ?? 'Failed') === 'Success') {
                $completedJobsCount = DB::table('Trip_TripJobMapping')
                    ->where('TripInfoID', $tripId)
                    ->where('IsDeleted', false)
                    ->whereNotNull('EndDate')
                    ->count();

                // ✅ For response, display in Nairobi time for user
                return response()->json([
                    'success' => true,
                    'message' => 'Trip ended successfully',
                    'trip_no' => $trip->TripNo,
                    'end_date' => $tripEndDateTime->copy()->setTimezone('Africa/Nairobi')->format('Y-m-d H:i:s'),
                    'last_job_no' => $lastCompletedJob->JobNo ?: 'Internal Job',
                    'total_distance' => round($totalDistance / 1000, 2),
                    'completed_jobs' => $completedJobsCount,
                    'avg_speed' => $avgSpeed,
                    'max_speed' => $maxSpeed,
                    'gps_points' => $gpsPointsCount,
                    'duration_hours' => round($totalDuration / 3600, 2),
                    'duration_minutes' => round($totalDuration / 60, 1),
                ]);
            }

            $errors = [];
            foreach ($response['Remarks'] ?? [] as $item) {
                $errors[] = is_array($item) ? ($item['Remark'] ?? 'Unknown error') : $item;
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to end trip',
                'errors' => $errors,
            ], 422);

        } catch (\Exception $e) {
            Log::error('endTrip Exception', [
                'trip_id' => $tripId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error while ending trip',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

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
            'KDN 351Y' => '865784055074028', // correct
            'KDN 352Y' => '865784055082542',
        ];

        return $imeiMap[$vehicleNo] ?? null;
    }
}
