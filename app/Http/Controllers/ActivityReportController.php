<?php

namespace App\Http\Controllers;

use App\Models\Logintracker\AzureUser;
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

    // Build query using explicit table name
    $query = AzureUser::select([
            'Azure_Users.id',
            'Azure_Users.displayName',
            'Azure_Users.userPrincipalName',

            DB::raw('COUNT([dbo].[Azure_LoginDetails].user_id) AS login_count'),
            DB::raw('MIN([dbo].[Azure_LoginDetails].date_utc) AS first_login'),
            DB::raw('MAX([dbo].[Azure_LoginDetails].date_utc) AS last_login')
        ])
       ->leftJoin('dbo.Azure_LoginDetails', function ($join) use ($startDate, $endDate) {
    $join->on('dbo.Azure_LoginDetails.username', '=', 'Azure_Users.userPrincipalName')
         ->whereBetween('dbo.Azure_LoginDetails.date_utc', [$startDate, $endDate]);
});

    // Apply search filter if provided
    if ($request->filled('search')) {
        $searchTerm = $request->input('search');
        $query->where(function ($q) use ($searchTerm) {
            $q->where('Azure_Users.displayName', 'LIKE', "%{$searchTerm}%")
              ->orWhere('Azure_Users.userPrincipalName', 'LIKE', "%{$searchTerm}%")
              ->orWhere('Azure_Users.mail', 'LIKE', "%{$searchTerm}%");
        });
    }

    // Execute query
    $activity = $query
        ->groupBy('Azure_Users.id', 'Azure_Users.displayName', 'Azure_Users.userPrincipalName')
        ->orderByDesc('login_count')
        ->paginate(20);

    // Return view
    return view('logintracker.activity_report.index', compact('activity', 'startDate', 'endDate'));
}

}
