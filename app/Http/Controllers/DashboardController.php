<?php

namespace App\Http\Controllers;

use App\Models\Logintracker\User as TrackerUser;
use Illuminate\Http\Request;
use App\Services\LoginFilterService;
use App\Services\DashboardStatsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;

class DashboardController extends Controller
{
    protected $filterService;
    protected $statsService;

    public function __construct(LoginFilterService $filterService, DashboardStatsService $statsService)
    {
        $this->filterService = $filterService;
        $this->statsService = $statsService;
    }

    protected function getMockUsers()
    {
        return collect([
            [
                'id' => 'user-1',
                'userPrincipalName' => 'john.doe@example.com',
                'displayName' => 'John Doe',
                'surname1' => 'Doe',
                'givenName1' => 'John',
                'email' => 'john.doe@example.com',
                'userType' => 'Member',
                'jobTitle' => 'Developer',
                'department' => 'IT',
                'accountEnabled' => true,
                'logged_in' => true,
                'createdDateTime' => now()->subDays(10),
                'signIns' => [
                    ['id' => 1, 'application' => 'SCM', 'created_at' => now()->subDays(2)],
                    ['id' => 2, 'application' => 'ERP', 'created_at' => now()->subDays(1)],
                ],
            ],
            [
                'id' => 'user-2',
                'userPrincipalName' => 'jane.smith@example.com',
                'displayName' => 'Jane Smith',
                'surname1' => 'Smith',
                'givenName1' => 'Jane',
                'email' => 'jane.smith@example.com',
                'userType' => 'Member',
                'jobTitle' => 'Manager',
                'department' => 'HR',
                'accountEnabled' => true,
                'logged_in' => false,
                'createdDateTime' => now()->subDays(5),
                'signIns' => [],
            ],
            [
                'id' => 'user-3',
                'userPrincipalName' => 'bob.jones@example.com',
                'displayName' => 'Bob Jones',
                'surname1' => 'Jones',
                'givenName1' => 'Bob',
                'email' => 'bob.jones@example.com',
                'userType' => 'Guest',
                'jobTitle' => 'Consultant',
                'department' => 'Consulting',
                'accountEnabled' => false,
                'logged_in' => false,
                'createdDateTime' => now()->subDays(15),
                'signIns' => [
                    ['id' => 3, 'application' => 'SCM', 'created_at' => now()->subDays(3)],
                ],
            ],
        ]);
    }

    public function index(Request $request)
    {
        $filters = $this->filterService->parseFilters($request);

        // Use mock data for testing
        $users = $this->getMockUsers();

        // Search filter with sanitization
        if ($searchTerm = $request->input('search')) {
            $searchTerm = trim(strip_tags($searchTerm)); // Basic sanitization
            if (strlen($searchTerm) > 255) {
                Log::warning('Search term too long', ['searchTerm' => $searchTerm]);
                $searchTerm = substr($searchTerm, 0, 255);
            }
            $users = $users->filter(function ($user) use ($searchTerm) {
                return stripos($user['displayName'], $searchTerm) !== false ||
                       stripos($user['userPrincipalName'], $searchTerm) !== false;
            });
        }

        // Simulate pagination
        $perPage = 17;
        $currentPage = max(1, (int) $request->input('page', 1));
        $total = $users->count();
        $paginatedUsers = $users->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $users = new LengthAwarePaginator(
            $paginatedUsers,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Mock stats
        $stats = [
            'totalUsers' => $total,
            'loggedInCount' => $users->filter(function ($user) {
                return $user['logged_in'];
            })->count(),
            'notLoggedInCount' => $users->filter(function ($user) {
                return !$user['logged_in'];
            })->count(),
            'totalLogins' => $users->sum(function ($user) {
                return count($user['signIns']);
            }),
            'lastLogin' => $users->max(function ($user) {
                return collect($user['signIns'])->max('created_at');
            }) ?: null,
            'signIns' => $users->flatMap(function ($user) {
                return $user['signIns'];
            })->toArray(),
            'totalDays' => $users->max(function ($user) {
                return now()->diffInDays($user['createdDateTime']);
            }) ?: 0,
        ];

        $systems = $this->filterService->getSystemList();

        // AJAX Response
        if ($request->ajax()) {
            return response()->json([
                'users' => $users->items(),
                'totalUsers' => $stats['totalUsers'],
                'loggedInCount' => $stats['loggedInCount'],
                'notLoggedInCount' => $stats['notLoggedInCount'],
                'totalLogins' => $stats['totalLogins'],
                'lastLogin' => $stats['lastLogin'],
                'signIns' => $stats['signIns'],
                'totalDays' => $stats['totalDays'],
                'rangeInput' => $filters['range'],
                'rangeLabel' => $filters['rangeLabel'],
                'systemInput' => '',
                'systems' => $systems,
                'pagination' => $users->withQueryString()->links()->toHtml(),
            ], 200);
        }

        // Render View
        return view('logintracker.dashboard', [
            'users' => $users,
            'totalUsers' => $stats['totalUsers'],
            'loggedInCount' => $stats['loggedInCount'],
            'notLoggedInCount' => $stats['notLoggedInCount'],
            'totalLogins' => $stats['totalLogins'],
            'lastLogin' => $stats['lastLogin'],
            'signIns' => $stats['signIns'],
            'totalDays' => $stats['totalDays'],
            'rangeInput' => $filters['range'],
            'rangeLabel' => $filters['rangeLabel'],
            'systemInput' => '',
            'systems' => $systems,
            'selectedRange' => $filters['range'],
            'selectedSystem' => '',
        ]);
    }

    public function showDashboard()
    {
        $filters = $this->filterService->parseFilters(request());

        try {
            // Fetch real users from Azure_Users
            $query = TrackerUser::query();

            // Search filter with sanitization
            if ($searchTerm = request()->input('search')) {
                $searchTerm = trim(strip_tags($searchTerm)); // Basic sanitization
                if (strlen($searchTerm) > 255) {
                    Log::warning('Search term too long', ['searchTerm' => $searchTerm]);
                    $searchTerm = substr($searchTerm, 0, 255);
                }
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('displayName', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('userPrincipalName', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Pagination
            $perPage = 17;
            $users = $query->paginate($perPage)->withQueryString();

            // Collect stats
            $stats = [
                'totalUsers' => $users->total(),
                'loggedInCount' => TrackerUser::where(function ($q) {
                    $q->whereNotNull('last_login')
                      ->orWhere('logged_in', true);
                })->distinct('id')->count(),
                'notLoggedInCount' => TrackerUser::where(function ($q) {
                    $q->whereNull('last_login')
                      ->orWhere('logged_in', false);
                })->count(),
                'totalLogins' => SigninLog::count(),
                'lastLogin' => SigninLog::max('date_utc') ?: null,
                'signIns' => SigninLog::latest('date_utc')->limit(100)->get(),
                'totalDays' => TrackerUser::min('createdDateTime')
                    ? \Carbon\Carbon::parse(TrackerUser::min('createdDateTime'))->diffInDays(now())
                    : 0,
            ];
        } catch (QueryException $e) {
            Log::error('Database query failed in showDashboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->view('errors.500', [], 500);
        }

        $systems = $this->filterService->getSystemList();

        return view('logintracker.dashboard', [
            'users' => $users,
            'totalUsers' => $stats['totalUsers'],
            'loggedInCount' => $stats['loggedInCount'],
            'notLoggedInCount' => $stats['notLoggedInCount'],
            'totalLogins' => $stats['totalLogins'],
            'lastLogin' => $stats['lastLogin'],
            'signIns' => $stats['signIns'],
            'totalDays' => $stats['totalDays'],
            'rangeInput' => $filters['range'],
            'rangeLabel' => $filters['rangeLabel'],
            'systemInput' => '',
            'systems' => $systems,
            'selectedRange' => $filters['range'],
            'selectedSystem' => '',
        ]);
    }
}