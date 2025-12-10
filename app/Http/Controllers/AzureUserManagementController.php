<?php

namespace App\Http\Controllers;

use App\Http\Requests\AzureStoreUserRequest;
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
    public function index(Request $request)
    {
        try {
            // Start building the query
            $query = AzureUser::query();

            // Apply status filter if provided
            if ($status = $request->get('status')) {
                switch (strtolower($status)) {
                    case 'active':
                        $query->where('Status', 'Active');
                        break;
                    case 'pending':
                        $query->where('Status', 'Pending');
                        break;
                    case 'inactive':
                        $query->whereIn('Status', ['Inactive', null]);
                        break;
                }
            }

            // Apply search filter if provided
            if ($search = $request->get('search')) {
                $query->where(function ($q) use ($search) {
                    // Search in name fields
                    $q->where('displayName', 'like', "%{$search}%")
                        // Search in email username part only (before @)
                        ->orWhereRaw("LEFT(mail, CHARINDEX('@', mail) - 1) LIKE ?", ["%{$search}%"])
                        ->orWhereRaw("LEFT(userPrincipalName, CHARINDEX('@', userPrincipalName) - 1) LIKE ?", ["%{$search}%"])
                        // Search in other fields
                        ->orWhere('EmployeCode', 'like', "%{$search}%")
                        ->orWhere('department', 'like', "%{$search}%")
                        ->orWhere('companyName', 'like', "%{$search}%")
                        ->orWhere('officeLocation', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%")
                        ->orWhere('jobTitle', 'like', "%{$search}%");
                });
            }

            // Order by Id and paginate (preserving query parameters)
            $users = $query->orderBy('Id', 'desc')
                ->paginate(15)
                ->appends($request->except('page'));

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
     * Display active users page
     */
    public function active(Request $request)
    {
        try {
            // Build query for active users only
            $query = AzureUser::where('Status', 'Active');

            // Apply search filter if provided
            if ($search = $request->get('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('displayName', 'like', "%{$search}%")
                        ->orWhereRaw("LEFT(mail, CHARINDEX('@', mail) - 1) LIKE ?", ["%{$search}%"])
                        ->orWhereRaw("LEFT(userPrincipalName, CHARINDEX('@', userPrincipalName) - 1) LIKE ?", ["%{$search}%"])
                        ->orWhere('EmployeCode', 'like', "%{$search}%")
                        ->orWhere('department', 'like', "%{$search}%")
                        ->orWhere('companyName', 'like', "%{$search}%")
                        ->orWhere('officeLocation', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%")
                        ->orWhere('jobTitle', 'like', "%{$search}%");
                });
            }

            // Order by Id and paginate
            $users = $query->orderBy('Id', 'desc')
                ->paginate(15)
                ->appends($request->except('page'));

            return view('azure-management.users.active', compact('users'));
        } catch (\Exception $e) {
            Log::error('Active Users Page Error: ' . $e->getMessage());
            return redirect()->route('azure.dashboard.index')
                ->withErrors(['error' => 'Failed to load active users: ' . $e->getMessage()]);
        }
    }

    /**
     * Display pending users page
     */
    public function pending(Request $request)
    {
        try {
            // Build query for pending users only
            $query = AzureUser::where('Status', 'Pending');

            // Apply search filter if provided
            if ($search = $request->get('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('displayName', 'like', "%{$search}%")
                        ->orWhereRaw("LEFT(mail, CHARINDEX('@', mail) - 1) LIKE ?", ["%{$search}%"])
                        ->orWhereRaw("LEFT(userPrincipalName, CHARINDEX('@', userPrincipalName) - 1) LIKE ?", ["%{$search}%"])
                        ->orWhere('EmployeCode', 'like', "%{$search}%")
                        ->orWhere('department', 'like', "%{$search}%")
                        ->orWhere('companyName', 'like', "%{$search}%")
                        ->orWhere('officeLocation', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%")
                        ->orWhere('jobTitle', 'like', "%{$search}%");
                });
            }

            // Order by Id and paginate
            $users = $query->orderBy('Id', 'desc')
                ->paginate(15)
                ->appends($request->except('page'));

            return view('azure-management.users.pending', compact('users'));
        } catch (\Exception $e) {
            Log::error('Pending Users Page Error: ' . $e->getMessage());
            return redirect()->route('azure.dashboard.index')
                ->withErrors(['error' => 'Failed to load pending users: ' . $e->getMessage()]);
        }
    }

    /**
     * Display inactive users page
     */
    public function inactive(Request $request)
    {
        try {
            // Build query for inactive users only
            $query = AzureUser::whereIn('Status', ['Inactive', null]);

            // Apply search filter if provided
            if ($search = $request->get('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('displayName', 'like', "%{$search}%")
                        ->orWhereRaw("LEFT(mail, CHARINDEX('@', mail) - 1) LIKE ?", ["%{$search}%"])
                        ->orWhereRaw("LEFT(userPrincipalName, CHARINDEX('@', userPrincipalName) - 1) LIKE ?", ["%{$search}%"])
                        ->orWhere('EmployeCode', 'like', "%{$search}%")
                        ->orWhere('department', 'like', "%{$search}%")
                        ->orWhere('companyName', 'like', "%{$search}%")
                        ->orWhere('officeLocation', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%")
                        ->orWhere('jobTitle', 'like', "%{$search}%");
                });
            }

            // Order by Id and paginate
            $users = $query->orderBy('Id', 'desc')
                ->paginate(15)
                ->appends($request->except('page'));

            return view('azure-management.users.inactive', compact('users'));
        } catch (\Exception $e) {
            Log::error('Inactive Users Page Error: ' . $e->getMessage());
            return redirect()->route('azure.dashboard.index')
                ->withErrors(['error' => 'Failed to load inactive users: ' . $e->getMessage()]);
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
                    AND Name != 'Odoo'
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
     * Store a newly created user with Azure AD provisioning
     * 
     * Flow:
     * 1. Validate and decode modules JSON
     * 2. Expand ALL selections (locations, roles, functions) server-side
     * 3. Create user in local DB (Status='Pending')
     * 4. Create user in Azure AD
     * 5. Save AzureId to local DB
     * 6. Create all expanded module assignments
     * 7. Rollback everything if ANY step fails (all-or-nothing)
     * 
     * @param AzureStoreUserRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(AzureStoreUserRequest $request)
    {
        // Variables to track what was created (for rollback)
        $userId = null;
        $azureId = null;
        $azureService = null;

        try {
            Log::info('=== USER CREATION STARTED ===', [
                'name' => $request->name,
                'email' => $request->email,
                'employee_id' => $request->employee_id,
                'initiated_by' => Auth::id(),
            ]);

            // ====================================================================
            // STEP 0: Decode and validate modules JSON
            // ====================================================================
            $modulesData = [];

            if ($request->has('modules_json')) {
                $modulesData = json_decode($request->input('modules_json'), true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid modules JSON format: ' . json_last_error_msg());
                }

                Log::info('✓ Modules JSON decoded successfully', [
                    'compact_assignments' => count($modulesData)
                ]);
            } elseif ($request->has('modules')) {
                // Fallback to old format for compatibility
                $modulesData = $request->input('modules');

                Log::info('Using legacy modules format', [
                    'assignments' => count($modulesData)
                ]);
            }

            // Validate we have modules
            if (empty($modulesData)) {
                throw new \Exception('No module assignments provided. Please assign at least one module.');
            }

            // Start database transaction
            DB::connection('sqlsrv')->beginTransaction();

            // ====================================================================
            // STEP 1: Prepare user data
            // ====================================================================

            // Split name into first and last name
            $nameParts = explode(' ', trim($request->name), 2);
            $givenName = $nameParts[0];
            $surname = $nameParts[1] ?? $nameParts[0];

            // Use email as userPrincipalName
            $userPrincipalName = $request->email;

            // Generate new GUID for user ID
            $userId = DB::connection('sqlsrv')
                ->selectOne("SELECT NEWID() as newId")->newId;

            // Determine primary location from first module assignment
            $primaryLocation = 'KE'; // Default to Kenya
            if (!empty($modulesData)) {
                $firstModule = $modulesData[0];
                if (
                    !empty($firstModule['locationCodes']) &&
                    $firstModule['locationCodes'][0] !== 'ALL' &&
                    strtoupper($firstModule['locationCodes'][0]) !== 'ALL'
                ) {
                    $primaryLocation = $firstModule['locationCodes'][0];
                }
            }

            Log::info('✓ User data prepared', [
                'user_id' => $userId,
                'given_name' => $givenName,
                'surname' => $surname,
                'primary_location' => $primaryLocation
            ]);

            // ====================================================================
            // STEP 2: Insert into Azure_Users with Status = 'Pending'
            // ====================================================================

            DB::connection('sqlsrv')->insert("
            INSERT INTO Azure_Users (
                Id, userPrincipalName, displayName, surname, mail, givenName,
                telephoneNumber, mobilePhone, jobTitle, department, 
                officeLocation, accountEnabled, userType, usageLocation, country,
                createdDateTime, Status, EmployeCode, companyName, AzureId
                
            )
            VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                GETDATE(), ?, ?, ?, NULL
            )
        ", [
                $userId,
                $userPrincipalName,
                $request->name,
                $surname,
                $request->email,
                $givenName,
                $request->phone,
                $request->mobile_phone, // Can be null
                $request->job_title,     // Can be null
                $request->department,    // Can be null
                $primaryLocation,
                1, // accountEnabled = true
                'Member',
                'KE',
                'Kenya',
                'Pending', // Status = Pending
                $request->employee_id,
                $request->company_id, // Can be null
            ]);

            Log::info('✓ Step 2: User created in local database', [
                'user_id' => $userId,
                'email' => $request->email,
                'status' => 'Pending'
            ]);

            // ====================================================================
            // STEP 3: Create user in Azure AD
            // ====================================================================

            $azureService = app(\App\Services\AzureGraphService::class);
            $azureUpn = null;

            Log::info('→ Step 3: Checking Azure AD...', [
                'upn' => $userPrincipalName,
                'display_name' => $request->name
            ]);

            // Check if user already exists in Azure AD
            $existingAzureUser = $azureService->findUserByUPN($userPrincipalName);

            if ($existingAzureUser) {
                // User already exists in Azure - link to existing user
                $azureId = $existingAzureUser['id'];
                $azureUpn = $existingAzureUser['userPrincipalName'];

                Log::info('✓ Step 3: User already exists in Azure AD - linking', [
                    'azure_id' => $azureId,
                    'azure_upn' => $azureUpn
                ]);
            } else {
                // Create new user in Azure AD
                $azureUserData = [
                    'accountEnabled' => true,
                    'displayName' => $request->name,
                    'mailNickname' => explode('@', $request->email)[0],
                    'userPrincipalName' => $userPrincipalName,
                    'givenName' => $givenName,
                    'surname' => $surname,
                    'mail' => $request->email,
                    'usageLocation' => 'KE',
                    'passwordProfile' => [
                        'forceChangePasswordNextSignIn' => true,
                        'password' => $this->generateSecurePassword()
                    ]
                ];

                // Add optional fields if present
                if ($request->mobile_phone) {
                    $azureUserData['mobilePhone'] = $request->mobile_phone;
                }
                if ($request->phone) {
                    $azureUserData['businessPhones'] = [$request->phone];
                }
                if ($request->job_title) {
                    $azureUserData['jobTitle'] = $request->job_title;
                }
                if ($request->department) {
                    $azureUserData['department'] = $request->department;
                }
                if ($primaryLocation) {
                    $azureUserData['officeLocation'] = $primaryLocation;
                }

                $azureResult = $azureService->createUser($azureUserData);

                $azureId = $azureResult['id'];
                $azureUpn = $azureResult['userPrincipalName'];

                Log::info('✓ Step 3: User created in Azure AD', [
                    'azure_id' => $azureId,
                    'azure_upn' => $azureUpn
                ]);
            }

            // ====================================================================
            // STEP 4: Update local DB with AzureId
            // ====================================================================

            DB::connection('sqlsrv')->update("
            UPDATE Azure_Users 
            SET AzureId = ?
            WHERE Id = ?
        ", [
                $azureId,
                $userId
            ]);

            Log::info('✓ Step 4: Azure ID saved to local database', [
                'user_id' => $userId,
                'azure_id' => $azureId,
                'status' => 'Pending (awaiting external system sync)'
            ]);

            // ====================================================================
            // STEP 5: Expand module assignments (server-side)
            // ====================================================================

            $expandedAssignments = [];
            $expansionErrors = [];

            Log::info('→ Step 5: Expanding module assignments...', [
                'compact_assignments' => count($modulesData)
            ]);

            foreach ($modulesData as $index => $compactAssignment) {
                try {
                    // Validate required fields
                    if (
                        empty($compactAssignment['module_id']) ||
                        empty($compactAssignment['application_id']) ||
                        empty($compactAssignment['application_name'])
                    ) {
                        $expansionErrors[] = "Assignment {$index}: Missing required fields";
                        continue;
                    }

                    // Verify module exists
                    $module = DB::connection('sqlsrv')
                        ->selectOne(
                            "SELECT Id, Name, ApplicationId FROM Azure_SoftwareMaster 
                        WHERE Id = ? AND IsActive = 1 AND IsDeleted = 0",
                            [$compactAssignment['module_id']]
                        );

                    if (!$module) {
                        $expansionErrors[] = "Assignment {$index}: Module not found or inactive";
                        continue;
                    }

                    // --------------------------------------------------------
                    // 5.1: Expand LOCATIONS
                    // --------------------------------------------------------
                    $locations = [];

                    if (
                        $compactAssignment['location'] === 'ALL' ||
                        in_array('ALL', $compactAssignment['locationCodes'] ?? [])
                    ) {
                        // Fetch ALL locations
                        $locationsRaw = DB::connection('sqlsrv')
                            ->select("SELECT Id, code, Name FROM Cust_Office WHERE IsActive = 1 AND IsDeleted = 0");
                        $locations = collect($locationsRaw);

                        Log::info("  Expanded ALL locations to {$locations->count()} locations");
                    } else {
                        // Fetch specific locations
                        $locationCodes = $compactAssignment['locationCodes'] ?? [];
                        if (!empty($locationCodes)) {
                            $placeholders = implode(',', array_fill(0, count($locationCodes), '?'));
                            $locationsRaw = DB::connection('sqlsrv')
                                ->select(
                                    "SELECT Id, code, Name FROM Cust_Office 
                                WHERE code IN ({$placeholders}) AND IsActive = 1 AND IsDeleted = 0",
                                    $locationCodes
                                );
                            $locations = collect($locationsRaw);

                            Log::info("  Using {$locations->count()} specific locations");
                        }
                    }

                    if ($locations->isEmpty()) {
                        $expansionErrors[] = "Assignment {$index}: No valid locations found";
                        continue;
                    }

                    // --------------------------------------------------------
                    // 5.2: Expand ROLES
                    // --------------------------------------------------------
                    $roles = [];
                    $applicationCode = $compactAssignment['application_name'];

                    if (
                        $compactAssignment['role_id'] === 'ALL' ||
                        in_array('ALL', $compactAssignment['roleIds'] ?? [])
                    ) {
                        // Fetch ALL roles for this application
                        try {
                            $rolesRaw = DB::connection('sqlsrv')
                                ->select(
                                    'EXEC sp_UserCreation_ResourceRole @ApplicationCode = ?',
                                    [$applicationCode]
                                );

                            $roles = collect($rolesRaw)->map(function ($r) {
                                return (object)[
                                    'id' => $r->RoleId ?? $r->Id ?? $r->id,
                                    'code' => $r->RoleCode ?? $r->Code ?? $r->code
                                ];
                            })->filter(function ($r) {
                                return !empty($r->id) && !empty($r->code);
                            });

                            Log::info("  Expanded ALL roles to {$roles->count()} roles for {$applicationCode}");
                        } catch (\Exception $e) {
                            $expansionErrors[] = "Assignment {$index}: Failed to fetch roles - {$e->getMessage()}";
                            continue;
                        }
                    } else {
                        // Use specific roles
                        $roleIds = $compactAssignment['roleIds'] ?? [];
                        $roleCodes = $compactAssignment['roleCodes'] ?? [];

                        if (!empty($roleIds)) {
                            $roles = collect($roleIds)->map(function ($id, $idx) use ($roleCodes) {
                                return (object)[
                                    'id' => $id,
                                    'code' => $roleCodes[$idx] ?? ''
                                ];
                            })->filter(function ($r) {
                                return !empty($r->id) && !empty($r->code);
                            });

                            Log::info("  Using {$roles->count()} specific roles");
                        }
                    }

                    if ($roles->isEmpty()) {
                        $expansionErrors[] = "Assignment {$index}: No valid roles found";
                        continue;
                    }

                    // --------------------------------------------------------
                    // 5.3: Expand FUNCTIONS (SCM modules only)
                    // --------------------------------------------------------
                    $functions = collect([null]); // Default: no functions
                    $isSCM = in_array(strtoupper($applicationCode), ['OPS', 'OPS UAT', 'OPSUAT']);

                    if ($isSCM) {
                        if (
                            $compactAssignment['function_id'] === 'ALL' ||
                            in_array('ALL', $compactAssignment['functionIds'] ?? [])
                        ) {
                            // Fetch ALL functions
                            try {
                                $functionsRaw = DB::connection('sqlsrv')
                                    ->select('EXEC sp_UserCreation_Resourcefunction');

                                $functions = collect($functionsRaw)->map(function ($f) {
                                    return (object)[
                                        'id' => $f->ID ?? $f->Id ?? $f->id,
                                        'code' => $f->Code ?? $f->code
                                    ];
                                })->filter(function ($f) {
                                    return !empty($f->id) && !empty($f->code);
                                });

                                Log::info("  Expanded ALL functions to {$functions->count()} functions");
                            } catch (\Exception $e) {
                                $expansionErrors[] = "Assignment {$index}: Failed to fetch SCM functions - {$e->getMessage()}";
                                continue;
                            }
                        } else {
                            // Use specific functions
                            $functionIds = $compactAssignment['functionIds'] ?? [];
                            $functionCodes = $compactAssignment['functionCodes'] ?? [];

                            if (!empty($functionIds)) {
                                $functions = collect($functionIds)->map(function ($id, $idx) use ($functionCodes) {
                                    return (object)[
                                        'id' => $id,
                                        'code' => $functionCodes[$idx] ?? ''
                                    ];
                                })->filter(function ($f) {
                                    return !empty($f->id) && !empty($f->code);
                                });

                                Log::info("  Using {$functions->count()} specific functions");
                            }
                        }

                        if ($functions->isEmpty()) {
                            $expansionErrors[] = "Assignment {$index}: SCM module requires functions but none found";
                            continue;
                        }
                    }

                    // --------------------------------------------------------
                    // 5.4: Create all combinations
                    // --------------------------------------------------------
                    foreach ($locations as $location) {
                        foreach ($roles as $role) {
                            foreach ($functions as $function) {
                                $expandedAssignments[] = [
                                    'module_id' => $compactAssignment['module_id'],
                                    'application_id' => $compactAssignment['application_id'],
                                    'application_name' => $compactAssignment['application_name'],
                                    'location_id' => $location->Id,
                                    'location_code' => $location->code,
                                    'role_id' => $role->id,
                                    'role_code' => $role->code,
                                    'function_id' => $function->id ?? null,
                                    'function_code' => $function->code ?? null,
                                ];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $expansionErrors[] = "Assignment {$index}: Expansion error - {$e->getMessage()}";
                    Log::error("Error expanding assignment {$index}", [
                        'error' => $e->getMessage(),
                        'assignment' => $compactAssignment
                    ]);
                }
            }

            // Check if expansion failed
            if (!empty($expansionErrors)) {
                $errorMessage = "Failed to expand module assignments:\n" . implode("\n", $expansionErrors);
                Log::error('Expansion errors occurred', [
                    'errors' => $expansionErrors,
                    'expanded_count' => count($expandedAssignments)
                ]);
                throw new \Exception($errorMessage);
            }

            if (empty($expandedAssignments)) {
                throw new \Exception('No valid module assignments after expansion. Please check your selections.');
            }

            Log::info('✓ Step 5: Module assignments expanded successfully', [
                'compact_assignments' => count($modulesData),
                'expanded_assignments' => count($expandedAssignments)
            ]);

            // ====================================================================
            // STEP 6: Insert all expanded assignments into database
            // ====================================================================

            $assignmentsCreated = 0;
            $failedAssignments = [];

            Log::info('→ Step 6: Creating expanded module assignments...', [
                'total_assignments' => count($expandedAssignments)
            ]);

            foreach ($expandedAssignments as $index => $moduleData) {
                try {
                    // Generate new GUID for this mapping
                    $mappingId = DB::connection('sqlsrv')
                        ->selectOne("SELECT NEWID() as newId")->newId;

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
                ", [
                        $mappingId,
                        $userPrincipalName,
                        $moduleData['application_id'],
                        $request->name,
                        $moduleData['application_name'],
                        $moduleData['function_code'],
                        $moduleData['function_id'],
                        $moduleData['role_code'],
                        $moduleData['role_id'],
                        $moduleData['location_code'],
                        $moduleData['location_id'],
                        1, // IsActive
                        0, // IsDelete
                        Auth::id()
                    ]);

                    $assignmentsCreated++;

                    if ($assignmentsCreated % 10 === 0) {
                        Log::info("  Progress: {$assignmentsCreated}/{" . count($expandedAssignments) . "} assignments created");
                    }
                } catch (\Exception $moduleError) {
                    $failedAssignments[] = [
                        'index' => $index + 1,
                        'module' => $moduleData['application_name'],
                        'location' => $moduleData['location_code'],
                        'role' => $moduleData['role_code'],
                        'function' => $moduleData['function_code'] ?? 'N/A',
                        'reason' => $moduleError->getMessage()
                    ];

                    Log::error("  ✗ Assignment failed", [
                        'index' => $index + 1,
                        'module_data' => $moduleData,
                        'error' => $moduleError->getMessage()
                    ]);
                }
            }

            // Check if ALL assignments succeeded (ALL-OR-NOTHING requirement)
            if (!empty($failedAssignments)) {
                $errorMessage = sprintf(
                    'Failed to create %d out of %d module assignments. All assignments must succeed.',
                    count($failedAssignments),
                    count($expandedAssignments)
                );

                Log::error('Module assignment creation failed - rolling back', [
                    'failed_assignments' => $failedAssignments,
                    'succeeded' => $assignmentsCreated,
                    'total' => count($expandedAssignments)
                ]);

                throw new \Exception($errorMessage);
            }

            Log::info('✓ Step 6: All module assignments created successfully', [
                'assignments_created' => $assignmentsCreated
            ]);

            // ====================================================================
            // STEP 7: Commit transaction - everything succeeded!
            // ====================================================================

            DB::connection('sqlsrv')->commit();

            Log::info('=== USER CREATION COMPLETED SUCCESSFULLY ===', [
                'user_id' => $userId,
                'azure_id' => $azureId,
                'email' => $request->email,
                'status' => 'Pending',
                'modules_assigned' => $assignmentsCreated,
                'next_step' => 'External systems will sync, then admin can activate user'
            ]);

            // Get unique module names for success message
            $uniqueModules = collect($expandedAssignments)
                ->pluck('application_name')
                ->unique()
                ->values()
                ->toArray();

            $successMessage = sprintf(
                '✓ User created successfully!<br>' .
                    'Azure ID: %s<br>' .
                    'Status: <strong>Pending</strong> (awaiting external system sync)<br>' .
                    '%d module assignment(s) created across %d module(s): %s',
                substr($azureId, 0, 8) . '...',
                $assignmentsCreated,
                count($uniqueModules),
                implode(', ', $uniqueModules)
            );

            // ✅ DETECT AJAX REQUEST AND RETURN APPROPRIATE RESPONSE
            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => strip_tags($successMessage),
                    'redirect' => route('azure.dashboard.index'),
                    'data' => [
                        'user_id' => $userId,
                        'azure_id' => $azureId,
                        'email' => $request->email,
                        'assignments_created' => $assignmentsCreated,
                        'modules' => $uniqueModules
                    ]
                ]);
            }

            // Regular form submission - redirect
            return redirect()->route('azure.dashboard.index')
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            // ====================================================================
            // ERROR HANDLING & ROLLBACK
            // ====================================================================

            // Rollback local database transaction
            if (DB::connection('sqlsrv')->transactionLevel() > 0) {
                DB::connection('sqlsrv')->rollBack();
            }

            Log::error('Transaction rolled back due to error', [
                'error' => $e->getMessage()
            ]);

            // CRITICAL: If Azure user was created, attempt to delete it (cleanup)
            $cleanupWarning = '';
            if (isset($azureId) && $azureService) {
                try {
                    Log::info('Attempting to delete Azure user during rollback...', [
                        'azure_id' => $azureId
                    ]);

                    $azureService->deleteUser($azureId);

                    Log::info('✓ Azure user deleted during rollback', [
                        'azure_id' => $azureId
                    ]);
                } catch (\Exception $deleteError) {
                    Log::critical('✗ CRITICAL: Failed to delete Azure user during rollback', [
                        'azure_id' => $azureId,
                        'email' => $request->email ?? 'unknown',
                        'error' => $deleteError->getMessage(),
                        'action_required' => 'MANUAL CLEANUP NEEDED IN AZURE AD'
                    ]);

                    $cleanupWarning = sprintf(
                        '<br><strong>WARNING:</strong> Azure user (ID: %s) may still exist and require manual cleanup.',
                        substr($azureId, 0, 8) . '...'
                    );
                }
            }

            $errorMessage = 'Failed to create user: ' . $e->getMessage() . $cleanupWarning;

            Log::error('=== USER CREATION FAILED ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'azure_id' => $azureId,
                'request_data' => $request->except(['_token'])
            ]);

            // ✅ DETECT AJAX REQUEST AND RETURN APPROPRIATE ERROR RESPONSE
            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => strip_tags($errorMessage),
                    'error' => $e->getMessage(),
                    'user_id' => $userId,
                    'azure_id' => $azureId
                ], 422); // 422 Unprocessable Entity
            }

            // Regular form submission - redirect back with errors
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'error' => $errorMessage
                ]);
        }
    }

    /**
     * Generate a secure random password for Azure AD
     * Meets Azure AD password complexity requirements:
     * - At least 8 characters
     * - Contains uppercase, lowercase, number, and special character
     * 
     * @return string
     */
    private function generateSecurePassword(): string
    {
        $length = config('azure.default_password_length', 16);

        // Character sets
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*()-_=+[]{}|;:,.<>?';

        // Ensure at least one character from each set
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        // Fill remaining length with random characters from all sets
        $allChars = $uppercase . $lowercase . $numbers . $special;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Shuffle to avoid predictable pattern (special char always at position 4, etc.)
        return str_shuffle($password);
    }

    /**
     * Display the specified user
     */
    /**
     * Display the specified user with module assignments
     */
    public function show(AzureUser $user)
    {
        try {
            Log::info('Loading user details page', [
                'user_id' => $user->Id,
                'user_email' => $user->userPrincipalName
            ]);

            // Get provisioning status with error handling
            $provisioningStatus = null;
            try {
                $provisioningStatus = $this->provisioningService->getProvisioningStatus($user);
            } catch (\Exception $e) {
                Log::warning('Failed to get provisioning status', [
                    'user_id' => $user->Id,
                    'error' => $e->getMessage()
                ]);

                // Provide default status if service fails
                $provisioningStatus = [
                    'local_user' => [
                        'exists' => true,
                        'status' => $user->Status,
                        'created_at' => $user->createdDateTime,
                    ],
                    'azure' => [
                        'provisioned' => !empty($user->AzureId),
                        'azure_id' => $user->AzureId,
                        'azure_upn' => $user->userPrincipalName,
                        'synced' => false,
                        'account_enabled' => $user->accountEnabled,
                    ],
                    'modules' => [],
                    'overall_status' => $user->Status === 'Active' ? 'active' : 'pending',
                ];
            }

            // Fetch user's module assignments from Azure_Application_UserMapping
            $assignmentsRaw = DB::connection('sqlsrv')
                ->select("
                SELECT 
                    application_id,
                    ApplicationName,
                    OfficeCode,
                    OfficeID,
                    RoleCode,
                    RoleID,
                    FunctionCode,
                    FunctionID
                FROM Azure_Application_UserMapping
                WHERE user_id = ?
                    AND IsActive = 1
                    AND IsDelete = 0
                ORDER BY ApplicationName, OfficeCode, RoleCode
            ", [$user->userPrincipalName]);

            // Group assignments by module
            $moduleAssignments = collect($assignmentsRaw)
                ->groupBy('ApplicationName')
                ->map(function ($assignments, $moduleName) {
                    // Get unique locations
                    $locations = $assignments->pluck('OfficeCode')->unique()->sort()->values();

                    // Get unique roles
                    $roles = $assignments->map(function ($assignment) {
                        return [
                            'id' => $assignment->RoleID,
                            'code' => $assignment->RoleCode
                        ];
                    })->unique('code')->values();

                    // Get unique functions (for SCM modules)
                    $functions = $assignments
                        ->filter(function ($assignment) {
                            return !empty($assignment->FunctionCode);
                        })
                        ->map(function ($assignment) {
                            return [
                                'id' => $assignment->FunctionID,
                                'code' => $assignment->FunctionCode
                            ];
                        })
                        ->unique('code')
                        ->values();

                    return [
                        'application_id' => $assignments->first()->application_id,
                        'module_name' => $moduleName,
                        'locations' => $locations,
                        'locations_count' => $locations->count(),
                        'roles' => $roles,
                        'roles_count' => $roles->count(),
                        'functions' => $functions,
                        'functions_count' => $functions->count(),
                        'total_assignments' => $assignments->count()
                    ];
                })
                ->sortBy('module_name')
                ->values();

            Log::info('User details loaded successfully', [
                'user_id' => $user->Id,
                'modules_count' => $moduleAssignments->count(),
                'total_assignments' => $assignmentsRaw ? count($assignmentsRaw) : 0
            ]);

            return view('azure-management.users.show', compact(
                'user',
                'provisioningStatus',
                'moduleAssignments'
            ));
        } catch (\Exception $e) {
            Log::error('Failed to load user details page', [
                'user_id' => $user->Id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('azure.users.index')
                ->withErrors(['error' => 'Failed to load user details: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(AzureUser $user)
    {
        try {
            Log::info('Loading edit user page', [
                'user_id' => $user->Id,
                'user_email' => $user->userPrincipalName,
                'user_data' => [
                    'displayName' => $user->displayName ?? 'NULL',
                    'EmployeCode' => $user->EmployeCode ?? 'NULL',
                    'employeeId' => $user->employeeId ?? 'NULL',
                    'mail' => $user->mail ?? 'NULL',
                    'telephoneNumber' => $user->telephoneNumber ?? 'NULL',
                    'mobilePhone' => $user->mobilePhone ?? 'NULL',
                    'companyName' => $user->companyName ?? 'NULL',
                    'jobTitle' => $user->jobTitle ?? 'NULL',
                ]
            ]);

            // ====================================================================
            // 1. Fetch all modules
            // ====================================================================
            $modulesRaw = DB::connection('sqlsrv')
                ->select("SELECT Id, Name, ApplicationId 
                FROM Azure_SoftwareMaster 
                WHERE IsActive = 1 AND IsDeleted = 0 
                AND Name != 'Odoo'
                ORDER BY Name");

            $modules = collect($modulesRaw)->map(function ($module) {
                return (object)[
                    'id' => $module->Id,
                    'name' => $module->Name,
                    'application_id' => $module->ApplicationId
                ];
            });

            Log::info('Modules loaded', [
                'count' => $modules->count()
            ]);

            // ====================================================================
            // 2. Fetch all locations
            // ====================================================================
            $locationsRaw = DB::connection('sqlsrv')
                ->select("SELECT Id, Name, code 
                FROM Cust_Office 
                WHERE IsActive = 1 AND IsDeleted = 0 
                ORDER BY Name");

            $locations = collect($locationsRaw)->map(function ($location) {
                return (object)[
                    'id' => $location->Id,
                    'name' => $location->Name,
                    'code' => $location->code
                ];
            });

            Log::info('Locations loaded', [
                'count' => $locations->count()
            ]);

            // ====================================================================
            // 3. Fetch user's existing module assignments
            // ====================================================================
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
            ORDER BY aaum.ApplicationName, aaum.OfficeCode
        ", [$user->userPrincipalName]);

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

            Log::info('User module assignments loaded', [
                'user_id' => $user->Id,
                'assignments_count' => $userModuleAssignments->count(),
                'unique_modules' => $userModuleAssignments->pluck('module_name')->unique()->count()
            ]);

            // ====================================================================
            // 4. Log successful data loading
            // ====================================================================
            Log::info('Edit user page data loaded successfully', [
                'user_id' => $user->Id,
                'user_email' => $user->userPrincipalName,
                'user_name' => $user->displayName,
                'user_status' => $user->Status,
                'user_company' => $user->companyName ?? 'Not Set',
                'modules_count' => $modules->count(),
                'locations_count' => $locations->count(),
                'assignments_count' => $userModuleAssignments->count()
            ]);

            // Empty collections for companies and roles (not needed in edit)
            $companies = collect([]);
            $roles = collect([]);

            return view('azure-management.users.edit', compact(
                'user',
                'companies',
                'modules',
                'locations',
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
     * Update the specified user - Enhanced with better logging
     */
    public function update(Request $request, AzureUser $user)
    {
        // Set timeout to prevent 60-second timeout
        set_time_limit(120);

        // Enable error reporting for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        Log::info('=== UPDATE METHOD ENTRY - FULL REQUEST DEBUG ===', [
            'request_class' => get_class($request),
            'request_method' => $request->method(),
            'is_ajax' => $request->ajax(),
            'expects_json' => $request->expectsJson(),
            'content_type' => $request->header('Content-Type'),
            'all_input_keys' => array_keys($request->all()),
            'has_FirstName' => $request->has('FirstName'),
            'has_modules' => $request->has('modules'),
            'has_modules_empty' => $request->has('modules_empty'),
            'route_parameters' => ['user_id' => $user->Id],
        ]);

        try {
            Log::info('=== UPDATE USER REQUEST RECEIVED ===', [
                'user_id' => $user->Id,
                'user_principal' => $user->userPrincipalName,
                'request_method' => $request->method(),
                'is_ajax' => $request->ajax(),
            ]);

            // ====================================================================
            // STEP 1: Process modules data with proper error handling
            // ====================================================================
            $modulesData = $request->input('modules', []);

            Log::info('Processing modules data', [
                'raw_input' => $request->input('modules'),
                'modules_empty_flag' => $request->has('modules_empty'),
                'is_array' => is_array($modulesData),
                'is_string' => is_string($modulesData),
            ]);

            // Handle empty modules case
            if ($request->has('modules_empty')) {
                $modulesData = [];
                Log::info('Empty modules marker detected - deleting all assignments');
            } elseif (is_string($modulesData)) {
                // Handle JSON string
                if ($modulesData === '[]' || $modulesData === '{}' || $modulesData === '' || $modulesData === 'null') {
                    $modulesData = [];
                    Log::info('Empty string detected - converting to empty array');
                } else {
                    try {
                        $decoded = json_decode($modulesData, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $modulesData = $decoded;
                            Log::info('Decoded JSON string to array', ['count' => count($modulesData)]);
                        } else {
                            $modulesData = [];
                            Log::warning('Invalid JSON string - converting to empty array');
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to decode modules string', [
                            'string' => substr($modulesData, 0, 100),
                            'error' => $e->getMessage()
                        ]);
                        $modulesData = [];
                    }
                }
            }

            // Final validation: ensure it's an array
            if (!is_array($modulesData)) {
                Log::warning('Modules data is not an array, converting to empty array', [
                    'type' => gettype($modulesData),
                    'value' => $modulesData
                ]);
                $modulesData = [];
            }

            Log::info('Final modules data', [
                'count' => count($modulesData),
                'is_empty' => empty($modulesData),
                'type' => gettype($modulesData)
            ]);

            // ====================================================================
            // STEP 2: Validate modules only if we have them
            // ====================================================================
            if (!empty($modulesData)) {
                try {
                    $validated = $request->validate([
                        'modules' => 'required|array|min:1',
                        'modules.*.module_id' => 'required|string',
                        'modules.*.application_id' => 'required|string',
                        'modules.*.location_id' => 'required|string',
                        'modules.*.location_code' => 'required|string',
                        'modules.*.role_id' => 'required|string',
                        'modules.*.role_code' => 'required|string',
                        'modules.*.function_id' => 'nullable|string',
                        'modules.*.function_code' => 'nullable|string',
                    ]);

                    Log::info('Validation passed', ['modules_count' => count($modulesData)]);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    Log::error('Validation failed', ['errors' => $e->errors()]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $e->errors()
                    ], 422);
                }
            } else {
                // No modules to validate
                Log::info('No modules to validate - proceeding with empty array');
            }

            // ====================================================================
            // STEP 3: Start database transaction
            // ====================================================================
            DB::connection('sqlsrv')->beginTransaction();

            try {
                Log::info('📦 Processing modules for update', [
                    'total_assignments' => count($modulesData),
                    'user_principal' => $user->userPrincipalName
                ]);

                // Log sample assignments for debugging
                if (count($modulesData) > 0) {
                    Log::info('📝 Sample assignments (first 2):', [
                        'samples' => array_slice($modulesData, 0, 2)
                    ]);
                } else {
                    Log::info('📝 No module assignments - all existing modules will be deleted');
                }

                // ====================================================================
                // STEP 4: Soft delete existing assignments
                // ====================================================================
                $deletedCount = DB::connection('sqlsrv')
                    ->table('Azure_Application_UserMapping')
                    ->where('user_id', $user->userPrincipalName)
                    ->where('IsDelete', 0)
                    ->update([
                        'IsDelete' => 1,
                        'IsActive' => 0,
                        'LastModifiedBy' => Auth::id() ?? 'system',
                        'LastModifiedOn' => DB::raw('GETDATE()')
                    ]);

                Log::info('🗑️ Marked existing assignments as deleted', [
                    'user_principal' => $user->userPrincipalName,
                    'deleted_count' => $deletedCount
                ]);

                // ====================================================================
                // STEP 5: If no new modules, commit and return success
                // ====================================================================
                if (empty($modulesData)) {
                    DB::connection('sqlsrv')->commit();

                    Log::info('=== UPDATE COMPLETED (ALL MODULES DELETED) ===', [
                        'user_principal' => $user->userPrincipalName,
                        'deleted_assignments' => $deletedCount
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => "All module assignments removed successfully! {$deletedCount} assignments deleted.",
                        'data' => [
                            'deleted' => $deletedCount,
                            'inserted' => 0
                        ]
                    ]);
                }

                // ====================================================================
                // STEP 6: Insert new module assignments
                // ====================================================================
                $insertedCount = 0;
                $errors = [];

                foreach ($modulesData as $index => $moduleData) {
                    try {
                        // Validate required fields
                        $requiredFields = ['module_id', 'application_id', 'location_id', 'location_code', 'role_id', 'role_code'];
                        foreach ($requiredFields as $field) {
                            if (empty($moduleData[$field])) {
                                throw new \Exception("Missing required field: {$field}");
                            }
                        }

                        // Get module details
                        $module = DB::connection('sqlsrv')
                            ->table('Azure_SoftwareMaster')
                            ->where('Id', $moduleData['module_id'])
                            ->first();

                        if (!$module) {
                            throw new \Exception("Module not found: {$moduleData['module_id']}");
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

                        // Log first insertion for debugging
                        if ($insertedCount === 0) {
                            Log::info("💾 Inserting first assignment", [
                                'module' => $module->Name,
                                'location' => $moduleData['location_code'],
                                'role' => $moduleData['role_code'],
                                'function' => $moduleData['function_code'] ?? 'N/A'
                            ]);
                        }

                        // Insert the assignment
                        DB::connection('sqlsrv')
                            ->table('Azure_Application_UserMapping')
                            ->insert($insertData);

                        $insertedCount++;
                    } catch (\Exception $e) {
                        $errorMsg = "Error processing assignment {$index}: " . $e->getMessage();
                        $errors[] = $errorMsg;
                        Log::error("❌ Assignment insertion failed", [
                            'index' => $index,
                            'module_data' => $moduleData,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // ====================================================================
                // STEP 7: Check for errors and rollback if any
                // ====================================================================
                if (!empty($errors)) {
                    DB::connection('sqlsrv')->rollBack();

                    Log::error('❌ Update failed - rolling back', [
                        'errors' => $errors,
                        'succeeded' => $insertedCount,
                        'total' => count($modulesData)
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => sprintf(
                            'Failed to save %d out of %d assignments. Rolling back all changes.',
                            count($errors),
                            count($modulesData)
                        ),
                        'errors' => $errors
                    ], 422);
                }

                // ====================================================================
                // STEP 8: Commit transaction
                // ====================================================================
                DB::connection('sqlsrv')->commit();

                Log::info('=== UPDATE COMPLETED SUCCESSFULLY ===', [
                    'user_principal' => $user->userPrincipalName,
                    'assignments_saved' => $insertedCount,
                    'assignments_deleted' => $deletedCount
                ]);

                // ====================================================================
                // STEP 9: Verify data was saved
                // ====================================================================
                $verifyCount = DB::connection('sqlsrv')
                    ->table('Azure_Application_UserMapping')
                    ->where('user_id', $user->userPrincipalName)
                    ->where('IsActive', 1)
                    ->where('IsDelete', 0)
                    ->count();

                Log::info('✅ Verification check', [
                    'expected' => $insertedCount,
                    'actual_in_db' => $verifyCount,
                    'match' => $insertedCount === $verifyCount ? 'YES' : 'NO'
                ]);

                // ====================================================================
                // STEP 10: Return success response
                // ====================================================================
                return response()->json([
                    'success' => true,
                    'message' => "User updated successfully! {$insertedCount} assignments saved.",
                    'data' => [
                        'inserted' => $insertedCount,
                        'deleted' => $deletedCount,
                        'verified' => $verifyCount
                    ]
                ]);
            } catch (\Exception $e) {
                // ====================================================================
                // STEP 11: Rollback on any database error
                // ====================================================================
                if (DB::connection('sqlsrv')->transactionLevel() > 0) {
                    DB::connection('sqlsrv')->rollBack();
                }

                Log::error('❌ Database transaction failed', [
                    'user_id' => $user->Id,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage(),
                    'error_details' => config('app.debug') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'message' => $e->getMessage()
                    ] : null
                ], 500);
            }
        } catch (\Exception $e) {
            // ====================================================================
            // STEP 12: Catch any other unexpected errors
            // ====================================================================
            Log::error('❌ Unexpected error in update method', [
                'user_id' => $user->Id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage(),
                'error_details' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }

    /**
     * Test endpoint for debugging
     */
    public function testUpdate(AzureUser $user)
    {
        try {
            Log::info('=== TEST UPDATE ENDPOINT ===');

            // Simulate what the controller receives
            $requestData = [
                '_token' => csrf_token(),
                '_method' => 'PUT',
                'modules_empty' => '1'
            ];

            Log::info('Test data', $requestData);

            // Test database connection
            try {
                $test = DB::connection('sqlsrv')->select('SELECT 1 as test');
                Log::info('Database connection OK', ['test' => $test]);
            } catch (\Exception $e) {
                Log::error('Database connection failed', ['error' => $e->getMessage()]);
                throw $e;
            }

            return response()->json([
                'success' => true,
                'message' => 'Test endpoint working',
                'user' => [
                    'id' => $user->Id,
                    'email' => $user->userPrincipalName
                ],
                'database' => 'connected',
                'request' => $requestData
            ]);
        } catch (\Exception $e) {
            Log::error('Test endpoint error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Sync user status from Pending to Active
     * 
     * This method is called after external systems (SCM, Odoo, BIZ) have
     * created their users and populated the external ID columns.
     * 
     * @param AzureUser $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sync(AzureUser $user)
    {
        try {
            Log::info('=== USER SYNC STARTED ===', [
                'user_id' => $user->Id,
                'email' => $user->userPrincipalName,
                'current_status' => $user->Status,
                'initiated_by' => Auth::id()
            ]);

            // Validation 1: Check if user is already Active
            if ($user->Status === 'Active') {
                Log::info('User already active - no action needed', [
                    'user_id' => $user->Id,
                    'status' => $user->Status
                ]);

                return redirect()->back()
                    ->with('info', 'User is already Active. No changes made.');
            }

            // Validation 2: Check if user is Pending (only valid state for sync)
            if ($user->Status !== 'Pending') {
                Log::warning('Invalid status for sync operation', [
                    'user_id' => $user->Id,
                    'current_status' => $user->Status,
                    'expected_status' => 'Pending'
                ]);

                return redirect()->back()
                    ->with('error', sprintf(
                        'Only Pending users can be synced. Current status: %s',
                        $user->Status
                    ));
            }

            // Validation 3: Check if user has been provisioned to Azure AD
            if (empty($user->AzureId)) {
                Log::error('Cannot sync - user not provisioned to Azure AD', [
                    'user_id' => $user->Id,
                    'email' => $user->userPrincipalName
                ]);

                return redirect()->back()
                    ->with('error', 'Cannot sync: User has not been provisioned to Azure AD yet. Please contact IT support.');
            }

            // Check which external systems have synced this user
            $externalSystems = $this->getExternalSystemsSyncStatus($user);

            Log::info('External system sync check', [
                'user_id' => $user->Id,
                'scm_synced' => $externalSystems['scm']['synced'],
                'odoo_synced' => $externalSystems['odoo']['synced'],
                'biz_synced' => $externalSystems['biz']['synced'],
                'total_synced' => $externalSystems['synced_count']
            ]);

            // Get list of synced system names
            $syncedSystemNames = [];
            if ($externalSystems['scm']['synced']) {
                $syncedSystemNames[] = 'SCM';
            }
            if ($externalSystems['odoo']['synced']) {
                $syncedSystemNames[] = 'Odoo';
            }
            if ($externalSystems['biz']['synced']) {
                $syncedSystemNames[] = 'BIZ';
            }

            // Optional: Warn if no external systems have synced
            // (Uncomment this block if you want to require at least one external system)
            /*
        if (empty($syncedSystemNames)) {
            Log::warning('No external systems have synced this user', [
                'user_id' => $user->Id,
                'email' => $user->userPrincipalName
            ]);

            return redirect()->back()
                ->with('warning', 
                    'Warning: No external systems (SCM, Odoo, BIZ) have synced this user yet. ' .
                    'The user can login to Azure AD but may not have access to application modules.'
                );
        }
        */

            // Update status to Active
            DB::connection('sqlsrv')->update("
            UPDATE Azure_Users 
            SET Status = 'Active'
            WHERE Id = ?
        ", [
                $user->Id
            ]);

            Log::info('=== USER SYNC COMPLETED SUCCESSFULLY ===', [
                'user_id' => $user->Id,
                'email' => $user->userPrincipalName,
                'old_status' => 'Pending',
                'new_status' => 'Active',
                'synced_systems' => $syncedSystemNames,
                'synced_count' => count($syncedSystemNames),
                'synced_by' => Auth::id()
            ]);

            // Log audit trail
            $this->logUserActivity(
                $user->Id,
                'user.synced',
                'User status updated from Pending to Active',
                [
                    'old_status' => 'Pending',
                    'new_status' => 'Active',
                    'synced_systems' => $syncedSystemNames,
                    'external_ids' => [
                        'scm' => $externalSystems['scm']['user_id'],
                        'odoo' => $externalSystems['odoo']['user_id'],
                        'biz' => $externalSystems['biz']['user_id']
                    ]
                ]
            );

            // Build success message
            $systemsList = !empty($syncedSystemNames)
                ? sprintf(' Synced with: <strong>%s</strong>', implode(', ', $syncedSystemNames))
                : ' <em>(No external systems synced yet)</em>';

            return redirect()->back()
                ->with('success', '✓ User activated successfully!' . $systemsList);
        } catch (\Exception $e) {
            Log::error('=== USER SYNC FAILED ===', [
                'user_id' => $user->Id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to sync user: ' . $e->getMessage());
        }
    }

    /**
     * Get sync status for a user (AJAX endpoint)
     * 
     * Returns detailed information about external system sync status
     * Useful for displaying real-time sync status in the UI
     * 
     * @param AzureUser $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncStatus(AzureUser $user)
    {
        try {
            $externalSystems = $this->getExternalSystemsSyncStatus($user);

            $syncStatus = [
                'user_id' => $user->Id,
                'email' => $user->userPrincipalName,
                'current_status' => $user->Status,
                'azure_provisioned' => !empty($user->AzureId),
                'azure_id' => $user->AzureId,
                'external_systems' => $externalSystems,
                'can_be_activated' => $user->Status === 'Pending' && !empty($user->AzureId),
                'is_active' => $user->Status === 'Active',
                'module_assignments_count' => $user->moduleAssignments()->count(),
                'last_modified' => $user->LastModifiedOn,
                'last_modified_by' => $user->LastModifiedBy
            ];

            return response()->json([
                'success' => true,
                'data' => $syncStatus
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get sync status', [
                'user_id' => $user->Id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get sync status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method: Get external systems sync status
     * 
     * Checks which external systems (SCM, Odoo, BIZ) have synced the user
     * by checking if their respective ID columns are populated
     * 
     * @param AzureUser $user
     * @return array
     */
    private function getExternalSystemsSyncStatus(AzureUser $user): array
    {
        return [
            'scm' => [
                'synced' => !empty($user->SCMUserID),
                'user_id' => $user->SCMUserID,
                'resource_id' => $user->SCMResourceID,
                'login_id' => $user->SCMLoginId,
                'created_on' => $user->SCMCreatedOn
            ],
            'odoo' => [
                'synced' => !empty($user->OdooUserId),
                'user_id' => $user->OdooUserId,
                'employee_id' => $user->OdooEmployeeId,
                'login_id' => $user->OdooLoginId,
                'created_on' => $user->OdooCreatedOn
            ],
            'biz' => [
                'synced' => !empty($user->BizUserID),
                'user_id' => $user->BizUserID,
                'login_id' => $user->BizLoginId,
                'created_on' => $user->BizCreatedOn
            ],
            'synced_count' => (empty($user->SCMUserID) ? 0 : 1) +
                (empty($user->OdooUserId) ? 0 : 1) +
                (empty($user->BizUserID) ? 0 : 1)
        ];
    }

    /**
     * Helper method: Log user activity to audit trail
     * 
     * Creates an audit log entry for user-related actions
     * 
     * @param string|AzureUser $userOrId - Either Azure_Users.Id (GUID string) or AzureUser model instance
     * @param string $action - Action type (e.g., 'user.synced', 'user.created')
     * @param string $description - Human-readable description
     * @param array $metadata - Additional data to store
     * @return void
     */
    private function logUserActivity(
        $userOrId,
        string $action,
        string $description,
        array $metadata = []
    ): void {
        try {
            // Handle both string ID and AzureUser model
            $userId = $userOrId instanceof AzureUser ? $userOrId->Id : $userOrId;

            DB::connection('mysql')->table('audit_logs')->insert([
                'user_id' => Auth::id(),
                'action' => $action,
                'auditable_type' => 'AzureUser',
                'auditable_id' => $userId,
                'old_values' => null,
                'new_values' => json_encode($metadata),
                'performed_by' => Auth::id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'status' => 'success',
                'error_message' => null,
                'metadata' => json_encode([
                    'description' => $description,
                    'timestamp' => now()->toIso8601String()
                ]),
                'created_at' => now()
            ]);

            Log::info('Audit log created', [
                'action' => $action,
                'user_id' => $userId
            ]);
        } catch (\Exception $e) {
            // Don't fail the main operation if audit logging fails
            Log::error('Failed to create audit log', [
                'action' => $action,
                'user_id' => $userOrId instanceof AzureUser ? $userOrId->Id : $userOrId,
                'error' => $e->getMessage()
            ]);
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

    public function disable(AzureUser $user)
    {
        try {
            Log::info('=== DISABLE USER REQUEST ===', [
                'user_id' => $user->Id,
                'email' => $user->userPrincipalName,
                'current_status' => $user->Status,
                'initiated_by' => Auth::id()
            ]);

            // Check if user is already inactive
            if ($user->Status === 'Inactive') {
                Log::info('User already inactive', [
                    'user_id' => $user->Id,
                    'status' => $user->Status
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'User is already disabled'
                ], 400);
            }

            // Update user status using the model's disable method
            $success = $user->disable(Auth::id());

            if (!$success) {
                throw new \Exception('Failed to update user status in database');
            }

            // Try to disable in Azure (optional - don't fail if this fails)
            try {
                if (isset($this->provisioningService) && !empty($user->AzureId)) {
                    $this->provisioningService->disableUser($user);
                    Log::info('User disabled in Azure AD', [
                        'user_id' => $user->Id,
                        'azure_id' => $user->AzureId
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Azure disable failed but local disable succeeded', [
                    'user_id' => $user->Id,
                    'error' => $e->getMessage()
                ]);
            }

            // Log activity
            try {
                $this->logUserActivity(
                    $user->Id,
                    'user.disabled',
                    'User account disabled',
                    ['disabled_by' => Auth::id()]
                );
            } catch (\Exception $e) {
                Log::warning('Failed to log user activity', [
                    'user_id' => $user->Id,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('=== USER DISABLED SUCCESSFULLY ===', [
                'user_id' => $user->Id,
                'disabled_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User has been disabled successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to disable user', [
                'user_id' => $user->Id ?? null,
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
            Log::info('=== ENABLE USER REQUEST ===', [
                'user_id' => $user->Id,
                'email' => $user->userPrincipalName,
                'current_status' => $user->Status,
                'initiated_by' => Auth::id()
            ]);

            // Check if user is already active
            if ($user->Status === 'Active') {
                Log::info('User already active', [
                    'user_id' => $user->Id,
                    'status' => $user->Status
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'User is already enabled'
                ], 400);
            }

            // Update user status using the model's enable method
            $success = $user->enable(Auth::id());

            if (!$success) {
                throw new \Exception('Failed to update user status in database');
            }

            // Try to enable in Azure (optional - don't fail if this fails)
            try {
                if (isset($this->provisioningService) && !empty($user->AzureId)) {
                    $this->provisioningService->enableUser($user);
                    Log::info('User enabled in Azure AD', [
                        'user_id' => $user->Id,
                        'azure_id' => $user->AzureId
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Azure enable failed but local enable succeeded', [
                    'user_id' => $user->Id,
                    'error' => $e->getMessage()
                ]);
            }

            // Log activity
            try {
                $this->logUserActivity(
                    $user->Id,
                    'user.enabled',
                    'User account enabled',
                    ['enabled_by' => Auth::id()]
                );
            } catch (\Exception $e) {
                Log::warning('Failed to log user activity', [
                    'user_id' => $user->Id,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('=== USER ENABLED SUCCESSFULLY ===', [
                'user_id' => $user->Id,
                'enabled_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User has been enabled successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to enable user', [
                'user_id' => $user->Id ?? null,
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
     * Reset user password (placeholder implementation)
     * 
     * @param AzureUser $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(AzureUser $user)
    {
        try {
            Log::info('=== RESET PASSWORD REQUEST ===', [
                'user_id' => $user->Id,
                'email' => $user->userPrincipalName,
                'initiated_by' => Auth::id()
            ]);

            // Check if user is provisioned to Azure
            if (empty($user->AzureId)) {
                Log::warning('Cannot reset password - user not provisioned to Azure', [
                    'user_id' => $user->Id,
                    'email' => $user->userPrincipalName
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reset password: User is not provisioned to Azure AD yet.'
                ], 400);
            }

            // Log activity
            try {
                $this->logUserActivity(
                    $user->Id,
                    'user.password_reset_requested',
                    'Password reset requested for user',
                    [
                        'requested_by' => Auth::id(),
                        'user_email' => $user->userPrincipalName
                    ]
                );
            } catch (\Exception $e) {
                Log::warning('Failed to log password reset activity', [
                    'user_id' => $user->Id,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('=== PASSWORD RESET REQUEST LOGGED ===', [
                'user_id' => $user->Id,
                'requested_by' => Auth::id()
            ]);

            // TODO: Implement actual password reset logic via Azure Graph API
            // For now, return a placeholder message
            return response()->json([
                'success' => true,
                'message' => 'Password reset request logged. Please contact IT support to complete the reset process.'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process password reset request', [
                'user_id' => $user->Id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process password reset: ' . $e->getMessage()
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
