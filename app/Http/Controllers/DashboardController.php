<?php

namespace App\Http\Controllers;

use App\Models\Logintracker\User;

use Illuminate\Http\Request;
use App\Services\LoginFilterService;
use App\Services\DashboardStatsService;
use App\Models\Logintracker\User as TrackerUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

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

        // Use mock data instead of database query
        $users = $this->getMockUsers();

        // Search filter
        if ($searchTerm = $request->input('search')) {
            $users = $users->filter(function ($user) use ($searchTerm) {
                return stripos($user['displayName'], $searchTerm) !== false ||
                       stripos($user['userPrincipalName'], $searchTerm) !== false;
            });
        }

        // Mock relationship filter (uncomment to test)
        /*
        $selectedSystem = $request->input('system', 'SCM');
        if ($selectedSystem) {
            $mappedSystems = collect(config('systemmap'))
                ->filter(function ($system) use ($selectedSystem) {
                    return $system === $selectedSystem;
                })
                ->keys()
                ->toArray();

            if (!empty($mappedSystems)) {
                $users = $users->filter(function ($user) use ($mappedSystems) {
                    return collect($user['signIns'])->contains(function ($signIn) use ($mappedSystems) {
                        foreach ($mappedSystems as $mappedSystem) {
                            if (stripos($signIn['application'], $mappedSystem) !== false) {
                                return true;
                            }
                        }
                        return false;
                    });
                });
            }
        } else {
            $users = $users->filter(function ($user) {
                return !empty($user['signIns']);
            });
        }
        */

        // Simulate pagination
        $perPage = 17;
        $currentPage = $request->input('page', 1);
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
            }),
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
            ]);
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

    // Fetch real users
    $query = \App\Models\User::query();

    // Search filter
    if ($searchTerm = request()->input('search')) {
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
        'totalUsers'       => $users->total(),
        'loggedInCount'    => SigninLog::distinct('user_id')->count(), // users that have logged in
        'notLoggedInCount' => $users->total() - SigninLog::distinct('user_id')->count(),
        'totalLogins'      => SigninLog::count(),
        'lastLogin'        => SigninLog::max('date_utc'),
        'signIns'          => SigninLog::latest('date_utc')->limit(100)->get(), // last 100 sign-ins
        'totalDays'        => \Carbon\Carbon::parse(
                                \App\Models\User::min('createdDateTime')
                            )->diffInDays(now()),
    ];

    $systems = $this->filterService->getSystemList();

    return view('logintracker.dashboard', [
        'users'           => $users,
        'totalUsers'      => $stats['totalUsers'],
        'loggedInCount'   => $stats['loggedInCount'],
        'notLoggedInCount'=> $stats['notLoggedInCount'],
        'totalLogins'     => $stats['totalLogins'],
        'lastLogin'       => $stats['lastLogin'],
        'signIns'         => $stats['signIns'],
        'totalDays'       => $stats['totalDays'],
        'rangeInput'      => $filters['range'],
        'rangeLabel'      => $filters['rangeLabel'],
        'systemInput'     => '',
        'systems'         => $systems,
        'selectedRange'   => $filters['range'],
        'selectedSystem'  => '',
    ]);
}

}
