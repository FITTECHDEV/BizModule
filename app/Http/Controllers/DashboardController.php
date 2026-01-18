<?php

namespace App\Http\Controllers;

use App\Models\Logintracker\AzureUser;
use App\Models\Logintracker\SigninLog;
use App\Services\LoginFilterService;
use App\Services\DashboardStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $filterService;
    protected $statsService;

    public function __construct(LoginFilterService $filterService, DashboardStatsService $statsService)
    {
        $this->filterService = $filterService;
        $this->statsService = $statsService;
    }


    private $excludedSystems = [
        //         'Import-Unknown',
        //         'Unknown',
        //         'Microsoft Teams',
        //         'Office 365',
        //         'Windows Authentication',
        //         'IT Infrastructure',
        //         'FITerp UAT',
        //         'Web Browser',
        //         'Mobile/Desktop Client'
        //         // 'all'
    ];


// private function getSystemMapping()
// {
//     return [
//         // Microsoft Applications
//         'windows sign in' => 'SCM',
//         'microsoft azure cli' => 'OPS',
//         'microsoft exchange rest api based powershell' => 'Odoo',
//         'azure active directory powershell' => 'Fit Express',
//         'microsoft azure powershell' => 'OPS',
//         'azure portal' => 'OPS',
//         'microsoft office 365 portal' => 'Office 365',
//         'microsoft teams' => 'Microsoft Teams',
//         'microsoft outlook' => 'Microsoft Outlook',
//         'microsoft sharepoint' => 'Microsoft SharePoint',
//         'microsoft onedrive' => 'Microsoft OneDrive',
//         // FIT Systems
//         'fit erp' => 'FIT ERP',
//         'fit express' => 'Fit Express',
//         ' fit express' => 'Fit Express',
//         'fit express uat' => 'Fit Express UAT',
//         'fiterpuat' => 'FIT ERP UAT',
//         'fit portal' => 'FIT PORTAL',
//         'fit dashboard' => 'FIT DASHBOARD',
//         // Operations Systems
//         'ops' => 'OPS',
//         'ops uat' => 'OPS UAT',
//         'operations portal' => 'OPS',
//         // Other Systems
//         'odoo' => 'Odoo',
//         'scm' => 'SCM',
//         'd365 live' => 'SCM',
//         'd365' => 'SCM',
//         'd368 live' => 'D368 LIVE',
//         'd368' => 'D368 LIVE',
//         // Common variations
//         'microsoft d365' => 'SCM',
//         'dynamics 365' => 'SCM',
//         'microsoft dynamics' => 'SCM',
//     ];
// }

// Add this method to get raw system names for a mapped system
// private function getRawSystemNames($mappedSystem)
// {
//     $mapping = $this->getSystemMapping();
//     $rawSystems = [];

//     foreach ($mapping as $rawName => $mappedName) {
//         if ($mappedName === $mappedSystem) {
//             $rawSystems[] = $rawName;
//         }
//     }

//     return $rawSystems;
// }


    /**
     * IMPROVED: Get available systems from database, excluding unwanted ones
     */
    // private function getAvailableSystems()
    // {
    //     return Cache::remember('available_systems_clean_v2', 3600, function () {
    //         // Get all unique systems from the database
    //         $rawSystems = DB::table('Azure_LoginDetails')
    //             ->selectRaw('TRIM(system) as clean_system, COUNT(*) as count')
    //             ->whereNotNull('system')
    //             ->where('system', '!=', '')
    //             ->groupBy(DB::raw('TRIM(system)'))
    //             ->orderByRaw('COUNT(*) DESC')
    //             ->get();

    //         // Apply systemmap normalization and exclude unwanted systems
    //         $systems = $rawSystems
    //             ->map(function($item) {
    //                 $normalized = $this->normalizeSystemName($item->clean_system);
    //                 return $normalized;
    //             })
    //             ->unique()
    //             ->filter(function($system) {
    //                 return !in_array($system, $this->excludedSystems);
    //             })
    //             ->values()
    //             ->toArray();

    //         Log::info('Clean available systems loaded with enhanced filtering', [
    //             'count' => count($systems),
    //             'systems' => $systems,
    //             'excluded' => $this->excludedSystems
    //         ]);

    //         return $systems;
    //     });
    // }



    private function getAvailableSystems()
    {
        return Cache::remember('available_systems_clean_v3', 3600, function () {
            try {
                // Use the new System::getAllOrPopulate() method
                $systems = \App\Models\Logintracker\System::getAllOrPopulate();

                // Filter out excluded systems and get names
                $systemNames = $systems
                    ->pluck('name')
                    ->filter(function ($systemName) {
                        return !in_array($systemName, $this->excludedSystems);
                    })
                    ->values()
                    ->toArray();

                Log::info('Systems loaded from System model', [
                    'count' => count($systemNames),
                    'systems' => $systemNames,
                    'excluded' => $this->excludedSystems
                ]);

                return $systemNames;
            } catch (\Exception $e) {
                Log::error('Failed to load systems from System model, falling back to database query', [
                    'error' => $e->getMessage()
                ]);

                // Fallback to direct database query if System model fails
                $rawSystems = DB::table('Azure_LoginDetails')
                    ->selectRaw('TRIM(system) as clean_system, COUNT(*) as count')
                    ->whereNotNull('system')
                    ->where('system', '!=', '')
                    ->groupBy(DB::raw('TRIM(system)'))
                    ->orderByRaw('COUNT(*) DESC')
                    ->get();

                $systems = $rawSystems
                    ->map(function ($item) {
                        $normalized = $this->normalizeSystemName($item->clean_system);
                        return $normalized;
                    })
                    ->unique()
                    ->filter(function ($system) {
                        return !in_array($system, $this->excludedSystems);
                    })
                    ->values()
                    ->toArray();

                Log::info('Systems loaded from fallback query', [
                    'count' => count($systems),
                    'systems' => $systems
                ]);

                return $systems;
            }
        });
    }




// private function getAvailableSystems()
// {
//     try {
//         return Cache::remember('available_systems_clean_v2', 3600, function () {
//             $rawSystems = DB::table('Azure_LoginDetails')
//                 ->selectRaw('TRIM(system) as clean_system, COUNT(*) as count')
//                 ->whereNotNull('system')
//                 ->where('system', '!=', '')
//                 ->groupBy(DB::raw('TRIM(system)'))
//                 ->orderByRaw('COUNT(*) DESC')
//                 ->get();

//             $systems = $rawSystems
//                 ->map(function($item) {
//                     return $this->normalizeSystemName($item->clean_system);
//                 })
//                 ->unique()
//                 ->filter(function($system) {
//                     return !in_array($system, $this->excludedSystems);
//                 })
//                 ->values()
//                 ->toArray();

//             Log::info('Clean available systems loaded with enhanced filtering', [
//                 'count' => count($systems),
//                 'systems' => $systems,
//                 'excluded' => $this->excludedSystems
//             ]);

//             return $systems;
//         });
//     } catch (QueryException $e) {
//         Log::error('Failed to fetch available systems', [
//             'error' => $e->getMessage()
//         ]);
//         return [];
//     }
// }



    /**
     * Enhanced system name normalization using systemmap.php
     */
    private function normalizeSystemName($systemName)
    {
        if (empty($systemName)) {
            return 'Unknown';
        }

        $normalized = trim($systemName);
        $systemMap = config('systemmap', []);

        if (empty($systemMap)) {
            Log::warning('System map configuration not found');
            return $normalized;
        }

        $lowerNormalized = strtolower($normalized);

        // Single case-insensitive lookup
        foreach ($systemMap as $rawName => $cleanName) {
            if (strtolower($rawName) === $lowerNormalized) {
                return $cleanName;
            }
        }

        return $normalized;
    }

    /**
     * Helper function to get raw system names from config
     */
    private function getRawSystemNames($cleanSystemName)
    {
        $systemMap = config('systemmap', []);

        if (empty($systemMap)) {
            Log::warning('System map configuration not found for getRawSystemNames');
            return [$cleanSystemName]; // Return the clean name as fallback
        }

        $rawNames = collect($systemMap)
            ->filter(fn($mappedName) => $mappedName === $cleanSystemName)
            ->keys()
            ->toArray();

        // If no mappings found, return the clean system name itself
        if (empty($rawNames)) {
            return [$cleanSystemName];
        }

        return $rawNames;
    }



    public function index(Request $request)
    {
        set_time_limit(120); // 2 minutes
        try {
            // Get pagination and search parameters
            $perPage = $request->input('per_page', 17);
            $currentPage = $request->input('page', 1);
            $search = $request->input('search', '');

            Log::info('Dashboard index called', [
                'per_page' => $perPage,
                'page' => $currentPage,
                'search' => $search,
                'is_ajax' => $request->ajax()
            ]);

            // Get summary data from stored procedure
            $summary = $this->getUserSummary();

            // Get login list from stored procedure
            $loginList = $this->getLoginViewList();

            // Apply search filter if provided
            if (!empty($search)) {
                $searchLower = strtolower(trim($search));

                $loginList = $loginList->filter(function ($user) use ($searchLower) {
                    // Check if searching for users who never logged in
                    if ($searchLower === 'never') {
                        return empty($user['last_login']) || is_null($user['last_login']);
                    }

                    // Regular search in name and login_id
                    return str_contains(strtolower($user['name']), $searchLower) ||
                        str_contains(strtolower($user['login_id']), $searchLower);
                })->values();

                Log::info('Search applied', [
                    'search_term' => $search,
                    'filtered_count' => $loginList->count()
                ]);
            }

            // Paginate the results
            $total = $loginList->count();
            $pagedData = $loginList->slice(($currentPage - 1) * $perPage, $perPage)->values();

            $users = new \Illuminate\Pagination\LengthAwarePaginator(
                $pagedData,
                $total,
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            if ($request->ajax()) {
                return response()->json([
                    'total_users' => $summary['total_users'],
                    'active_this_month' => $summary['active_this_month'],
                    'active_last_month' => $summary['active_last_month'],
                    'inactive_users' => $summary['inactive_users'],
                    'users' => $pagedData->toArray(),
                    'total' => $total,
                    'pagination' => $users->links()->toHtml(),
                ], 200);
            }

            return view('logintracker.dashboard', [
                'total_users' => $summary['total_users'],
                'active_this_month' => $summary['active_this_month'],
                'active_last_month' => $summary['active_last_month'],
                'inactive_users' => $summary['inactive_users'],
                'users' => $users,
            ]);
        } catch (QueryException $e) {
            Log::error('Database query failed in DashboardController', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);

            if ($request->ajax()) {
                return response()->json(['error' => 'Database error occurred'], 500);
            }

            return response()->view('errors.500', [], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error in DashboardController', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);

            if ($request->ajax()) {
                return response()->json(['error' => 'An unexpected error occurred'], 500);
            }

            return response()->view('errors.500', [], 500);
        }
    }


    /**
     * Apply enhanced system filter - FIXED boolean logic
     */
    private function applySystemFilter($query, $systemName)
    {
        if (empty($systemName) || $systemName === 'all') {
            return $query;
        }

        $rawSystemNames = $this->getRawSystemNames($systemName);

        return $query->where(function ($q) use ($rawSystemNames, $systemName) {
            // Start with OR conditions from the beginning
            $q->where(DB::raw('LOWER(system)'), strtolower($systemName));

            // Add all raw system variations as OR conditions
            foreach ($rawSystemNames as $rawSystem) {
                $q->orWhere(DB::raw('LOWER(system)'), strtolower($rawSystem))
                    ->orWhere('application', 'LIKE', "%{$rawSystem}%");
            }

            // Also try direct application field match for the clean name
            $q->orWhere(DB::raw('LOWER(application)'), 'like', '%' . strtolower($systemName) . '%');
        });
    }

    private function getSystemVariations($systemName)
    {
        $variations = [$systemName]; // Include the original

        // Define known variations for each system
        $systemVariationsMap = [
            'Fit Express' => ['Fit Express', ' Fit Express', 'FIT Express', ' FIT Express', 'fit express', ' fit express'],
            'Fit Express UAT' => ['Fit Express UAT', ' Fit Express UAT', 'FIT Express UAT', ' FIT Express UAT'],
            'FIT ERP' => ['FIT ERP', ' FIT ERP', 'fit erp', ' fit erp'],
            'FIT ERP UAT' => ['FIT ERP UAT', 'FITERPUAT', ' FITERPUAT'],
            'D365 Live' => ['D365 Live', 'D365 LIVE', 'd365 live'],
            'Windows Authentication' => ['Windows Authentication', 'Windows Sign In', 'windows sign in'],
        ];

        // If we have predefined variations, use them
        if (isset($systemVariationsMap[$systemName])) {
            return $systemVariationsMap[$systemName];
        }

        // Otherwise, generate common variations
        $variations[] = ' ' . $systemName; // With leading space
        $variations[] = strtolower($systemName);
        $variations[] = ' ' . strtolower($systemName);
        $variations[] = strtoupper($systemName);
        $variations[] = ' ' . strtoupper($systemName);

        return array_unique($variations);
    }



    /**
     * IMPROVED: System-filtered stats calculation showing only users who use the selected system
     */

    private function calculateStats($startDate, $endDate, $system)
    {
        $cacheKey = 'dashboard_stats_fixed_v3_' . md5($startDate->format('Y-m-d') . $endDate->format('Y-m-d') . $system);

        return Cache::remember($cacheKey, 1800, function () use ($startDate, $endDate, $system) {
            $queryStartTime = microtime(true);

            try {
                if ($system && $system !== 'all') {
                    // For specific system: count users who have used that system in the period
                    $loggedInQuery = AzureUser::valid()
                        ->whereHas('signIns', function ($q) use ($startDate, $endDate, $system) {
                            $q->whereBetween('date_utc', [$startDate, $endDate]);
                            $this->applySystemFilter($q, $system);
                            $q->whereNotIn('system', $this->excludedSystems);
                        });

                    $loggedInCount = $loggedInQuery->count();

                    // Total users who have ever used this system
                    $totalSystemUsersQuery = AzureUser::valid()
                        ->whereHas('signIns', function ($q) use ($system) {
                            $this->applySystemFilter($q, $system);
                            $q->whereNotIn('system', $this->excludedSystems);
                        });

                    $totalUsers = $totalSystemUsersQuery->count();

                    // Total logins for this system in the period
                    $totalLoginsQuery = SigninLog::whereBetween('date_utc', [$startDate, $endDate])
                        ->whereNotIn('system', $this->excludedSystems);

                    $this->applySystemFilter($totalLoginsQuery, $system);
                    $totalLogins = $totalLoginsQuery->count();

                    // Last login for this system
                    $lastLoginQuery = SigninLog::whereBetween('date_utc', [$startDate, $endDate])
                        ->whereNotIn('system', $this->excludedSystems);

                    $this->applySystemFilter($lastLoginQuery, $system);
                    $lastLogin = $lastLoginQuery->max('date_utc');
                } else {
                    // For 'all' systems: use original logic but with enhanced exclusions
                    $loggedInCount = AzureUser::valid()
                        ->whereHas('signIns', function ($q) use ($startDate, $endDate) {
                            $q->whereBetween('date_utc', [$startDate, $endDate]);
                            $q->whereNotIn('system', $this->excludedSystems);
                        })
                        ->count();

                    $totalUsers = AzureUser::valid()->count();

                    $totalLogins = SigninLog::whereBetween('date_utc', [$startDate, $endDate])
                        ->whereNotIn('system', $this->excludedSystems)
                        ->count();

                    $lastLogin = SigninLog::whereBetween('date_utc', [$startDate, $endDate])
                        ->whereNotIn('system', $this->excludedSystems)
                        ->max('date_utc');
                }

                $notLoggedInCount = max(0, $totalUsers - $loggedInCount);

                // Get recent sign-ins with enhanced system filtering
                $signInsQuery = SigninLog::whereBetween('date_utc', [$startDate, $endDate])
                    ->whereNotIn('system', $this->excludedSystems)
                    ->whereNotNull('request_id');

                if ($system && $system !== 'all') {
                    $this->applySystemFilter($signInsQuery, $system);
                }

                $signInsRaw = $signInsQuery
                    ->orderBy('date_utc', 'desc')
                    ->limit(50)
                    ->select(['request_id', 'system', 'date_utc', 'application', 'username'])
                    ->get();

                $signIns = $signInsRaw->map(function ($item) {
                    return (object)[
                        'id' => $item->request_id,
                        'system' => $this->normalizeSystemName($item->system),
                        'date_utc' => \Carbon\Carbon::parse($item->date_utc),
                        'application' => $item->application ?? 'N/A',
                        'username' => $item->username ?? 'N/A'
                    ];
                });

                $totalQueryTime = microtime(true) - $queryStartTime;

                Log::info('Dashboard stats calculated with fixed system matching v3', [
                    'total_users' => $totalUsers,
                    'logged_in_count' => $loggedInCount,
                    'not_logged_in_count' => $notLoggedInCount,
                    'total_logins' => $totalLogins,
                    'system' => $system,
                    'excluded_systems' => $this->excludedSystems,
                    'query_time_ms' => round($totalQueryTime * 1000, 2),
                    'date_range' => [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')],
                    'system_specific' => ($system !== 'all')
                ]);

                return [
                    'totalUsers' => $totalUsers,
                    'loggedInCount' => $loggedInCount,
                    'notLoggedInCount' => $notLoggedInCount,
                    'totalLogins' => $totalLogins,
                    'lastLogin' => $lastLogin,
                    'signIns' => $signIns,
                    'totalDays' => $startDate->diffInDays($endDate) + 1,
                ];
            } catch (\Exception $e) {
                Log::error('Fixed system stats calculation failed v3', [
                    'error' => $e->getMessage(),
                    'system' => $system,
                    'excluded_systems' => $this->excludedSystems
                ]);

                return [
                    'totalUsers' => 0,
                    'loggedInCount' => 0,
                    'notLoggedInCount' => 0,
                    'totalLogins' => 0,
                    'lastLogin' => null,
                    'signIns' => collect(),
                    'totalDays' => $startDate->diffInDays($endDate) + 1,
                    'error' => true
                ];
            }
        });
    }




    public function debugSystemMapping(Request $request)
    {
        $system = $request->input('system', 'SCM');

        $rawSystemNames = $this->getRawSystemNames($system);

        // Get actual system values from the database
        $actualSystems = \DB::table('Azure_LoginDetails')
            ->selectRaw('system, COUNT(*) as count')
            ->whereNotNull('system')
            ->whereNotIn('system', $this->excludedSystems)
            ->groupBy('system')
            ->orderBy('count', 'desc')
            ->get();

        // Check which systems would match your mapping
        $matchingCounts = [];
        foreach ($rawSystemNames as $rawSystem) {
            $count = \DB::table('Azure_LoginDetails')
                ->where('system', $rawSystem)
                ->whereNotIn('system', $this->excludedSystems)
                ->count();
            $matchingCounts[$rawSystem] = $count;
        }

        return response()->json([
            'selected_system' => $system,
            'raw_system_names_for_selection' => $rawSystemNames,
            'system_mapping_from_config' => config('systemmap'),
            'actual_systems_in_db' => $actualSystems,
            'matching_counts' => $matchingCounts,
            'excluded_systems' => $this->excludedSystems,
            'available_systems_after_filtering' => $this->getAvailableSystems(),
            'recommendation' => $this->generateSystemMappingRecommendation($system, $actualSystems, $matchingCounts)
        ], 200, [], JSON_PRETTY_PRINT);
    }

    private function generateSystemMappingRecommendation($system, $actualSystems, $matchingCounts)
    {
        $recommendations = [];

        if (array_sum($matchingCounts) === 0) {
            $recommendations[] = "No records found for mapped system '{$system}'. Check if the mapping is correct.";

            // Find similar system names
            $similarSystems = $actualSystems->filter(function ($dbSystem) use ($system) {
                return stripos($dbSystem->system, strtolower($system)) !== false ||
                    stripos(strtolower($system), strtolower($dbSystem->system)) !== false;
            });

            if ($similarSystems->count() > 0) {
                $recommendations[] = "Similar systems found: " . $similarSystems->pluck('system')->implode(', ');
            }
        } else {
            $recommendations[] = "Found " . array_sum($matchingCounts) . " total records across mapped systems.";
        }

        return $recommendations;
    }
    /**
     * IMPROVED: Clean date range calculation excluding unwanted systems
     */
    private function getActualDateRange()
    {
        return Cache::remember('actual_date_range_clean_v2', 1800, function () {
            $dateRange = SigninLog::whereNotIn('system', $this->excludedSystems)
                ->selectRaw('MIN(date_utc) as min_date, MAX(date_utc) as max_date')
                ->first();

            $minDate = $dateRange?->min_date ? Carbon::parse($dateRange->min_date) : now()->subYear();
            $maxDate = $dateRange?->max_date ? Carbon::parse($dateRange->max_date) : now();
            $hasData = $dateRange?->min_date !== null;

            Log::info('Clean date range calculated v2', [
                'min_date' => $minDate->toDateString(),
                'max_date' => $maxDate->toDateString(),
                'has_data' => $hasData,
                'excluded_systems' => $this->excludedSystems
            ]);

            return [
                'min_date' => $minDate,
                'max_date' => $maxDate,
                'has_data' => $hasData
            ];
        });
    }



    // OPTIMIZED:

    public function getLoggedInUsersCount($startDate, $endDate, $system)
    {
        Log::info('getLoggedInUsersCount called (optimized version)', [
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'system' => $system
        ]);

        // Much simpler query - just count distinct usernames (no complex joins)
        $sql = "
        SELECT COUNT(DISTINCT username) as count
        FROM Azure_LoginDetails
        WHERE system = ?
        AND date_utc BETWEEN ? AND ?
        AND username IS NOT NULL
        AND username != ''
    ";

        $params = [
            $system,
            $startDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s')
        ];

        try {
            $startTime = microtime(true);
            $result = \DB::select($sql, $params);
            $queryTime = microtime(true) - $startTime;

            $count = (int) ($result[0]->count ?? 0);

            Log::info('Optimized username count completed', [
                'count' => $count,
                'system' => $system,
                'query_time_ms' => round($queryTime * 1000, 2),
                'date_range' => [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]
            ]);

            return $count;
        } catch (\Exception $e) {
            Log::error('Optimized username count failed', [
                'sql' => $sql,
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }


    /**
     * Clear dashboard-related caches (can be called after imports)
     */
    public function clearDashboardCache()
    {
        $cacheKeys = [
            'actual_date_range',
            'actual_date_range_clean',
            'actual_date_range_clean_v2',
            'available_systems',
            'available_systems_clean',
            'available_systems_clean_v2'
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        // Clear pattern-based cache keys
        try {
            Cache::tags(['dashboard', 'user_stats'])->flush();
            Log::info('Dashboard cache cleared using tags v2');
        } catch (\Exception $e) {
            // Fallback for drivers without tag support
            Log::info('Cache tags not supported, cleared individual keys v2');
        }

        return response()->json([
            'message' => 'Dashboard cache cleared successfully v2',
            'excluded_systems' => $this->excludedSystems
        ]);
    }

    private function validateSystemSelection($system)
    {
        if ($system === 'all') {
            return $system;
        }

        $availableSystems = $this->getAvailableSystems();
        if (!in_array($system, $availableSystems)) {
            Log::warning('Invalid system selection fixed v3', [
                'requested_system' => $system,
                'available_systems' => $availableSystems
            ]);
            return 'all'; // Fallback to all
        }

        return $system;
    }


    /**
     * Debug endpoint to verify system filtering
     */
    public function debugSystemFiltering(Request $request)
    {
        $system = $request->input('system', 'SCM');

        $analysis = [
            'excluded_systems' => $this->excludedSystems,
            'selected_system' => $system,
            'validated_system' => $this->validateSystemSelection($system),
            'available_systems' => $this->getAvailableSystems(),
        ];

        // System counts comparison
        $analysis['system_counts'] = [
            'all_systems' => \DB::table('Azure_LoginDetails')
                ->selectRaw('system, COUNT(*) as count')
                ->whereNotNull('system')
                ->groupBy('system')
                ->orderBy('count', 'desc')
                ->get(),
            'clean_systems_only' => \DB::table('Azure_LoginDetails')
                ->selectRaw('system, COUNT(*) as count')
                ->whereNotIn('system', $this->excludedSystems)
                ->whereNotNull('system')
                ->groupBy('system')
                ->orderBy('count', 'desc')
                ->get(),
        ];

        // Test specific system
        if ($system !== 'all') {
            $analysis['specific_system_test'] = [
                'system' => $system,
                'record_count' => \DB::table('Azure_LoginDetails')->where('system', $system)->count(),
                'is_excluded' => in_array($system, $this->excludedSystems),
                'sample_records' => \DB::table('Azure_LoginDetails')
                    ->where('system', $system)
                    ->limit(3)
                    ->get(['user_id', 'username', 'system', 'date_utc'])
            ];
        }

        return response()->json($analysis, 200, [], JSON_PRETTY_PRINT);
    }


    public function debugData(Request $request)
    {
        $system = $this->validateSystemSelection($request->input('system', 'SCM'));
        $range = $request->input('range', 'last_3_months');

        // FIXED: Use the same corrected date logic as the main index method
        $actualDateRange = $this->getActualDateRange();

        $intendedStartDate = match ($range) {
            'this_month' => now()->startOfMonth(),
            'last_month' => now()->subMonth()->startOfMonth(),
            'last_3_months' => now()->subMonths(3)->startOfDay(),
            'last_6_months' => now()->subMonths(6)->startOfDay(),
            'last_year' => now()->subYear()->startOfDay(),
            'all_time' => $actualDateRange['min_date'],
            default => now()->subMonths(3)->startOfDay(),
        };

        $intendedEndDate = match ($range) {
            'this_month' => now()->endOfMonth(),
            'last_month' => now()->subMonth()->endOfMonth(),
            'last_3_months' => now()->endOfDay(),
            'last_6_months' => now()->endOfDay(),
            'last_year' => now()->endOfDay(),
            'all_time' => $actualDateRange['max_date'],
            default => now()->endOfDay(),
        };

        $startDate = max($intendedStartDate, $actualDateRange['min_date']);
        $endDate = min($intendedEndDate, $actualDateRange['max_date']);

        if ($startDate > $endDate) {
            $startDate = $actualDateRange['min_date'];
            $endDate = $actualDateRange['max_date'];
        }

        $debug = [
            'filters_applied' => [
                'system' => $system,
                'range' => $range,
                'intended_start_date' => $intendedStartDate->toDateString(),
                'intended_end_date' => $intendedEndDate->toDateString(),
                'actual_start_date' => $startDate->toDateString(),
                'actual_end_date' => $endDate->toDateString(),
                'date_range_adjusted' => ($startDate != $intendedStartDate || $endDate != $intendedEndDate),
                'actual_data_range' => [
                    'min_date' => $actualDateRange['min_date']->toDateString(),
                    'max_date' => $actualDateRange['max_date']->toDateString(),
                    'has_data' => $actualDateRange['has_data']
                ]
            ],
            'table_info' => [
                'signin_table_name' => (new SigninLog())->getTable(),
                'user_table_name' => (new AzureUser())->getTable(),
            ],
            'raw_data_counts' => [
                'total_azure_users' => AzureUser::count(),
                'total_signin_logs' => SigninLog::count(),
                'signin_logs_in_date_range' => SigninLog::whereBetween('date_utc', [$startDate, $endDate])->count(),
                'signin_logs_with_system' => SigninLog::where('system', $system)->count(),
                'signin_logs_system_and_date' => SigninLog::whereBetween('date_utc', [$startDate, $endDate])
                    ->where('system', $system)->count(),
                'signin_logs_with_valid_users' => SigninLog::whereHas('user')->count(),
            ],
            'system_analysis' => [
                'all_system_values' => SigninLog::whereNotNull('system')
                    ->distinct('system')
                    ->pluck('system')
                    ->toArray(),
                'system_counts' => SigninLog::selectRaw('system, COUNT(*) as count')
                    ->whereNotNull('system')
                    ->groupBy('system')
                    ->get()
                    ->toArray(),
            ],
            'relationship_analysis' => [
                'signin_logs_with_user_id' => SigninLog::whereNotNull('user_id')->count(),
                'signin_logs_without_user_id' => SigninLog::whereNull('user_id')->count(),
                'users_referenced_in_signin_logs' => SigninLog::distinct('user_id')->count(),
                'signin_logs_with_valid_user_relationship' => SigninLog::whereHas('user')->count(),
                'case_insensitive_matches' => \DB::select("
                SELECT COUNT(DISTINCT au.id) as count
                FROM Azure_Users au
                INNER JOIN Azure_LoginDetails ald ON LOWER(au.userPrincipalName) = LOWER(ald.username)
                WHERE ald.system = ? AND ald.date_utc BETWEEN ? AND ?
            ", [$system, $startDate, $endDate])[0]->count ?? 0,
            ],
            'sample_data' => [
                'signin_logs' => SigninLog::with('user')
                    ->where('system', $system)
                    ->whereBetween('date_utc', [$startDate, $endDate])
                    ->limit(5)
                    ->get()
                    ->map(function ($log) {
                        return [
                            'id' => $log->id,
                            'user_id' => $log->user_id,
                            'system' => $log->system,
                            'application' => $log->application ?? 'N/A',
                            'username' => $log->username,
                            'user' => $log->user?->userPrincipalName ?? 'N/A',
                            'date_utc' => $log->date_utc->toIso8601String(),
                            'user_found' => $log->user ? 'YES' : 'NO',
                        ];
                    })->toArray(),
                'users_with_signins' => AzureUser::withCount(['signIns' => function ($q) use ($startDate, $endDate, $system) {
                    $q->whereBetween('date_utc', [$startDate, $endDate])
                        ->where('system', $system);
                }])
                    ->limit(5)
                    ->get()
                    ->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'userPrincipalName' => $user->userPrincipalName,
                            'displayName' => $user->displayName ?? 'N/A',
                            'mail' => $user->mail ?? 'N/A',
                            'signin_count' => $user->sign_ins_count,
                        ];
                    })->toArray(),
            ],
        ];

        return response()->json($debug, 200, [], JSON_PRETTY_PRINT);
    }
    public function diagnoseDataRelationships(Request $request)
    {
        $system = $request->input('system', 'SCM');
        $range = $request->input('range', 'last_3_months');

        $startDate = match ($range) {
            'this_month' => now()->startOfMonth(),
            'last_month' => now()->subMonth()->startOfMonth(),
            'last_3_months' => now()->subMonths(3)->startOfDay(),
            'last_6_months' => now()->subMonths(6)->startOfDay(),
            'last_year' => now()->subYear()->startOfDay(),
            'all_time' => null,
            default => now()->subMonths(3)->startOfDay(),
        };

        $endDate = match ($range) {
            'last_month' => now()->subMonth()->endOfMonth(),
            'all_time' => null,
            default => now()->endOfDay(),
        };

        $diagnostics = [];

        $diagnostics['sample_login_data'] = \DB::select("
            SELECT TOP 10
                user_id,
                username,
                sign_in_identifier,
                [user] as user_field,
                system,
                date_utc,
                CASE
                    WHEN user_id LIKE '%-%-%-%-%' THEN 'UUID_FORMAT'
                    WHEN user_id LIKE '%@%' THEN 'EMAIL_FORMAT'
                    WHEN user_id IS NULL THEN 'NULL_VALUE'
                    ELSE 'OTHER_FORMAT'
                END as format_type
            FROM Azure_LoginDetails
            WHERE system = ?
            AND date_utc BETWEEN ? AND ?
            ORDER BY date_utc DESC
        ", [$system, $startDate, $endDate]);

        $diagnostics['sample_user_data'] = \DB::select("
            SELECT TOP 10
                id,
                userPrincipalName,
                displayName,
                mail,
                LEN(id) as id_length
            FROM Azure_Users
            ORDER BY userPrincipalName
        ");

        $diagnostics['matching_analysis'] = \DB::select("
            SELECT
                'exact_id_match' as match_type,
                COUNT(DISTINCT au.id) as matches
            FROM Azure_Users au
            INNER JOIN Azure_LoginDetails ald ON au.id = ald.user_id
            WHERE ald.system = ? AND ald.date_utc BETWEEN ? AND ?

            UNION ALL

            SELECT
                'username_match' as match_type,
                COUNT(DISTINCT au.id) as matches
            FROM Azure_Users au
            INNER JOIN Azure_LoginDetails ald ON LOWER(au.userPrincipalName) = LOWER(ald.username)
            WHERE ald.system = ? AND ald.date_utc BETWEEN ? AND ?

            UNION ALL

            SELECT
                'sign_in_identifier_match' as match_type,
                COUNT(DISTINCT au.id) as matches
            FROM Azure_Users au
            INNER JOIN Azure_LoginDetails ald ON LOWER(au.userPrincipalName) = LOWER(ald.sign_in_identifier)
            WHERE ald.system = ? AND ald.date_utc BETWEEN ? AND ?

            UNION ALL

            SELECT
                'mail_match' as match_type,
                COUNT(DISTINCT au.id) as matches
            FROM Azure_Users au
            INNER JOIN Azure_LoginDetails ald ON LOWER(au.mail) = LOWER(ald.username)
            WHERE ald.system = ? AND ald.date_utc BETWEEN ? AND ?
            AND au.mail IS NOT NULL
        ", [
            $system,
            $startDate,
            $endDate,
            $system,
            $startDate,
            $endDate,
            $system,
            $startDate,
            $endDate,
            $system,
            $startDate,
            $endDate
        ]);

        $diagnostics['orphaned_logins'] = \DB::select("
            SELECT COUNT(*) as orphaned_count
            FROM Azure_LoginDetails ald
            LEFT JOIN Azure_Users au ON au.id = ald.user_id
            WHERE au.id IS NULL
            AND ald.system = ?
            AND ald.date_utc BETWEEN ? AND ?
        ", [$system, $startDate, $endDate])[0]->orphaned_count ?? 0;

        $diagnostics['user_id_quality'] = \DB::select("
            SELECT
                COUNT(*) as total_records,
                COUNT(CASE WHEN user_id IS NULL THEN 1 END) as null_user_id,
                COUNT(CASE WHEN user_id = '' THEN 1 END) as empty_user_id,
                COUNT(CASE WHEN user_id IS NOT NULL AND user_id != '' THEN 1 END) as valid_user_id,
                COUNT(DISTINCT user_id) as unique_user_ids
            FROM Azure_LoginDetails
            WHERE system = ?
            AND date_utc BETWEEN ? AND ?
        ", [$system, $startDate, $endDate])[0];

        $diagnostics['matching_recommendations'] = $this->generateMatchingRecommendations($diagnostics['matching_analysis']);

        return response()->json([
            'system' => $system,
            'date_range' => [$startDate?->toDateString(), $endDate?->toDateString()],
            'diagnostics' => $diagnostics,
            'summary' => [
                'total_logins_in_period' => SigninLog::where('system', $system)
                    ->whereBetween('date_utc', [$startDate, $endDate])
                    ->count(),
                'total_users' => AzureUser::count(),
                'valid_user_relationships' => SigninLog::whereHas('user')
                    ->where('system', $system)
                    ->whereBetween('date_utc', [$startDate, $endDate])
                    ->count(),
            ]
        ], 200, [], JSON_PRETTY_PRINT);
    }

    private function generateMatchingRecommendations($matchingAnalysis)
    {
        $recommendations = [];
        $hasMatches = false;

        foreach ($matchingAnalysis as $match) {
            if ($match->matches > 0) {
                $hasMatches = true;
                $recommendations[] = "Found {$match->matches} matches using {$match->match_type} strategy";
            }
        }

        if (!$hasMatches) {
            $recommendations[] = "No matches found. Consider: \n1. Data cleaning\n2. Import process review\n3. Manual user ID mapping";
        } else {
            $recommendations[] = "Optimize matching by prioritizing successful strategies";
        }

        return $recommendations;
    }

    /**
     * Invalidate dashboard cache
     * This method clears all dashboard-related cached data
     */
    private function invalidateDashboardCache(): void
    {
        try {
            // Clear specific dashboard-related cache keys
            $cacheKeys = [
                'dashboard_stats_cache',
                'actual_date_range',
                'actual_date_range_clean',
                'actual_date_range_clean_v2',
                'available_systems',
                'available_systems_clean',
                'available_systems_clean_v2',
                'available_systems_clean_v3'
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            // If using cache tags (Redis/Memcached)
            try {
                Cache::tags(['dashboard', 'user_stats', 'signin_logs'])->flush();
                Log::info('Dashboard cache cleared using tags');
            } catch (\Exception $e) {
                // Fallback for drivers that don't support tags
                Log::info('Dashboard cache cleared with individual key clearing (tag support unavailable)');
            }

            Log::info('Dashboard cache invalidated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to invalidate dashboard cache', [
                'error' => $e->getMessage()
            ]);
        }
    }
    /**
     * Get user summary from stored procedure - FIXED with actual column names
     */
    private function getUserSummary()
    {
        try {
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_Azure_UserSummary;");
            $stmt->execute();

            $results = $stmt->fetchAll(\PDO::FETCH_OBJ);

            while ($stmt->nextRowset()) {
                // Clear buffer
            }

            Log::info('SP UserSummary PDO results', [
                'count' => count($results),
                'first_result' => !empty($results) ? $results[0] : null,
            ]);

            if (!empty($results) && is_object($results[0])) {
                $firstRow = $results[0];

                // FIXED: Use actual column names from stored procedure
                return [
                    'total_users' => $firstRow->Totaluser ?? 0,
                    'active_this_month' => $firstRow->ActiveThisMonth ?? 0,
                    'active_last_month' => $firstRow->ActiveLastMonth ?? 0,
                    'inactive_users' => $firstRow->InActiveUser ?? 0,
                ];
            }

            Log::warning('SP UserSummary returned no data');

            return [
                'total_users' => 0,
                'active_this_month' => 0,
                'active_last_month' => 0,
                'inactive_users' => 0,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get user summary from SP', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => substr($e->getTraceAsString(), 0, 500)
            ]);

            return [
                'total_users' => 0,
                'active_this_month' => 0,
                'active_last_month' => 0,
                'inactive_users' => 0,
            ];
        }
    }

    /**
     * Get login view list from stored procedure - FIXED with day-based counting
     */
    private function getLoginViewList()
    {
        try {
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; EXEC dbo.sp_Azure_LoginViewList;");
            $stmt->execute();

            $results = $stmt->fetchAll(\PDO::FETCH_OBJ);

            while ($stmt->nextRowset()) {
                // Clear buffer
            }

            if (empty($results)) {
                Log::warning('SP LoginViewList returned no data');
                return collect();
            }

            return collect($results)->map(function ($item) {
                // Clean up the last login field
                $lastLogin = $item->LastLogin ?? null;
                if ($lastLogin === 'Never Logged In' || $lastLogin === 'Never' || empty($lastLogin)) {
                    $lastLogin = null;
                }

                // FIXED: Handle the asterisk (*) value
                $loginsLastMonth = $item->NoofLoginLastMonth ?? 0;

                // Log::debug('Processing user from SP', [
                //     'name' => $item->Name ?? 'N/A',
                //     'login_id' => $item->LoginID ?? 'N/A',
                //     'raw_logins_value' => $loginsLastMonth,
                //     'type' => gettype($loginsLastMonth),
                //     'is_numeric' => is_numeric($loginsLastMonth)
                // ]);

                // Convert to integer if it's numeric
                if (is_numeric($loginsLastMonth)) {
                    $loginsLastMonth = (int)$loginsLastMonth;
                }
                // Check for string representations
                elseif (is_string($loginsLastMonth)) {
                    $trimmed = trim($loginsLastMonth);

                    // Handle the asterisk case - query actual login count (DAYS, not records)
                    if ($trimmed === '*') {
                        $loginsLastMonth = $this->getActualLoginCount($item->LoginID);
                        Log::info('Asterisk value replaced with actual day count', [
                            'user' => $item->Name ?? 'N/A',
                            'login_id' => $item->LoginID ?? 'N/A',
                            'actual_days' => $loginsLastMonth
                        ]);
                    } elseif ($trimmed === 'Never Logged In' || $trimmed === 'Never' || $trimmed === '') {
                        $loginsLastMonth = 0;
                    } elseif (is_numeric($trimmed)) {
                        $loginsLastMonth = (int)$trimmed;
                    } else {
                        Log::warning('Unexpected logins value format', [
                            'user' => $item->Name ?? 'N/A',
                            'value' => $loginsLastMonth
                        ]);
                        $loginsLastMonth = 0;
                    }
                }
                // If null or other type, set to 0
                else {
                    $loginsLastMonth = 0;
                }

                return [
                    'name' => $item->Name ?? 'N/A',
                    'login_id' => $item->LoginID ?? 'N/A',
                    'logins_last_month' => $loginsLastMonth,
                    'last_login' => $lastLogin,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Failed to get login view list from SP', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => substr($e->getTraceAsString(), 0, 500)
            ]);

            return collect();
        }
    }



    /**
     * Get actual login count for a user when stored procedure returns asterisk
     * FIXED: Counts unique APPLICATION-DAY combinations
     * Example: If user accessed "Windows Sign In" 28 times on Oct 1st = counts as 1
     *          If user accessed "OPS" 5 times on Oct 1st = counts as 1
     *          Total for Oct 1st = 2 (not 33)
     */
    private function getActualLoginCount($loginId)
    {
        try {
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();

            // Count distinct APPLICATION + DATE combinations
            // This counts each application once per day, regardless of how many times accessed
            $count = DB::table('Azure_LoginDetails')
                ->where('username', $loginId)
                ->whereBetween('date_utc', [$lastMonthStart, $lastMonthEnd])
                ->whereNotNull('application')
                ->where('application', '!=', '')
                ->selectRaw('COUNT(DISTINCT CONCAT(CAST(date_utc AS DATE), \'|\', application)) as unique_app_days')
                ->first()
                ->unique_app_days ?? 0;

            Log::info('Unique application-day combinations calculated', [
                'login_id' => $loginId,
                'unique_app_days' => $count,
                'date_range' => [$lastMonthStart->toDateString(), $lastMonthEnd->toDateString()],
                'count_logic' => 'Each application counts once per day'
            ]);

            return $count;
        } catch (\Exception $e) {
            Log::error('Failed to get actual login count', [
                'login_id' => $loginId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }




    /**
     * Debug endpoint to verify login count for a specific user
     */
    public function debugUserLogins(Request $request)
    {
        $loginId = $request->input('login_id', 'M.mlyuka@freight-in-time.com');

        // Define last month date range
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // Get current month for comparison
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();

        try {
            // Count logins last month
            $lastMonthCount = DB::table('Azure_LoginDetails')
                ->where('username', $loginId)
                ->whereBetween('date_utc', [$lastMonthStart, $lastMonthEnd])
                ->count();

            // Count logins this month
            $currentMonthCount = DB::table('Azure_LoginDetails')
                ->where('username', $loginId)
                ->whereBetween('date_utc', [$currentMonthStart, $currentMonthEnd])
                ->count();

            // Get distinct days logged in last month
            $distinctDaysLastMonth = DB::table('Azure_LoginDetails')
                ->where('username', $loginId)
                ->whereBetween('date_utc', [$lastMonthStart, $lastMonthEnd])
                ->selectRaw('COUNT(DISTINCT CAST(date_utc AS DATE)) as distinct_days')
                ->first()
                ->distinct_days ?? 0;

            // Get breakdown by application (changed from system)
            $applicationBreakdown = DB::table('Azure_LoginDetails')
                ->where('username', $loginId)
                ->whereBetween('date_utc', [$lastMonthStart, $lastMonthEnd])
                ->selectRaw('application, COUNT(*) as count')
                ->groupBy('application')
                ->orderByDesc('count')
                ->get();

            // Get daily breakdown
            $dailyBreakdown = DB::table('Azure_LoginDetails')
                ->where('username', $loginId)
                ->whereBetween('date_utc', [$lastMonthStart, $lastMonthEnd])
                ->selectRaw('CAST(date_utc AS DATE) as login_date, COUNT(*) as count')
                ->groupBy(DB::raw('CAST(date_utc AS DATE)'))
                ->orderBy('login_date')
                ->get();

            // Get sample login records (first 10 and last 10)
            $firstLogins = DB::table('Azure_LoginDetails')
                ->where('username', $loginId)
                ->whereBetween('date_utc', [$lastMonthStart, $lastMonthEnd])
                ->orderBy('date_utc', 'asc')
                ->limit(10)
                ->get(['date_utc', 'application', 'ip_address', 'status']);

            $lastLogins = DB::table('Azure_LoginDetails')
                ->where('username', $loginId)
                ->whereBetween('date_utc', [$lastMonthStart, $lastMonthEnd])
                ->orderBy('date_utc', 'desc')
                ->limit(10)
                ->get(['date_utc', 'application', 'ip_address', 'status']);

            // Check for duplicates/imports (changed from system to application)
            $duplicateCheck = DB::table('Azure_LoginDetails')
                ->where('username', $loginId)
                ->whereBetween('date_utc', [$lastMonthStart, $lastMonthEnd])
                ->selectRaw('date_utc, application, COUNT(*) as duplicate_count')
                ->groupBy('date_utc', 'application')
                ->havingRaw('COUNT(*) > 1')
                ->orderByDesc('duplicate_count')
                ->limit(20)
                ->get();

            // Get all-time statistics
            $allTimeCount = DB::table('Azure_LoginDetails')
                ->where('username', $loginId)
                ->count();

            $firstEverLogin = DB::table('Azure_LoginDetails')
                ->where('username', $loginId)
                ->orderBy('date_utc', 'asc')
                ->first(['date_utc', 'application']);

            $lastEverLogin = DB::table('Azure_LoginDetails')
                ->where('username', $loginId)
                ->orderBy('date_utc', 'desc')
                ->first(['date_utc', 'application']);

            return response()->json([
                'user' => $loginId,
                'date_ranges' => [
                    'last_month' => [
                        'start' => $lastMonthStart->toDateString(),
                        'end' => $lastMonthEnd->toDateString(),
                    ],
                    'current_month' => [
                        'start' => $currentMonthStart->toDateString(),
                        'end' => $currentMonthEnd->toDateString(),
                    ]
                ],
                'summary' => [
                    'last_month_total_logins' => $lastMonthCount,
                    'current_month_total_logins' => $currentMonthCount,
                    'distinct_days_last_month' => $distinctDaysLastMonth,
                    'average_logins_per_day_last_month' => $distinctDaysLastMonth > 0
                        ? round($lastMonthCount / $distinctDaysLastMonth, 2)
                        : 0,
                    'all_time_logins' => $allTimeCount,
                ],
                'analysis' => [
                    'is_count_suspicious' => $lastMonthCount > 500,
                    'has_duplicates' => count($duplicateCheck) > 0,
                    'applications_used' => count($applicationBreakdown),
                    'interpretation' => $this->interpretLoginCount($lastMonthCount, $distinctDaysLastMonth, count($duplicateCheck)),
                ],
                'breakdown_by_application' => $applicationBreakdown,
                'daily_breakdown' => $dailyBreakdown,
                'duplicate_timestamps' => $duplicateCheck,
                'sample_logins' => [
                    'first_10_last_month' => $firstLogins,
                    'last_10_last_month' => $lastLogins,
                ],
                'historical' => [
                    'first_ever_login' => $firstEverLogin,
                    'last_ever_login' => $lastEverLogin,
                ],
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            Log::error('Failed to debug user logins', [
                'login_id' => $loginId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to retrieve login data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Interpret login count and provide insights
     */
    private function interpretLoginCount($totalLogins, $distinctDays, $duplicateCount)
    {
        $insights = [];

        if ($totalLogins > 1000) {
            $insights[] = "⚠️ Very high login count (>1000) - May indicate data duplication or automated processes";
        }

        if ($distinctDays > 0) {
            $avgPerDay = $totalLogins / $distinctDays;

            if ($avgPerDay > 50) {
                $insights[] = "⚠️ High average logins per day ({$avgPerDay}) - Unusual for manual user activity";
            } elseif ($avgPerDay > 20) {
                $insights[] = "ℹ️ Moderate activity ({$avgPerDay} logins/day) - Could be normal for power users";
            } else {
                $insights[] = "✅ Normal activity pattern ({$avgPerDay} logins/day)";
            }
        }

        if ($duplicateCount > 0) {
            $insights[] = "⚠️ Duplicate timestamps detected - May indicate data import issues";
        }

        if (empty($insights)) {
            $insights[] = "✅ Login count appears normal";
        }

        return $insights;
    }



    public function debugRelationships(Request $request)
    {
        $system = $request->input('system', 'SCM');

        // Check data existence
        $diagnostics = [
            'total_users' => \DB::select("SELECT COUNT(*) as count FROM Azure_Users")[0]->count,
            'total_logins' => \DB::select("SELECT COUNT(*) as count FROM Azure_LoginDetails")[0]->count,
            'logins_with_user_id' => \DB::select("SELECT COUNT(*) as count FROM Azure_LoginDetails WHERE user_id IS NOT NULL")[0]->count,
            'logins_with_username' => \DB::select("SELECT COUNT(*) as count FROM Azure_LoginDetails WHERE username IS NOT NULL")[0]->count,
            'system_logins' => \DB::select("SELECT COUNT(*) as count FROM Azure_LoginDetails WHERE system = ?", [$system])[0]->count,
        ];

        // Sample data
        $sampleLogins = \DB::select("SELECT TOP 5 user_id, username, system, date_utc FROM Azure_LoginDetails WHERE system = ? ORDER BY date_utc DESC", [$system]);
        $sampleUsers = \DB::select("SELECT TOP 5 id, userPrincipalName, displayName FROM Azure_Users");

        return response()->json([
            'diagnostics' => $diagnostics,
            'sample_logins' => $sampleLogins,
            'sample_users' => $sampleUsers
        ], 200, [], JSON_PRETTY_PRINT);
    }
}
