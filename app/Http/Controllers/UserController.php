<?php

namespace App\Http\Controllers;

use App\Models\Logintracker\AzureUser;
use App\Models\Logintracker\SigninLog;
use App\Models\Logintracker\System;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

    /**
 * System names to hide from dropdowns / filfters.
 * Stored lowercase for robust comparisons.
 *
 * @var string[]
 */
protected $excludedSystems = [
    // 'Import-Unknown',
    // 'Unknown', 
    // 'Microsoft Teams',
    // 'Office 365',
    // 'Windows Authentication',  
    // 'IT Infrastructure',       
    // 'FITerp UAT',             
    // 'Web Browser',           
    // 'Mobile/Desktop Client'
    // 'all'
];


   /**
     * Specific systems to display in the monthly summary table.
     * These will be the only columns shown in the Login Activity by System table.
     *
     * @var string[]
     */
    protected $displaySystems = [
        'BIZ PRO',
        'BIZ UAT',
        'D365 LIVE',
        'D365 UAT',
        'FIT Express',
        'FIT Express UAT',
        'Odoo',
        'OPS',
        'OPS UAT',
        'Windows Sign In'
    ];


/**
 * Return visible systems (excludes configured names) — case-insensitive.
 *
 * @return array
 */
// protected function visibleSystems(): array
// {
//     // Use LOWER(name) so matching is case-insensitive
//     return System::whereNotIn(DB::raw('LOWER(name)'), $this->excludedSystems)
//         ->orderBy('name')
//         ->pluck('name')
//         ->toArray();
// }

    protected $perPage = 17;

    public function create()
    {
        return view('logintracker.users.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|string|unique:Azure_Users,id|uuid',
                'userPrincipalName' => 'required|string|unique:Azure_Users,userPrincipalName|max:72',
                'displayName' => 'nullable|string|max:23',
                'surname' => 'nullable|string|max:14',
                'mail' => 'nullable|email|max:39',
                'alternateEmailAddress' => 'nullable|email|max:40',
                'givenName' => 'nullable|string|max:15',
                'userType' => 'nullable|string|max:6',
                'jobTitle' => 'nullable|string|max:36',
                'department' => 'nullable|string|max:22',
                'accountEnabled' => 'boolean',
            ]);

            AzureUser::create($validated + ['createdDateTime' => now()->format('Y-m-d H:i:s')]);

            return redirect()->route('dashboard')->with('success', 'User added successfully!');
        } catch (ValidationException $e) {
            Log::warning('Validation error in store', [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Error storing user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'An unexpected error occurred.');
        }
    }

    public function destroy($id)
    {
        try {
            if (!Str::isUuid($id)) {
                Log::warning('Invalid UUID format in destroy', ['id' => $id]);
                return redirect()->route('dashboard')->with('error', 'Invalid ID format.');
            }

            $user = AzureUser::find($id);
            if (!$user) {
                Log::notice('User not found in destroy', ['id' => $id]);
                return redirect()->route('dashboard')->with('error', 'User not found.');
            }

            $user->delete();
            return redirect()->route('dashboard')->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting user', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'An unexpected error occurred.');
        }
    }

  public function index(Request $request)
{
    try {
        [$start, $end] = $this->resolveDateRange($request);
        $rangeInput = $request->input('range', 'this_month');
        $system = $request->input('system');
        $search = $request->input('search');

        // Helper function to get raw system names (same as other methods)
        $getRawSystemNames = function($cleanSystemName) {
            $systemMap = config('systemmap', []);
            
            if (empty($systemMap)) {
                Log::warning('System map configuration not found');
                return [$cleanSystemName];
            }
            
            $rawNames = collect($systemMap)
                ->filter(fn($mappedName) => $mappedName === $cleanSystemName)
                ->keys()
                ->toArray();
            
            if (empty($rawNames)) {
                return [$cleanSystemName];
            }
            
            return $rawNames;
        };

        // FIXED: System filtering helper function (same as other methods)
        $applySystemFilter = function($query, $systemName) use ($getRawSystemNames) {
            if (empty($systemName)) {
                return $query;
            }
            
            $rawSystemNames = $getRawSystemNames($systemName);
            
            return $query->where(function ($q) use ($rawSystemNames, $systemName) {
                $q->where(DB::raw('LOWER(application)'), strtolower($systemName));
                
                foreach ($rawSystemNames as $rawSystem) {
                    $q->orWhere(DB::raw('LOWER(application)'), strtolower($rawSystem))
                      ->orWhere('application', 'LIKE', "%{$rawSystem}%");
                }
                
                $q->orWhere(DB::raw('LOWER(application)'), 'like', '%' . strtolower($systemName) . '%');
            });
        };

        if ($request->ajax()) {
            DB::enableQueryLog();
            Log::info('AJAX Request for index', [
                'range' => $rangeInput,
                'system' => $system,
                'search' => $search,
                'start' => $start->toDateTimeString(),
                'end' => $end->toDateTimeString(),
            ]);

            $query = AzureUser::valid();

            if ($request->filled('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('displayName', 'like', "%{$search}%")
                      ->orWhere('userPrincipalName', 'like', "%{$search}%")
                      ->orWhere('mail', 'like', "%{$search}%")
                      ->orWhere('alternateEmailAddress', 'like', "%{$search}%");
                });
            }

            if ($system) {
                $query->whereHas('systems', function ($q) use ($system) {
                    $q->where('name', $system);
                });
            } else {
                $query->whereHas('signIns', function ($q) use ($start, $end) {
                    $q->whereBetween('date_utc', [$start, $end])
                      ->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems);
                });
            }

            // FIXED: Apply proper system filtering with exclusions
            $query->withCount(['signIns as sign_ins_count' => function ($q) use ($start, $end, $system, $applySystemFilter) {
                $q->whereBetween('date_utc', [$start, $end])
                  ->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems);
                if ($system) {
                    $applySystemFilter($q, $system);
                }
            }])
            ->with(['signIns' => function ($q) use ($start, $end, $system, $applySystemFilter) {
                $q->whereBetween('date_utc', [$start, $end])
                  ->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems);
                if ($system) {
                    $applySystemFilter($q, $system);
                }
                $q->latest('date_utc');
            }]);

            $users = $query->paginate($this->perPage)->withQueryString();
            $totalDays = $start->diffInDays($end) + 1;

            // FIXED: Apply system filtering and exclusions to login query
            $logins = SigninLog::whereBetween('date_utc', [$start, $end])
                ->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems)
                ->when($system, function ($query) use ($system, $applySystemFilter) {
                    return $applySystemFilter($query, $system);
                })
                ->get();
                
            $loggedInEmails = $logins->pluck('username')->filter()->unique()->map(fn($email) => strtolower(trim($email)));
            $totalUsers = AzureUser::valid()->count();
            $loggedInCount = $loggedInEmails->isEmpty() ? 0 : AzureUser::valid()->whereIn(DB::raw('LOWER(userPrincipalName)'), $loggedInEmails)->count();
            $notLoggedInCount = $totalUsers - $loggedInCount;

            $systems = $this->visibleSystems();
            Log::info('Systems for Dropdown', ['systems' => $systems]);

            $mappedUsers = array_map(function ($user) {
                return [
                    'id' => $user->id,
                    'displayName' => $user->displayName,
                    'userPrincipalName' => $user->userPrincipalName,
                    'mail' => $user->mail,
                    'alternateEmailAddress' => $user->alternateEmailAddress,
                    'signIns' => $user->signIns->toArray(),
                    'sign_ins_count' => $user->sign_ins_count,
                ];
            }, $users->items());

            Log::debug('Executed Queries', DB::getQueryLog());
            Log::debug('AJAX Response Users', [
                'user_ids' => array_column($mappedUsers, 'id'),
                'sample_users' => array_slice($mappedUsers, 0, 5)
            ]);
            DB::disableQueryLog();

            return response()->json([
                'totalUsers' => $totalUsers,
                'loggedInCount' => $loggedInCount,
                'notLoggedInCount' => $notLoggedInCount,
                'users' => $mappedUsers,
                'totalDays' => $totalDays,
                'pagination' => (string) $users->links(),
            ]);
        }

        // FIXED: Non-AJAX path with same system filtering logic
        $query = AzureUser::valid();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('displayName', 'like', "%{$search}%")
                  ->orWhere('userPrincipalName', 'like', "%{$search}%")
                  ->orWhere('mail', 'like', "%{$search}%")
                  ->orWhere('alternateEmailAddress', 'like', "%{$search}%");
            });
        }

        if ($system) {
            $query->whereHas('systems', function ($q) use ($system) {
                $q->where('name', $system);
            });
        } else {
            $query->whereHas('signIns', function ($q) use ($start, $end) {
                $q->whereBetween('date_utc', [$start, $end])
                  ->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems);
            });
        }

        $query->withCount(['signIns as sign_ins_count' => function ($q) use ($start, $end, $system, $applySystemFilter) {
            $q->whereBetween('date_utc', [$start, $end])
              ->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems);
            if ($system) {
                $applySystemFilter($q, $system);
            }
        }])
        ->with(['signIns' => function ($q) use ($start, $end, $system, $applySystemFilter) {
            $q->whereBetween('date_utc', [$start, $end])
              ->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems);
            if ($system) {
                $applySystemFilter($q, $system);
            }
            $q->latest('date_utc');
        }]);

        $users = $query->paginate($this->perPage)->withQueryString();
        
        $logins = SigninLog::whereBetween('date_utc', [$start, $end])
            ->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems)
            ->when($system, function ($query) use ($system, $applySystemFilter) {
                return $applySystemFilter($query, $system);
            })
            ->get();
            
        $loggedInEmails = $logins->pluck('username')->filter()->unique()->map(fn($email) => strtolower(trim($email)));
        $totalUsers = AzureUser::valid()->count();
        $loggedInCount = $loggedInEmails->isEmpty() ? 0 : AzureUser::valid()->whereIn(DB::raw('LOWER(userPrincipalName)'), $loggedInEmails)->count();
        $notLoggedInCount = $totalUsers - $loggedInCount;

        $systems = $this->visibleSystems();
        Log::info('Systems for Dropdown (Non-AJAX)', ['systems' => $systems]);

        $mappedUsers = array_map(function ($user) {
            return [
                'id' => $user->id,
                'displayName' => $user->displayName,
                'userPrincipalName' => $user->userPrincipalName,
                'mail' => $user->mail,
                'alternateEmailAddress' => $user->alternateEmailAddress,
                'signIns' => $user->signIns->toArray(),
                'sign_ins_count' => $user->sign_ins_count,
            ];
        }, $users->items());

        $rangeLabel = $this->getRangeLabel($rangeInput);
        $systemInput = $system ?: 'All Systems';

        return view('logintracker.dashboard', compact('mappedUsers', 'loggedInCount', 'notLoggedInCount', 'rangeInput', 'system', 'totalUsers', 'rangeLabel', 'systemInput', 'systems'));
    } catch (\Exception $e) {
        Log::error('Error in index method', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        if ($request->ajax()) {
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
        return redirect()->back()->with('error', 'An unexpected error occurred.');
    }
}





public function show(Request $request, $identifier)
{
    Log::info('UserController@show called', [
        'identifier'    => $identifier,
        'request_url'   => $request->fullUrl(),
        'request_input' => $request->all(),
    ]);

    try {
        // ====================================================================
        // 1. RESOLVE USER
        // ====================================================================
        $user = AzureUser::findByIdentifier($identifier);
        if (!$user) {
            Log::notice('User not found in show', ['identifier' => $identifier]);
            abort(404, 'User not found.');
        }

        // ====================================================================
        // 2. RESOLVE DATE RANGE
        // ====================================================================
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate && strtotime($startDate) && strtotime($endDate)) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            
            if ($end->lt($start)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'End date cannot be before start date.');
            }
        } else {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
        }

        Log::info('Date Range Resolved', [
            'start'  => $start->toDateTimeString(),
            'end'    => $end->toDateTimeString(),
        ]);

        // Load system map once for reuse
        $systemMap = config('systemmap', []);
        
        if (empty($systemMap)) {
            Log::warning('System map configuration is empty or not found');
        }

        // ====================================================================
        // 3. CURRENT MONTH APPLICATIONS - COUNT DAYS (ALWAYS CURRENT MONTH)
        // ====================================================================
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();

        // Get raw data grouped by date and application for CURRENT MONTH
        $currentMonthApplicationsRaw = SigninLog::where('user_id', $user->id)
            ->whereBetween('date_utc', [$currentMonthStart, $currentMonthEnd])
            ->whereNotNull('application')
            ->where('application', '!=', '')
            ->select([
                'application',
                DB::raw('CONVERT(DATE, date_utc) as login_date'),
                DB::raw('MAX(date_utc) as last_used'),
            ])
            ->groupBy('application', DB::raw('CONVERT(DATE, date_utc)'))
            ->get();

        // Map and count unique days per clean system name
        $mappedCurrentMonth = [];

        foreach ($currentMonthApplicationsRaw as $app) {
            $rawAppLower = strtolower(trim($app->application));
            $cleanName = $systemMap[$rawAppLower] ?? $systemMap[$app->application] ?? $app->application;
            
            if (!isset($mappedCurrentMonth[$cleanName])) {
                $mappedCurrentMonth[$cleanName] = [
                    'application' => $cleanName,
                    'dates' => [],
                    'last_used' => $app->last_used
                ];
            }
            
            // Add this date to the array if not already there
            if (!in_array($app->login_date, $mappedCurrentMonth[$cleanName]['dates'])) {
                $mappedCurrentMonth[$cleanName]['dates'][] = $app->login_date;
            }
            
            // Keep the most recent last_used date
            if ($app->last_used > $mappedCurrentMonth[$cleanName]['last_used']) {
                $mappedCurrentMonth[$cleanName]['last_used'] = $app->last_used;
            }
        }

        // Convert to collection with usage_count = number of unique days
        $currentMonthApplications = collect($mappedCurrentMonth)
            ->map(function($item) {
                return (object)[
                    'application' => $item['application'],
                    'usage_count' => count($item['dates']), // Number of days used
                    'last_used' => $item['last_used']
                ];
            })
            ->sortByDesc('last_used')
            ->take(10)
            ->values();

        Log::info('Current Month Applications - DAYS COUNT', [
            'count' => $currentMonthApplications->count(),
            'month' => Carbon::now()->format('F Y'),
            'date_range' => [$currentMonthStart->toDateString(), $currentMonthEnd->toDateString()],
            'details' => $currentMonthApplications->map(function($app) {
                return ['name' => $app->application, 'days_used' => $app->usage_count];
            })->toArray()
        ]);

        // ====================================================================
        // 4. RECENT APPLICATIONS FOR SELECTED PERIOD - COUNT DAYS (DYNAMIC)
        // ====================================================================

        // Get raw data grouped by date and application for SELECTED PERIOD
        $recentApplicationsRaw = SigninLog::where('user_id', $user->id)
            ->whereBetween('date_utc', [$start, $end])
            ->whereNotNull('application')
            ->where('application', '!=', '')
            ->select([
                'application',
                DB::raw('CONVERT(DATE, date_utc) as login_date'),
                DB::raw('MAX(date_utc) as last_used'),
            ])
            ->groupBy('application', DB::raw('CONVERT(DATE, date_utc)'))
            ->get();

        // Map and count unique days per clean system name
        $mappedRecent = [];

        foreach ($recentApplicationsRaw as $app) {
            $rawAppLower = strtolower(trim($app->application));
            $cleanName = $systemMap[$rawAppLower] ?? $systemMap[$app->application] ?? $app->application;
            
            if (!isset($mappedRecent[$cleanName])) {
                $mappedRecent[$cleanName] = [
                    'application' => $cleanName,
                    'dates' => [],
                    'last_used' => $app->last_used
                ];
            }
            
            // Add this date to the array if not already there
            if (!in_array($app->login_date, $mappedRecent[$cleanName]['dates'])) {
                $mappedRecent[$cleanName]['dates'][] = $app->login_date;
            }
            
            // Keep the most recent last_used date
            if ($app->last_used > $mappedRecent[$cleanName]['last_used']) {
                $mappedRecent[$cleanName]['last_used'] = $app->last_used;
            }
        }

        // Convert to collection with usage_count = number of unique days
        $recentApplications = collect($mappedRecent)
            ->map(function($item) {
                return (object)[
                    'application' => $item['application'],
                    'usage_count' => count($item['dates']), // Number of days used
                    'last_used' => $item['last_used']
                ];
            })
            ->sortByDesc('last_used')
            ->take(10)
            ->values();

        Log::info('Recent Applications (Filtered Period) - DAYS COUNT', [
            'count' => $recentApplications->count(),
            'date_range' => [$start->toDateString(), $end->toDateString()],
            'details' => $recentApplications->map(function($app) {
                return ['name' => $app->application, 'days_used' => $app->usage_count];
            })->toArray()
        ]);

        // ====================================================================
        // 5. BUILD MONTHLY SUMMARY - OPTIMIZED AND FIXED
        // ====================================================================
        $monthlySummary = [];
        
        try {
            Log::info('Starting Monthly Summary Build', [
                'date_range' => [$start->toDateString(), $end->toDateString()],
                'display_systems' => $this->displaySystems
            ]);

            if (empty($systemMap)) {
                throw new \Exception('System map configuration is empty or not found');
            }

            // Fetch raw login data with optimized query
            $loginDataRaw = SigninLog::where('user_id', $user->id)
                ->whereBetween('date_utc', [
                    $start->format('Y-m-d 00:00:00'),
                    $end->format('Y-m-d 23:59:59')
                ])
                ->whereNotNull('application')
                ->where('application', '!=', '')
                ->select([
                    DB::raw('CONVERT(DATE, date_utc) as login_date'),
                    DB::raw('LTRIM(RTRIM(LOWER(application))) as application'),
                    DB::raw('COUNT(*) as login_count')
                ])
                ->groupBy(
                    DB::raw('CONVERT(DATE, date_utc)'),
                    DB::raw('LTRIM(RTRIM(LOWER(application)))')
                )
                ->orderBy('login_date')
                ->get();
            
            Log::info('Raw Login Data Retrieved', [
                'total_records' => $loginDataRaw->count(),
                'unique_apps' => $loginDataRaw->pluck('application')->unique()->values()->toArray(),
                'date_range' => [
                    $loginDataRaw->min('login_date') ?? 'N/A',
                    $loginDataRaw->max('login_date') ?? 'N/A'
                ]
            ]);

            // Build optimized reverse lookup: clean system name => array of lowercase raw variations
            $cleanToRawMap = $this->buildCleanToRawMapping($systemMap);

            Log::info('Clean to Raw Mapping Built', [
                'total_mappings' => count($cleanToRawMap),
                'mapping_details' => array_map(function($variations) {
                    return [
                        'count' => count($variations),
                        'samples' => array_slice($variations, 0, 3)
                    ];
                }, $cleanToRawMap)
            ]);

            // Process and map raw data to clean system names
            $processedData = $this->processLoginData($loginDataRaw, $cleanToRawMap);

            Log::info('Processed Data Structure', [
                'dates_with_activity' => count($processedData),
                'total_logins_mapped' => array_sum(array_map(function($systems) {
                    return array_sum($systems);
                }, $processedData)),
                'sample_date_data' => !empty($processedData) ? [
                    'date' => array_key_first($processedData),
                    'systems' => array_keys($processedData[array_key_first($processedData)])
                ] : null
            ]);

            // Build the monthly summary rows using associative arrays
            $monthlySummary = $this->buildMonthlySummaryRows($processedData);
            
            // Sort chronologically
            usort($monthlySummary, function($a, $b) {
                $dateA = Carbon::createFromFormat('d-M-Y', $a['LoginDate']);
                $dateB = Carbon::createFromFormat('d-M-Y', $b['LoginDate']);
                return $dateA <=> $dateB;
            });

            // Collect all systems that have data across all rows
            $systemsWithData = $this->getSystemsWithData($monthlySummary);

            Log::info('Monthly Summary Built Successfully', [
                'total_rows' => count($monthlySummary),
                'date_range' => [$start->format('d/M/Y'), $end->format('d/M/Y')],
                'systems_with_data' => $systemsWithData,
                'first_3_rows' => array_slice(array_map(function($row) {
                    return [
                        'date' => $row['LoginDate'],
                        'systems' => array_filter($row, function($v, $k) {
                            return $k !== 'LoginDate' && $v !== '';
                        }, ARRAY_FILTER_USE_BOTH)
                    ];
                }, $monthlySummary), 0, 3)
            ]);

        } catch (\Exception $e) {
            Log::error('Error building monthly summary', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
            $monthlySummary = [];
        }

        // ====================================================================
        // 6. SIGN-INS QUERY (NO EXCLUSIONS - SHOW ALL APPLICATIONS)
        // ====================================================================
        $signInsQuery = $user->signIns()
            ->whereBetween('date_utc', [$start, $end]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $signInsQuery->where(function ($q) use ($search) {
                $q->where('application', 'like', '%' . $search . '%');
            });
        }

        $signIns = $signInsQuery
            ->orderBy('date_utc', 'desc')
            ->paginate($this->perPage)
            ->withQueryString();

        Log::info('Sign-ins Query', [
            'total' => $signIns->total(),
            'current_page' => $signIns->currentPage(),
        ]);

        // ====================================================================
        // 7. UNIQUE APPS COUNT (ALL APPS, NO EXCLUSIONS)
        // ====================================================================
        $uniqueAppsCount = SigninLog::where('user_id', $user->id)
            ->whereBetween('date_utc', [$start, $end])
            ->whereNotNull('application')
            ->where('application', '!=', '')
            ->distinct('application')
            ->count('application');

        // ====================================================================
        // 8. GENERATE RANGE LABEL
        // ====================================================================
        $rangeLabel = 'Custom Range';
        $range = 'custom';

        Log::info('UserController@show completed successfully', [
            'user_id' => $user->id,
            'date_range' => [$start->toDateString(), $end->toDateString()],
            'sign_ins_count' => $signIns->total(),
            'unique_apps' => $uniqueAppsCount,
            'monthly_summary_rows' => count($monthlySummary)
        ]);

        // ====================================================================
        // 9. RETURN VIEW
        // ====================================================================
        return view('logintracker.users.show', compact(
            'user',
            'signIns',
            'start',
            'end',
            'range',
            'rangeLabel',
            'recentApplications',
            'currentMonthApplications',
            'monthlySummary',
            'uniqueAppsCount'
        ));

    } catch (\Throwable $e) {
        Log::error('UserController@show failed', [
            'identifier' => $identifier,
            'error_message' => $e->getMessage(),
            'error_line' => $e->getLine(),
            'error_file' => $e->getFile(),
            'stack_trace' => $e->getTraceAsString(),
        ]);
        
        return redirect()
            ->route('dashboard')
            ->with('error', 'Unable to load user details. Please try again.');
    }
}

/**
 * Build clean to raw system name mapping
 * 
 * @param array $systemMap
 * @return array
 */
private function buildCleanToRawMapping(array $systemMap): array
{
    $cleanToRawMap = [];
    
    foreach ($this->displaySystems as $cleanName) {
        $cleanToRawMap[$cleanName] = [];
        
        // Find all raw names that map to this clean name (case-insensitive)
        foreach ($systemMap as $rawName => $mappedCleanName) {
            if (strcasecmp(trim($mappedCleanName), trim($cleanName)) === 0) {
                $cleanToRawMap[$cleanName][] = strtolower(trim($rawName));
            }
        }
        
        // Also add the clean name itself as a variation
        $cleanToRawMap[$cleanName][] = strtolower(trim($cleanName));
        
        // Remove duplicates
        $cleanToRawMap[$cleanName] = array_unique($cleanToRawMap[$cleanName]);
    }
    
    return $cleanToRawMap;
}

/**
 * Process raw login data and map to clean system names
 * 
 * @param \Illuminate\Support\Collection $loginDataRaw
 * @param array $cleanToRawMap
 * @return array
 */
private function processLoginData($loginDataRaw, array $cleanToRawMap): array
{
    $processedData = []; // [date][cleanSystem] = count
    $unmatchedApps = [];
    
    foreach ($loginDataRaw as $record) {
        $dateKey = Carbon::parse($record->login_date)->format('Y-m-d');
        $rawApp = $record->application; // Already lowercased and trimmed in query
        $matched = false;
        
        // Try to match this raw app to a clean system
        foreach ($cleanToRawMap as $cleanSystem => $rawVariations) {
            if (in_array($rawApp, $rawVariations, true)) {
                // Match found!
                if (!isset($processedData[$dateKey])) {
                    $processedData[$dateKey] = [];
                }
                if (!isset($processedData[$dateKey][$cleanSystem])) {
                    $processedData[$dateKey][$cleanSystem] = 0;
                }
                
                $processedData[$dateKey][$cleanSystem] += (int)$record->login_count;
                $matched = true;
                
                Log::debug('Application Matched', [
                    'date' => $dateKey,
                    'raw_app' => $record->application,
                    'matched_to' => $cleanSystem,
                    'count' => $record->login_count
                ]);
                
                break; // Stop after first match
            }
        }
        
        if (!$matched) {
            $unmatchedApps[] = $record->application;
            Log::warning('Unmatched Application', [
                'date' => $dateKey,
                'raw_app' => $record->application,
                'count' => $record->login_count
            ]);
        }
    }

    if (!empty($unmatchedApps)) {
        Log::warning('Summary of Unmatched Applications', [
            'unique_unmatched' => array_unique($unmatchedApps),
            'total_unmatched' => count($unmatchedApps)
        ]);
    }
    
    return $processedData;
}

/**
 * Build monthly summary rows from processed data
 * 
 * @param array $processedData
 * @return array
 */
private function buildMonthlySummaryRows(array $processedData): array
{
    $monthlySummary = [];
    
    foreach ($processedData as $dateStr => $systemCounts) {
        // Use associative array instead of stdClass for better reliability
        $row = ['LoginDate' => Carbon::parse($dateStr)->format('d-M-Y')];
        
        // Initialize ALL display systems with empty string for this date
        foreach ($this->displaySystems as $systemName) {
            $row[$systemName] = '';
        }
        
        // Now fill in the actual counts for systems active on this date
        foreach ($systemCounts as $cleanSystem => $count) {
            if (in_array($cleanSystem, $this->displaySystems, true)) {
                // Use '1' for any activity (or show actual count if preferred)
                $row[$cleanSystem] = ($count > 0) ? '1' : '';
            } else {
                Log::warning('Clean system not in display systems', [
                    'date' => $dateStr,
                    'clean_system' => $cleanSystem,
                    'count' => $count,
                    'available_systems' => $this->displaySystems
                ]);
            }
        }
        
        // Add this row to the summary
        $monthlySummary[] = $row;
        
        Log::debug('Row Created', [
            'date' => $row['LoginDate'],
            'active_systems' => array_keys(array_filter($row, function($v, $k) {
                return $k !== 'LoginDate' && $v !== '';
            }, ARRAY_FILTER_USE_BOTH)),
            'row_data' => $row
        ]);
    }
    
    return $monthlySummary;
}

/**
 * Get systems that have data across all rows
 * 
 * @param array $monthlySummary
 * @return array
 */
private function getSystemsWithData(array $monthlySummary): array
{
    $systemsWithData = [];
    
    foreach ($monthlySummary as $row) {
        foreach ($this->displaySystems as $system) {
            if (!empty($row[$system]) && !in_array($system, $systemsWithData)) {
                $systemsWithData[] = $system;
            }
        }
    }
    
    return $systemsWithData;
}


//   public function loggedInUsers(Request $request)
// {
//     try {
//         $filterService = new \App\Services\LoginFilterService();
//         $filters = $filterService->parseFilters($request);
//         $search = $request->input('search');

//         if ($search === 'logged-in' || empty($search)) {
//             $search = null;
//         }

//         Log::info('UserController@loggedInUsers called', [
//             'filters' => $filters,
//             'search' => $search,
//             'request_url' => $request->fullUrl(),
//         ]);

//         // Helper function to get raw system names
//         $getRawSystemNames = function($cleanSystemName) {
//             $systemMap = config('systemmap', []);
            
//             if (empty($systemMap)) {
//                 Log::warning('System map configuration not found');
//                 return [$cleanSystemName]; // Return the clean name as fallback
//             }
            
//             $rawNames = collect($systemMap)
//                 ->filter(fn($mappedName) => $mappedName === $cleanSystemName)
//                 ->keys()
//                 ->toArray();
            
//             // If no mappings found, return the clean system name itself
//             if (empty($rawNames)) {
//                 return [$cleanSystemName];
//             }
            
//             return $rawNames;
//         };

//         // FIXED: System filtering helper function
//         $applySystemFilter = function($query, $systemName) use ($getRawSystemNames) {
//             if (empty($systemName)) {
//                 return $query;
//             }
            
//             $rawSystemNames = $getRawSystemNames($systemName);
            
//             return $query->where(function ($q) use ($rawSystemNames, $systemName) {
//                 // Start with OR conditions from the beginning
//                 $q->where(DB::raw('LOWER(application)'), strtolower($systemName));
                
//                 // Add all raw system variations as OR conditions
//                 foreach ($rawSystemNames as $rawSystem) {
//                     $q->orWhere(DB::raw('LOWER(application)'), strtolower($rawSystem))
//                       ->orWhere('application', 'LIKE', "%{$rawSystem}%");
//                 }
                
//                 // Also try direct application field match for the clean name
//                 $q->orWhere(DB::raw('LOWER(application)'), 'like', '%' . strtolower($systemName) . '%');
//             });
//         };

//         // Main query
//         $query = AzureUser::valid()->whereHas('signIns', function ($q) use ($filters, $applySystemFilter) {
//             $q->whereBetween('date_utc', [$filters['startDate'], $filters['endDate']]);
            
//             // FIXED: Apply system filter correctly
//             if (!empty($filters['system'])) {
//                 $applySystemFilter($q, $filters['system']);
//             }
//         });

//         if ($search) {
//             $query->where(function ($q) use ($search) {
//                 $q->where('displayName', 'like', "%{$search}%")
//                   ->orWhere('userPrincipalName', 'like', "%{$search}%")
//                   ->orWhere('mail', 'like', "%{$search}%")
//                   ->orWhere('alternateEmailAddress', 'like', "%{$search}%");
//             });
//         }

//         $query->withCount(['signIns as login_count' => function ($q) use ($filters, $applySystemFilter) {
//             $q->whereBetween('date_utc', [$filters['startDate'], $filters['endDate']]);
            
//             // FIXED: Same system filtering logic for count
//             if (!empty($filters['system'])) {
//                 $applySystemFilter($q, $filters['system']);
//             }
//         }])
//         ->with(['signIns' => function ($q) use ($filters, $applySystemFilter) {
//             $q->whereBetween('date_utc', [$filters['startDate'], $filters['endDate']]);
            
//             // FIXED: Same system filtering logic for relations
//             if (!empty($filters['system'])) {
//                 $applySystemFilter($q, $filters['system']);
//             }
            
//             $q->orderByDesc('date_utc')->limit(5);
//         }])
//         ->orderBy('displayName');

//         $users = $query->paginate($this->perPage)->withQueryString();
//         $systems = $filterService->getSystemList();
//         $type = 'default';

//         Log::info('loggedInUsers loaded with fixed system filtering', [
//             'user_count' => $users->total(),
//             'filters' => $filters,
//             'search' => $search,
//             'user_ids' => $users->pluck('id')->toArray(),
//             'applied_system_filter' => $filters['system'] ?? 'none',
//         ]);

//         return view('logintracker.users.logged-in', compact('users', 'filters', 'search', 'systems', 'type'));
//     } catch (\Exception $e) {
//         Log::error('Error in loggedInUsers', [
//             'error' => $e->getMessage(),
//             'trace' => $e->getTraceAsString(),
//         ]);
//         return redirect()->route('dashboard')->with('error', 'An unexpected error occurred.');
//     }
// }


// public function notLoggedInUsers(Request $request)
// {
//     try {
//         $filterService = new \App\Services\LoginFilterService();
//         $filters = $filterService->parseFilters($request);
//         $search = $request->input('search');

//         Log::info('UserController@notLoggedInUsers called', [
//             'filters' => $filters,
//             'search' => $search,
//             'request_url' => $request->fullUrl(),
//         ]);

//         // Helper functions (same as before)
//         $getRawSystemNames = function($cleanSystemName) {
//             $systemMap = config('systemmap', []);
            
//             if (empty($systemMap)) {
//                 Log::warning('System map configuration not found');
//                 return [$cleanSystemName];
//             }
            
//             $rawNames = collect($systemMap)
//                 ->filter(fn($mappedName) => $mappedName === $cleanSystemName)
//                 ->keys()
//                 ->toArray();
            
//             if (empty($rawNames)) {
//                 return [$cleanSystemName];
//             }
            
//             return $rawNames;
//         };

//         $applySystemFilter = function($query, $systemName) use ($getRawSystemNames) {
//             if (empty($systemName)) {
//                 return $query;
//             }
            
//             $rawSystemNames = $getRawSystemNames($systemName);
            
//             return $query->where(function ($q) use ($rawSystemNames, $systemName) {
//                 $q->where(DB::raw('LOWER(application)'), strtolower($systemName));
                
//                 foreach ($rawSystemNames as $rawSystem) {
//                     $q->orWhere(DB::raw('LOWER(application)'), strtolower($rawSystem))
//                       ->orWhere('application', 'LIKE', "%{$rawSystem}%");
//                 }
                
//                 $q->orWhere(DB::raw('LOWER(application)'), 'like', '%' . strtolower($systemName) . '%');
//             });
//         };

//         // FIXED: Show users who have used the system before but not during this period
//         if (!empty($filters['system'])) {
//             // Users who have used this specific system before
//             $baseQuery = AzureUser::valid()
//                 ->whereHas('signIns', function ($q) use ($applySystemFilter, $filters) {
//                     // They have used this system at some point
//                     $applySystemFilter($q, $filters['system']);
//                 })
//                 ->whereDoesntHave('signIns', function ($q) use ($filters, $applySystemFilter) {
//                     // But NOT during the selected period for this system
//                     $q->whereBetween('date_utc', [$filters['startDate'], $filters['endDate']]);
//                     $applySystemFilter($q, $filters['system']);
//                 });
//         } else {
//             // For "All Systems" - users who haven't logged in anywhere during the period
//             $baseQuery = AzureUser::valid()
//                 ->whereDoesntHave('signIns', function ($q) use ($filters) {
//                     $q->whereBetween('date_utc', [$filters['startDate'], $filters['endDate']]);
//                 });
//         }

//         // Apply search filter if provided
//         if ($search) {
//             $baseQuery->where(function ($q) use ($search) {
//                 $q->where('displayName', 'like', "%{$search}%")
//                   ->orWhere('userPrincipalName', 'like', "%{$search}%")
//                   ->orWhere('mail', 'like', "%{$search}%")
//                   ->orWhere('alternateEmailAddress', 'like', "%{$search}%");
//             });
//         }

//         // Order and paginate
//         $users = $baseQuery->orderBy('displayName')
//             ->paginate($this->perPage)
//             ->withQueryString();

//         $systems = $filterService->getSystemList();

//         Log::info('notLoggedInUsers loaded with system-specific filtering', [
//             'user_count' => $users->total(),
//             'filters' => $filters,
//             'search' => $search,
//             'applied_system_filter' => $filters['system'] ?? 'none',
//             'logic' => $filters['system'] ? 'system_specific' : 'all_systems'
//         ]);

//         return view('logintracker.users.not-logged-in', compact('users', 'filters', 'search', 'systems'));
        
//     } catch (\Exception $e) {
//         Log::error('Error in notLoggedInUsers', [
//             'error' => $e->getMessage(),
//             'trace' => $e->getTraceAsString(),
//         ]);
//         return redirect()->back()->with('error', 'An unexpected error occurred.');
//     }
// }




/**
 * Show users active this month
 */
public function activeThisMonth(Request $request)
{
    try {
        $search = $request->input('search');
        
        // Get current month date range
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        Log::info('UserController@activeThisMonth called', [
            'search' => $search,
            'date_range' => [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]
        ]);

        // Query users who logged in this month
        $query = AzureUser::valid()
            ->whereHas('signIns', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date_utc', [$startDate, $endDate])
                  ->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems);
            });

        // Apply search filter if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('displayName', 'like', "%{$search}%")
                  ->orWhere('userPrincipalName', 'like', "%{$search}%")
                  ->orWhere('mail', 'like', "%{$search}%")
                  ->orWhere('alternateEmailAddress', 'like', "%{$search}%");
            });
        }

        // Add login count and latest sign-ins
        $query->withCount(['signIns as login_count' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('date_utc', [$startDate, $endDate])
              ->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems);
        }])
        ->with(['signIns' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('date_utc', [$startDate, $endDate])
              ->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems)
              ->orderByDesc('date_utc')
              ->limit(5);
        }])
        ->orderByDesc('login_count');

        $users = $query->paginate($this->perPage)->withQueryString();

        Log::info('Active this month users loaded', [
            'total_count' => $users->total(),
            'search' => $search
        ]);

        return view('logintracker.users.active-this-month', compact('users', 'search'));
        
    } catch (\Exception $e) {
        Log::error('Error in activeThisMonth', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->route('dashboard')->with('error', 'An unexpected error occurred.');
    }
}

/**
 * Show users active last month
 */
public function activeLastMonth(Request $request)
{
    try {
        $search = $request->input('search');
        
        // Get last month date range
        $startDate = Carbon::now()->subMonth()->startOfMonth();
        $endDate = Carbon::now()->subMonth()->endOfMonth();

        Log::info('UserController@activeLastMonth called', [
            'search' => $search,
            'date_range' => [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]
        ]);

        // Query users who logged in last month
        $query = AzureUser::valid()
            ->whereHas('signIns', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date_utc', [$startDate, $endDate])
                  ->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems);
            });

        // Apply search filter if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('displayName', 'like', "%{$search}%")
                  ->orWhere('userPrincipalName', 'like', "%{$search}%")
                  ->orWhere('mail', 'like', "%{$search}%")
                  ->orWhere('alternateEmailAddress', 'like', "%{$search}%");
            });
        }

        // Add login count and latest sign-ins
        $query->withCount(['signIns as login_count' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('date_utc', [$startDate, $endDate])
              ->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems);
        }])
        ->with(['signIns' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('date_utc', [$startDate, $endDate])
              ->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems)
              ->orderByDesc('date_utc')
              ->limit(5);
        }])
        ->orderByDesc('login_count');

        $users = $query->paginate($this->perPage)->withQueryString();

        Log::info('Active last month users loaded', [
            'total_count' => $users->total(),
            'search' => $search
        ]);

        return view('logintracker.users.active-last-month', compact('users', 'search'));
        
    } catch (\Exception $e) {
        Log::error('Error in activeLastMonth', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->route('dashboard')->with('error', 'An unexpected error occurred.');
    }
}

/**
 * Show inactive users (users who have never logged in)
 */
public function inactiveUsers(Request $request)
{
    try {
        $search = $request->input('search');

        Log::info('UserController@inactiveUsers called', [
            'search' => $search
        ]);

        // Query users who have never logged in
        $query = AzureUser::valid()
            ->whereDoesntHave('signIns', function ($q) {
                $q->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems);
            });

        // Apply search filter if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('displayName', 'like', "%{$search}%")
                  ->orWhere('userPrincipalName', 'like', "%{$search}%")
                  ->orWhere('mail', 'like', "%{$search}%")
                  ->orWhere('alternateEmailAddress', 'like', "%{$search}%");
            });
        }

        // Load their last sign-in (if any) for display
        $query->with(['signIns' => function ($q) {
            $q->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems)
              ->orderByDesc('date_utc')
              ->limit(1);
        }])
        ->orderBy('displayName');

        $users = $query->paginate($this->perPage)->withQueryString();

        Log::info('Inactive users loaded', [
            'total_count' => $users->total(),
            'search' => $search
        ]);

        return view('logintracker.users.inactive-users', compact('users', 'search'));
        
    } catch (\Exception $e) {
        Log::error('Error in inactiveUsers', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->route('dashboard')->with('error', 'An unexpected error occurred.');
    }
}



   /**
     * Helper method to resolve date range based on selection
     */
    protected function resolveDateRange($input = null)
    {
        // Handle Request object
        if ($input instanceof Request) {
            $range = $input->input('range', 'this_month');
            $startDate = $input->input('start_date');
            $endDate = $input->input('end_date');
            
            // Check for custom date first
            if ($range === 'custom') {
                if ($startDate && $endDate && strtotime($startDate) && strtotime($endDate)) {
                    $start = Carbon::parse($startDate)->startOfDay();
                    $end = Carbon::parse($endDate)->endOfDay();
                    return [$start, $end];
                } else {
                    // If custom is selected but dates are missing, default to this month
                    Log::warning('Custom range selected but dates missing', [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ]);
                    $start = Carbon::now()->startOfMonth();
                    $end = Carbon::now()->endOfMonth();
                    return [$start, $end];
                }
            }
            
            // Otherwise resolve the range string
            return $this->resolveDateRange($range);
        }
        
        // Handle string input (range type)
        switch ($input) {
            case 'this_month':
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
                break;
                
            case 'last_month':
                $start = Carbon::now()->subMonth()->startOfMonth();
                $end = Carbon::now()->subMonth()->endOfMonth();
                break;
                
            case 'last_3_months':
                $start = Carbon::now()->subMonths(3)->startOfMonth();
                $end = Carbon::now()->endOfMonth();
                break;
                
            case 'custom':
                // If custom is called directly without dates, default to this month
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
                break;
                
            default:
                // Default to this month if invalid range
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
        }

        Log::info('Resolved Date Range', [
            'input' => $input,
            'start' => $start->toDateTimeString(),
            'end' => $end->toDateTimeString(),
        ]);

        return [$start, $end];
    }

    /**
     * Helper method to get range label for display
     */
    private function getRangeLabel($range, $start = null, $end = null)
    {
        if ($range === 'custom' && $start && $end) {
            if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
                return 'Custom Date: ' . $start->format('M j, Y');
            }
            return 'Custom Range: ' . $start->format('M j, Y') . ' - ' . $end->format('M j, Y');
        }
        
        $labels = [
            'this_month' => 'This Month',
            'last_month' => 'Last Month',
            'last_3_months' => 'Last 3 Months',
            'custom' => 'Custom Range',
        ];
        
        return $labels[$range] ?? 'This Month';
    }

    /**
     * Helper method for SQL Server compatible string aggregation
     */
    private function getSystemUsageWithAggregation($userId, $start, $end)
    {
        // Check if using SQL Server
        if (DB::connection()->getDriverName() === 'sqlsrv') {
            return SigninLog::select([
                    'system',
                    DB::raw('COUNT(*) as usage_count'),
                    DB::raw('MAX(date_utc) as last_used'),
                    DB::raw('STRING_AGG(CAST(system AS NVARCHAR(MAX)), \', \') WITHIN GROUP (ORDER BY system) as systems')
                ])
                ->where('user_id', $userId)
                ->whereBetween('date_utc', [$start, $end])
                ->whereNotNull('system')
                ->groupBy('system')
                ->orderBy('last_used', 'desc')
                ->limit(10)
                ->get();
        } else {
            // MySQL/other databases
            return SigninLog::select([
                    'system',
                    DB::raw('COUNT(*) as usage_count'),
                    DB::raw('MAX(date_utc) as last_used'),
                    DB::raw('GROUP_CONCAT(DISTINCT system ORDER BY system SEPARATOR ", ") as systems')
                ])
                ->where('user_id', $userId)
                ->whereBetween('date_utc', [$start, $end])
                ->whereNotNull('system')
                ->groupBy('system')
                ->orderBy('last_used', 'desc')
                ->limit(10)
                ->get();
        }
    }


}