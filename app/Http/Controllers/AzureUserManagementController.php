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
    /**
     * Display a listing of users with statistics
     */
    public function index()
    {
        try {
            // Order by Id instead of createdDateTime (which is NULL for most users)
            $users = AzureUser::orderBy('Id', 'desc')->paginate(15);

            // Calculate stats - handle NULL Status values
            $stats = [
                'total' => AzureUser::count(),
                'active' => AzureUser::where('Status', 'Active')->count(),
                'pending' => AzureUser::where('Status', 'Pending')->count(),
                'inactive' => AzureUser::whereIn('Status', ['Inactive', null])->count(),
            ];

            return view('azure-management.users.index', compact('users', 'stats'));
        } catch (\Exception $e) {
            Log::error('Azure Dashboard Error: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to load dashboard: ' . $e->getMessage()]);
        }
    }
    /**
     * Get user statistics for dashboard (API endpoint)
     */
    public function stats()
    {
        $stats = [
            'total' => AzureUser::count(),
            'active' => AzureUser::where('Status', 'Active')->count(),
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
        try {
            // Fetch modules from SQL Server Azure_SoftwareMaster
            $modulesRaw = DB::connection('sqlsrv')
                ->select("SELECT Id, Name, ApplicationId 
                     FROM Azure_SoftwareMaster 
                     WHERE IsActive = 1 AND IsDeleted = 0 
                     ORDER BY Name");

            // Convert to Laravel collection with proper structure
            $modules = collect($modulesRaw)->map(function ($module) {
                return (object)[
                    'id' => $module->Id,
                    'name' => $module->Name,
                    'application_id' => $module->ApplicationId
                ];
            });

            // Fetch locations from Cust_Office table
            $locationsRaw = DB::connection('sqlsrv')
                ->select("SELECT Id, Name, code 
                     FROM Cust_Office 
                     WHERE IsActive = 1 AND IsDeleted = 0 
                     ORDER BY Name");

            // Convert to Laravel collection
            $locations = collect($locationsRaw)->map(function ($location) {
                return (object)[
                    'id' => $location->Id,
                    'name' => $location->Name,
                    'code' => $location->code
                ];
            });

            // Empty collections for now (we'll add these later if needed)
            $companies = collect([]);
            $roles = collect([]);
            $rolePermissions = [];

            return view('azure-management.users.create', compact(
                'locations',
                'companies',
                'modules',
                'roles',
                'rolePermissions'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading create form', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('azure.dashboard.index')
                ->withErrors(['error' => 'Failed to load form: ' . $e->getMessage()]);
        }
    }

    /**
     * Store a newly created user (with Azure provisioning)
     */
    public function store(Request $request)
    {
        try {

            // Validate required fields
            $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|email|max:255',
                'employee_id' => 'required|string|max:50',
                'phone' => 'required|string|max:30',
            ]);

            DB::connection('sqlsrv')->beginTransaction();

            // Split name into first and last name
            $nameParts = explode(' ', trim($request->name), 2);
            $givenName = $nameParts[0];
            $surname = $nameParts[1] ?? $nameParts[0];

            // Generate userPrincipalName from email
            $userPrincipalName = $request->email;

            // Generate new GUID for user ID
            $userId = DB::connection('sqlsrv')
                ->selectOne("SELECT NEWID() as newId")->newId;

            // Get primary location from first module assignment (if any)
            $primaryLocation = 'KE'; // Default
            if ($request->has('modules') && is_array($request->modules) && count($request->modules) > 0) {
                $firstModule = $request->modules[0];
                if (isset($firstModule['location_code']) && $firstModule['location_code'] !== 'ALL') {
                    $primaryLocation = $firstModule['location_code'];
                }
            }

            // Insert into Azure_Users with Status = 'Pending'
            DB::connection('sqlsrv')->insert("
            INSERT INTO Azure_Users (
                Id, userPrincipalName, displayName, surname, mail, givenName,
                telephoneNumber, mobilePhone, jobTitle, department, 
                officeLocation, accountEnabled, userType, usageLocation, country,
                createdDateTime, Status, EmployeCode, companyName
            )
            VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                GETDATE(), ?, ?, ?
            )
        ", [
                $userId,
                $userPrincipalName,
                $request->name,
                $surname,
                $request->email,
                $givenName,
                $request->phone,
                $request->mobile_phone,
                $request->job_title,
                $request->department,
                $primaryLocation,
                1, // accountEnabled = true
                'Member',
                'KE',
                'Kenya',
                'Pending', // Status
                $request->employee_id,
                $request->company_id
            ]);

            // Process module assignments (if any)
            if ($request->has('modules') && is_array($request->modules)) {
                foreach ($request->modules as $moduleData) {
                    // Check for required fields
                    if (
                        !isset($moduleData['module_id']) ||
                        !isset($moduleData['application_id']) ||
                        !isset($moduleData['location_code']) ||
                        !isset($moduleData['role_id'])
                    ) {
                        continue; // Skip incomplete assignments
                    }

                    // Get the module details
                    $module = DB::connection('sqlsrv')
                        ->selectOne(
                            "SELECT Id, Name, ApplicationId FROM Azure_SoftwareMaster WHERE Id = ?",
                            [$moduleData['module_id']]
                        );

                    if (!$module) {
                        Log::warning('Module not found', ['module_id' => $moduleData['module_id']]);
                        continue;
                    }

                    // Generate new GUID for this mapping
                    $mappingId = DB::connection('sqlsrv')
                        ->selectOne("SELECT NEWID() as newId")->newId;

                    // Determine if this is an SCM module
                    $applicationId = $moduleData['application_id'];
                    $isSCM = in_array($applicationId, ['OPS', 'OPSUAT']);

                    // Prepare insert data
                    $insertData = [
                        $mappingId,                          // Id
                        $userPrincipalName,                  // user_id
                        $applicationId,                      // application_id
                        $request->name,                      // Username
                        $module->Name,                       // ApplicationName
                        $moduleData['function_code'] ?? null, // FunctionCode
                        $moduleData['function_id'] ?? null,   // FunctionID
                        $moduleData['role_code'] ?? null,     // RoleCode
                        $moduleData['role_id'] ?? null,       // RoleID
                        $moduleData['location_code'] ?? null, // OfficeCode
                        $moduleData['location_id'] ?? null,   // OfficeID
                        1,                                    // IsActive
                        0,                                    // IsDelete
                        Auth::id() ?? 'system',              // CreatedBy
                        // CreatedOn will be GETDATE()
                    ];

                    // Insert into Azure_Application_UserMapping
                    DB::connection('sqlsrv')->insert("
                    INSERT INTO Azure_Application_UserMapping (
                        Id,
                        user_id,
                        application_id,
                        Username,
                        ApplicationName,
                        FunctionCode,
                        FunctionID,
                        RoleCode,
                        RoleID,
                        OfficeCode,
                        OfficeID,
                        IsActive,
                        IsDelete,
                        CreatedBy,
                        CreatedOn
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, GETDATE())
                ", $insertData);

                    Log::info('Module assignment created', [
                        'user' => $userPrincipalName,
                        'module' => $module->Name,
                        'application_id' => $applicationId,
                        'location_code' => $moduleData['location_code'],
                        'location_id' => $moduleData['location_id'],
                        'role_code' => $moduleData['role_code'],
                        'role_id' => $moduleData['role_id'],
                        'function_code' => $moduleData['function_code'] ?? null,
                        'function_id' => $moduleData['function_id'] ?? null,
                        'is_scm' => $isSCM
                    ]);
                }
            }

            DB::connection('sqlsrv')->commit();

            Log::info('User created successfully in SQL Server', [
                'user_id' => $userId,
                'email' => $request->email,
                'primary_location' => $primaryLocation,
                'status' => 'Pending',
                'modules_count' => count($request->modules ?? []),
                'created_by' => Auth::id()
            ]);

            return redirect()->route('azure.dashboard.index')
                ->with('success', 'User created successfully! Status: Pending. Module assignments have been configured.');
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();

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
        try {
            // Fetch modules from SQL Server Azure_SoftwareMaster
            $modulesRaw = DB::connection('sqlsrv')
                ->select("SELECT Id, Name, ApplicationId 
                     FROM Azure_SoftwareMaster 
                     WHERE IsActive = 1 AND IsDeleted = 0 
                     ORDER BY Name");

            // Convert to Laravel collection with proper structure
            $modules = collect($modulesRaw)->map(function ($module) {
                return (object)[
                    'id' => $module->Id,
                    'name' => $module->Name,
                    'application_id' => $module->ApplicationId
                ];
            });

            // Fetch locations from Cust_Office table
            $locationsRaw = DB::connection('sqlsrv')
                ->select("SELECT Id, Name, code 
                     FROM Cust_Office 
                     WHERE IsActive = 1 AND IsDeleted = 0 
                     ORDER BY Name");

            // Convert to Laravel collection
            $locations = collect($locationsRaw)->map(function ($location) {
                return (object)[
                    'id' => $location->Id,
                    'name' => $location->Name,
                    'code' => $location->code
                ];
            });

            // Fetch user's existing module assignments
            $userAssignmentsRaw = DB::connection('sqlsrv')
                ->select("
                SELECT 
                    aaum.Id,
                    aaum.user_id,
                    aaum.application_id,
                    aaum.ApplicationName as module_name,
                    aaum.FunctionCode as function_code,
                    aaum.FunctionID as function_id,
                    aaum.RoleCode as role_code,
                    aaum.RoleID as role_id,
                    aaum.OfficeCode as location,
                    aaum.OfficeID as location_id,
                    asm.Id as module_id
                FROM Azure_Application_UserMapping aaum
                LEFT JOIN Azure_SoftwareMaster asm 
                    ON aaum.ApplicationName = asm.Name 
                    AND aaum.application_id = asm.ApplicationId
                WHERE aaum.user_id = ?
                    AND aaum.IsActive = 1
                    AND aaum.IsDelete = 0
                ORDER BY aaum.ApplicationName
            ", [$user->userPrincipalName]);

            // Convert to Laravel collection
            $userModuleAssignments = collect($userAssignmentsRaw)->map(function ($assignment) {
                return [
                    'id' => $assignment->Id,
                    'module_id' => $assignment->module_id,
                    'module_name' => $assignment->module_name,
                    'application_id' => $assignment->application_id,
                    'location' => $assignment->location,
                    'location_id' => $assignment->location_id,
                    'role_id' => $assignment->role_id,
                    'role_code' => $assignment->role_code,
                    'function_id' => $assignment->function_id,
                    'function_code' => $assignment->function_code,
                ];
            });

            Log::info('Edit user page loaded', [
                'user_id' => $user->Id,
                'modules_count' => $modules->count(),
                'locations_count' => $locations->count(),
                'assignments_count' => $userModuleAssignments->count()
            ]);

            // Empty collections for companies and roles (not needed in edit)
            $companies = collect([]);
            $roles = collect([]);

            return view('azure-management.users.edit', compact(
                'user',
                'locations',
                'companies',
                'modules',
                'roles',
                'userModuleAssignments'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading edit form', [
                'user_id' => $user->Id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('azure.dashboard.index')
                ->withErrors(['error' => 'Failed to load edit form: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, AzureUser $user)
    {
        try {
            Log::info('=== UPDATE USER REQUEST ===', [
                'user_id' => $user->Id,
                'user_principal' => $user->userPrincipalName,
                'modules' => $request->input('modules', [])
            ]);

            // Validate the request
            $validated = $request->validate([
                'modules' => 'nullable|array',
                'modules.*.module_id' => 'required',
                'modules.*.application_id' => 'required|string',
                'modules.*.location_id' => 'required',
                'modules.*.location_code' => 'required|string',
                'modules.*.role_id' => 'required',
                'modules.*.role_code' => 'required|string',
                'modules.*.function_id' => 'nullable',
                'modules.*.function_code' => 'nullable|string',
            ]);

            DB::connection('sqlsrv')->beginTransaction();

            // Get the modules data
            $modulesData = $request->input('modules', []);

            Log::info('Processing modules', [
                'count' => count($modulesData),
                'user_principal' => $user->userPrincipalName
            ]);

            // Delete existing module assignments for this user
            DB::connection('sqlsrv')
                ->table('Azure_Application_UserMapping')
                ->where('user_id', $user->userPrincipalName)
                ->update([
                    'IsDelete' => 1,
                    'IsActive' => 0,
                    'ModifiedBy' => Auth::id() ?? 'system',
                    'ModifiedOn' => DB::raw('GETDATE()')
                ]);

            Log::info('Marked existing assignments as deleted', [
                'user_principal' => $user->userPrincipalName
            ]);

            // Process each module assignment
            $insertedCount = 0;
            $errors = [];

            foreach ($modulesData as $index => $moduleData) {
                try {
                    // Get module details
                    $module = DB::connection('sqlsrv')
                        ->table('Azure_SoftwareMaster')
                        ->where('Id', $moduleData['module_id'])
                        ->first();

                    if (!$module) {
                        $errors[] = "Module not found: {$moduleData['module_id']}";
                        continue;
                    }

                    // Generate new GUID for this mapping
                    $mappingId = DB::connection('sqlsrv')
                        ->selectOne("SELECT NEWID() as newId")->newId;

                    // Prepare insert data
                    $insertData = [
                        'Id' => $mappingId,
                        'user_id' => $user->userPrincipalName,
                        'application_id' => $moduleData['application_id'],
                        'Username' => $user->displayName,
                        'ApplicationName' => $module->Name,
                        'FunctionCode' => $moduleData['function_code'] ?? null,
                        'FunctionID' => $moduleData['function_id'] ?? null,
                        'RoleCode' => $moduleData['role_code'],
                        'RoleID' => $moduleData['role_id'],
                        'OfficeCode' => $moduleData['location_code'],
                        'OfficeID' => $moduleData['location_id'],
                        'IsActive' => 1,
                        'IsDelete' => 0,
                        'CreatedBy' => Auth::id() ?? 'system',
                        'CreatedOn' => DB::raw('GETDATE()'),
                    ];

                    // Insert the assignment
                    DB::connection('sqlsrv')
                        ->table('Azure_Application_UserMapping')
                        ->insert($insertData);

                    $insertedCount++;

                    Log::info("Inserted assignment {$insertedCount}", [
                        'module' => $module->Name,
                        'application_id' => $moduleData['application_id'],
                        'location' => $moduleData['location_code'],
                        'role' => $moduleData['role_code']
                    ]);
                } catch (\Exception $e) {
                    $errors[] = "Error processing module {$index}: " . $e->getMessage();
                    Log::error("Error processing module assignment", [
                        'index' => $index,
                        'module_data' => $moduleData,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::connection('sqlsrv')->commit();

            Log::info('Update completed', [
                'user_principal' => $user->userPrincipalName,
                'inserted' => $insertedCount,
                'errors_count' => count($errors)
            ]);

            // Log activity
            try {
                $this->logUserActivity($user, 'updated', "User module assignments updated. {$insertedCount} assignments saved.");
            } catch (\Exception $e) {
                Log::warning('Failed to log user activity', [
                    'error' => $e->getMessage()
                ]);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "User updated successfully! {$insertedCount} assignments saved.",
                    'data' => [
                        'inserted' => $insertedCount,
                        'errors' => $errors
                    ]
                ]);
            }

            return redirect()->route('azure.dashboard.index')
                ->with('success', "User updated successfully! {$insertedCount} assignments saved.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::connection('sqlsrv')->rollBack();

            Log::error('Validation failed', [
                'errors' => $e->errors()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();

            Log::error('Update user failed', [
                'user_id' => $user->Id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update user: ' . $e->getMessage()
                ], 500);
            }

            return back()
                ->with('error', 'Failed to update user: ' . $e->getMessage())
                ->withInput();
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


    /**
     * Get functions for SCM modules (OPS/OPSUAT) using sp_UserCreation_Resourcefunction
     * API endpoint: GET /api/azure/modules/scm-functions
     */
    public function getSCMFunctions()
    {
        try {
            $functions = DB::connection('sqlsrv')
                ->select('EXEC sp_UserCreation_Resourcefunction');

            return response()->json([
                'success' => true,
                'data' => $functions
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch SCM functions', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch functions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get roles based on Application Code using sp_UserCreation_ResourceRole
     * API endpoint: GET /api/azure/modules/roles?application_code=XXX&filter=YYY
     */
    public function getModuleRolesByCode(Request $request)
    {
        // Enable debug mode from config
        $debugMode = config('azure_modules.debug', false);

        try {
            // Step 1: Get parameters from request
            $applicationCode = $request->query('application_code');
            $filter = $request->query('filter', null);

            if ($debugMode) {
                Log::info('🔍 [AZURE ROLES] Request received', [
                    'application_code' => $applicationCode,
                    'filter' => $filter,
                    'request_url' => $request->fullUrl(),
                    'timestamp' => now()->toDateTimeString()
                ]);
            }

            // Step 2: Validate application code
            if (!$applicationCode) {
                Log::warning('⚠️ [AZURE ROLES] Missing application code');

                return response()->json([
                    'success' => false,
                    'message' => 'Application code is required'
                ], 400);
            }

            // Step 3: Get mapping from config file
            $mapping = config('azure_modules.module_mapping', []);

            if ($debugMode) {
                Log::info('📋 [AZURE ROLES] Config mapping loaded', [
                    'total_mappings' => count($mapping),
                    'available_modules' => array_keys($mapping)
                ]);
            }

            // Step 4: Map the application code
            $finalCode = $mapping[$applicationCode] ?? $applicationCode;

            $wasMapped = isset($mapping[$applicationCode]);

            Log::info('🔄 [AZURE ROLES] Application code mapping', [
                'original_code' => $applicationCode,
                'mapped_code' => $finalCode,
                'was_mapped' => $wasMapped,
                'mapping_source' => $wasMapped ? 'config file' : 'direct (no mapping found)'
            ]);

            // Step 5: Call stored procedure
            if ($debugMode) {
                Log::info('🎯 [AZURE ROLES] Calling stored procedure', [
                    'procedure' => 'sp_UserCreation_ResourceRole',
                    'parameters' => [
                        'ApplicationCode' => $finalCode,
                        'Filter' => $filter
                    ]
                ]);
            }

            if ($filter) {
                $roles = DB::connection('sqlsrv')
                    ->select(
                        'EXEC sp_UserCreation_ResourceRole @ApplicationCode = ?, @Filter = ?',
                        [$finalCode, $filter]
                    );
            } else {
                $roles = DB::connection('sqlsrv')
                    ->select(
                        'EXEC sp_UserCreation_ResourceRole @ApplicationCode = ?',
                        [$finalCode]
                    );
            }

            // Step 6: Process results
            $roleCount = count($roles);

            Log::info('✅ [AZURE ROLES] Roles fetched successfully', [
                'application_code' => $applicationCode,
                'mapped_to' => $finalCode,
                'role_count' => $roleCount,
                'filter_applied' => $filter ? true : false
            ]);

            if ($debugMode && $roleCount > 0) {
                Log::info('📊 [AZURE ROLES] Sample roles', [
                    'first_role' => $roles[0] ?? null,
                    'total_count' => $roleCount
                ]);
            }

            // Step 7: Return response
            return response()->json([
                'success' => true,
                'data' => $roles,
                'meta' => [
                    'application_code_requested' => $applicationCode,
                    'application_code_used' => $finalCode,
                    'was_mapped' => $wasMapped,
                    'role_count' => $roleCount,
                    'filter' => $filter
                ]
            ]);
        } catch (\Exception $e) {
            // Step 8: Error handling
            Log::error('❌ [AZURE ROLES] Failed to fetch module roles', [
                'application_code' => $request->query('application_code'),
                'filter' => $request->query('filter'),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch roles: ' . $e->getMessage(),
                'error_details' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ] : null
            ], 500);
        }
    }
}
