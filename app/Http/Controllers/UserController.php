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
    'Import-Unknown',
    'Unknown', 
    'Microsoft Teams',
    'Office 365',
    'Windows Authentication',  
    'IT Infrastructure',       
    'FITerp UAT',             
    'Web Browser',           
    'Mobile/Desktop Client'
    // 'all'
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
        // Resolve user (UUID, UPN, or fallback email)
        $user = AzureUser::findByIdentifier($identifier);
        if (!$user) {
            Log::notice('User not found in show', ['identifier' => $identifier]);
            abort(404, 'User not found.');
        }

        // Handle date range
        $range        = $request->input('range', 'this_month');
        $system       = $request->input('system');
        $selectedDate = $request->input('date');

        if ($selectedDate && strtotime($selectedDate)) {
            $date  = Carbon::parse($selectedDate);
            $start = $date->copy()->startOfDay();
            $end   = $date->copy()->endOfDay();
            $range = 'custom';
        } else {
            [$start, $end] = $this->resolveDateRange($range);
        }

        Log::info('Final Date Range', [
            'start'  => $start->toDateTimeString(),
            'end'    => $end->toDateTimeString(),
            'range'  => $range,
            'system' => $system,
            'user_id' => $user->id, // Log user_id to track NULL cases
            'has_valid_user_id' => !is_null($user->id),
        ]);

        // IMPROVED: Primary query with excluded systems filtering
        $signInsQuery = $user->signIns()
            ->whereBetween('date_utc', [$start, $end])
            ->whereNotIn('system', $this->excludedSystems);

        // IMPROVED: Apply proper system filtering using the same logic as other methods
        if ($system) {
            $systemMap = config('systemmap', []);
            $rawSystemNames = collect($systemMap)
                ->filter(fn($mappedName) => $mappedName === $system)
                ->keys()
                ->toArray();

            if (!empty($rawSystemNames)) {
                $signInsQuery->where(function ($q) use ($rawSystemNames, $system) {
                    $q->where(DB::raw('LOWER(system)'), strtolower($system));
                    
                    foreach ($rawSystemNames as $rawSystem) {
                        $q->orWhere(DB::raw('LOWER(system)'), strtolower($rawSystem))
                          ->orWhere('application', 'LIKE', "%{$rawSystem}%");
                    }
                    
                    $q->orWhere(DB::raw('LOWER(application)'), 'like', '%' . strtolower($system) . '%');
                });
            } else {
                $signInsQuery->where(function ($q) use ($system) {
                    $q->where(DB::raw('LOWER(system)'), strtolower($system))
                      ->orWhere(DB::raw('LOWER(application)'), 'like', '%' . strtolower($system) . '%');
                });
            }
        }

        $signIns = $signInsQuery
            ->orderBy('date_utc', 'desc')
            ->paginate($this->perPage)
            ->withQueryString();

        // IMPROVED: Fallback with excluded systems and proper system filtering
        // CRITICAL FIX: Always use username fallback (even if user->id exists) to ensure we find all records
        // This handles cases where user_id is NULL or doesn't match
        if ($signIns->isEmpty() && !empty($user->userPrincipalName)) {
            Log::info('Fallback to username for signIns', [
                'userPrincipalName' => $user->userPrincipalName,
                'user_id_was_null' => is_null($user->id),
                'reason' => 'Primary query returned no results'
            ]);

            $fallbackQuery = SigninLog::whereRaw('LOWER(username) = ?', [strtolower($user->userPrincipalName)])
                ->whereBetween('date_utc', [$start, $end])
                ->whereNotIn('system', $this->excludedSystems);

            if ($system) {
                $systemMap = config('systemmap', []);
                $rawSystemNames = collect($systemMap)
                    ->filter(fn($mappedName) => $mappedName === $system)
                    ->keys()
                    ->toArray();

                if (!empty($rawSystemNames)) {
                    $fallbackQuery->where(function ($q) use ($rawSystemNames, $system) {
                        $q->where(DB::raw('LOWER(system)'), strtolower($system));
                        
                        foreach ($rawSystemNames as $rawSystem) {
                            $q->orWhere(DB::raw('LOWER(system)'), strtolower($rawSystem))
                              ->orWhere('application', 'LIKE', "%{$rawSystem}%");
                        }
                        
                        $q->orWhere(DB::raw('LOWER(application)'), 'like', '%' . strtolower($system) . '%');
                    });
                } else {
                    $fallbackQuery->where(function ($q) use ($system) {
                        $q->where(DB::raw('LOWER(system)'), strtolower($system))
                          ->orWhere(DB::raw('LOWER(application)'), 'like', '%' . strtolower($system) . '%');
                    });
                }
            }

            $signIns = $fallbackQuery
                ->orderBy('date_utc', 'desc')
                ->paginate($this->perPage)
                ->withQueryString();
        }

        // IMPROVED: Recent applications with excluded systems and proper system filtering
        // CRITICAL FIX: Use username fallback when user_id is NULL
        // This ensures we find applications even for users without a valid user_id
        $recentApplicationsQuery = null;

        if (!is_null($user->id)) {
            // Primary query using user_id
            $recentApplicationsQuery = SigninLog::where('user_id', $user->id)
                ->whereBetween('date_utc', [$start, $end])
                ->whereNotIn('system', $this->excludedSystems)
                ->whereNotNull('application')
                ->where('application', '!=', '');

            Log::info('Using user_id for recent applications', [
                'user_id' => $user->id,
                'userPrincipalName' => $user->userPrincipalName,
            ]);
        } else {
            // Fallback: user_id is NULL, use username instead
            Log::info('User has NULL id - using username fallback for recent applications', [
                'userPrincipalName' => $user->userPrincipalName,
                'reason' => 'user_id is NULL in Azure_Users table'
            ]);

            $recentApplicationsQuery = SigninLog::whereRaw('LOWER(username) = ?', [strtolower($user->userPrincipalName)])
                ->whereBetween('date_utc', [$start, $end])
                ->whereNotIn('system', $this->excludedSystems)
                ->whereNotNull('application')
                ->where('application', '!=', '');
        }

        // Apply system filtering if specified
        if ($system) {
            $systemMap = config('systemmap', []);
            $rawSystemNames = collect($systemMap)
                ->filter(fn($mappedName) => $mappedName === $system)
                ->keys()
                ->toArray();

            if (!empty($rawSystemNames)) {
                $recentApplicationsQuery->where(function ($q) use ($rawSystemNames, $system) {
                    $q->where(DB::raw('LOWER(system)'), strtolower($system));
                    
                    foreach ($rawSystemNames as $rawSystem) {
                        $q->orWhere(DB::raw('LOWER(system)'), strtolower($rawSystem))
                          ->orWhere('application', 'LIKE', "%{$rawSystem}%");
                    }
                    
                    $q->orWhere(DB::raw('LOWER(application)'), 'like', '%' . strtolower($system) . '%');
                });
            } else {
                $recentApplicationsQuery->where(function ($q) use ($system) {
                    $q->where(DB::raw('LOWER(system)'), strtolower($system))
                      ->orWhere(DB::raw('LOWER(application)'), 'like', '%' . strtolower($system) . '%');
                });
            }
        }

        $recentApplications = $recentApplicationsQuery
            ->select([
                'application',
                'system',
                DB::raw('COUNT(*) as usage_count'),
                DB::raw('MAX(date_utc) as last_used'),
            ])
            ->groupBy('application', 'system')
            ->orderBy('last_used', 'desc')
            ->limit(10)
            ->get();

        Log::info('Recent applications retrieved', [
            'user_id' => $user->id,
            'userPrincipalName' => $user->userPrincipalName,
            'application_count' => $recentApplications->count(),
            'used_fallback' => is_null($user->id),
        ]);

        // Load all systems for filters
        $systems    = $this->visibleSystems();
        $rangeLabel = $this->getRangeLabel($range);

        return view('logintracker.users.show', compact(
            'user',
            'signIns',
            'start',
            'end',
            'system',
            'range',
            'rangeLabel',
            'recentApplications',
            'systems',
            'selectedDate'
        ));

    } catch (\Throwable $e) {
        Log::error('UserController@show failed', [
            'identifier'    => $identifier,
            'error_message' => $e->getMessage(),
            'stack_trace'   => $e->getTraceAsString(),
        ]);
        abort(500, 'An unexpected error occurred.');
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




    protected function resolveDateRange($input = null)
    {
        if ($input instanceof Request) {
            if ($input->has('range')) {
                return $this->resolveDateRange($input->input('range'));
            } elseif ($input->has('date') && strtotime($input->input('date'))) {
                $date = Carbon::parse($input->input('date'));
                return [$date->startOfDay(), $date->endOfDay()];
            }
            return [now()->startOfMonth(), now()->endOfDay()];
        }

        switch ($input) {
            case 'last_month':
                $start = now()->subMonthNoOverflow()->startOfMonth();
                $end = now()->subMonthNoOverflow()->endOfMonth();
                break;
            case 'last_3_months':
                $start = now()->subMonthsNoOverflow(3)->startOfMonth();
                $end = now()->endOfDay();
                break;
            default:
                $start = now()->startOfMonth();
                $end = now()->endOfDay();
        }

        Log::info('Resolved Date Range', [
            'start' => $start->toDateTimeString(),
            'end' => $end->toDateTimeString(),
        ]);

        return [$start, $end];
    }

    private function getRangeLabel($range)
    {
        return match ($range) {
            'this_month' => 'This Month',
            'last_month' => 'Last Month',
            'last_3_months' => 'Last 3 Months',
            'custom' => 'Custom Range',
            default => 'This Month',
        };
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