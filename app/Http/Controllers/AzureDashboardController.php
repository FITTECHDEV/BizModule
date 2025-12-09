<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AzureUser;  // FIXED: Changed from User to AzureUser
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
        
        // Build query
        $query = AzureUser::query();
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('displayName', 'LIKE', "%{$search}%")
                ->orWhere('mail', 'LIKE', "%{$search}%")
                ->orWhere('userPrincipalName', 'LIKE', "%{$search}%")
                ->orWhere('EmployeCode', 'LIKE', "%{$search}%");
            });
        }
        
        // Apply status filter (case-insensitive)
        if ($status) {
            $query->where('Status', ucfirst(strtolower($status)));
        }
        
        // Get paginated users
        $users = $query->orderBy('Id', 'desc')->paginate(15);
        
        // Calculate stats - handle NULL Status values
        $stats = [
            'total' => AzureUser::count(),
            'active' => AzureUser::where('Status', 'Active')->count(),
            'pending' => AzureUser::where('Status', 'Pending')->count(),
            'inactive' => AzureUser::where('Status', 'Inactive')->count(),
        ];
        
        return view('azure-management.dashboard.index', compact('stats', 'users'));
        
    } catch (\Exception $e) {
        Log::error('Azure Dashboard Error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->back()
            ->withErrors(['error' => 'Failed to load dashboard: ' . $e->getMessage()]);
    }
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
