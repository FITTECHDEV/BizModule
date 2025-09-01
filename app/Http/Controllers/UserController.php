<?php

namespace App\Http\Controllers;

use App\Models\Logintracker\User;
use App\Models\Logintracker\SigninLog;
use App\Models\Logintracker\System; // Explicitly import System model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    protected $perPage = 17;

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('logintracker.users.create');
    }

    /**
     * Store a newly created user in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|string|unique:Azure_Users,id|uuid', // Use table name Azure_Users
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

            User::create($validated + ['createdDateTime' => now()->format('Y-m-d H:i:s')]);

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

    /**
     * Remove the specified user from storage.
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            if (!Str::isUuid($id)) {
                Log::warning('Invalid UUID format in destroy', ['id' => $id]);
                return redirect()->route('dashboard')->with('error', 'Invalid ID format.');
            }

            $user = User::find($id);
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

    /**
     * Display a listing of users.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            [$start, $end] = $this->resolveDateRange($request);
            $rangeInput = $request->input('range', 'this_month');
            $system = $request->input('system');
            $search = $request->input('search');

            if ($request->ajax()) {
                DB::enableQueryLog();
                Log::info('AJAX Request for index', [
                    'range' => $rangeInput,
                    'system' => $system,
                    'search' => $search,
                    'start' => $start->toDateTimeString(),
                    'end' => $end->toDateTimeString(),
                ]);

                $query = User::query();
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
                        $q->whereBetween('date_utc', [$start, $end]);
                    });
                }

                $query->withCount(['signIns as sign_ins_count' => function ($q) use ($start, $end, $system) {
                    $q->whereBetween('date_utc', [$start, $end]);
                    if ($system) {
                        $q->where(DB::raw('LOWER(system)'), strtolower($system));
                    }
                }])
                ->with(['signIns' => function ($q) use ($start, $end, $system) {
                    $q->whereBetween('date_utc', [$start, $end]);
                    if ($system) {
                        $q->where(DB::raw('LOWER(system)'), strtolower($system));
                    }
                    $q->latest('date_utc');
                }]);

                $users = $query->paginate($this->perPage)->withQueryString();
                $totalDays = $start->diffInDays($end) + 1;

                $logins = SigninLog::whereBetween('date_utc', [$start, $end])
                    ->when($system, function ($query) use ($system) {
                        return $query->where(DB::raw('LOWER(system)'), strtolower($system));
                    })
                    ->get();
                $loggedInEmails = $logins->pluck('username')->filter()->unique()->map(fn($email) => strtolower(trim($email)));
                $totalUsers = User::count();
                $loggedInCount = $loggedInEmails->isEmpty() ? 0 : User::whereIn(DB::raw('LOWER(userPrincipalName)'), $loggedInEmails)->count();
                $notLoggedInCount = $totalUsers - $loggedInCount;

                $systems = System::orderBy('name')->pluck('name')->toArray();
                Log::info('Systems for Dropdown', ['systems' => $systems]);

                // Map users to ensure UUID id
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

            $query = User::query();
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
                    $q->whereBetween('date_utc', [$start, $end]);
                });
            }

            $query->withCount(['signIns as sign_ins_count' => function ($q) use ($start, $end, $system) {
                $q->whereBetween('date_utc', [$start, $end]);
                if ($system) {
                    $q->where(DB::raw('LOWER(system)'), strtolower($system));
                }
            }])
            ->with(['signIns' => function ($q) use ($start, $end, $system) {
                $q->whereBetween('date_utc', [$start, $end]);
                if ($system) {
                    $q->where(DB::raw('LOWER(system)'), strtolower($system));
                }
                $q->latest('date_utc');
            }]);

            $users = $query->paginate($this->perPage)->withQueryString();
            $logins = SigninLog::whereBetween('date_utc', [$start, $end])
                ->when($system, function ($query) use ($system) {
                    return $query->where(DB::raw('LOWER(system)'), strtolower($system));
                })
                ->get();
            $loggedInEmails = $logins->pluck('username')->filter()->unique()->map(fn($email) => strtolower(trim($email)));
            $totalUsers = User::count();
            $loggedInCount = $loggedInEmails->isEmpty() ? 0 : User::whereIn(DB::raw('LOWER(userPrincipalName)'), $loggedInEmails)->count();
            $notLoggedInCount = $totalUsers - $loggedInCount;

            $systems = System::orderBy('name')->pluck('name')->toArray();
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

    /**
     * Display the specified user.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $identifier
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $identifier)
    {
        Log::info('UserController@show called', [
            'identifier' => $identifier,
            'request_url' => $request->fullUrl(),
            'request_input' => $request->all(),
        ]);

        try {
            // Use findByIdentifier to support userPrincipalName or id
            $user = User::findByIdentifier($identifier);
            if (!$user) {
                Log::notice('User not found in show', ['identifier' => $identifier]);
                abort(404, 'User not found.');
            }

            $range = $request->input('range', 'this_month');
            $system = $request->input('system');

            [$start, $end] = $this->resolveDateRange($range);

            $signIns = $user->signIns()
                ->whereBetween('date_utc', [$start, $end])
                ->when($system, function ($query) use ($system) {
                    return $query->where(DB::raw('LOWER(system)'), strtolower($system));
                })
                ->orderBy('date_utc', 'desc')
                ->paginate($this->perPage)
                ->withQueryString();

            $recentApplications = $user->signIns()
                ->whereBetween('date_utc', [$start, $end])
                ->select(
                    'system',
                    DB::raw('COUNT(*) as usage_count'),
                    DB::raw('MAX(date_utc) as last_used'),
                    DB::raw('GROUP_CONCAT(DISTINCT system ORDER BY system SEPARATOR ", ") as systems')
                )
                ->whereNotNull('system')
                ->groupBy('system')
                ->orderBy('last_used', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    if ($item->systems) {
                        $systemArray = array_unique(array_map('trim', explode(',', $item->systems)));
                        $item->systems = implode(', ', $systemArray);
                    }
                    return $item;
                });

            $systems = System::orderBy('name')->pluck('name')->toArray();
            $rangeLabel = $this->getRangeLabel($range);

            Log::info('UserController@show loaded', [
                'user_id' => $user->id,
                'range' => $range,
                'system' => $system,
                'sign_ins_count' => $signIns->total(),
                'recent_applications_count' => $recentApplications->count(),
            ]);

            return view('logintracker.users.show', compact(
                'user', 'signIns', 'start', 'end', 'system', 'range', 'rangeLabel', 'recentApplications', 'systems'
            ));

        } catch (\Exception $e) {
            Log::error('UserController@show failed', [
                'identifier' => $identifier,
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'An unexpected error occurred.');
        }
    }

    /**
     * Display users with recent sign-in activity.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function loggedInUsers(Request $request)
    {
        try {
            $filterService = new \App\Services\LoginFilterService();
            $filters = $filterService->parseFilters($request);
            $search = $request->input('search');

            $query = User::whereHas('signIns', function ($q) use ($filters) {
                $q->whereBetween('date_utc', [$filters['startDate'], $filters['endDate']]);
                if (!empty($filters['system'])) {
                    $mappedSystems = collect(config('systemmap'))
                        ->filter(function ($system) use ($filters) {
                            return $system === $filters['system'];
                        })
                        ->keys()
                        ->toArray();
                    if (!empty($mappedSystems)) {
                        $q->where(function ($query) use ($mappedSystems) {
                            foreach ($mappedSystems as $mappedSystem) {
                                $query->orWhere('application', 'LIKE', "%{$mappedSystem}%");
                            }
                        });
                    }
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

            $query->withCount(['signIns as login_count' => function ($q) use ($filters) {
                $q->whereBetween('date_utc', [$filters['startDate'], $filters['endDate']]);
                if (!empty($filters['system'])) {
                    $mappedSystems = collect(config('systemmap'))
                        ->filter(function ($system) use ($filters) {
                            return $system === $filters['system'];
                        })
                        ->keys()
                        ->toArray();
                    if (!empty($mappedSystems)) {
                        $q->where(function ($query) use ($mappedSystems) {
                            foreach ($mappedSystems as $mappedSystem) {
                                $query->orWhere('application', 'LIKE', "%{$mappedSystem}%");
                            }
                        });
                    }
                }
            }])
            ->with(['signIns' => function ($q) use ($filters) {
                $q->whereBetween('date_utc', [$filters['startDate'], $filters['endDate']])
                  ->orderByDesc('date_utc')
                  ->limit(5);
            }])
            ->orderBy('displayName');

            $users = $query->paginate($this->perPage)->withQueryString();
            $systems = $filterService->getSystemList();
            $type = 'default';

            Log::info('loggedInUsers loaded', [
                'user_count' => $users->total(),
                'filters' => $filters,
                'search' => $search,
            ]);

            return view('logintracker.users.logged-in', compact('users', 'filters', 'search', 'systems', 'type'));
        } catch (\Exception $e) {
            Log::error('Error in loggedInUsers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'An unexpected error occurred.');
        }
    }

    /**
     * Display users with no sign-in activity.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function notLoggedInUsers(Request $request)
    {
        try {
            $filterService = new \App\Services\LoginFilterService();
            $filters = $filterService->parseFilters($request);
            $search = $request->input('search');

            $query = User::query();

            if (!empty($filters['system'])) {
                $mappedSystems = collect(config('systemmap'))
                    ->filter(function ($system) use ($filters) {
                        return $system === $filters['system'];
                    })
                    ->keys()
                    ->toArray();
                if (!empty($mappedSystems)) {
                    $query->whereHas('signIns', function ($q) use ($mappedSystems) {
                        $q->where(function ($query) use ($mappedSystems) {
                            foreach ($mappedSystems as $mappedSystem) {
                                $query->orWhere('application', 'LIKE', "%{$mappedSystem}%");
                            }
                        });
                    });
                }
            }

            $query->whereDoesntHave('signIns', function ($q) use ($filters) {
                $q->whereBetween('date_utc', [$filters['startDate'], $filters['endDate']]);
                if (!empty($filters['system'])) {
                    $mappedSystems = collect(config('systemmap'))
                        ->filter(function ($system) use ($filters) {
                            return $system === $filters['system'];
                        })
                        ->keys()
                        ->toArray();
                    if (!empty($mappedSystems)) {
                        $q->where(function ($query) use ($mappedSystems) {
                            foreach ($mappedSystems as $mappedSystem) {
                                $query->orWhere('application', 'LIKE', "%{$mappedSystem}%");
                            }
                        });
                    }
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

            $users = $query->orderBy('displayName')
                          ->paginate($this->perPage)
                          ->withQueryString();

            $systems = $filterService->getSystemList();

            Log::info('notLoggedInUsers loaded', [
                'user_count' => $users->total(),
                'filters' => $filters,
                'search' => $search,
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
     * Resolve date range for filtering.
     *
     * @param mixed $input
     * @return array
     */
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

    /**
     * Get label for date range.
     *
     * @param string $range
     * @return string
     */
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
}
?>