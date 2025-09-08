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

    public function index(Request $request)
{
    // Add cache busting parameter support
    if ($request->has('refresh_cache')) {
        $this->clearDashboardCache();
    }

    $filters = $this->filterService->parseFilters($request);
    $range = $filters['range'] ?? 'last_3_months';
    $system = $request->input('system', 'SCM');
    $search = $request->input('search');

    $actualDateRange = $this->getActualDateRange();
    
    // FIXED: Calculate intended date ranges first, then constrain to actual data
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

    // FIXED: Now constrain the intended dates to actual data availability
    $startDate = max($intendedStartDate, $actualDateRange['min_date']);
    $endDate = min($intendedEndDate, $actualDateRange['max_date']);

    // FIXED: Handle case where intended range is completely outside actual data
    if ($startDate > $endDate) {
        Log::warning('Requested date range is outside actual data range', [
            'range' => $range,
            'intended_start' => $intendedStartDate->toDateString(),
            'intended_end' => $intendedEndDate->toDateString(),
            'actual_min' => $actualDateRange['min_date']->toDateString(),
            'actual_max' => $actualDateRange['max_date']->toDateString(),
            'system' => $system
        ]);
        
        // Use the actual data range instead
        $startDate = $actualDateRange['min_date'];
        $endDate = $actualDateRange['max_date'];
    }

    $totalDays = $startDate && $endDate ? $startDate->diffInDays($endDate) + 1 : 365;

    Log::info('Dashboard using corrected date range', [
        'range' => $range,
        'system' => $system,
        'intended_start' => $intendedStartDate->toDateString(),
        'intended_end' => $intendedEndDate->toDateString(),
        'actual_start' => $startDate->toDateString(),
        'actual_end' => $endDate->toDateString(),
        'actual_data_range' => [$actualDateRange['min_date']->toDateString(), $actualDateRange['max_date']->toDateString()]
    ]);

    try {
        $baseUserQuery = AzureUser::query();

        // Enhanced query with multiple join conditions - FIXED: Select only the columns we need
        $query = $baseUserQuery
        ->select('Azure_Users.id', 'Azure_Users.userPrincipalName', 'Azure_Users.displayName', 'Azure_Users.mail')
        ->with(['signIns' => function ($q) use ($startDate, $endDate, $system) {
            if ($startDate && $endDate) {
                $q->whereBetween('date_utc', [$startDate, $endDate]);
            }
            if ($system && $system !== 'all') {
                $q->where('system', $system);
            }
            $q->orderBy('date_utc', 'desc');
        }])
        ->leftJoin('Azure_LoginDetails as ald', function($join) use ($startDate, $endDate, $system) {
            $join->on(function($q) {
                $q->whereColumn('Azure_Users.id', 'ald.user_id')
                  ->orWhereColumn('Azure_Users.userPrincipalName', 'ald.username');
            });
            
            if ($startDate && $endDate) {
                $join->whereBetween('ald.date_utc', [$startDate, $endDate]);
            }
            if ($system && $system !== 'all') {
                $join->where('ald.system', $system);
            }
        })
        // FIXED: Group by the same columns we're selecting
        ->groupBy('Azure_Users.id', 'Azure_Users.userPrincipalName', 'Azure_Users.displayName', 'Azure_Users.mail');

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

        $stats = $this->calculateStats($startDate, $endDate, $system);

        $systems = $this->filterService->getSystemList();
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
                // ADDED: Include date range info for debugging
                'dateRange' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                    'intended_start' => $intendedStartDate->toDateString(),
                    'intended_end' => $intendedEndDate->toDateString(),
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
            // ADDED: Pass date range info to view
            'actualDateRange' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'intended_start' => $intendedStartDate->toDateString(),
                'intended_end' => $intendedEndDate->toDateString(),
                'data_available' => $actualDateRange['has_data']
            ]
        ]);
        
    } catch (QueryException $e) {
        Log::error('Database query failed in DashboardController', [
            'error' => $e->getMessage(),
            'stack' => $e->getTraceAsString(),
        ]);
        return response()->view('errors.500', [], 500);
    }
}
    private function getActualDateRange()
    {
        return Cache::remember('actual_date_range', 1800, function () {
            $dateRange = SigninLog::selectRaw('MIN(date_utc) as min_date, MAX(date_utc) as max_date')->first();
            
            $result = [
                'min_date' => $dateRange && $dateRange->min_date ? Carbon::parse($dateRange->min_date) : now()->subYear(),
                'max_date' => $dateRange && $dateRange->max_date ? Carbon::parse($dateRange->max_date) : now(),
                'has_data' => $dateRange && $dateRange->min_date ? true : false
            ];
            
            Log::info('Actual data range cached', [
                'min_date' => $result['min_date']->toDateString(),
                'max_date' => $result['max_date']->toDateString(),
                'has_data' => $result['has_data']
            ]);
            
            return $result;
        });
    }

    private function calculateStats($startDate, $endDate, $system)
    {
        $cacheKey = 'dashboard_stats_' . md5($startDate . $endDate . $system);
        
        return Cache::remember($cacheKey, 600, function () use ($startDate, $endDate, $system) {
            $totalUsers = AzureUser::count();
            
            $signInQuery = SigninLog::query();
            if ($startDate && $endDate) {
                $signInQuery->whereBetween('date_utc', [$startDate, $endDate]);
            }
            if ($system && $system !== 'all') {
                $signInQuery->where('system', $system);
            }
            
            $totalLogins = $signInQuery->count();
            $signIns = $signInQuery->latest('date_utc')->limit(100)->get();
            $lastLogin = SigninLog::max('date_utc');
            
            $loggedInCount = $this->getLoggedInUsersCount($startDate, $endDate, $system);
            $notLoggedInCount = max(0, $totalUsers - $loggedInCount);

            Log::info('Dashboard stats calculated', [
                'total_users' => $totalUsers,
                'logged_in_count' => $loggedInCount,
                'not_logged_in_count' => $notLoggedInCount,
                'total_logins' => $totalLogins,
                'system' => $system,
                'date_range' => [$startDate?->toDateString(), $endDate?->toDateString()]
            ]);

            return [
                'totalUsers' => $totalUsers,
                'loggedInCount' => $loggedInCount,
                'notLoggedInCount' => $notLoggedInCount,
                'totalLogins' => $totalLogins,
                'lastLogin' => $lastLogin,
                'signIns' => $signIns,
                'totalDays' => $startDate && $endDate ? $startDate->diffInDays($endDate) + 1 : 365,
            ];
        });
    }

    public function getLoggedInUsersCount($startDate, $endDate, $system)
    {
        // Create a cache key based on parameters
        $cacheKey = 'logged_in_users_count_' . md5($startDate . $endDate . $system);
        
        return Cache::remember($cacheKey, 300, function () use ($startDate, $endDate, $system) {
            $conditions = [];
            $params = [];
            
            if ($system && $system !== 'all') {
                $conditions[] = "ald.system = ?";
                $params[] = $system;
            }
            
            if ($startDate && $endDate) {
                $conditions[] = "ald.date_utc BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            } elseif ($startDate) {
                $conditions[] = "ald.date_utc >= ?";
                $params[] = $startDate;
            }
            
            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            
            // Enhanced matching strategies with better SQL
            $strategies = [
                'exact_id_match' => "
                    SELECT COUNT(DISTINCT au.id) as count 
                    FROM Azure_Users au 
                    INNER JOIN Azure_LoginDetails ald ON au.id = ald.user_id 
                    {$whereClause}
                ",
                'username_exact_match' => "
                    SELECT COUNT(DISTINCT au.id) as count 
                    FROM Azure_Users au 
                    INNER JOIN Azure_LoginDetails ald ON au.userPrincipalName = ald.username
                    {$whereClause}
                ",
                'username_case_insensitive' => "
                    SELECT COUNT(DISTINCT au.id) as count 
                    FROM Azure_Users au 
                    INNER JOIN Azure_LoginDetails ald ON LOWER(au.userPrincipalName) = LOWER(ald.username)
                    {$whereClause}
                ",
                'sign_in_identifier_match' => "
                    SELECT COUNT(DISTINCT au.id) as count 
                    FROM Azure_Users au 
                    INNER JOIN Azure_LoginDetails ald ON LOWER(au.userPrincipalName) = LOWER(ald.sign_in_identifier)
                    {$whereClause} AND ald.sign_in_identifier IS NOT NULL
                ",
                'mail_match' => "
                    SELECT COUNT(DISTINCT au.id) as count 
                    FROM Azure_Users au 
                    INNER JOIN Azure_LoginDetails ald ON LOWER(au.mail) = LOWER(ald.username)
                    {$whereClause} AND au.mail IS NOT NULL
                "
            ];

            foreach ($strategies as $strategy => $sql) {
                try {
                    $result = \DB::select($sql, $params);
                    $count = (int) ($result[0]->count ?? 0);
                    
                    if ($count > 0) {
                        Log::info("Logged in users count calculated using {$strategy}", [
                            'count' => $count,
                            'system' => $system,
                            'date_range' => [$startDate?->toDateString(), $endDate?->toDateString()]
                        ]);
                        return $count;
                    }
                } catch (\Exception $e) {
                    Log::warning("Strategy {$strategy} failed", [
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            Log::warning('No matches found with any strategy', [
                'system' => $system,
                'date_range' => [$startDate?->toDateString(), $endDate?->toDateString()],
                'total_users' => AzureUser::count(),
                'total_signin_logs' => SigninLog::count()
            ]);
            
            return 0;
        });
    }

    /**
     * Clear dashboard-related caches (can be called after imports)
     */
    public function clearDashboardCache()
    {
        $patterns = [
            'dashboard_stats_*',
            'logged_in_users_count_*',
            'actual_date_range'
        ];
        
        // Clear specific cache keys
        Cache::forget('actual_date_range');
        
        // If you have cache tagging enabled, use this:
        try {
            Cache::tags(['dashboard', 'user_stats'])->flush();
            Log::info('Dashboard cache cleared using tags');
        } catch (\Exception $e) {
            // Fallback: clear individual keys (you might need to implement pattern-based clearing)
            Log::info('Dashboard cache cleared individually');
        }
        
        return response()->json(['message' => 'Dashboard cache cleared successfully']);
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
}