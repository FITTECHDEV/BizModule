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
        'Import-Unknown',
        'Unknown', 
        'Microsoft Teams',
        'Office 365',
        'Windows Authentication',  
        'IT Infrastructure',       
        'FITerp UAT',             
        'Web Browser',           
        'Mobile/Desktop Client'
    ];

    /**
     * IMPROVED: Get available systems from database, excluding unwanted ones
     */
    private function getAvailableSystems()
    {
        return Cache::remember('available_systems_clean_v2', 3600, function () {
            // Get all unique systems from the database
            $rawSystems = DB::table('Azure_LoginDetails')
                ->selectRaw('TRIM(system) as clean_system, COUNT(*) as count')
                ->whereNotNull('system')
                ->where('system', '!=', '')
                ->groupBy(DB::raw('TRIM(system)'))
                ->orderByRaw('COUNT(*) DESC')
                ->get();

            // Apply systemmap normalization and exclude unwanted systems
            $systems = $rawSystems
                ->map(function($item) {
                    $normalized = $this->normalizeSystemName($item->clean_system);
                    return $normalized;
                })
                ->unique()
                ->filter(function($system) {
                    return !in_array($system, $this->excludedSystems);
                })
                ->values()
                ->toArray();
            
            Log::info('Clean available systems loaded with enhanced filtering', [
                'count' => count($systems),
                'systems' => $systems,
                'excluded' => $this->excludedSystems
            ]);
            
            return $systems;
        });
    }

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

    /**
     * IMPROVED: Enhanced user query with search-safe logic
     * 
     * SEARCH BEHAVIOR (Production-Safe):
     * - When searching: Query skips system filtering JOIN, finds all matching users
     * - When NOT searching: Query uses INNER JOIN, shows only system users
     * - Sign-ins relationship: Always filtered by selected system
     * 
     * This means search results include ALL users matching the query,
     * but their displayed sign-ins are still filtered by the selected system.
     */
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
            $system = $this->validateSystemSelection($request->input('system', 'all'));
            $search = $request->input('search');
            $date = $request->input('date'); // Add custom date support

            Log::info('Dashboard query with enhanced system filtering v3 and search-safe logic', [
                'selected_system' => $system,
                'range' => $range,
                'custom_date' => $date,
                'search_active' => !empty($search),
                'excluded_systems' => $this->excludedSystems
            ]);

            // Get actual date range from clean data
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
            
            // Calculate intended date ranges with custom date support
            if ($range === 'custom' && $date) {
                // For custom date, use the specific date provided
                $startDate = Carbon::parse($date)->startOfDay();
                $endDate = Carbon::parse($date)->endOfDay();
            } else {
                // Use predefined ranges
                $startDate = match ($range) {
                    'this_month' => now()->startOfMonth(),
                    'last_month' => now()->subMonth()->startOfMonth(),
                    'last_3_months' => now()->subMonths(3)->startOfDay(),
                    'last_6_months' => now()->subMonths(6)->startOfDay(),
                    'last_year' => now()->subYear()->startOfDay(),
                    'all_time' => $actualDateRange['min_date'],
                    default => now()->subMonths(3)->startOfDay(),
                };
                
                $endDate = match ($range) {
                    'this_month' => now()->endOfMonth(),
                    'last_month' => now()->subMonth()->endOfMonth(),
                    'last_3_months' => now()->endOfDay(),
                    'last_6_months' => now()->endOfDay(),
                    'last_year' => now()->endOfDay(),
                    'all_time' => $actualDateRange['max_date'],
                    default => now()->endOfDay(),
                };
            }

            // Only constrain dates for 'all_time', not for custom dates
            if ($range === 'all_time') {
                $startDate = max($startDate, $actualDateRange['min_date']);
                $endDate = min($endDate, $actualDateRange['max_date']);
                
                if ($startDate > $endDate) {
                    $startDate = $actualDateRange['min_date'];
                    $endDate = $actualDateRange['max_date'];
                }
            }

            // For custom dates, check if the date has data
            $hasDataInPeriod = $range === 'custom' ? 
                ($startDate >= $actualDateRange['min_date'] && $startDate <= $actualDateRange['max_date']) :
                ($startDate >= $actualDateRange['min_date'] && $startDate <= $actualDateRange['max_date']);

            if (!$hasDataInPeriod && $range !== 'all_time' && $range !== 'custom') {
                Log::info('Selected date range has no data - will show empty results', [
                    'range' => $range,
                    'selected_start' => $startDate->toDateString(),
                    'selected_end' => $endDate->toDateString(),
                    'actual_data_range' => [
                        'min' => $actualDateRange['min_date']->toDateString(),
                        'max' => $actualDateRange['max_date']->toDateString()
                    ]
                ]);
            }

            $totalDays = $startDate && $endDate ? $startDate->diffInDays($endDate) + 1 : 365;

            Log::info('Dashboard using selected date range with calendar support', [
                'range' => $range,
                'system' => $system,
                'custom_date' => $date,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'has_data_in_period' => $hasDataInPeriod,
                'excluded_systems' => $this->excludedSystems
            ]);

            // IMPROVED: Search-safe user query logic
            if ($system && $system !== 'all') {
                $query = AzureUser::valid()
                    ->select('Azure_Users.id', 'Azure_Users.userPrincipalName', 'Azure_Users.displayName', 'Azure_Users.mail');

                // KEY FIX: Only use JOIN when NOT searching
                // This allows search to find all users, regardless of their system activity
                if (!$search) {
                    Log::info('Dashboard: Non-search mode - using INNER JOIN to show only system users', [
                        'system' => $system
                    ]);
                    
                    $query->join('Azure_LoginDetails as ald_filter', 'Azure_Users.id', '=', 'ald_filter.user_id')
                          ->whereNotIn('ald_filter.system', $this->excludedSystems);
                } else {
                    Log::info('Dashboard: Search mode - no JOIN to find all matching users', [
                        'search_term' => $search,
                        'system_filter_note' => 'Sign-ins will still be filtered by system'
                    ]);
                }

                // Apply system filtering to sign-ins relationship (always applied)
                $query = $query->with(['signIns' => function ($q) use ($startDate, $endDate, $system) {
                    if ($startDate && $endDate) {
                        $q->whereBetween('date_utc', [$startDate, $endDate]);
                    }
                    $this->applySystemFilter($q, $system);
                    $q->whereNotIn('system', $this->excludedSystems);
                    $q->orderBy('date_utc', 'desc');
                }]);

                // GROUP BY only needed when using JOIN (non-search mode)
                if (!$search) {
                    $query->groupBy('Azure_Users.id', 'Azure_Users.userPrincipalName', 'Azure_Users.displayName', 'Azure_Users.mail');
                }
            } else {
                // For 'all' systems: show all users but filter sign-ins by date range and excluded systems
                $query = AzureUser::valid()
                    ->select('Azure_Users.id', 'Azure_Users.userPrincipalName', 'Azure_Users.displayName', 'Azure_Users.mail')
                    ->with(['signIns' => function ($q) use ($startDate, $endDate) {
                        if ($startDate && $endDate) {
                            $q->whereBetween('date_utc', [$startDate, $endDate]);
                        }
                        $q->whereNotIn('system', $this->excludedSystems);
                        $q->orderBy('date_utc', 'desc');
                    }]);
            }

            // Add search functionality - applied to all users regardless of system
            if ($search) {
                $search = trim(strip_tags($search));
                if (strlen($search) > 255) {
                    $search = substr($search, 0, 255);
                }
                
                Log::info('Applying search filter - will find all matching users', [
                    'search_term' => $search,
                    'system' => $system,
                    'note' => 'Stats are based on system activity, not search results'
                ]);
                
                $query->where(function ($q) use ($search) {
                    $q->where('Azure_Users.displayName', 'LIKE', "%{$search}%")
                      ->orWhere('Azure_Users.userPrincipalName', 'LIKE', "%{$search}%")
                      ->orWhere('Azure_Users.mail', 'LIKE', "%{$search}%");
                });
            }

            $perPage = 17;
            $users = $query->paginate($perPage)->withQueryString();

            Log::info('User query executed', [
                'total_results' => $users->total(),
                'current_page' => $users->currentPage(),
                'system' => $system,
                'search_active' => !empty($search)
            ]);

            // Calculate stats with improved method
            try {
                $stats = $this->calculateStats($startDate, $endDate, $system, $search);
            } catch (\Exception $e) {
                Log::error('Failed to calculate dashboard stats', [
                    'error' => $e->getMessage(),
                    'system' => $system,
                    'date_range' => [$startDate->toDateString(), $endDate->toDateString()]
                ]);
                
                // Provide fallback stats
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

            // Get clean systems list (excludes unwanted systems)
            $systems = $this->getAvailableSystems();

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
                                    'system' => $this->normalizeSystemName($signIn->system),
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
                            'system' => $this->normalizeSystemName($signIn->system),
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
                        'data_available' => $hasDataInPeriod
                    ],
                    'selectedDate' => $date,
                    'excludedSystems' => $this->excludedSystems
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
                'selectedDate' => $date,
                'actualDateRange' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                    'data_available' => $hasDataInPeriod
                ],
                'excludedSystems' => $this->excludedSystems
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
     * IMPROVED: Apply enhanced system filter with documentation
     * 
     * SEARCH BEHAVIOR NOTE:
     * When search is active, users may appear in results even if they don't match
     * this system filter. The sign-ins relationship will still apply this filter,
     * so only their sign-ins for the selected system will be displayed.
     * 
     * When search is NOT active, only users with sign-ins in this system appear.
     */
    private function applySystemFilter($query, $systemName)
    {
        if (empty($systemName) || $systemName === 'all') {
            Log::debug('No system filter applied (all systems mode)');
            return $query;
        }

        $rawSystemNames = $this->getRawSystemNames($systemName);
        
        Log::debug('Applying system filter to sign-ins', [
            'clean_system_name' => $systemName,
            'raw_variations_count' => count($rawSystemNames)
        ]);
        
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
     * IMPROVED: System-filtered stats calculation with search-aware documentation
     * 
     * IMPORTANT BEHAVIORAL NOTE:
     * When a user performs a search, this method counts users based on their ACTUAL activity
     * in the date range, NOT based on search results. This means:
     * 
     * Example:
     * - User searches for "John Smith" with system "SCM" selected
     * - John appears in the user table (search found him)
     * - But if John has no SCM sign-ins in the date range, he's NOT counted in "Logged In Users"
     * - This is INTENTIONAL - stats reflect actual system usage, not search matches
     * 
     * The view should display this clearly to users (e.g., "Users with activity in this system")
     */
    private function calculateStats($startDate, $endDate, $system, $search = null)
    {
        $cacheKey = 'dashboard_stats_fixed_v3_' . md5($startDate->format('Y-m-d') . $endDate->format('Y-m-d') . $system);
        
        return Cache::remember($cacheKey, 1800, function () use ($startDate, $endDate, $system, $search) {
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
                    
                    Log::info('System-specific stats calculated (search does not affect these counts)', [
                        'total_users_in_system' => $totalUsers,
                        'logged_in_in_period' => $loggedInCount,
                        'system' => $system,
                        'search_active' => !empty($search),
                        'note' => 'Stats based on actual system activity, not search results'
                    ]);
                    
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
                    
                    Log::info('All-systems stats calculated', [
                        'total_users' => $totalUsers,
                        'logged_in_in_period' => $loggedInCount
                    ]);
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

                $signIns = $signInsRaw->map(function($item) {
                    return (object)[
                        'id' => $item->request_id,
                        'system' => $this->normalizeSystemName($item->system),
                        'date_utc' => \Carbon\Carbon::parse($item->date_utc),
                        'application' => $item->application ?? 'N/A',
                        'username' => $item->username ?? 'N/A'
                    ];
                });

                $totalQueryTime = microtime(true) - $queryStartTime;

                Log::info('Dashboard stats calculated with search-safe system matching', [
                    'total_users' => $totalUsers,
                    'logged_in_count' => $loggedInCount,
                    'not_logged_in_count' => $notLoggedInCount,
                    'total_logins' => $totalLogins,
                    'system' => $system,
                    'excluded_systems' => $this->excludedSystems,
                    'query_time_ms' => round($totalQueryTime * 1000, 2),
                    'date_range' => [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')],
                    'system_specific' => ($system !== 'all'),
                    'search_active' => !empty($search)
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
                Log::error('Stats calculation failed', [
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

            Log::info('Clean date range calculated', [
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

    /**
     * Optimized logged in users count
     */
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
            Log::info('Dashboard cache cleared using tags');
        } catch (\Exception $e) {
            // Fallback for drivers without tag support
            Log::info('Cache tags not supported, cleared individual keys');
        }
        
        return response()->json([
            'message' => 'Dashboard cache cleared successfully',
            'excluded_systems' => $this->excludedSystems
        ]);
    }

    /**
     * IMPROVED: System selection validation with search-aware documentation
     * 
     * SEARCH BEHAVIOR NOTE:
     * When a user searches, the system selection is still validated against
     * available systems. However, the search results may include users who
     * don't have any activity in the selected system - this is INTENTIONAL
     * and allows cross-system user lookups.
     */
    private function validateSystemSelection($system)
    {
        if ($system === 'all') {
            return $system;
        }
        
        $availableSystems = $this->getAvailableSystems();
        if (!in_array($system, $availableSystems)) {
            Log::warning('Invalid system selection - fallback to all', [
                'requested_system' => $system,
                'available_systems' => $availableSystems,
                'note' => 'Search functionality may still find users outside available systems'
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

    /**
     * Debug data endpoint
     */
    public function debugData(Request $request)
    {
        $system = $this->validateSystemSelection($request->input('system', 'SCM'));
        $range = $request->input('range', 'last_3_months');
        
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

    /**
     * Diagnose data relationships
     */
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
            $cacheKeys = [
                'dashboard_stats_cache',
                'actual_date_range'
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            
            try {
                Cache::tags(['dashboard', 'user_stats', 'signin_logs'])->flush();
                Log::info('Dashboard cache cleared using tags');
            } catch (\Exception $e) {
                Cache::flush();
                Log::info('Dashboard cache cleared with full flush');
            }

            Log::info('Dashboard cache invalidated after operation');

        } catch (\Exception $e) {
            Log::error('Failed to invalidate dashboard cache', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Debug relationships
     */
    public function debugRelationships(Request $request) 
    {
        $system = $request->input('system', 'SCM');
        
        $diagnostics = [
            'total_users' => \DB::select("SELECT COUNT(*) as count FROM Azure_Users")[0]->count,
            'total_logins' => \DB::select("SELECT COUNT(*) as count FROM Azure_LoginDetails")[0]->count,
            'logins_with_user_id' => \DB::select("SELECT COUNT(*) as count FROM Azure_LoginDetails WHERE user_id IS NOT NULL")[0]->count,
            'logins_with_username' => \DB::select("SELECT COUNT(*) as count FROM Azure_LoginDetails WHERE username IS NOT NULL")[0]->count,
            'system_logins' => \DB::select("SELECT COUNT(*) as count FROM Azure_LoginDetails WHERE system = ?", [$system])[0]->count,
        ];
        
        $sampleLogins = \DB::select("SELECT TOP 5 user_id, username, system, date_utc FROM Azure_LoginDetails WHERE system = ? ORDER BY date_utc DESC", [$system]);
        $sampleUsers = \DB::select("SELECT TOP 5 id, userPrincipalName, displayName FROM Azure_Users");
        
        return response()->json([
            'diagnostics' => $diagnostics,
            'sample_logins' => $sampleLogins,
            'sample_users' => $sampleUsers
        ], 200, [], JSON_PRETTY_PRINT);
    }
}