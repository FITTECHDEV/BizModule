<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
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
            // Simple user statistics
            $stats = [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'pending' => User::where('status', 'pending')->count(),
                'inactive' => User::where('status', 'inactive')->count(),
            ];
            
            // Simple user list - no pagination for now
            $users = User::orderBy('created_at', 'desc')->limit(10)->get();
            
            // FIXED: Changed view path to match your folder structure
            return view('azure-management.dashboard.index', compact('stats', 'users'));
            
        } catch (\Exception $e) {
            Log::error('Azure Dashboard Error: ' . $e->getMessage());
            
            // If there are no users yet, create empty data
            $stats = [
                'total' => 0,
                'active' => 0,
                'pending' => 0,
                'inactive' => 0,
            ];
            
            $users = collect([]); // Empty collection
            
            // FIXED: Changed view path here too
            return view('azure-management.dashboard.index', compact('stats', 'users'));
        }
    }

    /**
     * Get dashboard statistics for AJAX requests
     */
    public function getStats()
    {
        try {
            $stats = [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'pending' => User::where('status', 'pending')->count(),
                'inactive' => User::where('status', 'inactive')->count(),
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
            $usersByLocation = User::selectRaw('location, count(*) as count')
                ->groupBy('location')
                ->get();

            // Users by status
            $usersByStatus = User::selectRaw('status, count(*) as count')
                ->groupBy('status')
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
            $recentUsers = User::orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($user) {
                    return [
                        'type' => 'user_created',
                        'message' => "New user {$user->name} was created",
                        'timestamp' => $user->created_at->diffForHumans(),
                        'user' => $user->name,
                        'status' => $user->status ?? 'pending',
                    ];
                });

            return response()->json($recentUsers);
            
        } catch (\Exception $e) {
            Log::error('Azure Dashboard Activity Error: ' . $e->getMessage());
            
            return response()->json([]);
        }
    }
}