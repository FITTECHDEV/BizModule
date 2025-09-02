<?php

namespace App\Http\Controllers;

use App\Models\Logintracker\AzureUser;
use App\Models\Logintracker\SigninLog;
use App\Services\LoginFilterService;
use App\Services\DashboardStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $filters = $this->filterService->parseFilters($request);
        $range = $filters['range'] ?? 'this_month';
        $system = $request->input('system', 'SCM');
        $search = $request->input('search');

        // Resolve date range
        $startDate = match ($range) {
            'this_month' => now()->startOfMonth(),
            'last_month' => now()->subMonth()->startOfMonth(),
            'last_3_months' => now()->subMonths(3)->startOfMonth(),
            default => now()->startOfMonth(),
        };
        $endDate = $range === 'last_month' ? now()->subMonth()->endOfMonth() : now()->endOfMonth();
        $totalDays = $startDate->diffInDays($endDate) + 1;

        try {
            // Build query with signIns relationship
            $query = AzureUser::query()->with(['signIns' => function ($q) use ($startDate, $endDate, $system) {
                $q->whereBetween('date_utc', [$startDate, $endDate]);
                if ($system) {
                    $q->where('system', $system);
                }
            }]);

            // Search filter with sanitization
            if ($search) {
                $search = trim(strip_tags($search));
                if (strlen($search) > 255) {
                    Log::warning('Search term too long', ['searchTerm' => $search]);
                    $search = substr($search, 0, 255);
                }
                $query->where(function ($q) use ($search) {
                    $q->where('displayName', 'LIKE', "%{$search}%")
                      ->orWhere('userPrincipalName', 'LIKE', "%{$search}%");
                });
            }

            // Pagination
            $perPage = 17;
            $users = $query->paginate($perPage)->withQueryString();

            // Collect stats
            $stats = [
                'totalUsers' => $users->total(),
                'loggedInCount' => AzureUser::whereHas('signIns', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date_utc', [$startDate, $endDate]);
                })->distinct('id')->count(),
                'notLoggedInCount' => AzureUser::whereDoesntHave('signIns', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date_utc', [$startDate, $endDate]);
                })->count(),
                'totalLogins' => SigninLog::whereBetween('date_utc', [$startDate, $endDate])->count(),
                'lastLogin' => SigninLog::max('date_utc') ?: null,
                'signIns' => SigninLog::whereBetween('date_utc', [$startDate, $endDate])
                    ->latest('date_utc')
                    ->limit(100)
                    ->get(),
                'totalDays' => $totalDays,
            ];

            $systems = $this->filterService->getSystemList();

            // Log users data for debugging
            Log::debug('Dashboard users data', ['users' => $users->toArray()]);

            if ($request->ajax()) {
                return response()->json([
                    'users' => $users->items()->map(function ($user) {
                        return [
                            'id' => $user->id ?? $user->userPrincipalName,
                            'userPrincipalName' => $user->userPrincipalName,
                            'displayName' => $user->displayName ?? 'Unknown',
                            'signIns' => $user->signIns->map(function ($signIn) {
                                return [
                                    'id' => $signIn->id,
                                    'system' => $signIn->system,
                                    'date_utc' => $signIn->date_utc->toIso8601String(),
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
                        ];
                    })->toArray(),
                    'totalDays' => $stats['totalDays'],
                    'rangeInput' => $range,
                    'rangeLabel' => $filters['rangeLabel'] ?? $range,
                    'systemInput' => $system,
                    'systems' => $systems,
                    'pagination' => $users->withQueryString()->links()->toHtml(),
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
                'totalDays' => $stats['totalDays'],
                'rangeInput' => $range,
                'rangeLabel' => $filters['rangeLabel'] ?? $range,
                'systemInput' => $system,
                'systems' => $systems,
                'selectedRange' => $range,
                'selectedSystem' => $system,
            ]);
        } catch (QueryException $e) {
            Log::error('Database query failed in DashboardController', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->view('errors.500', [], 500);
        }
    }
}