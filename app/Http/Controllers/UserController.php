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
    'microsoft teams',
    'office 365',
    'unknown',
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

        if ($request->ajax()) {
            DB::enableQueryLog();
            Log::info('AJAX Request for index', [
                'range' => $rangeInput,
                'system' => $system,
                'search' => $search,
                'start' => $start->toDateTimeString(),
                'end' => $end->toDateTimeString(),
            ]);

            // ✅ hide system-generated users
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
            $totalUsers = AzureUser::valid()->count(); // ✅ count only valid users
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

        // ✅ non-AJAX path also hides system-generated users
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
        $totalUsers = AzureUser::valid()->count(); // ✅ count only valid users
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
        // 🔹 Resolve user (UUID, UPN, or fallback email)
        $user = AzureUser::findByIdentifier($identifier);
        if (!$user) {
            Log::notice('User not found in show', ['identifier' => $identifier]);
            abort(404, 'User not found.');
        }

        // 🔹 Handle date range
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
        ]);

        // 🔹 Primary query: by user_id (UUID)
        $signInsQuery = $user->signIns()
            ->whereBetween('date_utc', [$start, $end]);

        if ($system) {
            $signInsQuery->whereRaw('LOWER(system) = ?', [strtolower($system)]);
        }

        $signIns = $signInsQuery
            ->orderBy('date_utc', 'desc')
            ->paginate($this->perPage)
            ->withQueryString();

        // 🔹 Fallback: by username (UPN) if no sign-ins via UUID
        if ($signIns->isEmpty() && !empty($user->userPrincipalName)) {
            Log::info('Fallback to username for signIns', [
                'userPrincipalName' => $user->userPrincipalName,
            ]);

            $signIns = SigninLog::whereRaw('LOWER(username) = ?', [strtolower($user->userPrincipalName)])
                ->whereBetween('date_utc', [$start, $end])
                ->when($system, fn($q) => $q->whereRaw('LOWER(system) = ?', [strtolower($system)]))
                ->orderBy('date_utc', 'desc')
                ->paginate($this->perPage)
                ->withQueryString();
        }

        // 🔹 Recent applications used by the user
        $recentApplications = SigninLog::where('user_id', $user->id)
            ->whereBetween('date_utc', [$start, $end])
            ->when($system, fn($q) => $q->whereRaw('LOWER(system) = ?', [strtolower($system)]))
            ->whereNotNull('application')
            ->where('application', '!=', '')
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

        // 🔹 Load all systems for filters
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

        // ✅ hide system-generated users
        $query = AzureUser::valid()->whereHas('signIns', function ($q) use ($filters) {
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
            'user_ids' => $users->pluck('id')->toArray(),
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

        // ✅ exclude system-generated users with displayName like User-%
$baseQuery = "
    SELECT DISTINCT u.id, u.userPrincipalName, u.displayName, u.mail, u.alternateEmailAddress, 
           u.accountEnabled, u.userType, u.jobTitle, u.department
    FROM Azure_Users u
    WHERE (
        u.displayName IS NULL 
        OR u.displayName = '' 
        OR LOWER(u.displayName) NOT LIKE 'user-%'
    )
    AND NOT EXISTS (
        SELECT 1 
        FROM dbo.Azure_LoginDetails ald 
        WHERE u.id = ald.user_id 
        AND ald.date_utc BETWEEN ? AND ?";


        $params = [$filters['startDate'], $filters['endDate']];

        if (!empty($filters['system'])) {
            $mappedSystems = collect(config('systemmap'))
                ->filter(function ($system) use ($filters) {
                    return $system === $filters['system'];
                })
                ->keys()
                ->toArray();
            
            if (!empty($mappedSystems)) {
                $systemConditions = [];
                foreach ($mappedSystems as $mappedSystem) {
                    $systemConditions[] = "ald.application LIKE ?";
                    $params[] = "%{$mappedSystem}%";
                }
                $baseQuery .= " AND (" . implode(' OR ', $systemConditions) . ")";
            }
        }

        $baseQuery .= ")";

        if ($search) {
            $baseQuery .= " AND (u.displayName LIKE ? OR u.userPrincipalName LIKE ? OR u.mail LIKE ? OR u.alternateEmailAddress LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        $baseQuery .= " ORDER BY u.displayName";

        $countQueryBase = str_replace(" ORDER BY u.displayName", "", $baseQuery);
        $countQuery = "SELECT COUNT(*) as total FROM ({$countQueryBase}) as counted_results";
        $totalCount = \DB::selectOne($countQuery, $params)->total;

        $currentPage = $request->get('page', 1);
        $perPage = $this->perPage;
        $offset = ($currentPage - 1) * $perPage;

        $paginatedQuery = $baseQuery . " OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
        $params[] = $offset;
        $params[] = $perPage;

        $usersData = \DB::select($paginatedQuery, $params);

        $users = new \Illuminate\Pagination\LengthAwarePaginator(
            collect($usersData)->map(function ($user) {
                $azureUser = new AzureUser();
                foreach ((array) $user as $key => $value) {
                    $azureUser->{$key} = $value;
                }
                $azureUser->exists = true;
                return $azureUser;
            }),
            $totalCount,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );

        $users->withQueryString();
        $systems = $filterService->getSystemList();

        Log::info('notLoggedInUsers loaded', [
            'user_count' => $users->total(),
            'filters' => $filters,
            'search' => $search,
            'sample_user_data' => $users->first() ? [
                'id' => $users->first()->id,
                'userPrincipalName' => $users->first()->userPrincipalName,
                'displayName' => $users->first()->displayName,
            ] : 'No users found'
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