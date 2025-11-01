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
        'Biz PrO',
        'Biz UAT',
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
protected function visibleSystems(): array
{
    // Use LOWER(name) so matching is case-insensitive
    return System::whereNotIn(DB::raw('LOWER(name)'), $this->excludedSystems)
        ->orderBy('name')
        ->pluck('name')
        ->toArray();
}

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
                $q->where(DB::raw('LOWER(system)'), strtolower($systemName));
                
                foreach ($rawSystemNames as $rawSystem) {
                    $q->orWhere(DB::raw('LOWER(system)'), strtolower($rawSystem))
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
                      ->whereNotIn(DB::raw('LOWER(system)'), $this->excludedSystems);
                });
            }

            // FIXED: Apply proper system filtering with exclusions
            $query->withCount(['signIns as sign_ins_count' => function ($q) use ($start, $end, $system, $applySystemFilter) {
                $q->whereBetween('date_utc', [$start, $end])
                  ->whereNotIn(DB::raw('LOWER(system)'), $this->excludedSystems);
                if ($system) {
                    $applySystemFilter($q, $system);
                }
            }])
            ->with(['signIns' => function ($q) use ($start, $end, $system, $applySystemFilter) {
                $q->whereBetween('date_utc', [$start, $end])
                  ->whereNotIn(DB::raw('LOWER(system)'), $this->excludedSystems);
                if ($system) {
                    $applySystemFilter($q, $system);
                }
                $q->latest('date_utc');
            }]);

            $users = $query->paginate($this->perPage)->withQueryString();
            $totalDays = $start->diffInDays($end) + 1;

            // FIXED: Apply system filtering and exclusions to login query
            $logins = SigninLog::whereBetween('date_utc', [$start, $end])
                ->whereNotIn(DB::raw('LOWER(system)'), $this->excludedSystems)
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
                  ->whereNotIn(DB::raw('LOWER(system)'), $this->excludedSystems);
            });
        }

        $query->withCount(['signIns as sign_ins_count' => function ($q) use ($start, $end, $system, $applySystemFilter) {
            $q->whereBetween('date_utc', [$start, $end])
              ->whereNotIn(DB::raw('LOWER(system)'), $this->excludedSystems);
            if ($system) {
                $applySystemFilter($q, $system);
            }
        }])
        ->with(['signIns' => function ($q) use ($start, $end, $system, $applySystemFilter) {
            $q->whereBetween('date_utc', [$start, $end])
              ->whereNotIn(DB::raw('LOWER(system)'), $this->excludedSystems);
            if ($system) {
                $applySystemFilter($q, $system);
            }
            $q->latest('date_utc');
        }]);

        $users = $query->paginate($this->perPage)->withQueryString();
        
        $logins = SigninLog::whereBetween('date_utc', [$start, $end])
            ->whereNotIn(DB::raw('LOWER(system)'), $this->excludedSystems)
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
            
            // Validate date range
            if ($end->lt($start)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'End date cannot be before start date.');
            }
        } else {
            // Default to current month
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
        }

        Log::info('Date Range Resolved', [
            'start'  => $start->toDateTimeString(),
            'end'    => $end->toDateTimeString(),
        ]);

        // ====================================================================
        // 3. HELPER FUNCTIONS FOR SYSTEM FILTERING
        // ====================================================================
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
            
            return empty($rawNames) ? [$cleanSystemName] : $rawNames;
        };

        $applySystemFilter = function($query, $systemName) use ($getRawSystemNames) {
            if (empty($systemName)) {
                return $query;
            }
            
            $rawSystemNames = $getRawSystemNames($systemName);
            
            return $query->where(function ($q) use ($rawSystemNames, $systemName) {
                $q->where(DB::raw('LOWER(system)'), strtolower($systemName));
                
                foreach ($rawSystemNames as $rawSystem) {
                    $q->orWhere(DB::raw('LOWER(system)'), strtolower($rawSystem))
                      ->orWhere('application', 'LIKE', "%{$rawSystem}%");
                }
                
                $q->orWhere(DB::raw('LOWER(application)'), 'like', '%' . strtolower($systemName) . '%');
            });
        };

        // ====================================================================
        // 4. CURRENT MONTH APPLICATIONS (ALWAYS CURRENT MONTH - UNCHANGING)
        // ====================================================================
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();

        $currentMonthApplications = SigninLog::where('user_id', $user->id)
            ->whereBetween('date_utc', [$currentMonthStart, $currentMonthEnd])
            ->whereNotNull('application')
            ->where('application', '!=', '')
            ->whereNotIn(DB::raw('LOWER(system)'), $this->excludedSystems)
            ->select([
                'application',
                DB::raw('COUNT(*) as usage_count'),
                DB::raw('MAX(date_utc) as last_used'),
            ])
            ->groupBy('application')
            ->orderBy('last_used', 'desc')
            ->limit(10)
            ->get();

        Log::info('Current Month Applications', [
            'count' => $currentMonthApplications->count(),
            'apps' => $currentMonthApplications->pluck('application')->toArray()
        ]);

        // ====================================================================
        // 5. RECENT APPLICATIONS FOR SELECTED PERIOD (UPDATES WITH DATE FILTER)
        // ====================================================================
        $system = $request->input('system');
        
        $recentApplicationsQuery = SigninLog::where('user_id', $user->id)
            ->whereBetween('date_utc', [$start, $end])
            ->whereNotNull('application')
            ->where('application', '!=', '')
            ->whereNotIn(DB::raw('LOWER(system)'), $this->excludedSystems);

        if ($system) {
            $applySystemFilter($recentApplicationsQuery, $system);
        }

        $recentApplications = $recentApplicationsQuery
            ->select([
                'application',
                DB::raw('COUNT(*) as usage_count'),
                DB::raw('MAX(date_utc) as last_used'),
            ])
            ->groupBy('application')
            ->orderBy('last_used', 'desc')
            ->limit(10)
            ->get();

        Log::info('Recent Applications (Filtered Period)', [
            'count' => $recentApplications->count(),
            'date_range' => [$start->toDateString(), $end->toDateString()],
            'system_filter' => $system ?? 'none'
        ]);

        // ====================================================================
        // 6. BUILD MONTHLY SUMMARY - SHOW ONLY SPECIFIED SYSTEMS
        // ====================================================================
        $monthlySummary = [];
        try {
            // First, let's see what applications actually exist for this user
            $actualApplications = SigninLog::where('user_id', $user->id)
                ->whereBetween('date_utc', [$start, $end])
                ->whereNotNull('application')
                ->where('application', '!=', '')
                ->whereNotIn(DB::raw('LOWER(system)'), $this->excludedSystems)
                ->distinct()
                ->pluck('application')
                ->toArray();

            Log::info('Actual Applications Found', [
                'count' => count($actualApplications),
                'applications' => $actualApplications
            ]);

            // Use the predefined list of systems to display
            $systemsUsed = $this->displaySystems;

            Log::info('Systems to Display in Table', [
                'count' => count($systemsUsed),
                'systems' => $systemsUsed,
                'source' => 'predefined_display_systems'
            ]);

            if (!empty($systemsUsed)) {
                // Pre-fetch all login data for the date range to optimize queries
                // Use case-insensitive matching
                $loginDataRaw = SigninLog::where('user_id', $user->id)
                    ->whereBetween('date_utc', [$start, $end])
                    ->whereNotIn(DB::raw('LOWER(system)'), $this->excludedSystems)
                    ->where(function($q) use ($systemsUsed) {
                        foreach ($systemsUsed as $system) {
                            $q->orWhere(DB::raw('LOWER(application)'), strtolower($system));
                        }
                    })
                    ->select([
                        DB::raw('CAST(date_utc AS DATE) as login_date'),
                        'application',
                        DB::raw('COUNT(*) as login_count')
                    ])
                    ->groupBy(DB::raw('CAST(date_utc AS DATE)'), 'application')
                    ->get();
                
                Log::info('Login Data Raw Query Results', [
                    'count' => $loginDataRaw->count(),
                    'sample' => $loginDataRaw->take(5)->toArray()
                ]);

                // Group by date for easy lookup
                $loginData = [];
                foreach ($loginDataRaw as $record) {
                    $dateKey = Carbon::parse($record->login_date)->format('Y-m-d');
                    if (!isset($loginData[$dateKey])) {
                        $loginData[$dateKey] = [];
                    }
                    // Store with original application name as key
                    $loginData[$dateKey][$record->application] = $record->login_count;
                }

                Log::info('Login Data Pre-fetched', [
                    'total_records' => $loginDataRaw->count(),
                    'unique_dates' => count($loginData),
                    'sample_date' => !empty($loginData) ? array_key_first($loginData) : null,
                    'sample_data' => !empty($loginData) ? $loginData[array_key_first($loginData)] : null
                ]);

                // Generate all dates in the range
                $dateRange = new \DatePeriod(
                    $start,
                    new \DateInterval('P1D'),
                    $end->copy()->addDay()
                );

                // Build the summary data structure with ONLY the 10 specified systems
                foreach ($dateRange as $date) {
                    $dateStr = $date->format('Y-m-d');
                    $row = new \stdClass();
                    $row->LoginDate = $dateStr;

                    // Initialize ONLY the 10 specified systems with 0 by default
                    foreach ($systemsUsed as $appName) {
                        $row->$appName = 0;
                    }

                    // Update with actual counts from pre-fetched data (case-insensitive matching)
                    if (isset($loginData[$dateStr])) {
                        foreach ($loginData[$dateStr] as $actualAppName => $count) {
                            // Find matching system name (case-insensitive)
                            foreach ($systemsUsed as $displaySystem) {
                                if (strtolower($actualAppName) === strtolower($displaySystem)) {
                                    $row->$displaySystem = $count;
                                    break;
                                }
                            }
                        }
                    }

                    $monthlySummary[] = $row;
                }

                Log::info('Monthly Summary Built', [
                    'total_rows' => count($monthlySummary),
                    'date_range' => [$start->format('Y-m-d'), $end->format('Y-m-d')],
                    'systems_count' => count($systemsUsed),
                    'systems_displayed' => $systemsUsed,
                    'first_row' => !empty($monthlySummary) ? (array)$monthlySummary[0] : null,
                    'last_row' => !empty($monthlySummary) ? (array)$monthlySummary[count($monthlySummary) - 1] : null
                ]);
            } else {
                Log::warning('Display systems list is empty - monthly summary will be empty');
            }

        } catch (\Exception $e) {
            Log::error('Error building monthly summary', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            // Set empty array so page still renders
            $monthlySummary = [];
        }

        // ====================================================================
        // 7. SIGN-INS QUERY (UPDATES WITH DATE FILTER)
        // ====================================================================
        $signInsQuery = $user->signIns()
            ->whereBetween('date_utc', [$start, $end])
            ->whereNotIn(DB::raw('LOWER(system)'), $this->excludedSystems);

        if ($system) {
            $applySystemFilter($signInsQuery, $system);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $signInsQuery->where(function ($q) use ($search) {
                $q->where('application', 'like', '%' . $search . '%')
                  ->orWhere('system', 'like', '%' . $search . '%');
            });
        }

        $signIns = $signInsQuery
            ->orderBy('date_utc', 'desc')
            ->paginate($this->perPage)
            ->withQueryString();

        Log::info('Sign-ins Query', [
            'total' => $signIns->total(),
            'current_page' => $signIns->currentPage(),
            'per_page' => $signIns->perPage()
        ]);

        // ====================================================================
        // 8. UNIQUE APPS COUNT
        // ====================================================================
        $uniqueAppsCountQuery = SigninLog::where('user_id', $user->id)
            ->whereBetween('date_utc', [$start, $end])
            ->whereNotNull('application')
            ->where('application', '!=', '')
            ->whereNotIn(DB::raw('LOWER(system)'), $this->excludedSystems);

        if ($system) {
            $applySystemFilter($uniqueAppsCountQuery, $system);
        }

        $uniqueAppsCount = $uniqueAppsCountQuery
            ->distinct('application')
            ->count('application');

        // ====================================================================
        // 9. LOAD SYSTEMS FOR FILTER DROPDOWN
        // ====================================================================
        $systems = $this->visibleSystems();
        
        // ====================================================================
        // 10. GENERATE RANGE LABEL FOR DISPLAY
        // ====================================================================
        $rangeLabel = 'Custom Range';
        $range = 'custom';

        Log::info('UserController@show completed successfully', [
            'user_id' => $user->id,
            'date_range' => [$start->toDateString(), $end->toDateString()],
            'system_filter' => $system ?? 'none',
            'sign_ins_count' => $signIns->total(),
            'unique_apps' => $uniqueAppsCount,
            'monthly_summary_rows' => count($monthlySummary)
        ]);

        // ====================================================================
        // 11. RETURN VIEW WITH ALL DATA
        // ====================================================================
        return view('logintracker.users.show', compact(
            'user',
            'signIns',
            'start',
            'end',
            'system',
            'range',
            'rangeLabel',
            'recentApplications',
            'currentMonthApplications',
            'systems',
            'monthlySummary',
            'uniqueAppsCount'
        ));

    } catch (\Throwable $e) {
        Log::error('UserController@show failed', [
            'identifier'    => $identifier,
            'error_message' => $e->getMessage(),
            'error_line'    => $e->getLine(),
            'error_file'    => $e->getFile(),
            'stack_trace'   => $e->getTraceAsString(),
        ]);
        
        // Return user-friendly error
        return redirect()
            ->route('dashboard')
            ->with('error', 'Unable to load user details. Please try again or contact support.');
    }
}







  public function loggedInUsers(Request $request)
{
    try {
        $filterService = new \App\Services\LoginFilterService();
        $filters = $filterService->parseFilters($request);
        $search = $request->input('search');

        if ($search === 'logged-in' || empty($search)) {
            $search = null;
        }

        Log::info('UserController@loggedInUsers called', [
            'filters' => $filters,
            'search' => $search,
            'request_url' => $request->fullUrl(),
        ]);

        // Helper function to get raw system names
        $getRawSystemNames = function($cleanSystemName) {
            $systemMap = config('systemmap', []);
            
            if (empty($systemMap)) {
                Log::warning('System map configuration not found');
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
        };

        // FIXED: System filtering helper function
        $applySystemFilter = function($query, $systemName) use ($getRawSystemNames) {
            if (empty($systemName)) {
                return $query;
            }
            
            $rawSystemNames = $getRawSystemNames($systemName);
            
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
        };

        // Main query
        $query = AzureUser::valid()->whereHas('signIns', function ($q) use ($filters, $applySystemFilter) {
            $q->whereBetween('date_utc', [$filters['startDate'], $filters['endDate']]);
            
            // FIXED: Apply system filter correctly
            if (!empty($filters['system'])) {
                $applySystemFilter($q, $filters['system']);
            }
        });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('displayName', 'like', "%{$search}%")
                  ->orWhere('userPrincipalName', 'like', "%{$search}%")
                  ->orWhere('mail', 'like', "%{$search}%")
                  ->orWhere('alternateEmailAddress', 'like', "%{$search}%");
            });
        }

        $query->withCount(['signIns as login_count' => function ($q) use ($filters, $applySystemFilter) {
            $q->whereBetween('date_utc', [$filters['startDate'], $filters['endDate']]);
            
            // FIXED: Same system filtering logic for count
            if (!empty($filters['system'])) {
                $applySystemFilter($q, $filters['system']);
            }
        }])
        ->with(['signIns' => function ($q) use ($filters, $applySystemFilter) {
            $q->whereBetween('date_utc', [$filters['startDate'], $filters['endDate']]);
            
            // FIXED: Same system filtering logic for relations
            if (!empty($filters['system'])) {
                $applySystemFilter($q, $filters['system']);
            }
            
            $q->orderByDesc('date_utc')->limit(5);
        }])
        ->orderBy('displayName');

        $users = $query->paginate($this->perPage)->withQueryString();
        $systems = $filterService->getSystemList();
        $type = 'default';

        Log::info('loggedInUsers loaded with fixed system filtering', [
            'user_count' => $users->total(),
            'filters' => $filters,
            'search' => $search,
            'user_ids' => $users->pluck('id')->toArray(),
            'applied_system_filter' => $filters['system'] ?? 'none',
        ]);

        return view('logintracker.users.logged-in', compact('users', 'filters', 'search', 'systems', 'type'));
    } catch (\Exception $e) {
        Log::error('Error in loggedInUsers', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return redirect()->route('dashboard')->with('error', 'An unexpected error occurred.');
    }
}


public function notLoggedInUsers(Request $request)
{
    try {
        $filterService = new \App\Services\LoginFilterService();
        $filters = $filterService->parseFilters($request);
        $search = $request->input('search');

        Log::info('UserController@notLoggedInUsers called', [
            'filters' => $filters,
            'search' => $search,
            'request_url' => $request->fullUrl(),
        ]);

        // Helper functions (same as before)
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

        $applySystemFilter = function($query, $systemName) use ($getRawSystemNames) {
            if (empty($systemName)) {
                return $query;
            }
            
            $rawSystemNames = $getRawSystemNames($systemName);
            
            return $query->where(function ($q) use ($rawSystemNames, $systemName) {
                $q->where(DB::raw('LOWER(system)'), strtolower($systemName));
                
                foreach ($rawSystemNames as $rawSystem) {
                    $q->orWhere(DB::raw('LOWER(system)'), strtolower($rawSystem))
                      ->orWhere('application', 'LIKE', "%{$rawSystem}%");
                }
                
                $q->orWhere(DB::raw('LOWER(application)'), 'like', '%' . strtolower($systemName) . '%');
            });
        };

        // FIXED: Show users who have used the system before but not during this period
        if (!empty($filters['system'])) {
            // Users who have used this specific system before
            $baseQuery = AzureUser::valid()
                ->whereHas('signIns', function ($q) use ($applySystemFilter, $filters) {
                    // They have used this system at some point
                    $applySystemFilter($q, $filters['system']);
                })
                ->whereDoesntHave('signIns', function ($q) use ($filters, $applySystemFilter) {
                    // But NOT during the selected period for this system
                    $q->whereBetween('date_utc', [$filters['startDate'], $filters['endDate']]);
                    $applySystemFilter($q, $filters['system']);
                });
        } else {
            // For "All Systems" - users who haven't logged in anywhere during the period
            $baseQuery = AzureUser::valid()
                ->whereDoesntHave('signIns', function ($q) use ($filters) {
                    $q->whereBetween('date_utc', [$filters['startDate'], $filters['endDate']]);
                });
        }

        // Apply search filter if provided
        if ($search) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('displayName', 'like', "%{$search}%")
                  ->orWhere('userPrincipalName', 'like', "%{$search}%")
                  ->orWhere('mail', 'like', "%{$search}%")
                  ->orWhere('alternateEmailAddress', 'like', "%{$search}%");
            });
        }

        // Order and paginate
        $users = $baseQuery->orderBy('displayName')
            ->paginate($this->perPage)
            ->withQueryString();

        $systems = $filterService->getSystemList();

        Log::info('notLoggedInUsers loaded with system-specific filtering', [
            'user_count' => $users->total(),
            'filters' => $filters,
            'search' => $search,
            'applied_system_filter' => $filters['system'] ?? 'none',
            'logic' => $filters['system'] ? 'system_specific' : 'all_systems'
        ]);

        return view('logintracker.users.not-logged-in', compact('users', 'filters', 'search', 'systems'));
        
    } catch (\Exception $e) {
        Log::error('Error in notLoggedInUsers', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return redirect()->back()->with('error', 'An unexpected error occurred.');
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