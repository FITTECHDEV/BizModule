<?php

namespace App\Services;

use App\Models\Logintracker\SigninLog;
use App\Models\Logintracker\AzureUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\LoginFilterService;

class DashboardStatsService
{
    protected $filterService;

    public function __construct(LoginFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    /**
     * Get the actual date range of imported data
     * Cached for 5 minutes to avoid repeated queries
     */
    public function getActualDateRange(): array
    {
        return Cache::remember('actual_date_range', 300, function () {
            $minDate = SigninLog::min('date_utc');
            $maxDate = SigninLog::max('date_utc');

            if (!$minDate || !$maxDate) {
                // No data exists, return default range
                return [
                    'min' => Carbon::now()->subDays(30),
                    'max' => Carbon::now(),
                    'has_data' => false
                ];
            }

            return [
                'min' => Carbon::parse($minDate),
                'max' => Carbon::parse($maxDate),
                'has_data' => true
            ];
        });
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
     * SIMPLIFIED: Apply system filter - only checks application field
     * More reliable since import only populates 'application', not 'system'
     */
    private function applySystemFilter($query, $systemName)
    {
        if (empty($systemName)) {
            return $query;
        }

        // Get any mapped raw system names from config
        $rawSystemNames = $this->getRawSystemNames($systemName);
        
        return $query->where(function ($q) use ($rawSystemNames, $systemName) {
            // Primary: Direct case-insensitive match on application field
            $q->whereRaw('LOWER(application) = ?', [strtolower($systemName)]);
            
            // Secondary: Check raw system name mappings
            if (!empty($rawSystemNames)) {
                foreach ($rawSystemNames as $rawSystem) {
                    $q->orWhereRaw('LOWER(application) LIKE ?', ['%' . strtolower($rawSystem) . '%']);
                }
            }
            
            // Tertiary: Partial match fallback
            $q->orWhereRaw('LOWER(application) LIKE ?', ['%' . strtolower($systemName) . '%']);
        });
    }

    /**
     * Validate if the selected date range contains any data
     */
    private function validateDateRange($startDate, $endDate): array
    {
        $actualRange = $this->getActualDateRange();
        
        if (!$actualRange['has_data']) {
            return [
                'valid' => false,
                'message' => 'No sign-in data exists in the database.',
                'suggestion' => 'Please import sign-in data first.'
            ];
        }

        // Check if selected range overlaps with actual data
        $selectedStart = Carbon::parse($startDate);
        $selectedEnd = Carbon::parse($endDate);
        $dataStart = $actualRange['min'];
        $dataEnd = $actualRange['max'];

        // No overlap if selected range is completely before or after data range
        if ($selectedEnd->lt($dataStart) || $selectedStart->gt($dataEnd)) {
            return [
                'valid' => false,
                'message' => "No data exists in selected date range ({$selectedStart->format('M d, Y')} - {$selectedEnd->format('M d, Y')}).",
                'suggestion' => "Available data: {$dataStart->format('M d, Y')} - {$dataEnd->format('M d, Y')}",
                'actual_range' => $actualRange
            ];
        }

        return [
            'valid' => true,
            'actual_range' => $actualRange
        ];
    }

    public function generateStats(array $filters)
    {
        ['startDate' => $startDate, 'endDate' => $endDate, 'system' => $system, 'search' => $search] = $filters;
        
        // Validate date range and get actual data range
        $dateValidation = $this->validateDateRange($startDate, $endDate);
        
        // Log warning if date range doesn't match data
        if (!$dateValidation['valid']) {
            Log::warning('Dashboard date range mismatch', [
                'selected_range' => "{$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}",
                'validation' => $dateValidation
            ]);
        }

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

        // Load signIns separately after pagination
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
                'Azure_Users.displayName',
                'Azure_Users.userPrincipalName as email'
            )
            ->whereBetween('Azure_LoginDetails.date_utc', [$startDate, $endDate]);

        if ($system) {
            // SIMPLIFIED: Only check application field
            $rawSystemNames = $this->getRawSystemNames($system);
            
            $signInsQuery->where(function ($query) use ($rawSystemNames, $system) {
                // Primary: Direct match
                $query->whereRaw('LOWER(Azure_LoginDetails.application) = ?', [strtolower($system)]);
                
                // Secondary: Raw system mappings
                if (!empty($rawSystemNames)) {
                    foreach ($rawSystemNames as $rawSystem) {
                        $query->orWhereRaw('LOWER(Azure_LoginDetails.application) LIKE ?', ['%' . strtolower($rawSystem) . '%']);
                    }
                }
                
                // Tertiary: Partial match
                $query->orWhereRaw('LOWER(Azure_LoginDetails.application) LIKE ?', ['%' . strtolower($system) . '%']);
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
            'totalDays',
            'dateValidation'  // NEW: Include validation info for the view
        );
    }

    /**
     * Get available systems/applications from actual data
     * Cached for 5 minutes
     */
    public function getAvailableSystems(): array
    {
        return Cache::remember('available_systems', 300, function () {
            return SigninLog::query()
                ->select('application')
                ->distinct()
                ->whereNotNull('application')
                ->where('application', '!=', '')
                ->orderBy('application')
                ->pluck('application')
                ->toArray();
        });
    }

    /**
     * Get summary statistics for quick overview
     */
    public function getSummaryStats(): array
    {
        return Cache::remember('dashboard_summary_stats', 300, function () {
            $actualRange = $this->getActualDateRange();
            
            if (!$actualRange['has_data']) {
                return [
                    'has_data' => false,
                    'total_users' => AzureUser::valid()->count(),
                    'total_logins' => 0,
                    'date_range' => null
                ];
            }

            return [
                'has_data' => true,
                'total_users' => AzureUser::valid()->count(),
                'total_logins' => SigninLog::count(),
                'date_range' => [
                    'min' => $actualRange['min']->format('M d, Y'),
                    'max' => $actualRange['max']->format('M d, Y'),
                ],
                'available_systems' => $this->getAvailableSystems(),
                'users_with_logins' => AzureUser::valid()
                    ->whereHas('signIns')
                    ->count()
            ];
        });
    }
}