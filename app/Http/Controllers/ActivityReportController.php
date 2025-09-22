<?php

namespace App\Http\Controllers;

use App\Models\Logintracker\AzureUser;
use App\Models\Logintracker\SigninLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller responsible for generating user activity reports
 * over a specified time period.
 */
class ActivityReportController extends Controller
{
    /**
     * System names to exclude from reports (same as other controllers)
     */
    private $excludedSystems = [
        'import-unknown',
        'unknown',
        'microsoft teams',
        'office 365',
        'windows authentication',
        'it infrastructure',
        'fiterp uat',
        'web browser',
        'mobile/desktop client',
        'all'
    ];

    /**
     * Display a report of user activity (login count, first login, last login)
     * for the selected date range, defaulting to the past 30 days.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function userActivityReport(Request $request)
    {
        // Determine start date
        $startDate = $request->has('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : now()->subDays(30);
            
        // Determine end date
        $endDate = $request->has('date')
            ? Carbon::parse($request->input('date'))->endOfDay()
            : now();

        // FIXED: Use proper model relationships and exclude unwanted systems
        $query = AzureUser::valid() // Use the valid scope to exclude system users
            ->select([
                'Azure_Users.id',
                'Azure_Users.displayName',
                'Azure_Users.userPrincipalName',
                'Azure_Users.mail'
            ])
            ->withCount(['signIns as login_count' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date_utc', [$startDate, $endDate])
                  ->whereNotIn(DB::raw('LOWER(system)'), $this->excludedSystems);
            }])
            ->with(['signIns' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date_utc', [$startDate, $endDate])
                  ->whereNotIn(DB::raw('LOWER(system)'), $this->excludedSystems)
                  ->select('user_id', 'date_utc', 'system')
                  ->orderBy('date_utc');
            }]);

        // Apply search filter if provided
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('Azure_Users.displayName', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('Azure_Users.userPrincipalName', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('Azure_Users.mail', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Execute query and calculate first/last login from the relationship
        $users = $query->orderByDesc('login_count')->paginate(20);
        
        // FIXED: Calculate first_login and last_login from the loaded signIns relationship
        $activity = $users->getCollection()->map(function ($user) {
            $signIns = $user->signIns;
            
            return (object)[
                'id' => $user->id,
                'displayName' => $user->displayName,
                'userPrincipalName' => $user->userPrincipalName,
                'mail' => $user->mail,
                'login_count' => $user->login_count,
                'first_login' => $signIns->isNotEmpty() ? $signIns->min('date_utc') : null,
                'last_login' => $signIns->isNotEmpty() ? $signIns->max('date_utc') : null,
            ];
        });
        
        // Replace the collection in the paginator
        $users->setCollection($activity);

        return view('logintracker.activity_report.index', [
            'activity' => $users,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    /**
     * Alternative method using raw SQL for better performance on large datasets
     */
    public function userActivityReportOptimized(Request $request)
    {
        $startDate = $request->has('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : now()->subDays(30);
            
        $endDate = $request->has('date')
            ? Carbon::parse($request->input('date'))->endOfDay()
            : now();

        // Build excluded systems for SQL IN clause
        $excludedSystemsSQL = "'" . implode("', '", $this->excludedSystems) . "'";

        // Raw SQL query for better performance
        $sql = "
            SELECT 
                au.id,
                au.displayName,
                au.userPrincipalName,
                au.mail,
                COUNT(ald.user_id) AS login_count,
                MIN(ald.date_utc) AS first_login,
                MAX(ald.date_utc) AS last_login
            FROM Azure_Users au
            LEFT JOIN Azure_LoginDetails ald ON (
                au.id = ald.user_id 
                AND ald.date_utc BETWEEN ? AND ?
                AND LOWER(ald.system) NOT IN ({$excludedSystemsSQL})
            )
            WHERE au.displayName NOT LIKE 'User-%'
        ";

        $params = [$startDate, $endDate];

        // Add search filter if provided
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $sql .= " AND (
                au.displayName LIKE ? 
                OR au.userPrincipalName LIKE ? 
                OR au.mail LIKE ?
            )";
            $params = array_merge($params, ["%{$searchTerm}%", "%{$searchTerm}%", "%{$searchTerm}%"]);
        }

        $sql .= "
            GROUP BY au.id, au.displayName, au.userPrincipalName, au.mail
            ORDER BY login_count DESC
        ";

        // Execute query
        $results = DB::select($sql, $params);
        
        // Convert to collection for pagination
        $collection = collect($results);
        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $currentItems = $collection->slice(($currentPage - 1) * $perPage, $perPage);
        
        $activity = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $collection->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('logintracker.activity_report.index', [
            'activity' => $activity,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
}