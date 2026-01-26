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

    /**
     * Get raw system names that map to a clean system name
     */
    private function getRawSystemNames($cleanSystemName)
    {
        return collect(config('systemmap'))
            ->filter(fn($mappedName) => $mappedName === $cleanSystemName)
            ->keys()
            ->toArray();
    }

    /**
     * Apply system filter to a query - improved version that checks multiple fields
     */
    private function applySystemFilter($query, $systemName)
    {
        if (empty($systemName)) {
            return $query;
        }

        $rawSystemNames = $this->getRawSystemNames($systemName);
        
        return $query->where(function ($q) use ($rawSystemNames, $systemName) {
            // Try direct system field match first
            $q->where(DB::raw('LOWER(application)'), strtolower($systemName));
            
            // If there are raw system mappings, also check application field
            if (!empty($rawSystemNames)) {
                foreach ($rawSystemNames as $rawSystem) {
                    $q->orWhere('application', 'LIKE', "%{$rawSystem}%");
                }
            }
            
            // Also try direct application field match
            $q->orWhere(DB::raw('LOWER(application)'), 'like', '%' . strtolower($systemName) . '%');
        });
    }

    public function generateStats(array $filters)
    {
        ['startDate' => $startDate, 'endDate' => $endDate, 'system' => $system, 'search' => $search] = $filters;
        $totalDays = (int) ($startDate->diffInDays($endDate) + 1);

        // 🔹 Users with filters - show all users who have used the system, with date-filtered login counts
        $usersQuery = AzureUser::valid()
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
                    $this->applySystemFilter($q, $filters['system']);
                }
            }]);

        if ($search) {
            $search = strtolower(trim($search));
            $usersQuery->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(displayName) LIKE ?', ["%$search%"])
                    ->orWhereRaw('LOWER(userPrincipalName) LIKE ?', ["%$search%"]);
            });
        }

        $users = $usersQuery->orderBy('displayName')->paginate(17);

        // FIXED: Load signIns separately after pagination to avoid eager loading issues
        $users->getCollection()->transform(function ($user) use ($filters) {
            $signInsQuery = SigninLog::where('user_id', $user->id);
            
            // Apply date filters
            if (!empty($filters['startDate'])) {
                $signInsQuery->where('date_utc', '>=', $filters['startDate']);
            }
            if (!empty($filters['endDate'])) {
                $signInsQuery->where('date_utc', '<=', $filters['endDate']);
            }
            
            // Apply system filter
            if (!empty($filters['system'])) {
                $this->applySystemFilter($signInsQuery, $filters['system']);
            }
            
            $user->setRelation('signIns', $signInsQuery->orderByDesc('date_utc')->limit(5)->get());
            return $user;
        });

        // Calculate missed days and last login for each user
        foreach ($users as $user) {
            $uniqueDays = $user->signIns->pluck('date_utc')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->unique()
                ->count();
            $user->missedDays = max(0, $totalDays - $uniqueDays);
            $user->lastLoginDate = optional($user->signIns->first())->date_utc;
        }

        // 🔹 Logged in / not logged in count
        $loggedInQuery = AzureUser::valid();

        $loggedInCount = $loggedInQuery->whereHas('signIns', function ($q) use ($filters) {
            // Apply date filters
            if (!empty($filters['startDate'])) {
                $q->where('date_utc', '>=', $filters['startDate']);
            }
            if (!empty($filters['endDate'])) {
                $q->where('date_utc', '<=', $filters['endDate']);
            }
            // Apply system filter
            if (!empty($filters['system'])) {
                $this->applySystemFilter($q, $filters['system']);
            }
        })->count();

        // Total users should always be the total count of valid users in the database
        $totalUsers = AzureUser::valid()->count();

        // Calculate not logged in count - simply total users minus those who logged in during the filtered period
        $notLoggedInCount = $totalUsers - $loggedInCount;

        // 🔹 All sign-ins for listing
        $signInsQuery = SigninLog::query()
            ->join('Azure_Users', 'Azure_LoginDetails.user_id', '=', 'Azure_Users.id')
            ->select(
                'Azure_Users.id',
                'Azure_LoginDetails.date_utc',
                'Azure_LoginDetails.application',
                'Azure_LoginDetails.system',
                'Azure_Users.displayName',
                'Azure_Users.userPrincipalName as email'
            )
            ->whereBetween('Azure_LoginDetails.date_utc', [$startDate, $endDate]);

        if ($system) {
            // Apply system filter to the join query
            $rawSystemNames = $this->getRawSystemNames($system);
            
            $signInsQuery->where(function ($query) use ($rawSystemNames, $system) {
                // Try direct system field match first
                $query->where(DB::raw('LOWER(Azure_LoginDetails.system)'), strtolower($system));
                
                // If there are raw system mappings, also check application field
                if (!empty($rawSystemNames)) {
                    foreach ($rawSystemNames as $rawSystem) {
                        $query->orWhere('Azure_LoginDetails.application', 'LIKE', "%{$rawSystem}%");
                    }
                }
                
                // Also try direct application field match
                $query->orWhere(DB::raw('LOWER(Azure_LoginDetails.application)'), 'like', '%' . strtolower($system) . '%');
            });
        }

        $signIns = $signInsQuery->orderByDesc('Azure_LoginDetails.date_utc')->get();

        // 🔹 Total and last login
        $loginQuery = SigninLog::query()
            ->whereBetween('date_utc', [$startDate, $endDate]);
        
        if ($system) {
            $this->applySystemFilter($loginQuery, $system);
        }
        
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