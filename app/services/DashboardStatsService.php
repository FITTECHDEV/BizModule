<?php

namespace App\Services;

use App\Models\Logintracker\SigninLog;
use App\Models\Logintracker\AzureUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\LoginFilterService;

class DashboardStatsService
{
    protected $filterService;

    public function __construct(LoginFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    public function generateStats(array $filters)
    {
        ['startDate' => $startDate, 'endDate' => $endDate, 'system' => $system, 'search' => $search] = $filters;
        $totalDays = (int) ($startDate->diffInDays($endDate) + 1);

        // 🔹 Users with filters - show all users who have used the system, with date-filtered login counts
        $usersQuery = AzureUser::query()
            ->select('id', 'displayName', 'userPrincipalName')
            ->withCount(['signIns as sign_ins_count' => function ($q) use ($filters) {
                // Apply date filters to count only logins in the selected period
                if (!empty($filters['startDate'])) {
                    $q->where('date_utc', '>=', $filters['startDate']);
                }
                if (!empty($filters['endDate'])) {
                    $q->where('date_utc', '<=', $filters['endDate']);
                }
                // Apply system filter
                if (!empty($filters['system'])) {
                    $mappedSystems = collect(config('systemmap'))
                        ->filter(function ($systemName) use ($filters) {
                            return $systemName === $filters['system'];
                        })
                        ->keys()
                        ->toArray();
                    if (!empty($mappedSystems)) {
                        $q->where(function ($query) use ($mappedSystems) {
                            foreach ($mappedSystems as $mappedSystem) {
                                $query->orWhere('application', 'LIKE', "%$mappedSystem%");
                            }
                        });
                    }
                }
            }])
            ->with(['signIns' => function ($q) use ($filters) {
                // Show recent logins filtered by date and system
                if (!empty($filters['startDate'])) {
                    $q->where('date_utc', '>=', $filters['startDate']);
                }
                if (!empty($filters['endDate'])) {
                    $q->where('date_utc', '<=', $filters['endDate']);
                }
                if (!empty($filters['system'])) {
                    $mappedSystems = collect(config('systemmap'))
                        ->filter(function ($systemName) use ($filters) {
                            return $systemName === $filters['system'];
                        })
                        ->keys()
                        ->toArray();
                    if (!empty($mappedSystems)) {
                        $q->where(function ($query) use ($mappedSystems) {
                            foreach ($mappedSystems as $mappedSystem) {
                                $query->orWhere('application', 'LIKE', "%$mappedSystem%");
                            }
                        });
                    }
                }
                $q->orderByDesc('date_utc')->limit(5);
            }]);

        if ($search) {
            $search = strtolower(trim($search));
            $usersQuery->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(displayName) LIKE ?', ["%$search%"])
                  ->orWhereRaw('LOWER(userPrincipalName) LIKE ?', ["%$search%"]);
            });
        }

        $users = $usersQuery->orderBy('displayName')->paginate(17);

        foreach ($users as $user) {
            $uniqueDays = $user->signIns->pluck('date_utc')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->unique()
                ->count();
            $user->missedDays = max(0, $totalDays - $uniqueDays);
            $user->lastLoginDate = optional($user->signIns->first())->date_utc;
        }

        // 🔹 Logged in / not logged in count
        $loggedInQuery = AzureUser::query();

        $loggedInCount = $loggedInQuery->whereHas('signIns', function ($q) use ($filters) {
            // Apply date filters
            if (!empty($filters['startDate'])) {
                $q->where('date_utc', '>=', $filters['startDate']);
            }
            if (!empty($filters['endDate'])) {
                $q->where('date_utc', '<=', $filters['endDate']);
            }
            // Apply system filter to signin logs
            if (!empty($filters['system'])) {
                $mappedSystems = collect(config('systemmap'))
                    ->filter(function ($systemName) use ($filters) {
                        return $systemName === $filters['system'];
                    })
                    ->keys()
                    ->toArray();
                if (!empty($mappedSystems)) {
                    $q->where(function ($query) use ($mappedSystems) {
                        foreach ($mappedSystems as $mappedSystem) {
                            $query->orWhere('application', 'LIKE', "%$mappedSystem%");
                        }
                    });
                }
            }
        })->count();

        // Total users should always be the total count in the database
        $totalUsers = AzureUser::count();

        // Calculate not logged in count - simply total users minus those who logged in during the filtered period
        $notLoggedInCount = $totalUsers - $loggedInCount;

        // 🔹 All sign-ins for listing
        $signInsQuery = SigninLog::query()
            ->join('Azure_Users', 'Azure_LoginDetails.user_id', '=', 'Azure_Users.id')
            ->select(
                'Azure_Users.id',
                'Azure_LoginDetails.date_utc',
                'Azure_LoginDetails.application',
                'Azure_Users.displayName',
                'Azure_Users.userPrincipalName as email'
            )
            ->whereBetween('Azure_LoginDetails.date_utc', [$startDate, $endDate]);

        if ($system) {
            $mappedSystems = collect(config('systemmap'))
                ->filter(function ($systemName) use ($system) {
                    return $systemName === $system;
                })
                ->keys()
                ->toArray();
            if (!empty($mappedSystems)) {
                $signInsQuery->where(function ($query) use ($mappedSystems) {
                    foreach ($mappedSystems as $mappedSystem) {
                        $query->orWhere('Azure_LoginDetails.application', 'LIKE', "%$mappedSystem%");
                    }
                });
            }
        }

        $signIns = $signInsQuery->orderByDesc('Azure_LoginDetails.date_utc')->get();

        // 🔹 Total and last login
        $loginQuery = $this->filterService->applyDateAndSystemFilters(SigninLog::query(), $filters);
        $totalLogins = $loginQuery->count();
        $lastLogin = $loginQuery->max('date_utc');

        return compact(
            'users',
            'loggedInCount',
            'notLoggedInCount',
            'totalUsers',
            'signIns',
            'totalLogins',
            'lastLogin',
            'totalDays'
        );
    }
}