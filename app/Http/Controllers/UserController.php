<?php

namespace App\Http\Controllers;

use App\Models\Logintracker\AzureUser;
use App\Models\Logintracker\SigninLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * System names to hide from dropdowns / filters.
     * Stored lowercase for robust comparisons.
     */
    protected array $excludedSystems = [
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
     */
    protected array $displaySystems = [
        'BIZ PRO',
        'BIZ UAT',
        'D365 LIVE',
        'D365 UAT',
        'FIT Express',
        'FIT Express UAT',
        'Odoo',
        'OPS',
        'OPS UAT',
        'Windows Sign In',
    ];

    /**
     * Rows per page for all paginated views.
     * Set to 17 to fit within the standard dashboard viewport without scrolling.
     */
    protected int $perPage = 17;

    // =========================================================================
    // PUBLIC ACTIONS
    // =========================================================================

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

            $users = $this->fetchPaginatedUsers($request, $start, $end, $system, $search);
            $loginStats = $this->computeLoginStats($start, $end, $system);
            $systems = $this->visibleSystems();
            $mappedUsers = $this->mapUsersForResponse($users->items());
            $totalDays = $start->diffInDays($end) + 1;

            if ($request->ajax()) {
                return response()->json([
                    'totalUsers' => $loginStats['totalUsers'],
                    'loggedInCount' => $loginStats['loggedInCount'],
                    'notLoggedInCount' => $loginStats['notLoggedInCount'],
                    'users' => $mappedUsers,
                    'totalDays' => $totalDays,
                    'pagination' => (string) $users->links(),
                ]);
            }

            $rangeLabel = $this->getRangeLabel($rangeInput);
            $systemInput = $system ?: 'All Systems';

            return view(
                'logintracker.dashboard',
                array_merge(
                    compact('mappedUsers', 'rangeInput', 'system', 'systems', 'systemInput', 'rangeLabel', 'totalDays'),
                    $loginStats,
                ),
            );
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
        try {
            // 1. Resolve user
            $user = AzureUser::findByIdentifier($identifier);

            if (!$user) {
                Log::notice('User not found in show', ['identifier' => $identifier]);
                abort(404, 'User not found.');
            }

            // 2. Validate custom date order before resolving (gives user a clear error message)
            if ($request->input('date_preset') === 'custom') {
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');

                if ($startDate && $endDate && Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
                    return redirect()->back()->withInput()->with('error', 'End date cannot be before start date.');
                }
            }

            // 3. Resolve date range
            [$start, $end, $preset] = $this->resolveDateRangeForShow($request);

            // 4. Load system map (used by multiple sections below)
            $systemMap = config('systemmap', []);

            if (empty($systemMap)) {
                Log::warning('System map configuration is empty or not found');
            }

            // 5. Applications used in the current calendar month (always fixed to now)
            $currentMonthApplications = $this->buildApplicationSummary(
                $user->userPrincipalName,
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
                $systemMap,
            );

            // 6. Applications used in the selected date range (controlled by date preset)
            $recentApplications = $this->buildApplicationSummary($user->userPrincipalName, $start, $end, $systemMap);

            // 7. Monthly summary table (only displaySystems columns)
            $monthlySummary = $this->buildMonthlySummary($user->userPrincipalName, $start, $end, $systemMap);

            // 8. Sign-ins list (paginated, all applications, no exclusions)
            $signIns = $this->fetchUserSignIns($request, $user->userPrincipalName, $start, $end);

            // 9. Unique application count for the selected range
            $uniqueAppsCount = $this->countUniqueApps($user->userPrincipalName, $start, $end);

            // 10. Labels for the view
            [$range, $rangeLabel] = $this->buildRangeLabelForShow($preset);

            Log::info('UserController@show completed', [
                'user_id' => $user->id,
                'date_range' => [$start->toDateString(), $end->toDateString()],
                'sign_ins_count' => $signIns->total(),
                'unique_apps' => $uniqueAppsCount,
                'monthly_summary_rows' => count($monthlySummary),
            ]);

            return view(
                'logintracker.users.show',
                compact(
                    'user',
                    'signIns',
                    'start',
                    'end',
                    'range',
                    'rangeLabel',
                    'recentApplications',
                    'currentMonthApplications',
                    'monthlySummary',
                    'uniqueAppsCount',
                ),
            );
        } catch (\Throwable $e) {
            Log::error('UserController@show failed', [
                'identifier' => $identifier,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('dashboard')->with('error', 'Unable to load user details. Please try again.');
        }
    }

    public function activeThisMonth(Request $request)
    {
        try {
            return $this->handleActiveUsersRequest(
                $request,
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
                'logintracker.users.active-this-month',
            );
        } catch (\Exception $e) {
            Log::error('Error in activeThisMonth', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->ajax()) {
                return response()->json(['error' => 'An unexpected error occurred.'], 500);
            }

            return redirect()->route('dashboard')->with('error', 'An unexpected error occurred.');
        }
    }

    public function activeLastMonth(Request $request)
    {
        try {
            return $this->handleActiveUsersRequest(
                $request,
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth(),
                'logintracker.users.active-last-month',
            );
        } catch (\Exception $e) {
            Log::error('Error in activeLastMonth', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->ajax()) {
                return response()->json(['error' => 'An unexpected error occurred.'], 500);
            }

            return redirect()->route('dashboard')->with('error', 'An unexpected error occurred.');
        }
    }

    public function inactiveUsers(Request $request)
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', $this->perPage);
            $threeMonthsAgo = Carbon::now()->subMonths(3)->startOfDay();

            // NOT EXISTS short-circuits on the first match per user — faster than LEFT JOIN + whereNull
            // for exclusion queries on large tables.
            // LOWER() removed so SQL Server can use indexes (valid when collation is CI_AS).
            $query = AzureUser::query()
                ->whereNotNull('displayName')
                ->where('displayName', '!=', '')
                ->whereRaw("displayName NOT LIKE 'User-%'")
                ->whereNotNull('userPrincipalName')
                ->where('userPrincipalName', '!=', '')
                ->whereRaw("userPrincipalName NOT LIKE 'User-%'")
                ->whereRaw(
                    "NOT EXISTS (
                        SELECT 1 FROM Azure_LoginDetails ald
                        WHERE ald.username = Azure_Users.userPrincipalName
                          AND ald.date_utc >= ?
                    )",
                    [$threeMonthsAgo],
                );

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('displayName', 'like', "%{$search}%")
                        ->orWhere('userPrincipalName', 'like', "%{$search}%")
                        ->orWhere('mail', 'like', "%{$search}%")
                        ->orWhere('alternateEmailAddress', 'like', "%{$search}%");
                });
            }

            $users = $query->orderBy('displayName')->paginate($perPage)->withQueryString();

            $users->getCollection()->transform(function ($user) {
                $user->setRelation('signIns', collect());
                return $user;
            });

            if ($request->ajax()) {
                $mappedUsers = $users->map(
                    fn($user) => [
                        'id' => $user->id,
                        'displayName' => $user->displayName,
                        'userPrincipalName' => $user->userPrincipalName,
                        'mail' => $user->mail,
                        'alternateEmailAddress' => $user->alternateEmailAddress,
                        'signIns' => [],
                    ],
                );

                return response()->json([
                    'users' => $mappedUsers->values()->toArray(),
                    'total' => $users->total(),
                    'current_page' => $users->currentPage(),
                    'per_page' => $users->perPage(),
                    'last_page' => $users->lastPage(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                    'pagination' => (string) $users->links(),
                ]);
            }

            return view('logintracker.users.inactive-users', compact('users', 'search'));
        } catch (\Exception $e) {
            Log::error('Error in inactiveUsers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->ajax()) {
                return response()->json(['error' => 'An unexpected error occurred.'], 500);
            }

            return redirect()->route('dashboard')->with('error', 'An unexpected error occurred.');
        }
    }

    // =========================================================================
    // PRIVATE — QUERY HELPERS
    // =========================================================================

    /**
     * Return unique application names from signin logs, excluding configured systems.
     */
    private function visibleSystems(): array
    {
        return SigninLog::select('application')
            ->distinct()
            ->whereNotNull('application')
            ->where('application', '!=', '')
            ->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems)
            ->orderBy('application')
            ->pluck('application')
            ->toArray();
    }

    /**
     * Fetch paginated users with sign-in counts and recent sign-ins loaded separately.
     * Sign-ins are loaded after pagination to avoid N+1 issues on large result sets.
     */
    private function fetchPaginatedUsers(Request $request, Carbon $start, Carbon $end, ?string $system, ?string $search)
    {
        $query = AzureUser::valid();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('displayName', 'like', "%{$search}%")
                    ->orWhere('userPrincipalName', 'like', "%{$search}%")
                    ->orWhere('mail', 'like', "%{$search}%")
                    ->orWhere('alternateEmailAddress', 'like', "%{$search}%");
            });
        }

        if ($system) {
            $query->whereHas('systems', fn($q) => $q->where('name', $system));
        } else {
            $query->whereHas('signIns', function ($q) use ($start, $end) {
                $q->whereBetween('date_utc', [$start, $end]);
                if (!empty($this->excludedSystems)) {
                    $q->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems);
                }
            });
        }

        $query->withCount([
            'signIns as sign_ins_count' => function ($q) use ($start, $end, $system) {
                $q->whereBetween('date_utc', [$start, $end]);
                if (!empty($this->excludedSystems)) {
                    $q->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems);
                }
                if ($system) {
                    $this->applySystemFilter($q, $system);
                }
            },
        ]);

        $users = $query->paginate($this->perPage)->withQueryString();

        // Sign-ins are loaded after pagination to avoid inflated row counts that
        // eager loading with constraints causes before the LIMIT is applied.
        $users->getCollection()->transform(function ($user) use ($start, $end, $system) {
            $signInsQuery = SigninLog::where('user_id', $user->id)->whereBetween('date_utc', [$start, $end]);

            if (!empty($this->excludedSystems)) {
                $signInsQuery->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems);
            }

            if ($system) {
                $this->applySystemFilter($signInsQuery, $system);
            }

            $user->setRelation('signIns', $signInsQuery->latest('date_utc')->get());
            return $user;
        });

        return $users;
    }

    /**
     * Compute total, logged-in, and not-logged-in user counts for the given range.
     */
    private function computeLoginStats(Carbon $start, Carbon $end, ?string $system): array
    {
        $loggedInEmails = SigninLog::whereBetween('date_utc', [$start, $end])
            ->when(
                !empty($this->excludedSystems),
                fn($q) => $q->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems),
            )
            ->when($system, fn($q) => $this->applySystemFilter($q, $system))
            ->get()
            ->pluck('username')
            ->filter()
            ->unique()
            ->map(fn($email) => strtolower(trim($email)));

        $totalUsers = AzureUser::valid()->count();
        $loggedInCount = $loggedInEmails->isEmpty()
            ? 0
            : AzureUser::valid()->whereIn(DB::raw('LOWER(userPrincipalName)'), $loggedInEmails)->count();

        return [
            'totalUsers' => $totalUsers,
            'loggedInCount' => $loggedInCount,
            'notLoggedInCount' => $totalUsers - $loggedInCount,
        ];
    }

    /**
     * Map user models to a plain array structure safe for JSON responses and views.
     */
    private function mapUsersForResponse(array $users): array
    {
        return array_map(
            fn($user) => [
                'id' => $user->id,
                'displayName' => $user->displayName,
                'userPrincipalName' => $user->userPrincipalName,
                'mail' => $user->mail,
                'alternateEmailAddress' => $user->alternateEmailAddress,
                'signIns' => $user->signIns->toArray(),
                'sign_ins_count' => $user->sign_ins_count,
            ],
            $users,
        );
    }

    /**
     * Fetch paginated sign-ins for a single user in the given date range.
     * No system exclusions — all applications are shown on the user profile.
     */
    private function fetchUserSignIns(Request $request, string $userPrincipalName, Carbon $start, Carbon $end)
    {
        $query = SigninLog::where(DB::raw('LOWER(username)'), strtolower($userPrincipalName))->whereBetween(
            'date_utc',
            [$start, $end],
        );

        if ($request->filled('search')) {
            $query->where('application', 'like', '%' . $request->input('search') . '%');
        }

        return $query->orderBy('date_utc', 'desc')->paginate($this->perPage)->withQueryString();
    }

    /**
     * Count distinct applications used by a user in the given date range.
     */
    private function countUniqueApps(string $userPrincipalName, Carbon $start, Carbon $end): int
    {
        return SigninLog::where(DB::raw('LOWER(username)'), strtolower($userPrincipalName))
            ->whereBetween('date_utc', [$start, $end])
            ->whereNotNull('application')
            ->where('application', '!=', '')
            ->distinct('application')
            ->count('application');
    }

    /**
     * Shared handler for activeThisMonth and activeLastMonth.
     * Both methods follow identical logic — only the date range and view differ.
     */
    private function handleActiveUsersRequest(Request $request, Carbon $start, Carbon $end, string $view)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', $this->perPage);

        $query = AzureUser::valid()->whereHas('signIns', function ($q) use ($start, $end) {
            $q->whereBetween('date_utc', [$start, $end]);
            if (!empty($this->excludedSystems)) {
                $q->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems);
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

        $query
            ->withCount([
                'signIns as login_count' => function ($q) use ($start, $end) {
                    $q->whereBetween('date_utc', [$start, $end]);
                    if (!empty($this->excludedSystems)) {
                        $q->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems);
                    }
                },
            ])
            ->orderByDesc('login_count');

        $users = $query->paginate($perPage)->withQueryString();

        // Load sign-ins separately after pagination (same reason as fetchPaginatedUsers)
        $users->getCollection()->transform(function ($user) use ($start, $end) {
            $user->setRelation(
                'signIns',
                SigninLog::where('user_id', $user->id)
                    ->whereBetween('date_utc', [$start, $end])
                    ->when(
                        !empty($this->excludedSystems),
                        fn($q) => $q->whereNotIn(DB::raw('LOWER(application)'), $this->excludedSystems),
                    )
                    ->orderByDesc('date_utc')
                    ->limit(5)
                    ->get(),
            );
            return $user;
        });

        if ($request->ajax()) {
            $mappedUsers = $users->map(
                fn($user) => [
                    'id' => $user->id,
                    'displayName' => $user->displayName,
                    'userPrincipalName' => $user->userPrincipalName,
                    'mail' => $user->mail,
                    'alternateEmailAddress' => $user->alternateEmailAddress,
                    'login_count' => $user->login_count ?? 0,
                    'signIns' => $user->signIns->toArray(),
                ],
            );

            return response()->json([
                'users' => $mappedUsers->values()->toArray(),
                'total' => $users->total(),
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
                'pagination' => (string) $users->links(),
            ]);
        }

        return view($view, compact('users', 'search'));
    }

    // =========================================================================
    // PRIVATE — SYSTEM FILTER HELPERS
    // =========================================================================

    /**
     * Resolve all raw application name variations for a given clean system name
     * using the systemmap config.
     */
    private function getRawSystemNames(string $cleanSystemName): array
    {
        $systemMap = config('systemmap', []);

        if (empty($systemMap)) {
            Log::warning('System map configuration not found');
            return [$cleanSystemName];
        }

        $rawNames = collect($systemMap)->filter(fn($mappedName) => $mappedName === $cleanSystemName)->keys()->toArray();

        return !empty($rawNames) ? $rawNames : [$cleanSystemName];
    }

    /**
     * Apply a system name filter to a query, matching against all known raw name variations.
     */
    private function applySystemFilter($query, string $systemName)
    {
        if (empty($systemName)) {
            return $query;
        }

        $rawSystemNames = $this->getRawSystemNames($systemName);

        return $query->where(function ($q) use ($rawSystemNames, $systemName) {
            $q->where(DB::raw('LOWER(application)'), strtolower($systemName));

            foreach ($rawSystemNames as $rawSystem) {
                $q->orWhere(DB::raw('LOWER(application)'), strtolower($rawSystem))->orWhere(
                    'application',
                    'LIKE',
                    "%{$rawSystem}%",
                );
            }

            $q->orWhere(DB::raw('LOWER(application)'), 'like', '%' . strtolower($systemName) . '%');
        });
    }

    // =========================================================================
    // PRIVATE — MONTHLY SUMMARY BUILDERS
    // =========================================================================

    /**
     * Build the full monthly summary pipeline:
     * fetch raw data → map to clean names → build one row per day.
     */
    private function buildMonthlySummary(string $userPrincipalName, Carbon $start, Carbon $end, array $systemMap): array
    {
        try {
            if (empty($systemMap)) {
                throw new \Exception('System map configuration is empty or not found');
            }

            $loginDataRaw = $this->fetchRawLoginData($userPrincipalName, $start, $end);
            $cleanToRawMap = $this->buildCleanToRawMapping($systemMap);
            $processedData = $this->processLoginData($loginDataRaw, $cleanToRawMap);
            $summary = $this->buildMonthlySummaryRows($processedData, $start, $end);

            usort(
                $summary,
                fn($a, $b) => Carbon::createFromFormat('d-M-Y', $a['LoginDate']) <=>
                    Carbon::createFromFormat('d-M-Y', $b['LoginDate']),
            );

            return $summary;
        } catch (\Exception $e) {
            Log::error('Error building monthly summary', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            return [];
        }
    }

    /**
     * Fetch raw login records grouped by date and application for a given user and range.
     * Application names are lowercased and trimmed in the query for consistent matching.
     */
    private function fetchRawLoginData(string $userPrincipalName, Carbon $start, Carbon $end)
    {
        return SigninLog::where(DB::raw('LOWER(username)'), strtolower($userPrincipalName))
            ->whereBetween('date_utc', [$start->format('Y-m-d 00:00:00'), $end->format('Y-m-d 23:59:59')])
            ->whereNotNull('application')
            ->where('application', '!=', '')
            ->select([
                DB::raw('CONVERT(DATE, date_utc) as login_date'),
                DB::raw('LTRIM(RTRIM(LOWER(application))) as application'),
                DB::raw('COUNT(*) as login_count'),
            ])
            ->groupBy(DB::raw('CONVERT(DATE, date_utc)'), DB::raw('LTRIM(RTRIM(LOWER(application)))'))
            ->orderBy('login_date')
            ->get();
    }

    /**
     * Build application usage summary (top 10 by last used) for a user and date range.
     * Counts unique days used per application — not total login events.
     * Includes all applications, both mapped and unmapped.
     */
    private function buildApplicationSummary(string $userPrincipalName, Carbon $start, Carbon $end, array $systemMap)
    {
        $rawData = SigninLog::where(DB::raw('LOWER(username)'), strtolower($userPrincipalName))
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

        $grouped = [];

        foreach ($rawData as $record) {
            $rawLower = strtolower(trim($record->application));
            $cleanName = $systemMap[$rawLower] ?? ($systemMap[$record->application] ?? $record->application);
            $isMapped = isset($systemMap[$rawLower]) || isset($systemMap[$record->application]);

            if (!isset($grouped[$cleanName])) {
                $grouped[$cleanName] = [
                    'application' => $cleanName,
                    'dates' => [],
                    'last_used' => $record->last_used,
                    'is_mapped' => $isMapped,
                ];
            }

            if (!in_array($record->login_date, $grouped[$cleanName]['dates'])) {
                $grouped[$cleanName]['dates'][] = $record->login_date;
            }

            if ($record->last_used > $grouped[$cleanName]['last_used']) {
                $grouped[$cleanName]['last_used'] = $record->last_used;
            }
        }

        return collect($grouped)
            ->map(
                fn($item) => (object) [
                    'application' => $item['application'],
                    'usage_count' => count($item['dates']),
                    'last_used' => $item['last_used'],
                    'is_mapped' => $item['is_mapped'],
                ],
            )
            ->sortByDesc('last_used')
            ->take(10)
            ->values();
    }

    /**
     * Build reverse lookup: clean display name => array of lowercase raw name variations.
     * Used to match raw application strings from signin logs to display system columns.
     */
    private function buildCleanToRawMapping(array $systemMap): array
    {
        $cleanToRawMap = [];

        foreach ($this->displaySystems as $cleanName) {
            $rawVariations = collect($systemMap)
                ->filter(fn($mapped) => strcasecmp(trim($mapped), trim($cleanName)) === 0)
                ->keys()
                ->map(fn($raw) => strtolower(trim($raw)))
                ->toArray();

            // Include the clean name itself as a fallback variation
            $rawVariations[] = strtolower(trim($cleanName));

            $cleanToRawMap[$cleanName] = array_unique($rawVariations);
        }

        return $cleanToRawMap;
    }

    /**
     * Map raw login records to clean system names.
     * Returns: [date => [cleanSystemName => count]]
     */
    private function processLoginData($loginDataRaw, array $cleanToRawMap): array
    {
        $processedData = [];
        $unmatchedApps = [];

        foreach ($loginDataRaw as $record) {
            $dateKey = Carbon::parse($record->login_date)->format('Y-m-d');
            $rawApp = $record->application; // Already lowercased and trimmed in the query
            $matched = false;

            foreach ($cleanToRawMap as $cleanSystem => $rawVariations) {
                if (in_array($rawApp, $rawVariations, true)) {
                    $processedData[$dateKey][$cleanSystem] =
                        ($processedData[$dateKey][$cleanSystem] ?? 0) + (int) $record->login_count;

                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                $unmatchedApps[] = $record->application;
            }
        }

        if (!empty($unmatchedApps)) {
            Log::warning('Unmatched applications in monthly summary', [
                'unique_unmatched' => array_unique($unmatchedApps),
                'total_unmatched' => count($unmatchedApps),
            ]);
        }

        return $processedData;
    }

    /**
     * Build one row per calendar day in the range.
     * Each row has a LoginDate and one column per displaySystem ('1' if active, '' if not).
     */
    private function buildMonthlySummaryRows(array $processedData, Carbon $start, Carbon $end): array
    {
        $summary = [];
        $currentDate = $start->copy();

        while ($currentDate <= $end) {
            $dateKey = $currentDate->format('Y-m-d');

            $row = ['LoginDate' => $currentDate->format('d-M-Y')];

            foreach ($this->displaySystems as $system) {
                $row[$system] = '';
            }

            if (isset($processedData[$dateKey])) {
                foreach ($processedData[$dateKey] as $cleanSystem => $count) {
                    if (in_array($cleanSystem, $this->displaySystems, true)) {
                        $row[$cleanSystem] = $count > 0 ? '1' : '';
                    }
                }
            }

            $summary[] = $row;
            $currentDate->addDay();
        }

        return $summary;
    }

    // =========================================================================
    // PRIVATE — DATE RANGE HELPERS
    // =========================================================================

    /**
     * Resolve date range for the index/dashboard view.
     * Accepts a Request or a plain string range key.
     * Both hyphen (this-month) and underscore (this_month) formats are supported
     * because the dashboard and user profile views each send a different format.
     */
    protected function resolveDateRange($input = null): array
    {
        if ($input instanceof Request) {
            $range = $input->input('range', 'this_month');
            $startDate = $input->input('start_date');
            $endDate = $input->input('end_date');

            if ($range === 'custom') {
                if ($startDate && $endDate && strtotime($startDate) && strtotime($endDate)) {
                    return [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()];
                }

                Log::warning('Custom range selected but dates missing', [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);

                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            }

            return $this->resolveDateRange($range);
        }

        // Normalise both formats so this-month and this_month resolve identically
        $normalised = str_replace('-', '_', (string) $input);

        switch ($normalised) {
            case 'last_month':
                return [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()];
            case 'last_3_months':
                return [Carbon::now()->subMonths(3)->startOfMonth(), Carbon::now()->endOfMonth()];
            case 'this_month':
            default:
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
        }
    }

    /**
     * Resolve date range for the user show/profile view.
     * Reads 'date_preset', 'start_date', and 'end_date' from the request.
     * Returns [$start, $end, $preset].
     */
    private function resolveDateRangeForShow(Request $request): array
    {
        $preset = $request->input('date_preset', 'custom');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $now = Carbon::now();

        if ($preset !== 'custom') {
            [$start, $end] = $this->resolveDateRange($preset);
            return [$start, $end, $preset];
        }

        if ($startDate && $endDate && strtotime($startDate) && strtotime($endDate)) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            if ($end->lt($start)) {
                // Return current month as safe fallback; caller handles the user-facing error
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), $preset];
            }

            return [$start, $end, $preset];
        }

        // Default: current month when no valid dates are supplied
        return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), $preset];
    }

    /**
     * Return the [rangeKey, displayLabel] pair for the show/profile view.
     */
    private function buildRangeLabelForShow(string $preset): array
    {
        $map = [
            'this-month' => ['this-month', 'This Month'],
            'last-month' => ['last-month', 'Last Month'],
            'last-3-months' => ['last-3-months', 'Last 3 Months'],
        ];

        return $map[$preset] ?? ['custom', 'Custom Range'];
    }

    /**
     * Return the display label for the dashboard range selector.
     */
    private function getRangeLabel(string $range, Carbon $start = null, Carbon $end = null): string
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
}
