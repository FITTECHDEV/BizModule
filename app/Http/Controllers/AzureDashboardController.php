<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AzureUser;
use App\Models\Company;
use App\Models\Module;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AzureDashboardController extends Controller
{
    /**
     * Display the dashboard with statistics and users
     */
    public function index()
    {
        try {
            // Get filter parameters from request
            $search = request('search', '');
            $status = request('status', '');
            $department = request('department', '');
            $company = request('company', '');
            $location = request('location', '');
            $searchType = request('search_type', 'all');

            // Build query
            $query = AzureUser::query();

            // Apply department filter
            if ($department) {
                if ($department === 'no_department') {
                    $query->whereNull('department');
                } else {
                    $query->where('department', $department);
                }
            }

            // Apply company filter
            if ($company) {
                if ($company === 'no_company') {
                    $query->whereNull('companyName');
                } else {
                    $query->where('companyName', $company);
                }
            }

            // Apply location filter
            if ($location) {
                if ($location === 'no_location') {
                    $query->whereNull('officeLocation');
                } else {
                    $query->where('officeLocation', $location);
                }
            }

            if ($search) {
                $searchLower = strtolower($search);

                $query->where(function ($q) use ($search, $searchLower, $searchType) {
                    if ($searchType === 'department') {
                        // Department-only search
                        $q->whereRaw('LOWER(department) = ?', [$searchLower])
                            ->orWhereRaw("LOWER(department) LIKE ?", ['% ' . $searchLower . ' %'])
                            ->orWhereRaw("LOWER(department) LIKE ?", [$searchLower . ' %'])
                            ->orWhereRaw("LOWER(department) LIKE ?", ['% ' . $searchLower]);
                    } elseif ($searchType === 'all') {
                        // All fields search (with department priority)
                        $q->whereRaw('LOWER(department) = ?', [$searchLower])
                            ->orWhereRaw("LOWER(department) LIKE ?", ['% ' . $searchLower . ' %'])
                            ->orWhereRaw("LOWER(department) LIKE ?", [$searchLower . ' %'])
                            ->orWhereRaw("LOWER(department) LIKE ?", ['% ' . $searchLower])
                            ->orWhereRaw('LOWER(companyName) LIKE ?', ['%' . $searchLower . '%'])
                            ->orWhereRaw('LOWER(displayName) LIKE ?', ['%' . $searchLower . '%'])
                            ->orWhereRaw('LOWER(officeLocation) LIKE ?', ['%' . $searchLower . '%'])
                            ->orWhere('EmployeCode', 'LIKE', "%{$search}%");

                        if (!str_contains($search, '@')) {
                            $q->orWhere('mail', 'LIKE', "{$search}%@%")
                                ->orWhere('userPrincipalName', 'LIKE', "{$search}%@%");
                        }
                    } elseif ($searchType === 'name') {
                        $q->whereRaw('LOWER(displayName) LIKE ?', ['%' . $searchLower . '%']);
                    } elseif ($searchType === 'company') {
                        $q->whereRaw('LOWER(companyName) LIKE ?', ['%' . $searchLower . '%']);
                    } elseif ($searchType === 'email') {
                        if (!str_contains($search, '@')) {
                            $q->where('mail', 'LIKE', "{$search}%@%")
                                ->orWhere('userPrincipalName', 'LIKE', "{$search}%@%");
                        }
                    }
                });
            }

            // Apply status filter (case-insensitive)
            if ($status) {
                $query->where('Status', ucfirst(strtolower($status)));
            }

            // Get paginated users
            $users = $query->orderBy('Id', 'desc')->paginate(15);

            // Calculate stats
            $stats = [
                'total' => AzureUser::count(),
                'active' => AzureUser::where('Status', 'Active')->count(),
                'pending' => AzureUser::where('Status', 'Pending')->count(),
                'inactive' => AzureUser::where('Status', 'Inactive')->count(),
            ];

            // IMPROVED: Fetch unique departments from database with better data cleaning
            $departments = AzureUser::selectRaw('TRIM(department) as department')
                ->whereNotNull('department')
                ->where('department', '!=', '')
                ->whereRaw('TRIM(department) != ?', [''])
                ->distinct()
                ->orderBy('department')
                ->pluck('department')
                ->filter(function ($dept) {
                    // Additional filtering to remove any whitespace-only entries
                    return !empty(trim($dept));
                })
                ->values(); // Re-index the collection after filtering

            // Fetch unique companies from database
            $companies = AzureUser::selectRaw('TRIM(companyName) as companyName')
                ->whereNotNull('companyName')
                ->where('companyName', '!=', '')
                ->whereRaw('TRIM(companyName) != ?', [''])
                ->distinct()
                ->orderBy('companyName')
                ->pluck('companyName')
                ->filter(function ($company) {
                    return !empty(trim($company));
                })
                ->values();

            // Fetch unique locations from database
            $locations = AzureUser::selectRaw('TRIM(officeLocation) as officeLocation')
                ->whereNotNull('officeLocation')
                ->where('officeLocation', '!=', '')
                ->whereRaw('TRIM(officeLocation) != ?', [''])
                ->distinct()
                ->orderBy('officeLocation')
                ->pluck('officeLocation')
                ->filter(function ($location) {
                    return !empty(trim($location));
                })
                ->values();

            return view('azure-management.dashboard.index', compact('stats', 'users', 'departments', 'companies', 'locations'));
        } catch (\Exception $e) {
            Log::error('Azure Dashboard Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to load dashboard: ' . $e->getMessage()]);
        }
    }

    // Add this method to your controller or create a test route
    public function testSearch()
    {
        $search = 'freight in time';

        // Test what's in the database
        $results = [];

        // Check department matches
        $results['department_matches'] = AzureUser::where('department', 'LIKE', '%freight in time%')
            ->select('displayName', 'department', 'mail')
            ->get()
            ->toArray();

        // Check email matches
        $results['email_matches'] = AzureUser::where('mail', 'LIKE', '%freight%')
            ->orWhere('userPrincipalName', 'LIKE', '%freight%')
            ->select('displayName', 'department', 'mail', 'userPrincipalName')
            ->get()
            ->toArray();

        // Check partial matches
        $results['freight_matches'] = AzureUser::where('department', 'LIKE', '%freight%')
            ->orWhere('mail', 'LIKE', '%freight%')
            ->orWhere('displayName', 'LIKE', '%freight%')
            ->select('displayName', 'department', 'mail')
            ->get()
            ->toArray();

        return response()->json($results);
    }

    /**
     * Get dashboard statistics for AJAX requests
     */
    public function getStats()
    {
        try {
            $stats = [
                'total' => AzureUser::count(),
                'active' => AzureUser::where('Status', 'Active')->count(),
                'pending' => AzureUser::where('Status', 'Pending')->count(),
                'inactive' => AzureUser::where('Status', 'Inactive')->count(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Azure Dashboard Stats Error: ' . $e->getMessage());

            return response()->json([
                'total' => 0,
                'active' => 0,
                'pending' => 0,
                'inactive' => 0,
            ], 500);
        }
    }

    /**
     * Generate reports data
     */
    public function reports()
    {
        try {
            // Users by location
            $usersByLocation = AzureUser::selectRaw('officeLocation, count(*) as count')
                ->whereNotNull('officeLocation')
                ->groupBy('officeLocation')
                ->get();

            // Users by status
            $usersByStatus = AzureUser::selectRaw('Status, count(*) as count')
                ->groupBy('Status')
                ->get();

            return response()->json([
                'users_by_location' => $usersByLocation,
                'users_by_status' => $usersByStatus,
            ]);
        } catch (\Exception $e) {
            Log::error('Azure Dashboard Reports Error: ' . $e->getMessage());

            return response()->json([
                'users_by_location' => [],
                'users_by_status' => [],
            ], 500);
        }
    }

    /**
     * Get recent activity for dashboard
     */
    public function getRecentActivity()
    {
        try {
            $recentUsers = AzureUser::whereNotNull('createdDateTime')
                ->orderBy('createdDateTime', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($user) {
                    return [
                        'type' => 'user_created',
                        'message' => "New user {$user->displayName} was created",
                        'timestamp' => $user->createdDateTime ? $user->createdDateTime->diffForHumans() : 'Unknown',
                        'user' => $user->displayName,
                        'status' => $user->Status ?? 'pending',
                    ];
                });

            return response()->json($recentUsers);
        } catch (\Exception $e) {
            Log::error('Azure Dashboard Activity Error: ' . $e->getMessage());

            return response()->json([]);
        }
    }
}
