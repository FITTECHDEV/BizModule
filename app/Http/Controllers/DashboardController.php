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

// Add this method to properly map raw system names to display names
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
     * Get available systems directly from database
     */
    private function getAvailableSystems()
    {
        return Cache::remember('available_systems', 3600, function () {
            $systems = \DB::table('Azure_LoginDetails')
                ->select('system')
                ->whereNotNull('system')
                ->where('system', '!=', '')
                ->groupBy('system')
                ->orderByRaw('COUNT(*) DESC')
                ->pluck('system')
                ->toArray();
            
            Log::info('Available systems loaded from database', ['systems' => $systems]);
            return $systems;
        });
    }

 public function index(Request $request)
    {
        set_time_limit(120); // 2 minutes

        try {
            // Add cache busting parameter support
            if ($request->has('refresh_cache')) {
                $this->clearDashboardCache();
            }

            $filters = $this->filterService->parseFilters($request);
            $range = $filters['range'] ?? 'last_3_months';
            $system = $request->input('system', 'all');
            $search = $request->input('search');

            Log::info('Dashboard query with simplified system filtering', [
                'selected_system' => $system,
                'range' => $range,
                'search' => $search ? 'yes' : 'no'
            ]);

            // Get actual date range from data
            try {
                $actualDateRange = $this->getActualDateRange();
            } catch (\Exception $e) {
                Log::error('Failed to get actual date range', [
                    'error' => $e->getMessage()
                ]);
                $actualDateRange = [
                    'min_date' => now()->subYear(),
                    'max_date' => now(),
                    'has_data' => false
                ];
            }
            
            // Calculate intended date ranges first, then constrain to actual data
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

            // Constrain the intended dates to actual data availability
            $startDate = max($intendedStartDate, $actualDateRange['min_date']);
            $endDate = min($intendedEndDate, $actualDateRange['max_date']);

            // Handle case where intended range is completely outside actual data
            if ($startDate > $endDate) {
                Log::warning('Requested date range is outside actual data range', [
                    'range' => $range,
                    'intended_start' => $intendedStartDate->toDateString(),
                    'intended_end' => $intendedEndDate->toDateString(),
                    'actual_min' => $actualDateRange['min_date']->toDateString(),
                    'actual_max' => $actualDateRange['max_date']->toDateString(),
                    'system' => $system
                ]);
                
                $startDate = $actualDateRange['min_date'];
                $endDate = $actualDateRange['max_date'];
            }

            $totalDays = $startDate && $endDate ? $startDate->diffInDays($endDate) + 1 : 365;

            Log::info('Dashboard using date range', [
                'range' => $range,
                'system' => $system,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ]);

            // SIMPLIFIED QUERY - No complex mapping
            $baseUserQuery = AzureUser::query();

            // Build query with direct system filtering
            $query = $baseUserQuery
                ->select('Azure_Users.id', 'Azure_Users.userPrincipalName', 'Azure_Users.displayName', 'Azure_Users.mail')
                ->with(['signIns' => function ($q) use ($startDate, $endDate, $system) {
                    if ($startDate && $endDate) {
                        $q->whereBetween('date_utc', [$startDate, $endDate]);
                    }
                    // Direct system filtering - no mapping needed
                    if ($system && $system !== 'all') {
                        $q->where('system', $system);
                    }
                    $q->orderBy('date_utc', 'desc');
                }])
                ->leftJoin('Azure_LoginDetails as ald', function($join) use ($startDate, $endDate, $system) {
                    // Use username matching
                    $join->on(\DB::raw('LOWER(LTRIM(RTRIM(Azure_Users.userPrincipalName)))'), '=', \DB::raw('LOWER(LTRIM(RTRIM(ald.username)))'));
                    
                    if ($startDate && $endDate) {
                        $join->whereBetween('ald.date_utc', [$startDate, $endDate]);
                    }
                    // Direct system filtering
                    if ($system && $system !== 'all') {
                        $join->where('ald.system', $system);
                    }
                })
                ->groupBy('Azure_Users.id', 'Azure_Users.userPrincipalName', 'Azure_Users.displayName', 'Azure_Users.mail');

            // Add search functionality
            if ($search) {
                $search = trim(strip_tags($search));
                if (strlen($search) > 255) {
                    $search = substr($search, 0, 255);
                }
                $query->where(function ($q) use ($search) {
                    $q->where('displayName', 'LIKE', "%{$search}%")
                      ->orWhere('userPrincipalName', 'LIKE', "%{$search}%")
                      ->orWhere('mail', 'LIKE', "%{$search}%");
                });
            }

            $perPage = 17;
            $users = $query->paginate($perPage)->withQueryString();

            // Calculate stats with simplified system filtering
            try {
                $stats = $this->calculateStatsSimplified($startDate, $endDate, $system);
            } catch (\Exception $e) {
                Log::error('Failed to calculate dashboard stats', [
                    'error' => $e->getMessage(),
                    'system' => $system,
                    'date_range' => [$startDate->toDateString(), $endDate->toDateString()]
                ]);
                
                $stats = [
                    'totalUsers' => 0,
                    'loggedInCount' => 0,
                    'notLoggedInCount' => 0,
                    'totalLogins' => 0,
                    'lastLogin' => null,
                    'signIns' => collect(),
                    'error' => true
                ];
            }

            // Get available systems from database
            $systems = $this->getAvailableSystems();
            array_unshift($systems, 'all');

            if ($request->ajax()) {
                return response()->json([
                    'users' => collect($users->items())->map(function ($user) {
                        return [
                            'id' => $user->id ?? $user->userPrincipalName,
                            'userPrincipalName' => $user->userPrincipalName,
                            'displayName' => $user->displayName ?? 'Unknown',
                            'mail' => $user->mail ?? 'N/A',
                            'signIns' => $user->signIns->map(function ($signIn) {
                                return [
                                    'id' => $signIn->id,
                                    'system' => $signIn->system,
                                    'date_utc' => $signIn->date_utc->toIso8601String(),
                                    'application' => $signIn->application ?? 'N/A',
                                ];
                            })->toArray(),
                        ];
                    })->toArray(),
                    'totalUsers' => $stats['totalUsers'],
                    'loggedInCount' => $stats['loggedInCount'],
                    'notLoggedInCount' => $stats['notLoggedInCount'],
                    'totalLogins' => $stats['totalLogins'],
                    'lastLogin' => $stats['lastLogin'],
                    'signIns' => $stats['signIns']->map(function ($signIn) {
                        return [
                            'id' => $signIn->id,
                            'system' => $signIn->system,
                            'date_utc' => $signIn->date_utc->toIso8601String(),
                            'application' => $signIn->application ?? 'N/A',
                        ];
                    })->toArray(),
                    'totalDays' => $totalDays,
                    'rangeInput' => $range,
                    'rangeLabel' => $filters['rangeLabel'] ?? $range,
                    'systemInput' => $system,
                    'systems' => $systems,
                    'pagination' => $users->withQueryString()->links()->toHtml(),
                    'dateRange' => [
                        'start' => $startDate->toDateString(),
                        'end' => $endDate->toDateString(),
                        'data_available' => $actualDateRange['has_data']
                    ]
                ], 200);
            }

            return view('logintracker.dashboard', [
                'users' => $users,
                'totalUsers' => $stats['totalUsers'],
                'loggedInCount' => $stats['loggedInCount'],
                'notLoggedInCount' => $stats['notLoggedInCount'],
                'totalLogins' => $stats['totalLogins'],
                'lastLogin' => $stats['lastLogin'],
                'signIns' => $stats['signIns'],
                'totalDays' => $totalDays,
                'rangeInput' => $range,
                'rangeLabel' => $filters['rangeLabel'] ?? $range,
                'systemInput' => $system,
                'systems' => $systems,
                'selectedRange' => $range,
                'selectedSystem' => $system,
                'actualDateRange' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                    'data_available' => $actualDateRange['has_data']
                ]
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
     * SIMPLIFIED stats calculation - no complex mapping
     */
    private function calculateStatsSimplified($startDate, $endDate, $system)
    {
        $cacheKey = 'dashboard_stats_simple_' . md5($startDate->format('Y-m-d') . $endDate->format('Y-m-d') . $system);
        
        return Cache::remember($cacheKey, 1800, function () use ($startDate, $endDate, $system, $cacheKey) {
            $queryStartTime = microtime(true);
            
            try {
                // Build the WHERE clause for system filtering
                $systemWhere = '';
                $params = [];
                
                if ($system && $system !== 'all') {
                    $systemWhere = 'AND ald.system = ?';
                    $params[] = $system;
                }
                
                // Add date range parameters
                $params[] = $startDate->format('Y-m-d H:i:s');
                $params[] = $endDate->format('Y-m-d H:i:s');
                
                // Simplified stats query
                $statsQuery = "
                    SELECT 
                        (SELECT COUNT(*) FROM Azure_Users) as total_users,
                        COUNT(DISTINCT CASE 
                            WHEN ald.username IS NOT NULL AND ald.username != '' THEN ald.username 
                            ELSE NULL 
                        END) as logged_in_count,
                        COUNT(*) as total_logins,
                        MAX(ald.date_utc) as last_login
                    FROM Azure_LoginDetails ald
                    WHERE ald.date_utc BETWEEN ? AND ?
                    {$systemWhere}
                ";
                
                // Reorder parameters for the query
                if ($system && $system !== 'all') {
                    $queryParams = [$startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s'), $system];
                } else {
                    $queryParams = [$startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')];
                }
                
                $statsResult = \DB::select($statsQuery, $queryParams);
                
                if (empty($statsResult)) {
                    throw new \Exception('No stats data returned from query');
                }
                
                $statsResult = $statsResult[0];
                $statsQueryTime = microtime(true) - $queryStartTime;

                // Get recent sign-ins
                $signInsStartTime = microtime(true);
                $signIns = collect();
                
                try {
                    $signInsQuery = \DB::table('Azure_LoginDetails')
                        ->whereBetween('date_utc', [$startDate, $endDate])
                        ->whereNotNull('request_id');
                    
                    // Apply system filter if specified
                    if ($system && $system !== 'all') {
                        $signInsQuery->where('system', $system);
                    }
                    
                    $signInsRaw = $signInsQuery
                        ->orderBy('date_utc', 'desc')
                        ->limit(50)
                        ->select(['request_id', 'system', 'date_utc', 'application', 'username', 'user'])
                        ->get();

                    $signIns = $signInsRaw->map(function($item) {
                        return (object)[
                            'id' => $item->request_id,
                            'system' => $item->system,
                            'date_utc' => \Carbon\Carbon::parse($item->date_utc),
                            'application' => $item->application ?? 'N/A',
                            'username' => $item->username ?? 'N/A',
                            'user' => $item->user ?? 'N/A'
                        ];
                    });
                } catch (\Exception $signInError) {
                    Log::warning('Failed to load recent sign-ins', [
                        'error' => $signInError->getMessage(),
                        'system' => $system
                    ]);
                }
                
                $signInsQueryTime = microtime(true) - $signInsStartTime;

                $totalUsers = max(0, (int) ($statsResult->total_users ?? 0));
                $loggedInCount = max(0, (int) ($statsResult->logged_in_count ?? 0));
                $notLoggedInCount = max(0, $totalUsers - $loggedInCount);
                $totalLogins = max(0, (int) ($statsResult->total_logins ?? 0));

                $totalQueryTime = microtime(true) - $queryStartTime;

                Log::info('Simplified dashboard stats calculated successfully', [
                    'total_users' => $totalUsers,
                    'logged_in_count' => $loggedInCount,
                    'not_logged_in_count' => $notLoggedInCount,
                    'total_logins' => $totalLogins,
                    'system' => $system,
                    'performance' => [
                        'total_query_time_ms' => round($totalQueryTime * 1000, 2),
                        'stats_query_time_ms' => round($statsQueryTime * 1000, 2),
                        'signins_query_time_ms' => round($signInsQueryTime * 1000, 2),
                        'signins_loaded' => $signIns->count()
                    ],
                    'date_range' => [$startDate->toDateString(), $endDate->toDateString()],
                    'cache_key' => $cacheKey
                ]);

                return [
                    'totalUsers' => $totalUsers,
                    'loggedInCount' => $loggedInCount,
                    'notLoggedInCount' => $notLoggedInCount,
                    'totalLogins' => $totalLogins,
                    'lastLogin' => $statsResult->last_login,
                    'signIns' => $signIns,
                    'totalDays' => $startDate->diffInDays($endDate) + 1,
                ];

            } catch (\Exception $e) {
                $totalQueryTime = microtime(true) - $queryStartTime;
                
                Log::error('Simplified dashboard stats calculation failed', [
                    'error' => $e->getMessage(),
                    'system' => $system,
                    'date_range' => [$startDate->toDateString(), $endDate->toDateString()],
                    'query_time_ms' => round($totalQueryTime * 1000, 2),
                    'cache_key' => $cacheKey,
                    'stack_trace' => $e->getTraceAsString()
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
    
    $systemMapping = $this->getSystemMapping();
    $rawSystemNames = $this->getRawSystemNames($system);
    
    // Get actual system values from the database
    $actualSystems = \DB::table('Azure_LoginDetails')
        ->selectRaw('system, COUNT(*) as count')
        ->whereNotNull('system')
        ->groupBy('system')
        ->orderBy('count', 'desc')
        ->get();
    
    // Check which systems would match your mapping
    $matchingCounts = [];
    foreach ($rawSystemNames as $rawSystem) {
        $count = \DB::table('Azure_LoginDetails')
            ->where('system', $rawSystem)
            ->count();
        $matchingCounts[$rawSystem] = $count;
    }
    
    return response()->json([
        'selected_system' => $system,
        'raw_system_names_for_selection' => $rawSystemNames,
        'system_mapping' => $systemMapping,
        'actual_systems_in_db' => $actualSystems,
        'matching_counts' => $matchingCounts,
        'recommendation' => $this->generateSystemMappingRecommendation($system, $actualSystems, $matchingCounts)
    ], 200, [], JSON_PRETTY_PRINT);
}

private function generateSystemMappingRecommendation($system, $actualSystems, $matchingCounts)
{
    $recommendations = [];
    
    if (array_sum($matchingCounts) === 0) {
        $recommendations[] = "No records found for mapped system '{$system}'. Check if the mapping is correct.";
        
        // Find similar system names
        $similarSystems = $actualSystems->filter(function($dbSystem) use ($system) {
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
 private function getActualDateRange()
    {
        return Cache::remember('actual_date_range', 1800, function () {
            $dateRange = SigninLog::selectRaw('MIN(date_utc) as min_date, MAX(date_utc) as max_date')->first();

            $minDate = $dateRange?->min_date ? Carbon::parse($dateRange->min_date) : now()->subYear();
            $maxDate = $dateRange?->max_date ? Carbon::parse($dateRange->max_date) : now();
            $hasData = $dateRange?->min_date !== null;

            Log::info('Actual data range cached', [
                'min_date' => $minDate->toDateString(),
                'max_date' => $maxDate->toDateString(),
                'has_data' => $hasData
            ]);

            return [
                'min_date' => $minDate,
                'max_date' => $maxDate,
                'has_data' => $hasData
            ];
        });
    }

private function calculateStats($startDate, $endDate, $system)
{
    $cacheKey = 'dashboard_stats_' . md5($startDate->format('Y-m-d') . $endDate->format('Y-m-d') . $system);
    
    return Cache::remember($cacheKey, 1800, function () use ($startDate, $endDate, $system, $cacheKey) {
        $queryStartTime = microtime(true);
        
        try {
            // Get raw system names for the mapped system
            $rawSystemNames = $this->getRawSystemNames($system);
            
            if (empty($rawSystemNames)) {
                // If no mapping found, use the system as-is
                $rawSystemNames = [$system];
            }
            
            // Create SQL placeholders for raw system names
            $systemPlaceholders = str_repeat('?,', count($rawSystemNames) - 1) . '?';
            
            // Fixed query that properly handles multiple raw system names
            $statsQuery = "
                SELECT 
                    (SELECT COUNT(*) FROM Azure_Users) as total_users,
                    COUNT(DISTINCT CASE 
                        WHEN ald.username IS NOT NULL AND ald.username != '' THEN ald.username 
                        ELSE NULL 
                    END) as logged_in_count,
                    COUNT(*) as total_logins,
                    MAX(ald.date_utc) as last_login
                FROM Azure_LoginDetails ald
                WHERE ald.system IN ({$systemPlaceholders})
                AND ald.date_utc BETWEEN ? AND ?
            ";
            
            $params = array_merge($rawSystemNames, [
                $startDate->format('Y-m-d H:i:s'),
                $endDate->format('Y-m-d H:i:s')
            ]);
            
            $statsResult = \DB::select($statsQuery, $params);
            
            if (empty($statsResult)) {
                throw new \Exception('No stats data returned from query');
            }
            
            $statsResult = $statsResult[0];
            $statsQueryTime = microtime(true) - $queryStartTime;

            // Get recent sign-ins with improved system filtering
            $signInsStartTime = microtime(true);
            $signIns = collect();
            
            try {
                $signInsRaw = \DB::table('Azure_LoginDetails')
                    ->whereIn('system', $rawSystemNames)  // Use raw system names
                    ->whereBetween('date_utc', [$startDate, $endDate])
                    ->whereNotNull('request_id')
                    ->orderBy('date_utc', 'desc')
                    ->limit(50)
                    ->select(['request_id', 'system', 'date_utc', 'application', 'username', 'user'])
                    ->get();

                $signIns = $signInsRaw->map(function($item) {
                    return (object)[
                        'id' => $item->request_id,
                        'system' => $item->system,
                        'date_utc' => \Carbon\Carbon::parse($item->date_utc),
                        'application' => $item->application ?? 'N/A',
                        'username' => $item->username ?? 'N/A',
                        'user' => $item->user ?? 'N/A'
                    ];
                });
            } catch (\Exception $signInError) {
                Log::warning('Failed to load recent sign-ins', [
                    'error' => $signInError->getMessage(),
                    'system' => $system,
                    'raw_systems' => $rawSystemNames
                ]);
            }
            
            $signInsQueryTime = microtime(true) - $signInsStartTime;

            $totalUsers = max(0, (int) ($statsResult->total_users ?? 0));
            $loggedInCount = max(0, (int) ($statsResult->logged_in_count ?? 0));
            $notLoggedInCount = max(0, $totalUsers - $loggedInCount);
            $totalLogins = max(0, (int) ($statsResult->total_logins ?? 0));

            $totalQueryTime = microtime(true) - $queryStartTime;

            Log::info('Fixed dashboard stats calculated', [
                'total_users' => $totalUsers,
                'logged_in_count' => $loggedInCount,
                'not_logged_in_count' => $notLoggedInCount,
                'total_logins' => $totalLogins,
                'system' => $system,
                'raw_systems' => $rawSystemNames,
                'performance' => [
                    'total_query_time_ms' => round($totalQueryTime * 1000, 2),
                    'stats_query_time_ms' => round($statsQueryTime * 1000, 2),
                    'signins_query_time_ms' => round($signInsQueryTime * 1000, 2),
                    'signins_loaded' => $signIns->count()
                ],
                'date_range' => [$startDate->toDateString(), $endDate->toDateString()],
                'cache_key' => $cacheKey
            ]);

            return [
                'totalUsers' => $totalUsers,
                'loggedInCount' => $loggedInCount,
                'notLoggedInCount' => $notLoggedInCount,
                'totalLogins' => $totalLogins,
                'lastLogin' => $statsResult->last_login,
                'signIns' => $signIns,
                'totalDays' => $startDate->diffInDays($endDate) + 1,
            ];

        } catch (\Exception $e) {
            $totalQueryTime = microtime(true) - $queryStartTime;
            
            Log::error('Dashboard stats calculation failed', [
                'error' => $e->getMessage(),
                'system' => $system,
                'raw_systems' => $rawSystemNames ?? [],
                'date_range' => [$startDate->toDateString(), $endDate->toDateString()],
                'query_time_ms' => round($totalQueryTime * 1000, 2),
                'cache_key' => $cacheKey,
                'stack_trace' => $e->getTraceAsString()
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
        Cache::forget('actual_date_range');
        Cache::forget('available_systems');
        
        try {
            Cache::tags(['dashboard', 'user_stats'])->flush();
            Log::info('Dashboard cache cleared using tags');
        } catch (\Exception $e) {
            Cache::flush();
            Log::info('Dashboard cache cleared with full flush');
        }
        
        return response()->json(['message' => 'Dashboard cache cleared successfully']);
    }

     /**
     * Debug endpoint to verify system filtering
     */
    public function debugSystemFiltering(Request $request)
    {
        $system = $request->input('system', 'SCM');
        
        // Get actual system counts
        $systemCounts = \DB::table('Azure_LoginDetails')
            ->selectRaw('system, COUNT(*) as count')
            ->whereNotNull('system')
            ->groupBy('system')
            ->orderBy('count', 'desc')
            ->get();
        
        // Test the specific system
        $systemCount = \DB::table('Azure_LoginDetails')
            ->where('system', $system)
            ->count();
            
        // Get sample records
        $sampleRecords = \DB::table('Azure_LoginDetails')
            ->where('system', $system)
            ->limit(5)
            ->get();
        
        return response()->json([
            'selected_system' => $system,
            'system_record_count' => $systemCount,
            'all_system_counts' => $systemCounts,
            'sample_records' => $sampleRecords,
            'available_systems' => $this->getAvailableSystems()
        ], 200, [], JSON_PRETTY_PRINT);
    }


   public function debugData(Request $request)
{
    $system = $request->input('system', 'SCM');
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
                ->map(function($log) {
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
            'users_with_signins' => AzureUser::withCount(['signIns' => function($q) use ($startDate, $endDate, $system) {
                $q->whereBetween('date_utc', [$startDate, $endDate])
                  ->where('system', $system);
            }])
                ->limit(5)
                ->get()
                ->map(function($user) {
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
            $system, $startDate, $endDate,
            $system, $startDate, $endDate,
            $system, $startDate, $endDate,
            $system, $startDate, $endDate
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

    private function invalidateDashboardCache(): void
    {
        try {
            // Clear specific dashboard-related cache keys
            $cacheKeys = [
                'dashboard_stats_cache',
                'actual_date_range',
                'logged_in_users_count_*', // Pattern - may need custom implementation
                'user_login_stats_*'       // Pattern - may need custom implementation
            ];

            Cache::forget('dashboard_stats_cache');
            Cache::forget('actual_date_range');
            
            // If using cache tags (Redis/Memcached)
            try {
                Cache::tags(['dashboard', 'user_stats', 'signin_logs'])->flush();
                Log::info('Dashboard cache cleared using tags');
            } catch (\Exception $e) {
                // Fallback for drivers that don't support tags
                Cache::flush(); // This is aggressive but ensures consistency
                Log::info('Dashboard cache cleared with full flush (tag support unavailable)');
            }

            Log::info('Dashboard cache invalidated after signin import', [
                'imported_records' => $this->stats['inserted'],
                'users_created' => $this->stats['users_created']
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to invalidate dashboard cache', [
                'error' => $e->getMessage(),
                'stats' => $this->stats
            ]);
        }
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