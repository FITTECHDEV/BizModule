<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 
use App\Models\AzureUser;
use App\Models\AzureCompany;
use App\Models\AzureLocation;
use App\Models\AzureModule;
use App\Models\AzureRole;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\AzureUserProvisioningService;
use App\Services\AzureModuleAssignmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AzureUserManagementController extends Controller
{
    private AzureUserProvisioningService $provisioningService;
    private AzureModuleAssignmentService $moduleAssignmentService;

    public function __construct(
        AzureUserProvisioningService $provisioningService,
        AzureModuleAssignmentService $moduleAssignmentService
    ) {
        $this->provisioningService = $provisioningService;
        $this->moduleAssignmentService = $moduleAssignmentService;
    }

    /**
     * Display a listing of users with statistics
     */
   public function index()
{
    try {
        $users = AzureUser::orderBy('createdDateTime', 'desc')->paginate(15);

        $stats = [
            'total' => AzureUser::count(),
            'active' => AzureUser::where('accountEnabled', true)->count(),
            'pending' => AzureUser::where('accountEnabled', false)->count(),
            'inactive' => AzureUser::where('accountEnabled', false)->count(),
        ];

        return view('azure-management.users.index', compact('users', 'stats'));
    } catch (\Exception $e) {
        dd('Error loading users:', $e->getMessage());
    }
}

    /**
     * Get user statistics for dashboard (API endpoint)
     */
    public function stats()
    {
        $stats = [
            'total' => AzureUser::count(),
            'active' => AzureUser::where('status', 'active')->count(),
            'pending' => AzureUser::where('status', 'pending')->count(),
            'inactive' => AzureUser::where('status', 'inactive')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Show the form for creating a new user
     */
public function create()
{
    // Provide empty collections with proper structure
    $locations = collect([]);
    $companies = collect([]);
    $modules = collect([]);
    $roles = collect([]);
    $rolePermissions = [];

    return view('azure-management.users.create', compact(
        'locations', 
        'companies', 
        'modules', 
        'roles',
        'rolePermissions'
    ));
}

    /**
     * Store a newly created user (with Azure provisioning)
     */
public function store(StoreUserRequest $request)
{
    try {
        // Split name into first and last name
        $nameParts = explode(' ', trim($request->name), 2);
        $givenName = $nameParts[0];
        $surname = $nameParts[1] ?? $nameParts[0];
        
        // Generate userPrincipalName from email
        $userPrincipalName = $request->email;
        
        // Create user with the actual azure_users table columns
        $user = AzureUser::create([
            'displayName' => $request->name,
            'givenName' => $givenName,
            'surname' => $surname,
            'mail' => $request->email,
            'userPrincipalName' => $userPrincipalName,
            'telephoneNumber' => $request->phone,
            'mobilePhone' => $request->mobile_phone,
            'jobTitle' => $request->job_title,
            'department' => $request->department,
            'officeLocation' => $request->location,
            'companyName' => $request->company_id,
            'accountEnabled' => true,
            'userType' => 'Member',
            'usageLocation' => 'KE',
            'country' => 'Kenya',
        ]);

        Log::info('User created successfully', [
            'user_id' => $user->id,
            'email' => $user->mail,
            'created_by' => Auth::id()
        ]);

        return redirect()->route('azure.dashboard.index')
            ->with('success', 'User created successfully!');

    } catch (\Exception $e) {
        Log::error('User creation failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->except(['_token'])
        ]);

        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => 'Failed to create user: ' . $e->getMessage()]);
    }
}

    /**
     * Display the specified user
     */
    public function show(AzureUser $user)
    {
        // $user->load(['company', 'modules.roles', 'roles']);
        
        // Get provisioning status
        $provisioningStatus = $this->provisioningService->getProvisioningStatus($user);
        
        return view('azure-management.users.show', compact('user', 'provisioningStatus'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(AzureUser $user)
    {
        // Comment out database queries
        // $locations = AzureLocation::all();
        // $companies = AzureCompany::all();
        // $modules = AzureModule::with('roles')->get();
        // $roles = AzureRole::all();
        
        // Provide empty collections
        $locations = collect([]);
        $companies = collect([]);
        $modules = collect([]);
        $roles = collect([]);

        // Comment out user module loading
        // $user->load(['modules' => function($query) {
        //     $query->withPivot('role_id', 'location', 'created_at');
        // }]);

        // $userModuleAssignments = $user->modules->map(function ($module) use ($roles, $locations) {
        //     $roleId = $module->pivot->role_id;
        //     $locationCode = $module->pivot->location;
            
        //     // Find role name
        //     $role = $roles->firstWhere('id', $roleId);
        //     $roleName = $role ? $role->name : 'N/A';
            
        //     // Find location name
        //     $location = $locations->firstWhere('code', $locationCode);
        //     $locationName = $location ? $location->name : $locationCode;
            
        //     return [
        //         'module_id' => $module->id,
        //         'module_name' => $module->name,
        //         'role_id' => $roleId,
        //         'role_name' => $roleName,
        //         'location' => $locationCode,
        //         'location_name' => $locationName,
        //         'assigned_at' => $module->pivot->created_at ?? now()
        //     ];
        // });
        
        $userModuleAssignments = collect([]);

        return view('azure-management.users.edit', compact(
            'user', 
            'locations', 
            'companies', 
            'modules', 
            'roles', 
            'userModuleAssignments'
        ));
    }

    /**
     * Update the specified user
     */
    public function update(UpdateUserRequest $request, AzureUser $user)
    {
        try {
            // Prepare update data
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'employee_id' => $request->employee_id,
                'phone' => $request->phone,
                'location' => $request->location,
                'company_id' => $request->company_id,
            ];

            // Update basic user information
            $result = $this->provisioningService->updateUser($user, $updateData);

            // Comment out module assignment changes since tables don't exist
            // Handle module assignment changes
            // if ($request->has('modules')) {
            //     // Process checkbox arrays FIRST (outside transaction)
            //     $processedModules = [];
                
            //     foreach ($request->modules as $index => $moduleData) {
            //         // ... processing code
            //     }

            //     // NOW start transaction
            //     DB::beginTransaction();

            //     try {
            //         // ... module assignment code
            //         DB::commit();
            //     } catch (\Exception $e) {
            //         DB::rollBack();
            //         throw $e;
            //     }
            // }

            $this->logUserActivity($user, 'updated', 'User account updated');

            $message = 'User updated successfully!';
            if (!empty($result['errors'])) {
                $message .= ' Some updates may have failed.';
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    // 'user' => $user->fresh()->load(['company', 'modules']),
                    'user' => $user->fresh(),
                    'update_details' => $result
                ]);
            }

            return redirect()->route('azure.dashboard.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update user: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update user: ' . $e->getMessage()]);
        }
    }

    /**
     * Get roles for a specific module (API endpoint)
     */
    public function getModuleRoles($moduleId)
    {
        try {
            // $module = AzureModule::with('roles')->findOrFail($moduleId);
            
            return response()->json([
                'success' => false,
                'message' => 'Module functionality is currently disabled'
            ], 503);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Module not found'
            ], 404);
        }
    }

    // ... rest of your methods remain the same (disable, enable, getStatus, destroy, retryModuleAssignments, logUserActivity)

    /**
     * Disable the specified user via POST request (for AJAX)
     */
    public function disable(AzureUser $user)
    {
        try {
            // Check if user is already inactive
            if ($user->status === 'inactive') {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already disabled'
                ], 400);
            }

            // Update user status to inactive
            $user->status = 'inactive';
            $user->disabled_at = now();
            $user->save();

            // Try to disable in Azure (optional - don't fail if this fails)
            try {
                if (isset($this->provisioningService)) {
                    $this->provisioningService->disableUser($user);
                }
            } catch (\Exception $e) {
                Log::warning('Azure disable failed but local disable succeeded', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Try to log activity (don't fail if this fails)
            try {
                $this->logUserActivity($user, 'disabled', 'User account disabled');
            } catch (\Exception $e) {
                Log::warning('Failed to log user activity', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'User has been disabled successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to disable user', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to disable user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enable a disabled user (local + Azure)
     */
    public function enable(AzureUser $user)
    {
        try {
            // Check if user is already active
            if ($user->status === 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already enabled'
                ], 400);
            }

            // Update user status to active
            $user->status = 'active';
            $user->disabled_at = null;
            $user->save();

            // Try to enable in Azure (optional)
            try {
                if (isset($this->provisioningService)) {
                    $this->provisioningService->enableUser($user);
                }
            } catch (\Exception $e) {
                Log::warning('Azure enable failed but local enable succeeded', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Try to log activity (don't fail if this fails)
            try {
                $this->logUserActivity($user, 'enabled', 'User account enabled');
            } catch (\Exception $e) {
                Log::warning('Failed to log user activity', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'User has been enabled successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to enable user', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to enable user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user status (API endpoint)
     */
    public function getStatus(AzureUser $user)
    {
        return response()->json([
            'id' => $user->id,
            'status' => $user->status,
            'disabled_at' => $user->disabled_at,
            'last_login_at' => $user->last_login_at,
            // 'is_provisioned' => $user->isProvisionedToAzure(),
        ]);
    }

    /**
     * Remove the specified user (soft delete)
     */
    public function destroy(AzureUser $user)
    {
        try {
            // For DELETE requests, do a soft delete or redirect
            $user->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User deleted successfully'
                ]);
            }

            return redirect()->route('azure.dashboard.index')
                ->with('success', 'User deleted successfully!');

        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete user: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete user: ' . $e->getMessage()]);
        }
    }

    /**
     * Retry failed module assignments for a user
     */
    public function retryModuleAssignments(AzureUser $user)
    {
        try {
            $results = $this->provisioningService->retryFailedModuleAssignments($user);

            $successCount = count(array_filter($results, fn($r) => $r['success']));
            $totalCount = count($results);

            $message = "Retried {$totalCount} module(s): {$successCount} succeeded.";

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'results' => $results
                ]);
            }

            return redirect()->back()
                ->with('success', $message);

        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retry module assignments: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Failed to retry: ' . $e->getMessage()]);
        }
    }

    /**
     * Log user activity for audit trail
     */
    private function logUserActivity(AzureUser $user, string $action, string $description)
    {
        // Comment out if auditLogs relationship doesn't exist
        // $user->auditLogs()->create([
        //     'action' => $action,
        //     'description' => $description,
        //     'performed_by' => Auth::id(),
        //     'ip_address' => request()->ip(),
        //     'user_agent' => request()->userAgent(),
        // ]);
        
        Log::info('User activity', [
            'user_id' => $user->id,
            'action' => $action,
            'description' => $description,
            'performed_by' => Auth::id(),
        ]);
    }
}