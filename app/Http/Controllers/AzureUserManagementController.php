<?php

namespace App\Http\Controllers;

use App\Http\Requests\AzureStoreUserRequest;
use App\Models\AzureModule;
use App\Models\AzureUser;
use App\Services\AzureModuleAssignmentService;
use App\Services\AzureUserProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     * Display a listing of users with statistics and filters
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

            // ====================================================================
            // Apply department (group) filter
            // ====================================================================
            if ($department = $request->get('department')) {
                if ($department === 'no_department') {
                    $query->where(function ($q) {
                        $q->whereNull('department')
                            ->orWhere('department', '');
                    });
                } else {
                    $query->where('department', $department);
                }
            }

            // ====================================================================
            // Apply company filter
            // ====================================================================
            if ($company = $request->get('company')) {
                if ($company === 'no_company') {
                    $query->where(function ($q) {
                        $q->whereNull('companyName')
                            ->orWhere('companyName', '');
                    });
                } else {
                    $query->where('companyName', $company);
                }
            }

            // ====================================================================
            // Apply location filter
            // ====================================================================
            if ($location = $request->get('location')) {
                if ($location === 'no_location') {
                    $query->where(function ($q) {
                        $q->whereNull('officeLocation')
                            ->orWhere('officeLocation', '');
                    });
                } else {
                    $query->where('officeLocation', $location);
                }
            }

            // Apply search filter if provided
            if ($search = $request->get('search')) {
                Log::info('Search query received', ['search' => $search]);

                $query->where(function ($q) use ($search) {
                    // Search in name fields
                    $q->where('displayName', 'like', "%{$search}%")
                        // Search in other fields
                        ->orWhere('EmployeCode', 'like', "%{$search}%")
                        ->orWhere('department', 'like', "%{$search}%")
                        ->orWhere('companyName', 'like', "%{$search}%")
                        ->orWhere('officeLocation', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%")
                        ->orWhere('jobTitle', 'like', "%{$search}%")
                        ->orWhereExists(function ($subquery) use ($search) {
                            $subquery->select(DB::raw(1))
                                ->from('Azure_Application_UserMapping')
                                ->whereColumn('Azure_Application_UserMapping.user_id', 'Azure_Users.userPrincipalName')
                                ->where('Azure_Application_UserMapping.ApplicationName', 'like', "%{$search}%")
                                ->where('Azure_Application_UserMapping.IsActive', 1)
                                ->where('Azure_Application_UserMapping.IsDelete', 0);
                        });
                });

                // Log the SQL query
                Log::info('Generated SQL', [
                    'sql' => $query->toSql(),
                    'bindings' => $query->getBindings(),
                ]);
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

            // ====================================================================
            // Fetch unique values for all filter dropdowns
            // ====================================================================

            // Get unique departments (groups)
            $departments = AzureUser::select('department')
                ->whereNotNull('department')
                ->where('department', '!=', '')
                ->distinct()
                ->orderBy('department')
                ->pluck('department');

            // Get unique companies
            $companies = AzureUser::select('companyName')
                ->whereNotNull('companyName')
                ->where('companyName', '!=', '')
                ->distinct()
                ->orderBy('companyName')
                ->pluck('companyName');

            // Get unique locations
            $locations = AzureUser::select('officeLocation')
                ->whereNotNull('officeLocation')
                ->where('officeLocation', '!=', '')
                ->distinct()
                ->orderBy('officeLocation')
                ->pluck('officeLocation');

            Log::info('Filter data loaded', [
                'departments_count' => $departments->count(),
                'companies_count' => $companies->count(),
                'locations_count' => $locations->count(),
            ]);

            // ====================================================================
            // Return view with all data including filter arrays
            // ====================================================================
            return view('azure-management.users.index', compact(
                'users',
                'stats',
                'departments',
                'companies',
                'locations'
            ));
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
        return response()->json(AzureUser::getStatistics());
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
                    AND Name NOT LIKE '%UAT%'
                    ORDER BY Name");

            // Convert to Laravel collection with proper structure
            $modules = collect($modulesRaw)->map(function ($module) {
                return (object) [
                    'id' => $module->Id,
                    'name' => $module->Name,
                    'application_id' => $module->ApplicationId,
                ];
            });

            // Fetch locations from Cust_Office table
            $locationsRaw = DB::connection('sqlsrv')
                ->select("SELECT Id, Name, code
                FROM Cust_Office
                WHERE IsActive = 1
                    AND IsDeleted = 0
                    AND code != 'ALL'
                ORDER BY Name");

            // Convert to Laravel collection
            $locations = collect($locationsRaw)->map(function ($location) {
                return (object) [
                    'id' => $location->Id,
                    'name' => $location->Name,
                    'code' => $location->code,
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
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('azure.dashboard.index')
                ->withErrors(['error' => 'Failed to load form: ' . $e->getMessage()]);
        }
    }

    /**
     * Store a newly created user with Azure AD provisioning + D365 provisioning
     * ENHANCED: Prevents duplicate users by email/UPN
     *
     * Flow:
     * 0. CHECK FOR DUPLICATES (email/UPN in DB and Azure AD)
     * 1. Validate and decode modules JSON
     * 2. Expand ALL selections (locations, roles, functions) server-side
     * 3. Create user in local DB (Status='Pending')
     * 4. Create user in Azure AD
     * 5. Save AzureId to local DB
     * 6. Create all expanded module assignments
     * 7. Provision user to D365 (if D365 LIVE modules assigned)
     * 8. Rollback everything if ANY critical step fails (all-or-nothing)
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(AzureStoreUserRequest $request)
    {
        // Variables to track what was created (for rollback)
        $userId = null;
        $azureId = null;
        $azureService = null;
        $d365ProvisioningStatus = null;

        try {
            Log::info('=== USER CREATION STARTED ===', [
                'name' => $request->name,
                'email' => $request->email,
                'employee_id' => $request->employee_id,
                'initiated_by' => Auth::id(),
            ]);

            // ====================================================================
            // STEP 0: CHECK FOR DUPLICATE USERS
            // ====================================================================

            $userEmail = strtolower(trim($request->email));

            Log::info('→ Step 0: Checking for duplicate users...', [
                'email' => $userEmail,
            ]);

            // Check 1: Database - by userPrincipalName
            $existingByUPN = DB::connection('sqlsrv')
                ->table('Azure_Users')
                ->where('userPrincipalName', $userEmail)
                ->first();

            if ($existingByUPN) {
                Log::warning('Duplicate user detected by userPrincipalName', [
                    'email' => $userEmail,
                    'existing_user_id' => $existingByUPN->Id,
                    'existing_display_name' => $existingByUPN->displayName,
                    'created_at' => $existingByUPN->CreatedAt,
                ]);

                $errorMessage = sprintf(
                    'A user with email "%s" already exists!<br>' .
                        '<strong>Name:</strong> %s<br>' .
                        '<strong>Created:</strong> %s<br>' .
                        '<strong>User ID:</strong> %s',
                    $userEmail,
                    $existingByUPN->displayName ?? 'Unknown',
                    $existingByUPN->CreatedAt ?? 'Unknown',
                    substr($existingByUPN->Id ?? '', 0, 8) . '...'
                );

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => strip_tags($errorMessage),
                        'error' => 'Duplicate user - email already exists',
                        'existing_user' => [
                            'id' => $existingByUPN->Id,
                            'name' => $existingByUPN->displayName,
                            'email' => $existingByUPN->userPrincipalName,
                            'created' => $existingByUPN->CreatedAt,
                        ],
                    ], 422);
                }

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['email' => strip_tags($errorMessage)]);
            }

            // Check 2: Database - by mail field (secondary check)
            $existingByMail = DB::connection('sqlsrv')
                ->table('Azure_Users')
                ->where('mail', $userEmail)
                ->where('userPrincipalName', '!=', $userEmail) // Different UPN but same email
                ->first();

            if ($existingByMail) {
                Log::warning('Duplicate user detected by mail field', [
                    'email' => $userEmail,
                    'existing_user_id' => $existingByMail->Id,
                    'existing_upn' => $existingByMail->userPrincipalName,
                ]);

                $errorMessage = sprintf(
                    'A user with email "%s" already exists under a different username!<br>' .
                        '<strong>Name:</strong> %s<br>' .
                        '<strong>Username:</strong> %s',
                    $userEmail,
                    $existingByMail->displayName ?? 'Unknown',
                    $existingByMail->userPrincipalName ?? 'Unknown'
                );

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => strip_tags($errorMessage),
                        'error' => 'Duplicate email found under different username',
                    ], 422);
                }

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['email' => strip_tags($errorMessage)]);
            }

            // Check 3: Azure AD - check if user exists there
            $azureService = app(\App\Services\AzureGraphService::class);

            try {
                $existingAzureUser = $azureService->findUserByUPN($userEmail);

                if ($existingAzureUser) {
                    // Check if this Azure user is already linked in our database
                    $linkedUser = DB::connection('sqlsrv')
                        ->table('Azure_Users')
                        ->where('AzureId', $existingAzureUser['id'])
                        ->first();

                    if ($linkedUser) {
                        Log::warning('Azure user exists and is already linked in database', [
                            'email' => $userEmail,
                            'azure_id' => $existingAzureUser['id'],
                            'linked_to' => $linkedUser->Id,
                        ]);

                        $errorMessage = sprintf(
                            'This user already exists in both Azure AD and our system!<br>' .
                                '<strong>Name:</strong> %s<br>' .
                                '<strong>Email:</strong> %s',
                            $linkedUser->displayName ?? 'Unknown',
                            $linkedUser->userPrincipalName ?? $userEmail
                        );
                    } else {
                        // Azure user exists but NOT in our database - we'll link it later
                        Log::info('Azure user exists but not in our database - will link during creation', [
                            'email' => $userEmail,
                            'azure_id' => $existingAzureUser['id'],
                        ]);

                        // Don't throw error - we'll link this user in Step 3
                        $existingAzureUser = null; // Reset to allow normal flow
                    }

                    if ($linkedUser) {
                        if ($request->wantsJson() || $request->ajax()) {
                            return response()->json([
                                'success' => false,
                                'message' => strip_tags($errorMessage),
                                'error' => 'User exists in Azure AD and database',
                            ], 422);
                        }

                        return redirect()->back()
                            ->withInput()
                            ->withErrors(['email' => strip_tags($errorMessage)]);
                    }
                }
            } catch (\Exception $azureCheckError) {
                // If Azure check fails, log warning but continue
                // (Azure might be temporarily unavailable)
                Log::warning('Could not check Azure AD for duplicate (will try again during creation)', [
                    'email' => $userEmail,
                    'error' => $azureCheckError->getMessage(),
                ]);
            }

            // Check 4: Employee ID uniqueness (if provided)
            if (! empty($request->employee_id)) {
                $existingByEmployeeId = DB::connection('sqlsrv')
                    ->table('Azure_Users')
                    ->where('EmployeCode', $request->employee_id)
                    ->first();

                if ($existingByEmployeeId) {
                    Log::warning('Duplicate employee ID detected', [
                        'employee_id' => $request->employee_id,
                        'existing_user' => $existingByEmployeeId->displayName,
                    ]);

                    $errorMessage = sprintf(
                        'Employee ID "%s" is already assigned to user: %s',
                        $request->employee_id,
                        $existingByEmployeeId->displayName ?? 'Unknown'
                    );

                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => $errorMessage,
                            'error' => 'Duplicate employee ID',
                        ], 422);
                    }

                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['employee_id' => $errorMessage]);
                }
            }

            Log::info('✓ Step 0: No duplicates found - proceeding with creation', [
                'email' => $userEmail,
            ]);

            // ====================================================================
            // STEP 1: Decode and validate modules JSON
            // ====================================================================
            $modulesData = [];

            if ($request->has('modules_json')) {
                $modulesData = json_decode($request->input('modules_json'), true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid modules JSON format: ' . json_last_error_msg());
                }

                Log::info('✓ Modules JSON decoded successfully', [
                    'compact_assignments' => count($modulesData),
                ]);
            } elseif ($request->has('modules')) {
                // Fallback to old format for compatibility
                $modulesData = $request->input('modules');

                Log::info('Using legacy modules format', [
                    'assignments' => count($modulesData),
                ]);
            }

            // Validate we have modules
            if (empty($modulesData)) {
                throw new \Exception('No module assignments provided. Please assign at least one module.');
            }

            // Start database transaction
            DB::connection('sqlsrv')->beginTransaction();

            // ====================================================================
            // STEP 2: Prepare user data
            // ====================================================================

            // Split name into first and last name
            $nameParts = explode(' ', trim($request->name), 2);
            $givenName = $nameParts[0];
            $surname = $nameParts[1] ?? $nameParts[0];

            // Use email as userPrincipalName
            $userPrincipalName = $userEmail;

            // Generate new GUID for user ID
            $userId = DB::connection('sqlsrv')
                ->selectOne('SELECT NEWID() as newId')->newId;

            // Determine primary location from first module assignment
            $primaryLocation = 'KE'; // Default to Kenya
            if (! empty($modulesData)) {
                $firstModule = $modulesData[0];
                if (
                    ! empty($firstModule['locationCodes']) &&
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
                'primary_location' => $primaryLocation,
            ]);

            // ====================================================================
            // STEP 3: Insert into Azure_Users with Status = 'Pending'
            // ====================================================================

            DB::connection('sqlsrv')->insert('
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
        ', [
                $userId,
                $userPrincipalName,
                $request->name,
                $surname,
                $userEmail,
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
                'Pending', // Status = Pending
                $request->employee_id,
                $request->company_id,
            ]);

            Log::info('✓ Step 3: User created in local database', [
                'user_id' => $userId,
                'email' => $userEmail,
                'status' => 'Pending',
            ]);

            // ====================================================================
            // STEP 4: Create user in Azure AD
            // ====================================================================

            $azureUpn = null;

            Log::info('→ Step 4: Checking Azure AD...', [
                'upn' => $userPrincipalName,
                'display_name' => $request->name,
            ]);

            // Check if user already exists in Azure AD (double-check)
            $existingAzureUser = $azureService->findUserByUPN($userPrincipalName);

            if ($existingAzureUser) {
                // User already exists in Azure - link to existing user
                $azureId = $existingAzureUser['id'];
                $azureUpn = $existingAzureUser['userPrincipalName'];

                Log::info('✓ Step 4: User already exists in Azure AD - linking', [
                    'azure_id' => $azureId,
                    'azure_upn' => $azureUpn,
                ]);
            } else {
                // Create new user in Azure AD
                $azureUserData = [
                    'accountEnabled' => true,
                    'displayName' => $request->name,
                    'mailNickname' => explode('@', $userEmail)[0],
                    'userPrincipalName' => $userPrincipalName,
                    'givenName' => $givenName,
                    'surname' => $surname,
                    'mail' => $userEmail,
                    'usageLocation' => 'KE',
                    'passwordProfile' => [
                        'forceChangePasswordNextSignIn' => true,
                        'password' => $this->generateSecurePassword(),
                    ],
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

                Log::info('✓ Step 4: User created in Azure AD', [
                    'azure_id' => $azureId,
                    'azure_upn' => $azureUpn,
                ]);
            }

            // ====================================================================
            // STEP 5: Update local DB with AzureId
            // ====================================================================

            DB::connection('sqlsrv')->update('
            UPDATE Azure_Users
            SET AzureId = ?
            WHERE Id = ?
        ', [
                $azureId,
                $userId,
            ]);

            Log::info('✓ Step 5: Azure ID saved to local database', [
                'user_id' => $userId,
                'azure_id' => $azureId,
                'status' => 'Pending (awaiting external system sync)',
            ]);

            // ====================================================================
            // STEP 6: Expand module assignments (server-side)
            // ====================================================================

            $expandedAssignments = [];
            $expansionErrors = [];

            Log::info('→ Step 6: Expanding module assignments...', [
                'compact_assignments' => count($modulesData),
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
                            'SELECT Id, Name, ApplicationId FROM Azure_SoftwareMaster
                        WHERE Id = ? AND IsActive = 1 AND IsDeleted = 0',
                            [$compactAssignment['module_id']]
                        );

                    if (! $module) {
                        $expansionErrors[] = "Assignment {$index}: Module not found or inactive";

                        continue;
                    }

                    // --------------------------------------------------------
                    // 6.1: Expand LOCATIONS
                    // --------------------------------------------------------
                    $locations = [];

                    if (
                        $compactAssignment['location'] === 'ALL' ||
                        in_array('ALL', $compactAssignment['locationCodes'] ?? [])
                    ) {
                        $locationsRaw = DB::connection('sqlsrv')
                            ->select('SELECT Id, code, Name FROM Cust_Office WHERE IsActive = 1 AND IsDeleted = 0');
                        $locations = collect($locationsRaw);

                        Log::info("  Expanded ALL locations to {$locations->count()} locations");
                    } else {
                        $locationCodes = $compactAssignment['locationCodes'] ?? [];
                        if (! empty($locationCodes)) {
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
                    // 6.2: Expand ROLES
                    // --------------------------------------------------------
                    $roles = [];
                    $applicationCode = $compactAssignment['application_name'];

                    if (
                        $compactAssignment['role_id'] === 'ALL' ||
                        in_array('ALL', $compactAssignment['roleIds'] ?? [])
                    ) {
                        try {
                            $rolesRaw = DB::connection('sqlsrv')
                                ->select(
                                    'EXEC sp_Azure_UserResourceRole @ApplicationCode = ?',
                                    [$applicationCode]
                                );

                            $roles = collect($rolesRaw)->map(function ($r) {
                                return (object) [
                                    'id' => $r->RoleId ?? $r->Id ?? $r->id,
                                    'code' => $r->RoleCode ?? $r->Code ?? $r->code,
                                ];
                            })->filter(function ($r) {
                                return ! empty($r->id) && ! empty($r->code);
                            });

                            Log::info("  Expanded ALL roles to {$roles->count()} roles for {$applicationCode}");
                        } catch (\Exception $e) {
                            $expansionErrors[] = "Assignment {$index}: Failed to fetch roles - {$e->getMessage()}";

                            continue;
                        }
                    } else {
                        $roleIds = $compactAssignment['roleIds'] ?? [];
                        $roleCodes = $compactAssignment['roleCodes'] ?? [];

                        if (! empty($roleIds)) {
                            $roles = collect($roleIds)->map(function ($id, $idx) use ($roleCodes) {
                                return (object) [
                                    'id' => $id,
                                    'code' => $roleCodes[$idx] ?? '',
                                ];
                            })->filter(function ($r) {
                                return ! empty($r->id) && ! empty($r->code);
                            });

                            Log::info("  Using {$roles->count()} specific roles");
                        }
                    }

                    if ($roles->isEmpty()) {
                        $expansionErrors[] = "Assignment {$index}: No valid roles found";

                        continue;
                    }

                    // --------------------------------------------------------
                    // 6.3: Expand FUNCTIONS (SCM modules only)
                    // --------------------------------------------------------
                    $functions = collect([null]);
                    $isSCM = in_array(strtoupper($applicationCode), ['OPS', 'OPS UAT', 'OPSUAT']);

                    if ($isSCM) {
                        if (
                            $compactAssignment['function_id'] === 'ALL' ||
                            in_array('ALL', $compactAssignment['functionIds'] ?? [])
                        ) {
                            try {
                                $functionsRaw = DB::connection('sqlsrv')
                                    ->select('EXEC sp_Azure_UserResourcefunction');

                                $functions = collect($functionsRaw)->map(function ($f) {
                                    return (object) [
                                        'id' => $f->ID ?? $f->Id ?? $f->id,
                                        'code' => $f->Code ?? $f->code,
                                    ];
                                })->filter(function ($f) {
                                    return ! empty($f->id) && ! empty($f->code);
                                });

                                Log::info("  Expanded ALL functions to {$functions->count()} functions");
                            } catch (\Exception $e) {
                                $expansionErrors[] = "Assignment {$index}: Failed to fetch SCM functions - {$e->getMessage()}";

                                continue;
                            }
                        } else {
                            $functionIds = $compactAssignment['functionIds'] ?? [];
                            $functionCodes = $compactAssignment['functionCodes'] ?? [];

                            if (! empty($functionIds)) {
                                $functions = collect($functionIds)->map(function ($id, $idx) use ($functionCodes) {
                                    return (object) [
                                        'id' => $id,
                                        'code' => $functionCodes[$idx] ?? '',
                                    ];
                                })->filter(function ($f) {
                                    return ! empty($f->id) && ! empty($f->code);
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
                    // 6.4: Create all combinations (role × location, function × location)
                    // NOTE: Using 1-to-1 method - roles separate from functions
                    // --------------------------------------------------------
                    foreach ($locations as $location) {
                        // Add roles (without functions)
                        foreach ($roles as $role) {
                            $expandedAssignments[] = [
                                'module_id' => $compactAssignment['module_id'],
                                'application_id' => $compactAssignment['application_id'],
                                'application_name' => $compactAssignment['application_name'],
                                'location_id' => $location->Id,
                                'location_code' => $location->code,
                                'role_id' => $role->id,
                                'role_code' => $role->code,
                                'function_id' => null,  // No function for role records
                                'function_code' => null,
                            ];
                        }

                        // Add functions separately (only for SCM, without roles)
                        if ($isSCM) {
                            foreach ($functions as $function) {
                                if ($function && $function->id) {
                                    $expandedAssignments[] = [
                                        'module_id' => $compactAssignment['module_id'],
                                        'application_id' => $compactAssignment['application_id'],
                                        'application_name' => $compactAssignment['application_name'],
                                        'location_id' => $location->Id,
                                        'location_code' => $location->code,
                                        'role_id' => null,  // No role for function records
                                        'role_code' => null,
                                        'function_id' => $function->id,
                                        'function_code' => $function->code,
                                    ];
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $expansionErrors[] = "Assignment {$index}: Expansion error - {$e->getMessage()}";
                    Log::error("Error expanding assignment {$index}", [
                        'error' => $e->getMessage(),
                        'assignment' => $compactAssignment,
                    ]);
                }
            }

            // Check if expansion failed
            if (! empty($expansionErrors)) {
                $errorMessage = "Failed to expand module assignments:\n" . implode("\n", $expansionErrors);
                Log::error('Expansion errors occurred', [
                    'errors' => $expansionErrors,
                    'expanded_count' => count($expandedAssignments),
                ]);
                throw new \Exception($errorMessage);
            }

            if (empty($expandedAssignments)) {
                throw new \Exception('No valid module assignments after expansion. Please check your selections.');
            }

            Log::info('✓ Step 6: Module assignments expanded successfully', [
                'compact_assignments' => count($modulesData),
                'expanded_assignments' => count($expandedAssignments),
            ]);

            // ====================================================================
            // STEP 7: Validate and sanitize all expanded assignments
            // ====================================================================

            $validatedAssignments = [];
            $validationErrors = [];

            Log::info('→ Step 7: Validating expanded assignments...', [
                'total_assignments' => count($expandedAssignments),
            ]);

            foreach ($expandedAssignments as $index => $assignment) {
                // Validate assignment
                $validation = $this->validateAssignment($assignment, $index);

                if (! $validation['valid']) {
                    $validationErrors[] = $validation['error'];
                    Log::error('Assignment validation failed', [
                        'index' => $index,
                        'error' => $validation['error'],
                        'assignment' => $assignment,
                    ]);

                    continue;
                }

                // Verify referenced data exists
                $dataCheck = $this->verifyReferencedData($validation['data']);

                if (! $dataCheck['valid']) {
                    $validationErrors[] = "Assignment {$index}: " . implode(', ', $dataCheck['errors']);
                    Log::error('Referenced data validation failed', [
                        'index' => $index,
                        'errors' => $dataCheck['errors'],
                        'assignment' => $assignment,
                    ]);

                    continue;
                }

                $validatedAssignments[] = $validation['data'];
            }

            // Check if validation failed
            if (! empty($validationErrors)) {
                $errorMessage = 'Validation failed for ' . count($validationErrors) . " assignment(s):\n" .
                    implode("\n", array_slice($validationErrors, 0, 5));

                if (count($validationErrors) > 5) {
                    $errorMessage .= "\n... and " . (count($validationErrors) - 5) . ' more errors.';
                }

                Log::error('Assignment validation failed', [
                    'total_errors' => count($validationErrors),
                    'errors' => $validationErrors,
                ]);

                throw new \Exception($errorMessage);
            }

            if (empty($validatedAssignments)) {
                throw new \Exception('No valid assignments after validation. Please check your data and try again.');
            }

            Log::info('✓ Step 7: All assignments validated successfully', [
                'valid_assignments' => count($validatedAssignments),
            ]);

            // ====================================================================
            // STEP 8: Separate roles and functions (1-to-1 method)
            // ====================================================================

            $roleAssignments = [];
            $functionAssignments = [];

            foreach ($validatedAssignments as $assignment) {
                if (! empty($assignment['role_id'])) {
                    $roleAssignments[] = $assignment;
                }
                if (! empty($assignment['function_id'])) {
                    $functionAssignments[] = $assignment;
                }
            }

            Log::info('→ Step 8: Separated assignments for 1-to-1 method', [
                'role_assignments' => count($roleAssignments),
                'function_assignments' => count($functionAssignments),
            ]);

            // ====================================================================
            // STEP 9: Insert ROLES to Azure_Application_UserMapping
            // ====================================================================

            $insertedRolesCount = 0;
            $roleInsertErrors = [];

            if (! empty($roleAssignments)) {
                Log::info('→ Step 9: Inserting role assignments...', [
                    'total_roles' => count($roleAssignments),
                ]);

                foreach ($roleAssignments as $roleData) {
                    try {
                        $mappingId = DB::connection('sqlsrv')
                            ->selectOne('SELECT NEWID() as newId')->newId;

                        DB::connection('sqlsrv')
                            ->table('Azure_Application_UserMapping')
                            ->insert([
                                'Id' => $mappingId,
                                'AzureUserID' => $azureId,
                                'user_id' => $userPrincipalName,
                                'application_id' => $roleData['application_id'],
                                'Username' => $request->name,
                                'ApplicationName' => $roleData['application_name'],
                                'RoleCode' => $roleData['role_code'],
                                'RoleID' => $roleData['role_id'],
                                'OfficeCode' => $roleData['location_code'],
                                'OfficeID' => $roleData['location_id'],
                                'IsActive' => 1,
                                'IsDelete' => 0,
                                'CreatedBy' => Auth::user()->name ?? 'system',
                                'CreatedOn' => DB::raw('GETDATE()'),
                                'LastModifiedBy' => null,
                                'LastModifiedOn' => null,
                            ]);

                        $insertedRolesCount++;
                    } catch (\Exception $e) {
                        $roleInsertErrors[] = [
                            'data' => $roleData,
                            'error' => $e->getMessage(),
                        ];
                        Log::error('Failed to insert role assignment', [
                            'role_data' => $roleData,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                Log::info('✓ Step 9: Role assignments inserted', [
                    'inserted' => $insertedRolesCount,
                    'failed' => count($roleInsertErrors),
                ]);
            }

            // ====================================================================
            // STEP 10: Insert FUNCTIONS to Azure_Application_UserFunction
            // ====================================================================

            $insertedFunctionsCount = 0;
            $functionInsertErrors = [];

            if (! empty($functionAssignments)) {
                Log::info('→ Step 10: Inserting function assignments...', [
                    'total_functions' => count($functionAssignments),
                ]);

                foreach ($functionAssignments as $funcData) {
                    try {
                        $functionId = DB::connection('sqlsrv')
                            ->selectOne('SELECT NEWID() as newId')->newId;

                        DB::connection('sqlsrv')
                            ->table('Azure_Application_UserFunction')
                            ->insert([
                                'Id' => $functionId,
                                'AzureUserID' => $azureId,
                                'user_id' => $userPrincipalName,
                                'application_id' => $funcData['application_id'],
                                'Username' => $request->name,
                                'ApplicationName' => $funcData['application_name'],
                                'FunctionCode' => $funcData['function_code'],
                                'FunctionID' => $funcData['function_id'],
                                'OfficeCode' => $funcData['location_code'],
                                'OfficeID' => $funcData['location_id'],
                                'IsActive' => 1,
                                'IsDelete' => 0,
                                'CreatedBy' => Auth::user()->name ?? 'system',
                                'CreatedOn' => DB::raw('GETDATE()'),
                                'LastModifiedBy' => null,
                                'LastModifiedOn' => null,
                            ]);

                        $insertedFunctionsCount++;
                    } catch (\Exception $e) {
                        $functionInsertErrors[] = [
                            'data' => $funcData,
                            'error' => $e->getMessage(),
                        ];
                        Log::error('Failed to insert function assignment', [
                            'function_data' => $funcData,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                Log::info('✓ Step 10: Function assignments inserted', [
                    'inserted' => $insertedFunctionsCount,
                    'failed' => count($functionInsertErrors),
                ]);
            }

            // Check for insertion errors
            $allInsertErrors = array_merge($roleInsertErrors, $functionInsertErrors);

            if (! empty($allInsertErrors)) {
                $errorMessage = sprintf(
                    'Failed to save %d assignments. First error: %s',
                    count($allInsertErrors),
                    $allInsertErrors[0]['error'] ?? 'Unknown error'
                );

                Log::error('Assignment insertion failed', [
                    'failed_count' => count($allInsertErrors),
                    'failed_records' => $allInsertErrors,
                ]);

                throw new \Exception($errorMessage);
            }

            $totalAssignmentsCreated = $insertedRolesCount + $insertedFunctionsCount;

            // ====================================================================
            // STEP 11: Provision user to D365 (if applicable)
            // ====================================================================

            // Check if user has D365 LIVE module assignments
            $hasD365Assignment = collect($validatedAssignments)
                ->contains(function ($assignment) {
                    return strtoupper($assignment['application_name']) === 'D365 LIVE';
                });

            if ($hasD365Assignment) {
                Log::info('→ Step 11: Provisioning user to D365...', [
                    'user_id' => $userId,
                    'azure_id' => $azureId,
                    'user_principal' => $userPrincipalName,
                ]);

                try {
                    $d365Result = DB::connection('sqlsrv')->select('
                    SET NOCOUNT ON;
                    DECLARE @Status VARCHAR(15), @Remark VARCHAR(200);
                    EXEC sp_Azure_D365UserCreation
                        @AzureUserID = ?,
                        @Status = @Status OUTPUT,
                        @Remark = @Remark OUTPUT;
                    SELECT @Status as Status, @Remark as Remark;
                ', [$azureId]);

                    $d365Status = $d365Result[0]->Status ?? 'Failed';
                    $d365Remark = $d365Result[0]->Remark ?? 'Unknown error';

                    if ($d365Status === 'Success') {
                        Log::info('✓ Step 11: D365 user created successfully', [
                            'user_id' => $userId,
                            'azure_id' => $azureId,
                            'remark' => $d365Remark,
                        ]);

                        $d365ProvisioningStatus = [
                            'success' => true,
                            'status' => $d365Status,
                            'message' => $d365Remark,
                        ];
                    } else {
                        Log::warning('⚠ Step 11: D365 user creation failed (non-critical)', [
                            'user_id' => $userId,
                            'azure_id' => $azureId,
                            'status' => $d365Status,
                            'remark' => $d365Remark,
                        ]);

                        $d365ProvisioningStatus = [
                            'success' => false,
                            'status' => $d365Status,
                            'message' => $d365Remark,
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error('✗ Step 11: D365 stored procedure error (non-critical)', [
                        'user_id' => $userId,
                        'azure_id' => $azureId,
                        'error' => $e->getMessage(),
                    ]);

                    $d365ProvisioningStatus = [
                        'success' => false,
                        'status' => 'Error',
                        'message' => 'Exception: ' . $e->getMessage(),
                    ];
                }
            } else {
                Log::info('⊘ Step 11: D365 provisioning skipped (no D365 LIVE assignments)', [
                    'user_id' => $userId,
                    'azure_id' => $azureId,
                ]);

                $d365ProvisioningStatus = [
                    'success' => true,
                    'status' => 'Skipped',
                    'message' => 'No D365 LIVE module assignments',
                ];
            }

            // ====================================================================
            // STEP 12: Commit transaction - everything succeeded!
            // ====================================================================

            DB::connection('sqlsrv')->commit();

            Log::info('=== USER CREATION COMPLETED SUCCESSFULLY ===', [
                'user_id' => $userId,
                'azure_id' => $azureId,
                'email' => $userEmail,
                'status' => 'Pending',
                'roles_assigned' => $insertedRolesCount,
                'functions_assigned' => $insertedFunctionsCount,
                'total_assignments' => $totalAssignmentsCreated,
                'd365_provisioning' => $d365ProvisioningStatus,
            ]);

            // Get unique module names for success message
            $uniqueModules = collect($expandedAssignments)
                ->pluck('application_name')
                ->unique()
                ->values()
                ->toArray();

            // Build success message
            $successMessage = sprintf(
                '✓ User created successfully!<br>' .
                    'Azure ID: %s<br>' .
                    'Status: <strong>Pending</strong><br>' .
                    '%d role(s) and %d function(s) created across %d module(s): %s',
                substr($azureId, 0, 8) . '...',
                $insertedRolesCount,
                $insertedFunctionsCount,
                count($uniqueModules),
                implode(', ', $uniqueModules)
            );

            // Add D365 status to message
            if ($d365ProvisioningStatus && $d365ProvisioningStatus['status'] !== 'Skipped') {
                if ($d365ProvisioningStatus['success']) {
                    $successMessage .= '<br>✓ D365: User provisioned successfully';
                } else {
                    $successMessage .= '<br>⚠ D365: Provisioning failed - ' . $d365ProvisioningStatus['message'];
                }
            }

            // Return appropriate response
            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => strip_tags($successMessage),
                    'redirect' => route('azure.dashboard.index'),
                    'data' => [
                        'user_id' => $userId,
                        'azure_id' => $azureId,
                        'email' => $userEmail,
                        'roles_created' => $insertedRolesCount,
                        'functions_created' => $insertedFunctionsCount,
                        'total_assignments' => $totalAssignmentsCreated,
                        'modules' => $uniqueModules,
                        'd365_provisioning' => $d365ProvisioningStatus,
                    ],
                ]);
            }

            return redirect()->route('azure.dashboard.index')
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            // ====================================================================
            // ERROR HANDLING & ROLLBACK
            // ====================================================================

            if (DB::connection('sqlsrv')->transactionLevel() > 0) {
                DB::connection('sqlsrv')->rollBack();
            }

            Log::error('Transaction rolled back due to error', [
                'error' => $e->getMessage(),
            ]);

            // Attempt Azure AD cleanup
            $cleanupWarning = '';
            if (isset($azureId) && $azureService) {
                try {
                    Log::info('Attempting to delete Azure user during rollback...', [
                        'azure_id' => $azureId,
                    ]);

                    $azureService->deleteUser($azureId);

                    Log::info('✓ Azure user deleted during rollback', [
                        'azure_id' => $azureId,
                    ]);
                } catch (\Exception $deleteError) {
                    Log::critical('✗ CRITICAL: Failed to delete Azure user during rollback', [
                        'azure_id' => $azureId,
                        'email' => $request->email ?? 'unknown',
                        'error' => $deleteError->getMessage(),
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
            ]);

            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => strip_tags($errorMessage),
                    'error' => $e->getMessage(),
                    'user_id' => $userId,
                    'azure_id' => $azureId,
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'error' => $errorMessage,
                ]);
        }
    }

    /**
     * Generate a secure random password for Azure AD
     * Meets Azure AD password complexity requirements:
     * - At least 8 characters
     * - Contains uppercase, lowercase, number, and special character
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
     * Display the specified user with module assignments
     */
    public function show(AzureUser $user)
    {
        try {
            Log::info('Loading user details page', [
                'user_id' => $user->Id,
                'user_email' => $user->userPrincipalName,
            ]);

            // Get provisioning status with error handling
            $provisioningStatus = null;
            try {
                $provisioningStatus = $this->provisioningService->getProvisioningStatus($user);
            } catch (\Exception $e) {
                Log::warning('Failed to get provisioning status', [
                    'user_id' => $user->Id,
                    'error' => $e->getMessage(),
                ]);

                $provisioningStatus = [
                    'local_user' => [
                        'exists' => true,
                        'status' => $user->Status,
                        'created_at' => $user->createdDateTime,
                    ],
                    'azure' => [
                        'provisioned' => ! empty($user->AzureId),
                        'azure_id' => $user->AzureId,
                        'azure_upn' => $user->userPrincipalName,
                        'synced' => false,
                        'account_enabled' => $user->accountEnabled,
                    ],
                    'modules' => [],
                    'overall_status' => $user->Status === 'Active' ? 'active' : 'pending',
                ];
            }

            // Fetch from main mapping table (has roles, no functions)
            $mappingAssignments = DB::connection('sqlsrv')
                ->table('Azure_Application_UserMapping')
                ->where('user_id', $user->userPrincipalName)
                ->where('IsActive', 1)
                ->where('IsDelete', 0)
                ->select([
                    'application_id',
                    'ApplicationName',
                    'OfficeCode',
                    'OfficeID',
                    'RoleCode',
                    'RoleID',
                    DB::raw('CAST(NULL AS VARCHAR(50)) as FunctionCode'),
                    DB::raw('CAST(NULL AS UNIQUEIDENTIFIER) as FunctionID'),
                ])
                ->get();

            // Fetch from function table (has functions, NO roles - we'll need to join to get roles)
            $functionAssignments = DB::connection('sqlsrv')
                ->select('
                SELECT
                    aauf.application_id,
                    aauf.ApplicationName,
                    aauf.OfficeCode,
                    aauf.OfficeID,
                    aaum.RoleCode,
                    aaum.RoleID,
                    aauf.FunctionCode,
                    aauf.FunctionID
                FROM Azure_Application_UserFunction aauf
                INNER JOIN Azure_Application_UserMapping aaum
                    ON aaum.user_id = aauf.user_id
                    AND aaum.application_id = aauf.application_id
                    AND aaum.OfficeCode = aauf.OfficeCode
                    AND aaum.IsActive = 1
                    AND aaum.IsDelete = 0
                WHERE aauf.user_id = ?
                    AND aauf.IsActive = 1
                    AND aauf.IsDelete = 0
            ', [$user->userPrincipalName]);

            // Convert to collection
            $functionAssignments = collect($functionAssignments);

            // Merge both collections
            $assignmentsRaw = $mappingAssignments->concat($functionAssignments);

            Log::info('Assignments fetched', [
                'mapping_count' => $mappingAssignments->count(),
                'function_count' => $functionAssignments->count(),
                'total_count' => $assignmentsRaw->count(),
            ]);

            // Group assignments by module
            $moduleAssignments = $assignmentsRaw
                ->groupBy('ApplicationName')
                ->map(function ($assignments, $moduleName) {
                    // Get unique locations
                    $locations = $assignments->pluck('OfficeCode')->unique()->sort()->values();

                    // Get unique roles
                    $roles = $assignments->map(function ($assignment) {
                        return [
                            'id' => $assignment->RoleID,
                            'code' => $assignment->RoleCode,
                        ];
                    })->unique('code')->values();

                    // Get unique functions
                    $functions = $assignments
                        ->filter(function ($assignment) {
                            return ! empty($assignment->FunctionCode);
                        })
                        ->map(function ($assignment) {
                            return [
                                'id' => $assignment->FunctionID,
                                'code' => $assignment->FunctionCode,
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
                        'total_assignments' => $assignments->count(),
                    ];
                })
                ->sortBy('module_name')
                ->values();

            Log::info('User details loaded successfully', [
                'user_id' => $user->Id,
                'modules_count' => $moduleAssignments->count(),
                'total_assignments' => $assignmentsRaw->count(),
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
                'trace' => $e->getTraceAsString(),
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
                ],
            ]);

            // ====================================================================
            // 1. Fetch all modules
            // ====================================================================
            $modulesRaw = DB::connection('sqlsrv')
                ->select("SELECT Id, Name, ApplicationId
                FROM Azure_SoftwareMaster
                WHERE IsActive = 1 AND IsDeleted = 0
                AND Name != 'Odoo'
                AND Name NOT LIKE '%UAT%'
                ORDER BY Name");

            $modules = collect($modulesRaw)->map(function ($module) {
                return (object) [
                    'id' => $module->Id,
                    'name' => $module->Name,
                    'application_id' => $module->ApplicationId,
                ];
            });

            Log::info('Modules loaded', [
                'count' => $modules->count(),
            ]);

            // ====================================================================
            // 2. Fetch all locations
            // ====================================================================
            $locationsRaw = DB::connection('sqlsrv')
                ->select("SELECT Id, Name, code
                FROM Cust_Office
                WHERE IsActive = 1
                    AND IsDeleted = 0
                    AND code != 'ALL'
                ORDER BY Name");

            $locations = collect($locationsRaw)->map(function ($location) {
                return (object) [
                    'id' => $location->Id,
                    'name' => $location->Name,
                    'code' => $location->code,
                ];
            });

            Log::info('Locations loaded', [
                'count' => $locations->count(),
            ]);

            // ====================================================================
            // 3. Fetch user's existing module assignments
            // ====================================================================

            // Fetch from mapping table (has roles, no functions)
            $mappingAssignments = DB::connection('sqlsrv')
                ->table('Azure_Application_UserMapping')
                ->where('user_id', $user->userPrincipalName)
                ->where('IsActive', 1)
                ->where('IsDelete', 0)
                ->select([
                    'Id',
                    'user_id',
                    'application_id',
                    'ApplicationName as module_name',
                    'RoleCode as role_code',
                    'RoleID as role_id',
                    'OfficeCode as location',
                    'OfficeID as location_id',
                    DB::raw('CAST(NULL AS VARCHAR(50)) as function_code'),
                    DB::raw('CAST(NULL AS UNIQUEIDENTIFIER) as function_id'),
                ])
                ->get();

            // Fetch from function table with JOIN to get roles
            $functionAssignments = DB::connection('sqlsrv')
                ->select('
        SELECT
            aauf.Id,
            aauf.user_id,
            aauf.application_id,
            aauf.ApplicationName as module_name,
            aauf.FunctionCode as function_code,
            aauf.FunctionID as function_id,
            aaum.RoleCode as role_code,
            aaum.RoleID as role_id,
            aauf.OfficeCode as location,
            aauf.OfficeID as location_id
        FROM Azure_Application_UserFunction aauf
        INNER JOIN Azure_Application_UserMapping aaum
            ON aaum.user_id = aauf.user_id
            AND aaum.application_id = aauf.application_id
            AND aaum.OfficeCode = aauf.OfficeCode
            AND aaum.IsActive = 1
            AND aaum.IsDelete = 0
        WHERE aauf.user_id = ?
            AND aauf.IsActive = 1
            AND aauf.IsDelete = 0
    ', [$user->userPrincipalName]);

            // Merge both collections
            $userAssignmentsRaw = $mappingAssignments->merge(collect($functionAssignments));

            // Get module IDs for the assignments
            $moduleIds = $userAssignmentsRaw->pluck('application_id')->unique()->filter();
            $modulesLookup = collect([]);

            if ($moduleIds->isNotEmpty()) {
                $modulesLookup = DB::connection('sqlsrv')
                    ->table('Azure_SoftwareMaster')
                    ->whereIn('ApplicationId', $moduleIds->toArray())
                    ->get()
                    ->keyBy('ApplicationId');
            }

            // Map assignments with module IDs
            $userModuleAssignments = $userAssignmentsRaw->map(function ($assignment) use ($modulesLookup) {
                $assignment = (object) $assignment; // Ensure it's an object
                $module = $modulesLookup[$assignment->application_id] ?? null;

                return [
                    'id' => $assignment->Id,
                    'module_id' => $module ? $module->Id : null,
                    'module_name' => $assignment->module_name,
                    'application_id' => $assignment->application_id,
                    'location' => $assignment->location,
                    'location_id' => $assignment->location_id,
                    'role_id' => $assignment->role_id,
                    'role_code' => $assignment->role_code,
                    'function_id' => $assignment->function_id ?? null,
                    'function_code' => $assignment->function_code ?? null,
                ];
            });

            Log::info('User module assignments loaded', [
                'user_id' => $user->Id,
                'assignments_count' => $userModuleAssignments->count(),
                'unique_modules' => $userModuleAssignments->pluck('module_name')->unique()->count(),
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
                'assignments_count' => $userModuleAssignments->count(),
            ]);

            // Empty collections for companies and roles (not needed in edit)
            $companies = collect([]);
            $roles = collect([]);

            // ====================================================================
            // 5. Fetch offices for Base Office dropdown
            // ====================================================================
            $officesRaw = DB::connection('sqlsrv')
                ->select("SELECT Id, Name, Code
        FROM Cust_Office
        WHERE IsActive = 1
            AND IsDeleted = 0
            AND code != 'ALL'
        ORDER BY Name");

            $offices = collect($officesRaw)->map(function ($office) {
                return (object) [
                    'Id' => $office->Id,
                    'Name' => $office->Name,
                    'Code' => $office->Code,
                ];
            });

            Log::info('Offices loaded', [
                'count' => $offices->count(),
            ]);

            return view('azure-management.users.edit', compact(
                'user',
                'companies',
                'modules',
                'locations',
                'offices',      // ← ADD THIS LINE
                'roles',
                'userModuleAssignments'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading edit form', [
                'user_id' => $user->Id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('azure.dashboard.index')
                ->withErrors(['error' => 'Failed to load edit form: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified user - Enhanced with 1-to-1 method (separate roles and functions tables)
     */
    public function update(Request $request, AzureUser $user)
    {

        Log::info('=== UPDATE USER DEBUG INFO ===', [
            'user_id' => $user->Id,
            'user_principal' => $user->userPrincipalName,
            'azure_id_from_model' => $user->AzureId,
            'azure_id_raw' => $user->getAttributes()['AzureId'] ?? 'NOT SET',
            'all_attributes' => $user->getAttributes(),
        ]);

        set_time_limit(120);
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        Log::info('=== UPDATE METHOD ENTRY - FULL REQUEST DEBUG ===', [
            'request_class' => get_class($request),
            'request_method' => $request->method(),
            'is_ajax' => $request->ajax(),
            'expects_json' => $request->expectsJson(),
            'content_type' => $request->header('Content-Type'),
            'all_input_keys' => array_keys($request->all()),
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
                            'error' => $e->getMessage(),
                        ]);
                        $modulesData = [];
                    }
                }
            }

            if (! is_array($modulesData)) {
                Log::warning('Modules data is not an array, converting to empty array', [
                    'type' => gettype($modulesData),
                    'value' => $modulesData,
                ]);
                $modulesData = [];
            }

            Log::info('Final modules data', [
                'count' => count($modulesData),
                'is_empty' => empty($modulesData),
                'type' => gettype($modulesData),
            ]);

            // ====================================================================
            // STEP 2: Validate modules only if we have them
            // ====================================================================
            if (! empty($modulesData)) {
                try {
                    $validated = $request->validate([
                        'modules' => 'required|array|min:1',
                        'modules.*.module_id' => 'required|string',
                        'modules.*.application_id' => 'required|string',
                        'modules.*.location_id' => 'required|string',
                        'modules.*.location_code' => 'required|string',
                        'modules.*.role_id' => 'nullable|string',
                        'modules.*.role_code' => 'nullable|string',
                        'modules.*.function_id' => 'nullable|string',
                        'modules.*.function_code' => 'nullable|string',
                    ]);

                    Log::info('Validation passed', ['modules_count' => count($modulesData)]);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    Log::error('Validation failed', ['errors' => $e->errors()]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $e->errors(),
                    ], 422);
                }
            } else {
                Log::info('No modules to validate - proceeding with empty array');
            }

            // ====================================================================
            // STEP 3: Start database transaction
            // ====================================================================
            DB::connection('sqlsrv')->beginTransaction();

            try {
                Log::info('📦 Processing modules for update', [
                    'total_assignments' => count($modulesData),
                    'user_principal' => $user->userPrincipalName,
                ]);

                if (count($modulesData) > 0) {
                    Log::info('📝 Sample assignments (first 2):', [
                        'samples' => array_slice($modulesData, 0, 2),
                    ]);
                } else {
                    Log::info('📝 No module assignments - all existing modules will be deleted');
                }

                // ====================================================================
                // STEP 4: Soft delete existing assignments FROM BOTH TABLES
                // ====================================================================

                $deletedRolesCount = DB::connection('sqlsrv')
                    ->table('Azure_Application_UserMapping')
                    ->where('user_id', $user->userPrincipalName)
                    ->where('IsDelete', 0)
                    ->update([
                        'IsDelete' => 1,
                        'IsActive' => 0,
                        'LastModifiedBy' => Auth::id() ?? 'system',
                        //'LastModifiedOn' => DB::raw('GETDATE()'),
                    ]);

                $deletedFunctionsCount = DB::connection('sqlsrv')
                    ->table('Azure_Application_UserFunction')
                    ->where('user_id', $user->userPrincipalName)
                    ->where('IsDelete', 0)
                    ->update([
                        'IsDelete' => 1,
                        'IsActive' => 0,
                        'LastModifiedBy' => Auth::id() ?? 'system',
                        //'LastModifiedOn' => DB::raw('GETDATE()'),
                    ]);

                $totalDeleted = $deletedRolesCount + $deletedFunctionsCount;

                Log::info('🗑️ Marked existing assignments as deleted', [
                    'user_principal' => $user->userPrincipalName,
                    'deleted_roles_count' => $deletedRolesCount,
                    'deleted_functions_count' => $deletedFunctionsCount,
                    'total_deleted' => $totalDeleted,
                ]);

                // ====================================================================
                // STEP 5: If no new modules, commit and return success
                // ====================================================================
                if (empty($modulesData)) {
                    DB::connection('sqlsrv')->commit();

                    Log::info('=== UPDATE COMPLETED (ALL MODULES DELETED) ===', [
                        'user_principal' => $user->userPrincipalName,
                        'deleted_assignments' => $totalDeleted,
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => "All module assignments removed successfully! {$totalDeleted} assignments deleted.",
                        'data' => [
                            'deleted' => $totalDeleted,
                            'inserted' => 0,
                        ],
                    ]);
                }

                // ====================================================================
                // STEP 6: Separate roles and functions (1-to-1 method)
                // ====================================================================

                $roleAssignments = [];
                $functionAssignments = [];

                Log::info('→ Step 6: Separating roles and functions (1-to-1 method)...', [
                    'total_assignments' => count($modulesData),
                ]);

                foreach ($modulesData as $index => $moduleData) {
                    $hasRole = ! empty($moduleData['role_id']);
                    $hasFunction = ! empty($moduleData['function_id']);

                    if (! $hasRole && ! $hasFunction) {
                        Log::warning('Assignment has neither role nor function - skipping', [
                            'index' => $index,
                            'data' => $moduleData,
                        ]);
                        continue;
                    }

                    if ($hasRole) {
                        $roleAssignments[] = $moduleData;
                    }

                    if ($hasFunction) {
                        $functionAssignments[] = $moduleData;
                    }
                }

                Log::info('✓ Step 6: Separated assignments', [
                    'role_assignments' => count($roleAssignments),
                    'function_assignments' => count($functionAssignments),
                    'total' => count($roleAssignments) + count($functionAssignments),
                ]);

                // ====================================================================
                // STEP 7: Validate and insert ROLES
                // ====================================================================

                $validatedRoles = [];
                $roleValidationErrors = [];

                Log::info('→ Step 7a: Validating role assignments...', [
                    'total_roles' => count($roleAssignments),
                ]);

                foreach ($roleAssignments as $index => $roleData) {
                    $validation = $this->validateAssignment($roleData, $index);

                    if (! $validation['valid']) {
                        $roleValidationErrors[] = $validation['error'];
                        Log::error('Role assignment validation failed', [
                            'index' => $index,
                            'error' => $validation['error'],
                        ]);
                        continue;
                    }

                    $dataCheck = $this->verifyReferencedData($validation['data']);

                    if (! $dataCheck['valid']) {
                        $roleValidationErrors[] = "Role assignment {$index}: " . implode(', ', $dataCheck['errors']);
                        Log::error('Referenced data validation failed for role', [
                            'index' => $index,
                            'errors' => $dataCheck['errors'],
                        ]);
                        continue;
                    }

                    $module = DB::connection('sqlsrv')
                        ->table('Azure_SoftwareMaster')
                        ->where('Id', $validation['data']['module_id'])
                        ->first();

                    $validation['data']['application_name'] = $module->Name;
                    $validatedRoles[] = $validation['data'];
                }

                if (! empty($roleValidationErrors)) {
                    Log::error('Role validation failed - rolling back', [
                        'total_errors' => count($roleValidationErrors),
                        'errors' => $roleValidationErrors,
                    ]);

                    DB::connection('sqlsrv')->rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Role validation failed: ' . implode(', ', array_slice($roleValidationErrors, 0, 3)),
                        'errors' => $roleValidationErrors,
                    ], 422);
                }

                Log::info('✓ Step 7a: Role assignments validated', [
                    'valid_roles' => count($validatedRoles),
                ]);

                // ====================================================================
                // STEP 8: Validate and insert FUNCTIONS
                // ====================================================================

                $validatedFunctions = [];
                $functionValidationErrors = [];

                Log::info('→ Step 8a: Validating function assignments...', [
                    'total_functions' => count($functionAssignments),
                ]);

                foreach ($functionAssignments as $index => $funcData) {
                    $validation = $this->validateAssignment($funcData, $index);

                    if (! $validation['valid']) {
                        $functionValidationErrors[] = $validation['error'];
                        Log::error('Function assignment validation failed', [
                            'index' => $index,
                            'error' => $validation['error'],
                        ]);
                        continue;
                    }

                    $dataCheck = $this->verifyReferencedData($validation['data']);

                    if (! $dataCheck['valid']) {
                        $functionValidationErrors[] = "Function assignment {$index}: " . implode(', ', $dataCheck['errors']);
                        Log::error('Referenced data validation failed for function', [
                            'index' => $index,
                            'errors' => $dataCheck['errors'],
                        ]);
                        continue;
                    }

                    $module = DB::connection('sqlsrv')
                        ->table('Azure_SoftwareMaster')
                        ->where('Id', $validation['data']['module_id'])
                        ->first();

                    $validation['data']['application_name'] = $module->Name;
                    $validatedFunctions[] = $validation['data'];
                }

                if (! empty($functionValidationErrors)) {
                    Log::error('Function validation failed - rolling back', [
                        'total_errors' => count($functionValidationErrors),
                        'errors' => $functionValidationErrors,
                    ]);

                    DB::connection('sqlsrv')->rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Function validation failed: ' . implode(', ', array_slice($functionValidationErrors, 0, 3)),
                        'errors' => $functionValidationErrors,
                    ], 422);
                }

                Log::info('✓ Step 8a: Function assignments validated', [
                    'valid_functions' => count($validatedFunctions),
                ]);

                // ====================================================================
                // STEP 9: Insert ROLES to Azure_Application_UserMapping
                // ====================================================================

                Log::info('→ Step 9: Inserting role assignments...', [
                    'total_roles' => count($validatedRoles),
                ]);

                $insertedRolesCount = 0;
                $roleInsertErrors = [];

                foreach ($validatedRoles as $roleData) {
                    try {
                        DB::connection('sqlsrv')
                            ->table('Azure_Application_UserMapping')
                            ->insert([
                                'Id' => \Illuminate\Support\Str::uuid()->toString(),
                                'AzureUserID' => $user->AzureId,
                                'user_id' => $user->userPrincipalName,
                                'application_id' => $roleData['application_id'],
                                'ApplicationName' => $roleData['application_name'],
                                'RoleID' => $roleData['role_id'],
                                'RoleCode' => $roleData['role_code'],
                                'OfficeID' => $roleData['location_id'],
                                'OfficeCode' => $roleData['location_code'],
                                'IsActive' => 1,
                                'IsDelete' => 0,
                                'CreatedBy' => Auth::user()->name ?? 'system',
                                'CreatedOn' => DB::raw('GETDATE()'),
                                //'LastModifiedBy' => Auth::user()->name ?? 'system',
                                //'LastModifiedOn' => DB::raw('GETDATE()'),
                            ]);

                        $insertedRolesCount++;
                    } catch (\Exception $e) {
                        $roleInsertErrors[] = [
                            'data' => $roleData,
                            'error' => $e->getMessage(),
                        ];
                        Log::error('Failed to insert role assignment', [
                            'role_data' => $roleData,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                Log::info('✓ Step 9: Role assignments inserted', [
                    'inserted' => $insertedRolesCount,
                    'failed' => count($roleInsertErrors),
                ]);

                // ====================================================================
                // STEP 10: Insert FUNCTIONS to Azure_Application_UserFunction
                // ====================================================================

                Log::info('→ Step 10: Inserting function assignments...', [
                    'total_functions' => count($validatedFunctions),
                ]);

                $insertedFunctionsCount = 0;
                $functionInsertErrors = [];

                foreach ($validatedFunctions as $funcData) {
                    try {
                        DB::connection('sqlsrv')
                            ->table('Azure_Application_UserFunction')
                            ->insert([
                                'Id' => \Illuminate\Support\Str::uuid()->toString(),
                                'AzureUserID' => $user->AzureId,
                                'user_id' => $user->userPrincipalName,
                                'application_id' => $funcData['application_id'],
                                'ApplicationName' => $funcData['application_name'],
                                'FunctionID' => $funcData['function_id'],
                                'FunctionCode' => $funcData['function_code'],
                                'OfficeID' => $funcData['location_id'],
                                'OfficeCode' => $funcData['location_code'],
                                'IsActive' => 1,
                                'IsDelete' => 0,
                                'CreatedBy' => Auth::user()->name ?? 'system',
                                'CreatedOn' => DB::raw('GETDATE()'),
                                //'LastModifiedBy' => Auth::user()->name ?? 'system',
                                //'LastModifiedOn' => DB::raw('GETDATE()'),
                            ]);

                        $insertedFunctionsCount++;
                    } catch (\Exception $e) {
                        $functionInsertErrors[] = [
                            'data' => $funcData,
                            'error' => $e->getMessage(),
                        ];
                        Log::error('Failed to insert function assignment', [
                            'function_data' => $funcData,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                Log::info('✓ Step 10: Function assignments inserted', [
                    'inserted' => $insertedFunctionsCount,
                    'failed' => count($functionInsertErrors),
                ]);

                // ====================================================================
                // STEP 11: Check for insertion errors
                // ====================================================================

                $allInsertErrors = array_merge($roleInsertErrors, $functionInsertErrors);

                if (! empty($allInsertErrors)) {
                    $errorMessage = sprintf(
                        'Failed to save %d assignments. First error: %s',
                        count($allInsertErrors),
                        $allInsertErrors[0]['error'] ?? 'Unknown error'
                    );

                    Log::error('Assignment insertion failed - rolling back', [
                        'failed_count' => count($allInsertErrors),
                        'failed_records' => $allInsertErrors,
                    ]);

                    DB::connection('sqlsrv')->rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'errors' => array_column($allInsertErrors, 'error'),
                    ], 422);
                }

                // ====================================================================
                // STEP 12: Commit transaction
                // ====================================================================

                DB::connection('sqlsrv')->commit();

                $totalInserted = $insertedRolesCount + $insertedFunctionsCount;

                Log::info('=== UPDATE COMPLETED SUCCESSFULLY ===', [
                    'user_principal' => $user->userPrincipalName,
                    'roles_inserted' => $insertedRolesCount,
                    'functions_inserted' => $insertedFunctionsCount,
                    'total_inserted' => $totalInserted,
                    'total_deleted' => $totalDeleted,
                ]);

                // This ensures we have the latest data before provisioning
                try {
                    $freshUser = AzureUser::find($user->Id);
                    if ($freshUser) {
                        $user = $freshUser;
                        Log::info('Model refreshed successfully', [
                            'user_id' => $user->Id,
                            'azure_id' => $user->AzureId,
                        ]);
                    } else {
                        Log::warning('Could not find user during refresh', ['user_id' => $user->Id]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to refresh model', ['error' => $e->getMessage()]);
                }

                // ====================================================================
                // STEP 13: PROVISION TO EXTERNAL APPLICATIONS
                // ====================================================================

                $provisioningResults = [];
                $allValidatedAssignments = array_merge($validatedRoles, $validatedFunctions);

                $assignedModules = collect($allValidatedAssignments)
                    ->pluck('application_name')
                    ->unique()
                    ->map(fn($name) => strtoupper(trim($name)))
                    ->filter()
                    ->values();

                Log::info('→ Step 13: Provisioning to external applications...', [
                    'modules' => $assignedModules->toArray(),
                ]);





                // ✅ CRITICAL FIX: Comprehensive Azure ID retrieval with multiple fallback methods
                $azureId = null;

                // Method 1: Try from the model object first
                if (!empty($user->AzureId)) {
                    $azureId = $this->normalizeAzureId($user->AzureId);
                    if ($azureId) {
                        Log::info('Method 1: Azure ID from model object', ['azure_id' => $azureId]);
                    }
                }

                // Method 2: Refresh model and try again
                if (!$azureId) {
                    try {
                        $user = $user->fresh();
                        if ($user && !empty($user->AzureId)) {
                            $azureId = $this->normalizeAzureId($user->AzureId);
                            if ($azureId) {
                                Log::info('Method 2: Azure ID after model refresh', ['azure_id' => $azureId]);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('Method 2 failed', ['error' => $e->getMessage()]);
                    }
                }

                // Method 3: Direct database query (bypassing Eloquent)
                if (!$azureId) {
                    try {
                        $dbResult = DB::connection('sqlsrv')
                            ->table('Azure_Users')
                            ->select('AzureId', 'Id', 'userPrincipalName')
                            ->where('Id', $user->Id)
                            ->first();

                        if ($dbResult && !empty($dbResult->AzureId)) {
                            $azureId = $this->normalizeAzureId($dbResult->AzureId);
                            if ($azureId) {
                                Log::info('Method 3: Azure ID from direct DB query', ['azure_id' => $azureId]);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Method 3 failed', ['error' => $e->getMessage()]);
                    }
                }

                // Method 4: Look up in Azure AD by email (last resort)
                if (!$azureId) {
                    try {
                        $azureService = app(\App\Services\AzureGraphService::class);
                        $azureUser = $azureService->findUserByUPN($user->userPrincipalName);

                        if ($azureUser && isset($azureUser['id']) && $this->isValidUuid($azureUser['id'])) {
                            $azureId = trim($azureUser['id']);

                            // Update the database with the correct Azure ID
                            DB::connection('sqlsrv')
                                ->table('Azure_Users')
                                ->where('Id', $user->Id)
                                ->update(['AzureId' => $azureId]);

                            Log::info('Method 4: Azure ID found via Azure AD lookup', [
                                'azure_id' => $azureId,
                                'upn' => $user->userPrincipalName
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Method 4 failed', [
                            'error' => $e->getMessage(),
                            'upn' => $user->userPrincipalName
                        ]);
                    }
                }

                $azureId = $this->getAzureIdWithFallback($user);

                if (empty($azureId)) {
                    Log::error('✗ CRITICAL: Cannot provision - no Azure ID found', [
                        'user_id' => $user->Id,
                        'user_principal' => $user->userPrincipalName,
                    ]);

                    $provisioningResults['ERROR'] = [
                        'success' => false,
                        'message' => 'User not provisioned to Azure AD - no valid Azure ID found',
                    ];
                } else {
                    Log::info('✓ Using Azure ID for provisioning', ['azure_id' => $azureId]);

                    // 13.1: D365 Live
                    if ($assignedModules->contains('D365 LIVE')) {
                        Log::info('→ Provisioning to D365 Live...');

                        try {
                            $d365Result = DB::connection('sqlsrv')->select('
                SET NOCOUNT ON;
                DECLARE @Status VARCHAR(15), @Remark VARCHAR(200);
                EXEC sp_Azure_D365UserCreation
                    @AzureUserID = ?,
                    @Status = @Status OUTPUT,
                    @Remark = @Remark OUTPUT;
                SELECT @Status as Status, @Remark as Remark;
            ', [$azureId]);

                            $d365Status = $d365Result[0]->Status ?? 'Failed';
                            $d365Remark = $d365Result[0]->Remark ?? 'Unknown error';

                            $provisioningResults['D365 LIVE'] = [
                                'success' => $d365Status === 'Success',
                                'message' => $d365Remark,
                            ];

                            Log::info($d365Status === 'Success' ? '✓ D365 provisioned' : '⚠ D365 failed', [
                                'status' => $d365Status,
                                'remark' => $d365Remark,
                            ]);
                        } catch (\Exception $e) {
                            $provisioningResults['D365 LIVE'] = [
                                'success' => false,
                                'message' => $e->getMessage(),
                            ];
                            Log::error('✗ D365 provisioning error', ['error' => $e->getMessage()]);
                        }
                    }

                    // 13.2: OPS/SCM
                    if ($assignedModules->contains('OPS') || $assignedModules->contains('OPS UAT') || $assignedModules->contains('OPSUAT')) {
                        Log::info('→ Provisioning to OPS/SCM...');

                        try {
                            $stmt = DB::connection('sqlsrv')->getPdo()->prepare(
                                'EXEC sp_Azure_SCMUserCreation ?, @Status OUTPUT, @Remark OUTPUT'
                            );

                            $status = '';
                            $remark = '';

                            $stmt->bindParam(1, $azureId, \PDO::PARAM_STR);
                            $stmt->bindParam(2, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 15);
                            $stmt->bindParam(3, $remark, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 200);

                            $stmt->execute();

                            $provisioningResults['OPS'] = [
                                'success' => strtoupper($status) === 'SUCCESS',
                                'message' => $remark,
                            ];

                            Log::info(strtoupper($status) === 'SUCCESS' ? '✓ OPS provisioned' : '⚠ OPS failed', [
                                'status' => $status,
                                'remark' => $remark,
                            ]);
                        } catch (\Exception $e) {
                            $provisioningResults['OPS'] = [
                                'success' => false,
                                'message' => $e->getMessage(),
                            ];
                            Log::error('✗ OPS provisioning error', ['error' => $e->getMessage()]);
                        }
                    }

                    // 13.3: BIZ PRO
                    if ($assignedModules->contains('BIZ PRO') || $assignedModules->contains('FITSYS')) {
                        Log::info('→ Provisioning to BIZ PRO...');

                        try {
                            $bizApp = DB::connection('sqlsrv')
                                ->table('Azure_SoftwareMaster')
                                ->where(function ($q) {
                                    $q->where('Name', 'LIKE', '%FITSYS%')
                                        ->orWhere('Name', 'LIKE', '%BIZ%');
                                })
                                ->first();

                            if ($bizApp) {
                                $stmt = DB::connection('sqlsrv')->getPdo()->prepare(
                                    'EXEC sp_Azure_FITSYSUserCreation ?, ?, @Status OUTPUT, @Remark OUTPUT'
                                );

                                $status = '';
                                $remark = '';

                                $stmt->bindParam(1, $azureId, \PDO::PARAM_STR);
                                $stmt->bindParam(2, $bizApp->Id, \PDO::PARAM_STR);
                                $stmt->bindParam(3, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 15);
                                $stmt->bindParam(4, $remark, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 200);

                                $stmt->execute();

                                $provisioningResults['BIZ PRO'] = [
                                    'success' => strtolower($status) === 'success',
                                    'message' => $remark,
                                ];

                                Log::info(strtolower($status) === 'success' ? '✓ BIZ PRO provisioned' : '⚠ BIZ PRO failed', [
                                    'status' => $status,
                                    'remark' => $remark,
                                ]);
                            }
                        } catch (\Exception $e) {
                            $provisioningResults['BIZ PRO'] = [
                                'success' => false,
                                'message' => $e->getMessage(),
                            ];
                            Log::error('✗ BIZ PRO provisioning error', ['error' => $e->getMessage()]);
                        }
                    }

                    // 13.4: FIT Express  
                    if ($assignedModules->contains('FIT EXPRESS')) {
                        Log::info('→ Provisioning to FIT Express...');

                        try {
                            $fitApp = DB::connection('sqlsrv')
                                ->table('Azure_SoftwareMaster')
                                ->where('Name', 'LIKE', '%FIT Express%')
                                ->first();

                            if ($fitApp) {
                                $stmt = DB::connection('sqlsrv')->getPdo()->prepare(
                                    'EXEC sp_Azure_FITSYSUserCreation ?, ?, @Status OUTPUT, @Remark OUTPUT'
                                );

                                $status = '';
                                $remark = '';

                                $stmt->bindParam(1, $azureId, \PDO::PARAM_STR);
                                $stmt->bindParam(2, $fitApp->Id, \PDO::PARAM_STR);
                                $stmt->bindParam(3, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 15);
                                $stmt->bindParam(4, $remark, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 200);

                                $stmt->execute();

                                $provisioningResults['FIT Express'] = [
                                    'success' => strtolower($status) === 'success',
                                    'message' => $remark,
                                ];


                                Log::info(strtolower($status) === 'success' ? '✓ FIT Express provisioned' : '⚠ FIT Express failed', [
                                    'status' => $status,
                                    'remark' => $remark,
                                ]);
                            }
                        } catch (\Exception $e) {
                            $provisioningResults['FIT Express'] = [
                                'success' => false,
                                'message' => $e->getMessage(),
                            ];
                            Log::error('✗ FIT Express provisioning error', ['error' => $e->getMessage()]);
                        }
                    }
                }

                Log::info('✓ Step 13: Provisioning completed', [
                    'results' => $provisioningResults,
                ]);

                // ====================================================================
                // STEP 14: Verify data was saved
                // ====================================================================

                $verifyRolesCount = DB::connection('sqlsrv')
                    ->table('Azure_Application_UserMapping')
                    ->where('user_id', $user->userPrincipalName)
                    ->where('IsActive', 1)
                    ->where('IsDelete', 0)
                    ->count();

                $verifyFunctionsCount = DB::connection('sqlsrv')
                    ->table('Azure_Application_UserFunction')
                    ->where('user_id', $user->userPrincipalName)
                    ->where('IsActive', 1)
                    ->where('IsDelete', 0)
                    ->count();

                Log::info('✅ Verification check', [
                    'expected_roles' => $insertedRolesCount,
                    'actual_roles_in_db' => $verifyRolesCount,
                    'expected_functions' => $insertedFunctionsCount,
                    'actual_functions_in_db' => $verifyFunctionsCount,
                    'roles_match' => $insertedRolesCount === $verifyRolesCount ? 'YES' : 'NO',
                    'functions_match' => $insertedFunctionsCount === $verifyFunctionsCount ? 'YES' : 'NO',
                ]);

                // ====================================================================
                // STEP 15: Return success response
                // ====================================================================

                $provisioningSummary = '';
                if (!empty($provisioningResults)) {
                    $successCount = collect($provisioningResults)->where('success', true)->count();
                    $totalCount = count($provisioningResults);

                    $provisioningSummary = "\n\nProvisioning: {$successCount}/{$totalCount} successful";

                    foreach ($provisioningResults as $module => $result) {
                        $icon = $result['success'] ? '✓' : '⚠';
                        $provisioningSummary .= "\n{$icon} {$module}: {$result['message']}";
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => "User updated successfully! {$insertedRolesCount} roles and {$insertedFunctionsCount} functions saved.{$provisioningSummary}",
                    'data' => [
                        'roles_inserted' => $insertedRolesCount,
                        'functions_inserted' => $insertedFunctionsCount,
                        'total_inserted' => $totalInserted,
                        'deleted' => $totalDeleted,
                        'roles_verified' => $verifyRolesCount,
                        'functions_verified' => $verifyFunctionsCount,
                        'user_id' => $user->Id,
                        'provisioning_results' => $provisioningResults,
                    ],
                ]);
            } catch (\Exception $e) {
                if (DB::connection('sqlsrv')->transactionLevel() > 0) {
                    DB::connection('sqlsrv')->rollBack();
                }

                Log::error('❌ Database transaction failed', [
                    'user_id' => $user->Id,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),

                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage(),
                    'error_details' => config('app.debug') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'message' => $e->getMessage(),
                    ] : null,
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('❌ Unexpected error in update method', [
                'user_id' => $user->Id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage(),
                'error_details' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null,
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
                'modules_empty' => '1',
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
                    'email' => $user->userPrincipalName,
                ],
                'database' => 'connected',
                'request' => $requestData,
            ]);
        } catch (\Exception $e) {
            Log::error('Test endpoint error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Sync user status from Pending to Active
     *
     * This method is called after external systems (SCM, Odoo, BIZ) have
     * created their users and populated the external ID columns.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sync(AzureUser $user)
    {
        try {
            Log::info('=== USER SYNC STARTED ===', [
                'user_id' => $user->Id,
                'email' => $user->userPrincipalName,
                'current_status' => $user->Status,
                'initiated_by' => Auth::id(),
            ]);

            // Validation 1: Check if user is already Active
            if ($user->Status === 'Active') {
                Log::info('User already active - no action needed', [
                    'user_id' => $user->Id,
                    'status' => $user->Status,
                ]);

                return redirect()->back()
                    ->with('info', 'User is already Active. No changes made.');
            }

            // Validation 2: Check if user is Pending (only valid state for sync)
            if ($user->Status !== 'Pending') {
                Log::warning('Invalid status for sync operation', [
                    'user_id' => $user->Id,
                    'current_status' => $user->Status,
                    'expected_status' => 'Pending',
                ]);

                return redirect()->back()
                    ->with('error', sprintf(
                        'Only Pending users can be synced. Current status: %s',
                        $user->Status
                    ));
            }

            // Validation 3: Check if user has been provisioned to Azure AD
            if (!$user->isProvisionedToAzure()) {
                Log::error('Cannot sync - user not provisioned to Azure AD', [
                    'user_id' => $user->Id,
                    'email' => $user->userPrincipalName,
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
                'total_synced' => $externalSystems['synced_count'],
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
                $user->Id,
            ]);

            Log::info('=== USER SYNC COMPLETED SUCCESSFULLY ===', [
                'user_id' => $user->Id,
                'email' => $user->userPrincipalName,
                'old_status' => 'Pending',
                'new_status' => 'Active',
                'synced_systems' => $syncedSystemNames,
                'synced_count' => count($syncedSystemNames),
                'synced_by' => Auth::id(),
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
                        'biz' => $externalSystems['biz']['user_id'],
                    ],
                ]
            );

            // Build success message
            $systemsList = ! empty($syncedSystemNames)
                ? sprintf(' Synced with: <strong>%s</strong>', implode(', ', $syncedSystemNames))
                : ' <em>(No external systems synced yet)</em>';

            return redirect()->back()
                ->with('success', '✓ User activated successfully!' . $systemsList);
        } catch (\Exception $e) {
            Log::error('=== USER SYNC FAILED ===', [
                'user_id' => $user->Id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
                'azure_provisioned' => ! empty($user->AzureId),
                'azure_id' => $user->AzureId,
                'external_systems' => $externalSystems,
                'can_be_activated' => $user->Status === 'Pending' && ! empty($user->AzureId),
                'is_active' => $user->Status === 'Active',
                'module_assignments_count' => $user->moduleAssignments()->count(),
                'last_modified' => $user->LastModifiedOn,
                'last_modified_by' => $user->LastModifiedBy,
            ];

            return response()->json([
                'success' => true,
                'data' => $syncStatus,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get sync status', [
                'user_id' => $user->Id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get sync status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper method: Get external systems sync status
     *
     * Checks which external systems (SCM, Odoo, BIZ) have synced the user
     * by checking if their respective ID columns are populated
     */
    private function getExternalSystemsSyncStatus(AzureUser $user): array
    {
        return [
            'scm' => [
                'synced' => ! empty($user->SCMUserID),
                'user_id' => $user->SCMUserID,
                'resource_id' => $user->SCMResourceID,
                'login_id' => $user->SCMLoginId,
                'created_on' => $user->SCMCreatedOn,
            ],
            'odoo' => [
                'synced' => ! empty($user->OdooUserId),
                'user_id' => $user->OdooUserId,
                'employee_id' => $user->OdooEmployeeId,
                'login_id' => $user->OdooLoginId,
                'created_on' => $user->OdooCreatedOn,
            ],
            'biz' => [
                'synced' => ! empty($user->BizUserID),
                'user_id' => $user->BizUserID,
                'login_id' => $user->BizLoginId,
                'created_on' => $user->BizCreatedOn,
            ],
            'synced_count' => (empty($user->SCMUserID) ? 0 : 1) +
                (empty($user->OdooUserId) ? 0 : 1) +
                (empty($user->BizUserID) ? 0 : 1),
        ];
    }

    /**
     * Helper method: Log user activity to audit trail
     *
     * Creates an audit log entry for user-related actions
     *
     * @param  string|AzureUser  $userOrId  - Either Azure_Users.Id (GUID string) or AzureUser model instance
     * @param  string  $action  - Action type (e.g., 'user.synced', 'user.created')
     * @param  string  $description  - Human-readable description
     * @param  array  $metadata  - Additional data to store
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
                    'timestamp' => now()->toIso8601String(),
                ]),
                'created_at' => now(),
            ]);

            Log::info('Audit log created', [
                'action' => $action,
                'user_id' => $userId,
            ]);
        } catch (\Exception $e) {
            // Don't fail the main operation if audit logging fails
            Log::error('Failed to create audit log', [
                'action' => $action,
                'user_id' => $userOrId instanceof AzureUser ? $userOrId->Id : $userOrId,
                'error' => $e->getMessage(),
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
                'message' => 'Module functionality is currently disabled',
            ], 503);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Module not found',
            ], 404);
        }
    }

    /**
     * Disable a user (Azure AD first, then database - all-or-nothing)
     */
    public function disable(AzureUser $user)
    {
        Log::info('DEBUG Azure ID check', [
            'user_id' => $user->Id,
            'azure_id_model' => $user->AzureId,
            'azure_id_type' => gettype($user->AzureId),
            'azure_id_empty' => empty($user->AzureId),
            'azure_id_null' => is_null($user->AzureId),
            'azure_id_string' => is_string($user->AzureId),
            'azure_id_length' => strlen($user->AzureId ?? ''),
            'attributes_array' => $user->getAttributes(),
        ]);

        try {
            Log::info('=== DISABLE USER REQUEST ===', [
                'user_id' => $user->Id,
                'email' => $user->userPrincipalName,
                'current_status' => $user->Status,
                'azure_id' => $user->AzureId,
                'initiated_by' => Auth::id(),
            ]);

            // Check if user is already inactive
            if ($user->Status === 'Inactive') {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already disabled',
                ], 400);
            }

            // **FIX 1: Double-check Azure ID from database directly**
            $azureId = null;

            $dbUser = DB::connection('sqlsrv')
                ->table('Azure_Users')
                ->select('AzureId')
                ->where('Id', $user->Id)
                ->first();

            if ($dbUser && !empty($dbUser->AzureId)) {
                $azureId = trim($dbUser->AzureId);
                Log::info('Azure ID retrieved from database', [
                    'azure_id' => $azureId,
                    'from_model' => $user->AzureId,
                    'match' => $azureId === trim($user->AzureId ?? ''),
                ]);
            } else {
                Log::warning('No Azure ID found in database for user', [
                    'user_id' => $user->Id,
                    'email' => $user->userPrincipalName,
                ]);
            }

            // STEP 1: Attempt to disable in Azure AD
            $azureDisabled = false;
            $azureNotFound = false;
            $azureIdUpdated = false;

            if (!empty($azureId) && $this->isValidUuid($azureId)) {
                Log::info('Step 1: Attempting to disable user in Azure AD...', [
                    'user_id' => $user->Id,
                    'azure_id' => $azureId,
                    'email' => $user->userPrincipalName,
                ]);

                try {
                    $azureService = app(\App\Services\AzureGraphService::class);
                    set_time_limit(120);

                    try {
                        $azureService->disableUser($azureId);
                        $azureDisabled = true;

                        Log::info('✓ Step 1: User disabled in Azure AD successfully', [
                            'user_id' => $user->Id,
                            'azure_id' => $azureId,
                        ]);
                    } catch (\Exception $directError) {
                        if (
                            str_contains($directError->getMessage(), '404') ||
                            str_contains($directError->getMessage(), 'ResourceNotFound') ||
                            str_contains($directError->getMessage(), 'Request_ResourceNotFound')
                        ) {
                            Log::warning('Azure ID not found, attempting lookup by email', [
                                'user_id' => $user->Id,
                                'stale_azure_id' => $azureId,
                                'email' => $user->userPrincipalName,
                            ]);

                            $maxRetries = 1;
                            $retryCount = 0;
                            $azureUserData = null;

                            // Try to find user by email (single attempt)
                            try {
                                $azureUserData = $azureService->findUserByUPN($user->userPrincipalName);
                            } catch (\Exception $lookupError) {
                                $azureUserData = null;
                            }

                            if ($azureUserData && isset($azureUserData['id'])) {
                                $correctAzureId = trim($azureUserData['id']);

                                Log::warning('Azure ID mismatch detected - updating local record', [
                                    'user_id' => $user->Id,
                                    'old_azure_id' => $azureId,
                                    'correct_azure_id' => $correctAzureId,
                                    'email' => $user->userPrincipalName,
                                ]);

                                DB::connection('sqlsrv')
                                    ->table('Azure_Users')
                                    ->where('Id', $user->Id)
                                    ->update(['AzureId' => $correctAzureId]);

                                $azureId = $correctAzureId;
                                $azureIdUpdated = true;

                                $azureService->disableUser($correctAzureId);
                                $azureDisabled = true;

                                Log::info('✓ Step 1: User disabled in Azure AD after ID correction', [
                                    'user_id' => $user->Id,
                                    'corrected_azure_id' => $correctAzureId,
                                ]);
                            } else {
                                $azureNotFound = true;

                                Log::warning('✓ Step 1: User not found in Azure AD (already deleted or never existed)', [
                                    'user_id' => $user->Id,
                                    'azure_id' => $azureId,
                                    'email' => $user->userPrincipalName,
                                    'treating_as' => 'already_disabled',
                                ]);
                            }
                        } else {
                            throw $directError;
                        }
                    }
                } catch (\Exception $azureException) {
                    if (
                        str_contains($azureException->getMessage(), 'Maximum execution time') ||
                        str_contains($azureException->getMessage(), 'timeout')
                    ) {
                        Log::warning('Azure operation timed out - proceeding with local disable only', [
                            'user_id' => $user->Id,
                            'error' => $azureException->getMessage(),
                        ]);
                    } else {
                        Log::error('✗ Step 1 FAILED: Could not disable user in Azure AD', [
                            'user_id' => $user->Id,
                            'azure_id' => $azureId,
                            'error' => $azureException->getMessage(),
                        ]);

                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to disable user in Azure AD: ' . $azureException->getMessage(),
                            'error' => $azureException->getMessage(),
                        ], 500);
                    }
                }
            } else {
                Log::warning('User has no valid Azure ID - proceeding with local disable only', [
                    'user_id' => $user->Id,
                    'email' => $user->userPrincipalName,
                    'azure_id_in_db' => $azureId,
                    'is_valid_uuid' => $azureId ? $this->isValidUuid($azureId) : false,
                ]);
            }

            // STEP 2: Update database
            Log::info('Step 2: Updating database...', [
                'user_id' => $user->Id,
                'azure_disabled' => $azureDisabled,
                'azure_not_found' => $azureNotFound,
                'azure_id_updated' => $azureIdUpdated,
            ]);

            try {
                $updateData = [
                    'Status' => 'Inactive',
                    'accountEnabled' => 0,
                ];

                if ($azureIdUpdated && !empty($azureId)) {
                    $updateData['AzureId'] = $azureId;
                }

                $dbSuccess = DB::connection('sqlsrv')
                    ->table('Azure_Users')
                    ->where('Id', $user->Id)
                    ->update($updateData);

                if ($dbSuccess === false) {
                    throw new \Exception('Database update failed');
                }

                Log::info('✓ Step 2: Database updated successfully', [
                    'user_id' => $user->Id,
                    'rows_affected' => $dbSuccess,
                ]);
            } catch (\Exception $dbError) {
                Log::error('✗ Step 2 FAILED: Database update failed', [
                    'user_id' => $user->Id,
                    'error' => $dbError->getMessage(),
                ]);

                if ($azureDisabled && !$azureNotFound && !empty($azureId)) {
                    try {
                        $azureService = app(\App\Services\AzureGraphService::class);
                        $azureService->enableUser($azureId);

                        Log::warning('⚠ ROLLBACK SUCCESSFUL: User re-enabled in Azure AD', [
                            'user_id' => $user->Id,
                            'azure_id' => $azureId,
                        ]);

                        return response()->json([
                            'success' => false,
                            'message' => 'Database update failed. User remains enabled (rollback successful).',
                            'error' => 'Database error: ' . $dbError->getMessage(),
                        ], 500);
                    } catch (\Exception $rollbackError) {
                        Log::critical('✗ ROLLBACK FAILED: User disabled in Azure but database not updated!', [
                            'user_id' => $user->Id,
                            'azure_id' => $azureId,
                            'db_error' => $dbError->getMessage(),
                            'rollback_error' => $rollbackError->getMessage(),
                            'action_required' => 'MANUAL INTERVENTION NEEDED',
                        ]);

                        return response()->json([
                            'success' => false,
                            'message' => 'CRITICAL ERROR: User disabled in Azure AD but database update failed. Manual intervention required.',
                            'error' => [
                                'database' => $dbError->getMessage(),
                                'rollback' => $rollbackError->getMessage(),
                            ],
                            'user_id' => $user->Id,
                            'azure_id' => $azureId,
                        ], 500);
                    }
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update database: ' . $dbError->getMessage(),
                ], 500);
            }

            // Log activity
            try {
                $this->logUserActivity(
                    $user->Id,
                    'user.disabled',
                    'User account disabled',
                    [
                        'disabled_by' => Auth::user()->name ?? Auth::id() ?? 'system',
                        'azure_disabled' => $azureDisabled,
                        'azure_not_found' => $azureNotFound,
                        'azure_id_updated' => $azureIdUpdated,
                        'final_azure_id' => $azureId,
                    ]
                );
            } catch (\Exception $e) {
                Log::warning('Failed to log user activity', [
                    'user_id' => $user->Id,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('=== USER DISABLED SUCCESSFULLY ===', [
                'user_id' => $user->Id,
                'azure_id' => $azureId,
                'disabled_by' => Auth::user()->name ?? Auth::id() ?? 'system',
                'azure_action' => $azureDisabled ? 'disabled' : ($azureNotFound ? 'not_found' : 'skipped'),
                'azure_id_corrected' => $azureIdUpdated,
            ]);

            // Build response message
            $message = '✅ User has been disabled successfully!';
            if ($azureIdUpdated) {
                $message .= ' Azure ID was corrected during the process.';
            }
            if ($azureNotFound) {
                $message .= ' Note: User was not found in Azure AD (may have been previously deleted).';
            }
            if (!$azureDisabled && !$azureNotFound && !empty($azureId)) {
                $message .= ' Note: Could not disable in Azure AD (timeout or error), but local status updated.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'details' => [
                    'azure_disabled' => $azureDisabled,
                    'azure_not_found' => $azureNotFound,
                    'azure_id_updated' => $azureIdUpdated,
                    'local_status_updated' => true,
                ],
                'reload' => true  // ← ADDED: Trigger page reload
            ]);
        } catch (\Exception $e) {
            Log::error('Unexpected error disabling user', [
                'user_id' => $user->Id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '❌ An unexpected error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate UUID format
     */
    private function isValidUuid($uuid): bool
    {
        if (empty($uuid) || !is_string($uuid)) {
            return false;
        }

        $uuid = trim($uuid);

        // Check for GUID format (with or without braces)
        $pattern = '/^\{?[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\}?$/i';

        return preg_match($pattern, $uuid) === 1;
    }

    /**
     * Normalize and validate Azure ID (GUID)
     * Handles UUIDs with or without braces
     * 
     * @param string|null $azureId
     * @return string|null Returns normalized UUID or null if invalid
     */
    private function normalizeAzureId(?string $azureId): ?string
    {
        if (empty($azureId) || !is_string($azureId)) {
            Log::debug('Azure ID is empty or not a string', [
                'value' => $azureId,
                'type' => gettype($azureId)
            ]);
            return null;
        }

        // Trim whitespace
        $azureId = trim($azureId);

        // Remove braces if present
        $azureId = trim($azureId, '{}');

        // Validate format
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

        if (preg_match($pattern, $azureId) === 1) {
            return strtolower($azureId); // ← Normalize to lowercase
        }

        Log::warning('Invalid Azure ID format', [
            'original' => $azureId,
            'length' => strlen($azureId),
            'pattern_matched' => false
        ]);

        return null;
    }

    /**
     * Enable a disabled user (Azure AD first, then database - all-or-nothing)
     */
    public function enable(AzureUser $user)
    {
        try {
            Log::info('=== ENABLE USER REQUEST ===', [
                'user_id' => $user->Id,
                'email' => $user->userPrincipalName,
                'current_status' => $user->Status,
                'azure_id' => $user->AzureId,
                'initiated_by' => Auth::id(),
            ]);

            // Check if user is already active
            if ($user->Status === 'Active') {
                Log::info('User already active', [
                    'user_id' => $user->Id,
                    'status' => $user->Status,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'User is already enabled',
                ], 400);
            }

            // Double-check Azure ID from database
            $azureId = null;
            $dbUser = DB::connection('sqlsrv')
                ->table('Azure_Users')
                ->select('AzureId')
                ->where('Id', $user->Id)
                ->first();

            if ($dbUser && !empty($dbUser->AzureId)) {
                $azureId = trim($dbUser->AzureId);
                Log::info('Azure ID retrieved from database', [
                    'azure_id' => $azureId,
                    'from_model' => $user->AzureId,
                    'match' => $azureId === trim($user->AzureId ?? ''),
                ]);
            }

            // Check if user has valid Azure ID
            if (empty($azureId) || !$this->isValidUuid($azureId)) {
                Log::warning('Cannot enable user - no valid Azure ID', [
                    'user_id' => $user->Id,
                    'email' => $user->userPrincipalName,
                    'azure_id_in_db' => $azureId,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Cannot enable user: User has not been provisioned to Azure AD or Azure ID is invalid',
                ], 400);
            }

            // STEP 1: Attempt to enable in Azure AD
            $azureEnabled = false;
            $azureNotFound = false;
            $azureIdUpdated = false;

            Log::info('Step 1: Attempting to enable user in Azure AD...', [
                'user_id' => $user->Id,
                'azure_id' => $azureId,
                'email' => $user->userPrincipalName,
            ]);

            try {
                $azureService = app(\App\Services\AzureGraphService::class);
                set_time_limit(120);

                try {
                    $azureService->enableUser($azureId);
                    $azureEnabled = true;

                    Log::info('✓ Step 1: User enabled in Azure AD successfully', [
                        'user_id' => $user->Id,
                        'azure_id' => $azureId,
                    ]);
                } catch (\Exception $directError) {
                    if (
                        str_contains($directError->getMessage(), '404') ||
                        str_contains($directError->getMessage(), 'ResourceNotFound') ||
                        str_contains($directError->getMessage(), 'Request_ResourceNotFound')
                    ) {
                        Log::warning('Azure ID not found, attempting lookup by email', [
                            'user_id' => $user->Id,
                            'stale_azure_id' => $azureId,
                            'email' => $user->userPrincipalName,
                        ]);

                        $maxRetries = 2;
                        $retryCount = 0;
                        $azureUserData = null;

                        while ($retryCount < $maxRetries && !$azureUserData) {
                            try {
                                $azureUserData = $azureService->findUserByUPN($user->userPrincipalName);
                                break;
                            } catch (\Exception $lookupError) {
                                $retryCount++;
                                if ($retryCount >= $maxRetries) {
                                    throw $lookupError;
                                }
                                sleep(2);
                            }
                        }

                        if ($azureUserData && isset($azureUserData['id'])) {
                            $correctAzureId = trim($azureUserData['id']);

                            Log::warning('Azure ID mismatch detected - updating local record', [
                                'user_id' => $user->Id,
                                'old_azure_id' => $azureId,
                                'correct_azure_id' => $correctAzureId,
                                'email' => $user->userPrincipalName,
                            ]);

                            DB::connection('sqlsrv')
                                ->table('Azure_Users')
                                ->where('Id', $user->Id)
                                ->update(['AzureId' => $correctAzureId]);

                            $azureId = $correctAzureId;
                            $azureIdUpdated = true;

                            $azureService->enableUser($correctAzureId);
                            $azureEnabled = true;

                            Log::info('✓ Step 1: User enabled in Azure AD after ID correction', [
                                'user_id' => $user->Id,
                                'corrected_azure_id' => $correctAzureId,
                            ]);
                        } else {
                            $azureNotFound = true;

                            Log::error('✗ Step 1: User not found in Azure AD', [
                                'user_id' => $user->Id,
                                'azure_id' => $azureId,
                                'email' => $user->userPrincipalName,
                            ]);

                            return response()->json([
                                'success' => false,
                                'message' => 'Cannot enable user: User not found in Azure AD. Please contact IT support.',
                            ], 404);
                        }
                    } else {
                        throw $directError;
                    }
                }
            } catch (\Exception $azureException) {
                if (
                    str_contains($azureException->getMessage(), 'Maximum execution time') ||
                    str_contains($azureException->getMessage(), 'timeout')
                ) {
                    Log::warning('Azure operation timed out - proceeding with local enable only', [
                        'user_id' => $user->Id,
                        'error' => $azureException->getMessage(),
                    ]);
                } else {
                    Log::error('✗ Step 1 FAILED: Could not enable user in Azure AD', [
                        'user_id' => $user->Id,
                        'azure_id' => $azureId,
                        'error' => $azureException->getMessage(),
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to enable user in Azure AD: ' . $azureException->getMessage(),
                        'error' => $azureException->getMessage(),
                    ], 500);
                }
            }

            // STEP 2: Update database
            Log::info('Step 2: Updating database...', [
                'user_id' => $user->Id,
                'azure_enabled' => $azureEnabled,
                'azure_not_found' => $azureNotFound,
                'azure_id_updated' => $azureIdUpdated,
            ]);

            try {
                $updateData = [
                    'Status' => 'Active',
                    'accountEnabled' => 1,
                ];

                if ($azureIdUpdated && !empty($azureId)) {
                    $updateData['AzureId'] = $azureId;
                }

                $dbSuccess = DB::connection('sqlsrv')
                    ->table('Azure_Users')
                    ->where('Id', $user->Id)
                    ->update($updateData);

                if ($dbSuccess === false) {
                    throw new \Exception('Database update failed');
                }

                Log::info('✓ Step 2: Database updated successfully', [
                    'user_id' => $user->Id,
                    'rows_affected' => $dbSuccess,
                ]);
            } catch (\Exception $dbError) {
                Log::error('✗ Step 2 FAILED: Database update failed', [
                    'user_id' => $user->Id,
                    'error' => $dbError->getMessage(),
                ]);

                // ROLLBACK: Only if we actually enabled in Azure
                if ($azureEnabled && !$azureNotFound && !empty($azureId)) {
                    try {
                        $azureService = app(\App\Services\AzureGraphService::class);
                        $azureService->disableUser($azureId);

                        Log::warning('⚠ ROLLBACK SUCCESSFUL: User re-disabled in Azure AD', [
                            'user_id' => $user->Id,
                            'azure_id' => $azureId,
                        ]);

                        return response()->json([
                            'success' => false,
                            'message' => 'Database update failed. User remains disabled (rollback successful).',
                            'error' => 'Database error: ' . $dbError->getMessage(),
                        ], 500);
                    } catch (\Exception $rollbackError) {
                        Log::critical('✗ ROLLBACK FAILED: User enabled in Azure but database not updated!', [
                            'user_id' => $user->Id,
                            'azure_id' => $azureId,
                            'db_error' => $dbError->getMessage(),
                            'rollback_error' => $rollbackError->getMessage(),
                            'action_required' => 'MANUAL INTERVENTION NEEDED',
                        ]);

                        return response()->json([
                            'success' => false,
                            'message' => 'CRITICAL ERROR: User enabled in Azure AD but database update failed. Manual intervention required.',
                            'error' => [
                                'database' => $dbError->getMessage(),
                                'rollback' => $rollbackError->getMessage(),
                            ],
                            'user_id' => $user->Id,
                            'azure_id' => $azureId,
                        ], 500);
                    }
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update database: ' . $dbError->getMessage(),
                ], 500);
            }

            // Log activity
            try {
                $this->logUserActivity(
                    $user->Id,
                    'user.enabled',
                    'User account enabled',
                    [
                        'enabled_by' => Auth::user()->name ?? Auth::id() ?? 'system',
                        'azure_enabled' => $azureEnabled,
                        'azure_not_found' => $azureNotFound,
                        'azure_id_updated' => $azureIdUpdated,
                        'final_azure_id' => $azureId,
                    ]
                );
            } catch (\Exception $e) {
                Log::warning('Failed to log user activity', [
                    'user_id' => $user->Id,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('=== USER ENABLED SUCCESSFULLY ===', [
                'user_id' => $user->Id,
                'azure_id' => $azureId,
                'enabled_by' => Auth::user()->name ?? Auth::id() ?? 'system',
                'azure_action' => $azureEnabled ? 'enabled' : ($azureNotFound ? 'not_found' : 'skipped'),
                'azure_id_corrected' => $azureIdUpdated,
            ]);

            // Build response message
            $message = '✅ User has been enabled successfully!';
            if ($azureIdUpdated) {
                $message .= ' Azure ID was corrected during the process.';
            }
            if (!$azureEnabled && !empty($azureId)) {
                $message .= ' Note: Could not enable in Azure AD (timeout or error), but local status updated.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'details' => [
                    'azure_enabled' => $azureEnabled,
                    'azure_not_found' => $azureNotFound,
                    'azure_id_updated' => $azureIdUpdated,
                    'local_status_updated' => true,
                ],
                'reload' => true  // ← ADDED: Trigger page reload
            ]);
        } catch (\Exception $e) {
            Log::error('Unexpected error enabling user', [
                'user_id' => $user->Id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '❌ An unexpected error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Diagnostic endpoint to check Azure ID issues
     */
    public function diagnoseAzureId(AzureUser $user)
    {
        try {
            // Get data from multiple sources
            $modelAzureId = $user->AzureId;

            $dbUser = DB::connection('sqlsrv')
                ->table('Azure_Users')
                ->select('AzureId', 'userPrincipalName', 'displayName')
                ->where('Id', $user->Id)
                ->first();

            $dbAzureId = $dbUser->AzureId ?? null;

            // Check Azure AD
            $azureService = app(\App\Services\AzureGraphService::class);
            $azureUser = null;

            try {
                if (!empty($dbAzureId) && $this->isValidUuid($dbAzureId)) {
                    $azureUser = $azureService->getUser($dbAzureId);
                }
            } catch (\Exception $e) {
                // Try by email
                try {
                    $azureUser = $azureService->findUserByUPN($user->userPrincipalName);
                } catch (\Exception $e2) {
                    // Both failed
                }
            }

            $diagnosis = [
                'user_id' => $user->Id,
                'email' => $user->userPrincipalName,
                'model_azure_id' => $modelAzureId,
                'model_azure_id_valid' => $this->isValidUuid($modelAzureId),
                'db_azure_id' => $dbAzureId,
                'db_azure_id_valid' => $this->isValidUuid($dbAzureId),
                'azure_ad_found' => !empty($azureUser),
                'azure_ad_id' => $azureUser['id'] ?? null,
                'match' => ($dbAzureId && $azureUser) ? trim($dbAzureId) === trim($azureUser['id'] ?? '') : false,
                'current_status' => $user->Status,
            ];

            return response()->json([
                'success' => true,
                'diagnosis' => $diagnosis,
                'recommendation' => $this->getAzureIdRecommendation($diagnosis),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Diagnosis failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getAzureIdRecommendation(array $diagnosis): string
    {
        if (!$diagnosis['db_azure_id_valid']) {
            return 'Azure ID in database is invalid. Check data integrity.';
        }

        if (!$diagnosis['azure_ad_found']) {
            return 'User not found in Azure AD. May have been deleted or never created.';
        }

        if (!$diagnosis['match']) {
            return 'Azure ID mismatch. Database: ' . $diagnosis['db_azure_id'] .
                ', Azure AD: ' . $diagnosis['azure_ad_id'];
        }

        return 'Azure ID is valid and matches.';
    }

    /**
     * Repair Azure ID for a user
     */
    public function repairAzureId(AzureUser $user)
    {
        try {
            $azureService = app(\App\Services\AzureGraphService::class);

            // Try to find user in Azure AD
            $azureUser = $azureService->findUserByUPN($user->userPrincipalName);

            if (!$azureUser || !isset($azureUser['id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found in Azure AD',
                ], 404);
            }

            $correctAzureId = trim($azureUser['id']);

            // Update database
            DB::connection('sqlsrv')
                ->table('Azure_Users')
                ->where('Id', $user->Id)
                ->update([
                    'AzureId' => $correctAzureId,
                    //'LastModifiedOn' => DB::raw('GETDATE()'),
                    //'LastModifiedBy' => Auth::id(),
                ]);

            Log::info('Azure ID repaired', [
                'user_id' => $user->Id,
                'old_azure_id' => $user->AzureId,
                'new_azure_id' => $correctAzureId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Azure ID repaired successfully',
                'old_azure_id' => $user->AzureId,
                'new_azure_id' => $correctAzureId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Repair failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset user password (placeholder implementation)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(AzureUser $user)
    {
        try {
            Log::info('=== RESET PASSWORD REQUEST ===', [
                'user_id' => $user->Id,
                'email' => $user->userPrincipalName,
                'initiated_by' => Auth::id(),
            ]);

            // Check if user is provisioned to Azure
            if (!$user->isProvisionedToAzure()) {
                Log::warning('Cannot reset password - user not provisioned to Azure', [
                    'user_id' => $user->Id,
                    'email' => $user->userPrincipalName,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reset password: User is not provisioned to Azure AD yet.',
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
                        'user_email' => $user->userPrincipalName,
                    ]
                );
            } catch (\Exception $e) {
                Log::warning('Failed to log password reset activity', [
                    'user_id' => $user->Id,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('=== PASSWORD RESET REQUEST LOGGED ===', [
                'user_id' => $user->Id,
                'requested_by' => Auth::id(),
            ]);

            // TODO: Implement actual password reset logic via Azure Graph API
            // For now, return a placeholder message
            return response()->json([
                'success' => true,
                'message' => 'Password reset request logged. Please contact IT support to complete the reset process.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process password reset request', [
                'user_id' => $user->Id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process password reset: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user status (API endpoint)
     */
    public function getStatus(AzureUser $user)
    {
        return response()->json([
            'id'     => $user->Id,
            'status' => $user->Status,
            'email'  => $user->email,  // accessor in your model
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
                    'message' => 'User deleted successfully',
                ]);
            }

            return redirect()->route('azure.dashboard.index')
                ->with('success', 'User deleted successfully!');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete user: ' . $e->getMessage(),
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
                    'results' => $results,
                ]);
            }

            return redirect()->back()
                ->with('success', $message);
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retry module assignments: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Failed to retry: ' . $e->getMessage()]);
        }
    }

    /**
     * Get functions for SCM modules (OPS/OPSUAT) using sp_Azure_UserResourcefunction
     * API endpoint: GET /api/azure/modules/scm-functions
     */
    public function getSCMFunctions()
    {
        try {
            $functions = DB::connection('sqlsrv')
                ->select('EXEC sp_Azure_UserResourcefunction');

            return response()->json([
                'success' => true,
                'data' => $functions,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch SCM functions', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch functions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get roles based on Application Code using sp_Azure_UserResourceRole
     * API endpoint: GET /api/azure/modules/roles?application_code=XXX&filter=YYY
     */
    public function getModuleRolesByCode(Request $request)
    {
        $debugMode = config('azure_modules.debug', false);

        try {
            $applicationCode = $request->query('application_code');
            $filter = $request->query('filter', null);

            if (! $applicationCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Application code is required',
                ], 400);
            }

            // ✅ ADD CACHING - cache for 1 hour
            // $cacheKey = "roles_{$applicationCode}_" . md5($filter ?? 'all');

            // $roles = Cache::remember($cacheKey, 3600, function () use ($applicationCode, $filter) {
            $mapping = config('azure_modules.module_mapping', []);
            $finalCode = $mapping[$applicationCode] ?? $applicationCode;

            // Call stored procedure
            if ($filter) {
                $roles = DB::connection('sqlsrv')
                    ->select(
                        'EXEC sp_Azure_UserResourceRole @ApplicationCode = ?, @Filter = ?',
                        [$finalCode, $filter]
                    );
            } else {
                $roles = DB::connection('sqlsrv')
                    ->select(
                        'EXEC sp_Azure_UserResourceRole @ApplicationCode = ?',
                        [$finalCode]
                    );
            }

            // Only map if RoleId is numeric (not already GUID)
            $needsMapping = collect($roles)->contains(function ($role) {
                return is_numeric($role->RoleId);
            });

            if ($needsMapping) {
                $roleIds = collect($roles)->pluck('RoleId')->filter()->toArray();

                $roleMapping = DB::connection('sqlsrv')
                    ->table('Azure_Users_Role')
                    ->select('Id', 'RoleId', 'RoleCode', 'Role')
                    ->where('ApplicationCode', $finalCode)
                    ->whereIn('RoleId', $roleIds)
                    ->where('IsActive', 1)
                    ->where('IsDeleted', 0)
                    ->get()
                    ->keyBy('RoleId');

                foreach ($roles as &$role) {
                    $oldRoleId = $role->RoleId;
                    if (isset($roleMapping[$oldRoleId])) {
                        $role->RoleId = $roleMapping[$oldRoleId]->Id;
                    }
                }
            }

            // });

            Log::info('✅ [AZURE ROLES] Roles fetched', [
                'application_code' => $applicationCode,
                'role_count' => count($roles),
                'cached' => true,
            ]);

            return response()->json([
                'success' => true,
                'data' => $roles,
                'meta' => [
                    'application_code_requested' => $applicationCode,
                    'role_count' => count($roles),
                    'filter' => $filter,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [AZURE ROLES] Failed to fetch module roles', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch roles: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Manual activation of pending users with D365 provisioning (called from web UI)
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activatePendingUsersManual()
    {
        try {
            Log::info('=== MANUAL USER ACTIVATION STARTED ===', [
                'initiated_by' => Auth::id(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            $stats = [
                'total_pending' => 0,
                'activated' => 0,
                'skipped' => 0,
                'd365_provisioned' => 0,
                'd365_skipped' => 0,
                'd365_failed' => 0,
                'errors' => [],
            ];

            // Fetch all pending users
            $pendingUsers = DB::connection('sqlsrv')
                ->table('Azure_Users')
                ->where('Status', 'Pending')
                ->get();

            $stats['total_pending'] = $pendingUsers->count();

            if ($stats['total_pending'] === 0) {
                return redirect()->back()
                    ->with('info', 'No pending users found.');
            }

            // Process each pending user
            foreach ($pendingUsers as $user) {
                try {
                    // Check eligibility for activation
                    $eligible = $this->checkUserEligibility($user);

                    if ($eligible['can_activate']) {

                        // ====================================================================
                        // D365 PROVISIONING (Before Activation)
                        // ====================================================================

                        $needsD365 = ! empty($user->AzureId) && empty($user->D365UserID);

                        if ($needsD365) {
                            // Check if user has D365 LIVE module assignments
                            $hasD365Module = DB::connection('sqlsrv')
                                ->table('Azure_Application_UserMapping')
                                ->where('AzureUserID', $user->AzureId)
                                ->where('ApplicationName', 'D365 LIVE')
                                ->where('IsActive', 1)
                                ->where('IsDelete', 0)
                                ->exists();

                            if ($hasD365Module) {
                                Log::info('→ Attempting D365 provisioning for user', [
                                    'user_id' => $user->Id,
                                    'upn' => $user->userPrincipalName,
                                    'azure_id' => $user->AzureId,
                                ]);

                                try {
                                    // Call the D365 stored procedure
                                    $d365Result = DB::connection('sqlsrv')->select('
                                    SET NOCOUNT ON;

                                    DECLARE @Status VARCHAR(15), @Remark VARCHAR(200);

                                    EXEC sp_Azure_D365UserCreation
                                        @AzureUserID = ?,
                                        @Status = @Status OUTPUT,
                                        @Remark = @Remark OUTPUT;

                                    SELECT @Status as Status, @Remark as Remark;
                                ', [$user->AzureId]);

                                    $d365Status = $d365Result[0]->Status ?? 'Failed';
                                    $d365Remark = $d365Result[0]->Remark ?? 'Unknown error';

                                    if ($d365Status === 'Success') {
                                        $stats['d365_provisioned']++;

                                        Log::info('✓ D365 provisioned successfully', [
                                            'user_id' => $user->Id,
                                            'upn' => $user->userPrincipalName,
                                            'remark' => $d365Remark,
                                        ]);
                                    } else {
                                        $stats['d365_failed']++;

                                        Log::warning('⚠ D365 provisioning failed (non-critical)', [
                                            'user_id' => $user->Id,
                                            'upn' => $user->userPrincipalName,
                                            'status' => $d365Status,
                                            'remark' => $d365Remark,
                                            'note' => 'User will still be activated, D365 can be retried later',
                                        ]);
                                    }
                                } catch (\Exception $d365Error) {
                                    $stats['d365_failed']++;

                                    Log::error('✗ D365 provisioning error (non-critical)', [
                                        'user_id' => $user->Id,
                                        'upn' => $user->userPrincipalName,
                                        'error' => $d365Error->getMessage(),
                                        'note' => 'User will still be activated, D365 can be retried later',
                                    ]);
                                }
                            } else {
                                $stats['d365_skipped']++;

                                Log::debug('⊘ D365 provisioning skipped - no D365 LIVE modules', [
                                    'user_id' => $user->Id,
                                    'upn' => $user->userPrincipalName,
                                ]);
                            }
                        } else {
                            if (!$user->isProvisionedToAzure()) {
                                Log::debug('⊘ D365 provisioning skipped - no Azure ID', [
                                    'user_id' => $user->Id,
                                    'upn' => $user->userPrincipalName,
                                ]);
                            } else {
                                Log::debug('⊘ D365 provisioning skipped - already provisioned', [
                                    'user_id' => $user->Id,
                                    'upn' => $user->userPrincipalName,
                                    'd365_user_id' => $user->D365UserID,
                                ]);
                            }
                            $stats['d365_skipped']++;
                        }

                        // ====================================================================
                        // ACTIVATE USER
                        // ====================================================================

                        DB::connection('sqlsrv')
                            ->table('Azure_Users')
                            ->where('Id', $user->Id)
                            ->update([
                                'Status' => 'Active',
                                //'LastModifiedOn' => DB::raw('GETDATE()'),
                                //'LastModifiedBy' => Auth::id(),
                            ]);

                        $stats['activated']++;

                        Log::info('✓ User activated', [
                            'user_id' => $user->Id,
                            'upn' => $user->userPrincipalName,
                            'reason' => $eligible['reason'],
                        ]);
                    } else {
                        $stats['skipped']++;

                        Log::debug('User skipped - not eligible', [
                            'user_id' => $user->Id,
                            'upn' => $user->userPrincipalName,
                            'reason' => $eligible['reason'],
                        ]);
                    }
                } catch (\Exception $e) {
                    $stats['errors'][] = [
                        'user' => $user->userPrincipalName ?? $user->Id,
                        'error' => $e->getMessage(),
                    ];

                    Log::error('Failed to process user', [
                        'user_id' => $user->Id,
                        'upn' => $user->userPrincipalName ?? 'N/A',
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('=== MANUAL USER ACTIVATION COMPLETED ===', $stats);

            // Build success message
            $messageParts = [
                sprintf('Processed: %d', $stats['total_pending']),
                sprintf('Activated: %d', $stats['activated']),
            ];

            if ($stats['d365_provisioned'] > 0) {
                $messageParts[] = sprintf('D365 Provisioned: %d', $stats['d365_provisioned']);
            }

            if ($stats['skipped'] > 0) {
                $messageParts[] = sprintf('Skipped: %d', $stats['skipped']);
            }

            if ($stats['d365_failed'] > 0) {
                $messageParts[] = sprintf('D365 Failed: %d (can retry manually)', $stats['d365_failed']);
            }

            if (! empty($stats['errors'])) {
                $messageParts[] = sprintf('Errors: %d', count($stats['errors']));
            }

            $message = '✓ Sync completed! ' . implode(' | ', $messageParts);

            // Add warning if there were D365 failures
            if ($stats['d365_failed'] > 0) {
                $message .= '<br><em>Note: Some D365 provisioning failed. Check user details pages for retry options.</em>';
            }

            return redirect()->back()
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('=== MANUAL USER ACTIVATION FAILED ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Activation failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Full sync: Odoo sync + User activation (called from web UI)
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function fullSyncManual()
    {
        try {
            Log::info('=== FULL MANUAL SYNC STARTED ===', [
                'initiated_by' => Auth::id(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            // Step 1: Sync Odoo users
            $odooController = new \App\Http\Controllers\OdooSyncController;
            // Note: syncUsers() returns HTML output, we'll capture it
            ob_start();
            $odooController->syncUsers();
            $odooOutput = ob_get_clean();

            Log::info('Odoo sync completed in full sync');

            // Step 2: Activate pending users
            return $this->activatePendingUsersManual();
        } catch (\Exception $e) {
            Log::error('=== FULL MANUAL SYNC FAILED ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Full sync failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Check if a user is eligible for activation
     *
     * @param  object  $user
     * @return array ['can_activate' => bool, 'reason' => string]
     */
    private function checkUserEligibility($user): array
    {
        // Check 1: Azure AD provisioning
        if (!$user->isProvisionedToAzure()) {
            return [
                'can_activate' => false,
                'reason' => 'Not provisioned to Azure AD',
            ];
        }

        // Check 2: External system synchronization
        $syncedSystems = [];
        if (! empty($user->OdooUserId)) {
            $syncedSystems[] = 'Odoo';
        }
        if (! empty($user->SCMUserID)) {
            $syncedSystems[] = 'SCM';
        }
        if (! empty($user->BizUserID)) {
            $syncedSystems[] = 'BIZ';
        }

        if (empty($syncedSystems)) {
            return [
                'can_activate' => false,
                'reason' => 'No external system IDs populated',
            ];
        }

        // Check 3: Module assignments
        $moduleCount = DB::connection('sqlsrv')
            ->table('Azure_Application_UserMapping')
            ->where('user_id', $user->userPrincipalName)
            ->where('IsActive', 1)
            ->where('IsDelete', 0)
            ->count();

        if ($moduleCount === 0) {
            return [
                'can_activate' => false,
                'reason' => 'No module assignments',
            ];
        }

        // All checks passed
        return [
            'can_activate' => true,
            'reason' => sprintf(
                'Synced with %s (%d modules)',
                implode(', ', $syncedSystems),
                $moduleCount
            ),
        ];
    }

    /**
     * Validate GUID format
     *
     * @param  string  $guid
     */
    private function isValidGuid($guid): bool
    {
        if (empty($guid) || ! is_string($guid)) {
            return false;
        }

        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $guid) === 1;
    }

    /**
     * Validate and sanitize module assignment data
     *
     * @return array ['valid' => bool, 'data' => array|null, 'error' => string|null]
     */
    private function validateAssignment(array $assignment, int $index): array
    {
        $errors = [];

        // Required fields validation
        $requiredFields = ['module_id', 'application_id', 'location_id', 'location_code', 'role_id', 'role_code'];
        foreach ($requiredFields as $field) {
            if (empty($assignment[$field])) {
                $errors[] = "Missing required field: {$field}";
            }
        }

        if (! empty($errors)) {
            return [
                'valid' => false,
                'data' => null,
                'error' => "Assignment {$index}: " . implode(', ', $errors),
            ];
        }

        // GUID format validation
        $guidFields = ['module_id', 'application_id', 'location_id', 'role_id'];
        foreach ($guidFields as $field) {
            if (! $this->isValidGuid($assignment[$field])) {
                return [
                    'valid' => false,
                    'data' => null,
                    'error' => "Assignment {$index}: Invalid GUID format for {$field}",
                ];
            }
        }

        // Validate function_id if present (for SCM modules)
        if (! empty($assignment['function_id']) && ! $this->isValidGuid($assignment['function_id'])) {
            return [
                'valid' => false,
                'data' => null,
                'error' => "Assignment {$index}: Invalid GUID format for function_id",
            ];
        }

        // Sanitize data - convert empty strings to NULL for optional fields
        $sanitized = $assignment;
        $sanitized['function_id'] = ! empty($assignment['function_id']) ? $assignment['function_id'] : null;
        $sanitized['function_code'] = ! empty($assignment['function_code']) ? $assignment['function_code'] : null;

        return [
            'valid' => true,
            'data' => $sanitized,
            'error' => null,
        ];
    }

    /**
     * Check if module assignment already exists (prevent duplicates)
     */
    private function assignmentExists(string $userPrincipalName, array $assignment): bool
    {
        $query = DB::connection('sqlsrv')
            ->table('Azure_Application_UserMapping')
            ->where('user_id', $userPrincipalName)
            ->where('application_id', $assignment['application_id'])
            ->where('RoleID', $assignment['role_id'])
            ->where('OfficeID', $assignment['location_id'])
            ->where('IsActive', 1)
            ->where('IsDelete', 0);

        // Add function check for SCM modules
        if (! empty($assignment['function_id'])) {
            $query->where('FunctionID', $assignment['function_id']);
        } else {
            $query->whereNull('FunctionID');
        }

        return $query->exists();
    }

    /**
     * Verify that referenced master data exists
     *
     * @return array ['valid' => bool, 'errors' => array]
     */
    private function verifyReferencedData(array $assignment): array
    {
        $errors = [];

        // Verify module exists
        $moduleExists = DB::connection('sqlsrv')
            ->table('Azure_SoftwareMaster')
            ->where('Id', $assignment['module_id'])
            ->where('ApplicationId', $assignment['application_id'])
            ->where('IsActive', 1)
            ->where('IsDeleted', 0)
            ->exists();

        if (! $moduleExists) {
            $errors[] = "Module not found or inactive: {$assignment['module_id']}";
        }

        // Verify location exists
        $locationExists = DB::connection('sqlsrv')
            ->table('Cust_Office')
            ->where('Id', $assignment['location_id'])
            ->where('code', $assignment['location_code'])
            ->where('IsActive', 1)
            ->where('IsDeleted', 0)
            ->exists();

        if (! $locationExists) {
            $errors[] = "Location not found or inactive: {$assignment['location_code']}";
        }

        // Verify role exists
        $roleExists = DB::connection('sqlsrv')
            ->table('Azure_Users_Role')
            ->where('Id', $assignment['role_id'])
            ->where('IsActive', 1)
            ->where('IsDeleted', 0)
            ->exists();

        if (! $roleExists) {
            $errors[] = "Role not found or inactive: {$assignment['role_code']}";
        }

        // Verify function if present (for SCM modules)
        if (! empty($assignment['function_id'])) {
            $functionExists = DB::connection('sqlsrv')
                ->table('Azure_SCM_Functions') // Adjust table name as needed
                ->where('Id', $assignment['function_id'])
                ->where('IsActive', 1)
                ->where('IsDeleted', 0)
                ->exists();

            if (! $functionExists) {
                $errors[] = "Function not found or inactive: {$assignment['function_code']}";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Retry D365 provisioning for a specific user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function retryD365Provisioning(AzureUser $user)
    {
        try {
            Log::info('=== D365 RETRY PROVISIONING STARTED ===', [
                'user_id' => $user->Id,
                'azure_id' => $user->AzureId,
                'initiated_by' => Auth::id(),
            ]);

            // Validation 1: Check if Azure ID exists
            if (!$user->isProvisionedToAzure()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot provision to D365: User not provisioned to Azure AD',
                ], 400);
            }

            // Validation 2: Check if user already has D365UserID
            if (! empty($user->D365UserID)) {
                Log::info('User already provisioned to D365', [
                    'user_id' => $user->Id,
                    'd365_user_id' => $user->D365UserID,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'User is already provisioned to D365',
                ], 400);
            }

            // Validation 3: Check if user has D365 LIVE module assignments
            $hasD365Assignment = DB::connection('sqlsrv')
                ->table('Azure_Application_UserMapping')
                ->where('AzureUserID', $user->AzureId)
                ->where('ApplicationName', 'D365 LIVE')
                ->where('IsActive', 1)
                ->where('IsDelete', 0)
                ->exists();

            if (! $hasD365Assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'User has no D365 LIVE module assignments',
                ], 400);
            }

            // Call the stored procedure
            $d365Result = DB::connection('sqlsrv')->select('
            SET NOCOUNT ON;

            DECLARE @Status VARCHAR(15), @Remark VARCHAR(200);

            EXEC sp_Azure_D365UserCreation
                @AzureUserID = ?,
                @Status = @Status OUTPUT,
                @Remark = @Remark OUTPUT;

            SELECT @Status as Status, @Remark as Remark;
        ', [$user->AzureId]);

            $d365Status = $d365Result[0]->Status ?? 'Failed';
            $d365Remark = $d365Result[0]->Remark ?? 'Unknown error';

            if ($d365Status === 'Success') {
                Log::info('=== D365 RETRY PROVISIONING SUCCESSFUL ===', [
                    'user_id' => $user->Id,
                    'azure_id' => $user->AzureId,
                    'remark' => $d365Remark,
                ]);

                // Log activity
                $this->logUserActivity(
                    $user->Id,
                    'user.d365_provisioned',
                    'User provisioned to D365',
                    [
                        'azure_id' => $user->AzureId,
                        'remark' => $d365Remark,
                        'provisioned_by' => Auth::id(),
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'D365 provisioning successful: ' . $d365Remark,
                    'data' => [
                        'status' => $d365Status,
                        'remark' => $d365Remark,
                    ],
                ]);
            } else {
                Log::error('=== D365 RETRY PROVISIONING FAILED ===', [
                    'user_id' => $user->Id,
                    'azure_id' => $user->AzureId,
                    'status' => $d365Status,
                    'remark' => $d365Remark,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'D365 provisioning failed: ' . $d365Remark,
                    'data' => [
                        'status' => $d365Status,
                        'remark' => $d365Remark,
                    ],
                ], 422);
            }
        } catch (\Exception $e) {
            Log::error('=== D365 RETRY PROVISIONING ERROR ===', [
                'user_id' => $user->Id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'D365 provisioning error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retry SCM provisioning for a user
     */
    public function retrySCMProvisioning(AzureUser $user)
    {
        try {
            Log::info('Attempting SCM provisioning', [
                'user_id' => $user->Id,
                'azure_id' => $user->AzureId,
            ]);

            // Call stored procedure
            $result = DB::connection('sqlsrv')->select(
                'EXEC sp_Azure_SCMUserCreation ?, @Status = ? OUTPUT, @Remark = ? OUTPUT',
                [
                    $user->AzureId,
                    ['', \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 15],
                    ['', \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 200],
                ]
            );

            $status = $result[0]->Status ?? 'UNKNOWN';
            $remark = $result[0]->Remark ?? 'No remark';

            if ($status === 'SUCCESS') {
                Log::info('SCM provisioning succeeded', [
                    'user_id' => $user->Id,
                    'status' => $status,
                    'remark' => $remark,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "SCM provisioning successful!\n\n{$remark}",
                ]);
            } else {
                Log::error('SCM provisioning failed', [
                    'user_id' => $user->Id,
                    'status' => $status,
                    'remark' => $remark,
                ]);

                return response()->json([
                    'success' => false,

                    'message' => "SCM provisioning failed.\n\nStatus: {$status}\nRemark: {$remark}",
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('SCM provisioning exception', [
                'user_id' => $user->Id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'SCM provisioning failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function configureOfficeAssignments(Request $request, AzureUser $user)
    {
        try {
            $validated = $request->validate([
                'office_code' => 'required|string',
                'modules' => 'required|array|min:1',
                'modules.*.module_id' => 'required|string',
                'modules.*.application_name' => 'required|string',
                'modules.*.roles' => 'required|array|min:1',
                'modules.*.functions' => 'nullable|array',
            ]);

            DB::connection('sqlsrv')->beginTransaction();

            $officeCode = $validated['office_code'];
            $insertedCount = 0;

            // Get office ID
            $office = DB::connection('sqlsrv')
                ->table('Cust_Office')
                ->where('code', $officeCode)
                ->first();

            if (! $office) {
                throw new \Exception("Office not found: {$officeCode}");
            }

            foreach ($validated['modules'] as $moduleData) {
                $hasFunctions = ! empty($moduleData['functions']);

                // ALWAYS insert roles to mapping table
                foreach ($moduleData['roles'] as $role) {
                    $mappingId = DB::connection('sqlsrv')->selectOne('SELECT NEWID() as newId')->newId;

                    DB::connection('sqlsrv')->table('Azure_Application_UserMapping')->insert([
                        'Id' => $mappingId,
                        'AzureUserID' => $user->AzureId,
                        'user_id' => $user->userPrincipalName,
                        'application_id' => $moduleData['module_id'],
                        'ApplicationName' => $moduleData['application_name'],
                        'Username' => $user->displayName,
                        'OfficeCode' => $officeCode,
                        'OfficeID' => $office->Id,
                        'RoleCode' => $role['code'],
                        'RoleID' => $role['id'],
                        'IsActive' => 1,
                        'IsDelete' => 0,
                        'CreatedBy' => Auth::id(),
                        'CreatedOn' => DB::raw('GETDATE()'),
                    ]);

                    $insertedCount++;
                }

                // If SCM module, insert functions separately (NOT combined with roles)
                if ($hasFunctions) {
                    foreach ($moduleData['functions'] as $function) {
                        $functionRecordId = DB::connection('sqlsrv')->selectOne('SELECT NEWID() as newId')->newId;

                        DB::connection('sqlsrv')->table('Azure_Application_UserFunction')->insert([
                            'Id' => $functionRecordId,
                            'AzureUserID' => $user->AzureId,
                            'user_id' => $user->userPrincipalName,
                            'application_id' => $moduleData['module_id'],
                            'Username' => $user->displayName,
                            'ApplicationName' => $moduleData['application_name'],
                            'FunctionID' => $function['id'],
                            'FunctionCode' => $function['code'],
                            'OfficeCode' => $officeCode,
                            'OfficeID' => $office->Id,
                            'IsActive' => 1,
                            'IsDelete' => 0,
                            'CreatedBy' => Auth::id(),
                            'CreatedOn' => DB::raw('GETDATE()'),
                        ]);

                        $insertedCount++;
                    }
                }
            }

            DB::connection('sqlsrv')->commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully configured {$insertedCount} assignments for office {$officeCode}",
            ]);
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get assignments for a specific office (for copy feature)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOfficeAssignments(AzureUser $user, string $officeCode)
    {
        try {
            $assignments = DB::connection('sqlsrv')
                ->table('Azure_Application_UserMapping')
                ->where('user_id', $user->userPrincipalName)
                ->where('OfficeCode', $officeCode)
                ->where('IsActive', 1)
                ->where('IsDelete', 0)
                ->get();

            $grouped = $assignments->groupBy('ApplicationName')->map(function ($items) {
                return [
                    'module_name' => $items->first()->ApplicationName,
                    'roles' => $items->pluck('RoleCode')->unique()->values(),
                    'functions' => $items->pluck('FunctionCode')->filter()->unique()->values(),
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $grouped,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function quickAssignModule(Request $request, AzureUser $user)
    {
        try {
            $validated = $request->validate([
                'module_id' => 'required|string',
                'application_name' => 'required|string',
                'office_codes' => 'required|array|min:1',
                'roles' => 'required|array|min:1',
                'functions' => 'nullable|array',
            ]);

            DB::connection('sqlsrv')->beginTransaction();

            $insertedCount = 0;
            $hasFunctions = ! empty($validated['functions']);

            // Get office IDs
            $offices = DB::connection('sqlsrv')
                ->table('Cust_Office')
                ->whereIn('code', $validated['office_codes'])
                ->get()
                ->keyBy('code');

            foreach ($validated['office_codes'] as $officeCode) {
                $office = $offices[$officeCode] ?? null;

                if (! $office) {
                    Log::warning("Office not found: {$officeCode}");

                    continue;
                }

                // ALWAYS insert roles to mapping table
                foreach ($validated['roles'] as $role) {
                    $mappingId = DB::connection('sqlsrv')->selectOne('SELECT NEWID() as newId')->newId;

                    DB::connection('sqlsrv')->table('Azure_Application_UserMapping')->insert([
                        'Id' => $mappingId,
                        'AzureUserID' => $user->AzureId,
                        'user_id' => $user->userPrincipalName,
                        'application_id' => $validated['module_id'],
                        'ApplicationName' => $validated['application_name'],
                        'Username' => $user->displayName,
                        'OfficeCode' => $officeCode,
                        'OfficeID' => $office->Id,
                        'RoleCode' => $role['code'],
                        'RoleID' => $role['id'],
                        'IsActive' => 1,
                        'IsDelete' => 0,
                        'CreatedBy' => Auth::id(),
                        'CreatedOn' => DB::raw('GETDATE()'),
                    ]);

                    $insertedCount++;
                }

                // If SCM module, insert functions separately
                if ($hasFunctions) {
                    foreach ($validated['functions'] as $function) {
                        $functionRecordId = DB::connection('sqlsrv')->selectOne('SELECT NEWID() as newId')->newId;

                        DB::connection('sqlsrv')->table('Azure_Application_UserFunction')->insert([
                            'Id' => $functionRecordId,
                            'AzureUserID' => $user->AzureId,
                            'user_id' => $user->userPrincipalName,
                            'application_id' => $validated['module_id'],
                            'Username' => $user->displayName,
                            'ApplicationName' => $validated['application_name'],
                            'FunctionID' => $function['id'],
                            'FunctionCode' => $function['code'],
                            'OfficeCode' => $officeCode,
                            'OfficeID' => $office->Id,
                            'IsActive' => 1,
                            'IsDelete' => 0,
                            'CreatedBy' => Auth::id(),
                            'CreatedOn' => DB::raw('GETDATE()'),
                        ]);

                        $insertedCount++;
                    }
                }
            }

            DB::connection('sqlsrv')->commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully created {$insertedCount} assignments across " . count($validated['office_codes']) . ' office(s)',
            ]);
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get module assignments for a user (for copy feature)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserModuleAssignments(AzureUser $user, string $moduleId)
    {
        try {
            $assignments = DB::connection('sqlsrv')
                ->table('Azure_Application_UserMapping')
                ->where('user_id', $user->userPrincipalName)
                ->where('application_id', $moduleId)
                ->where('IsActive', 1)
                ->where('IsDelete', 0)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'offices' => $assignments->pluck('OfficeCode')->unique()->values(),
                    'roles' => $assignments->map(fn($a) => ['id' => $a->RoleID, 'code' => $a->RoleCode])->unique('id')->values(),
                    'functions' => $assignments->filter(fn($a) => ! empty($a->FunctionCode))->map(fn($a) => ['id' => $a->FunctionID, 'code' => $a->FunctionCode])->unique('id')->values(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retry BIZ/FITSYS provisioning for a user
     */
    public function retryBIZProvisioning(AzureUser $user)
    {
        try {
            Log::info('Attempting BIZ/FITSYS provisioning', [
                'user_id' => $user->Id,
                'azure_id' => $user->AzureId,
            ]);

            // Check if user already provisioned
            if (! empty($user->BizUserID)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already provisioned to BIZ/FITSYS',
                ], 400);
            }

            // Get the FITSYS/BIZ application ID
            $bizApp = DB::connection('sqlsrv')
                ->table('Azure_SoftwareMaster')
                ->where('Name', 'LIKE', '%FITSYS%')
                ->orWhere('Name', 'LIKE', '%BIZ%')
                ->first();

            if (! $bizApp) {
                return response()->json([
                    'success' => false,
                    'message' => 'BIZ/FITSYS application not found in system',
                ], 400);
            }

            // Call stored procedure
            $statement = DB::connection('sqlsrv')->getPdo()->prepare(
                'EXEC sp_Azure_FITSYSUserCreation ?, ?, @Status OUTPUT, @Remark OUTPUT'
            );

            $statement->bindParam(1, $user->AzureId, \PDO::PARAM_STR);
            $statement->bindParam(2, $bizApp->Id, \PDO::PARAM_STR);
            $statement->bindParam(3, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 15);
            $statement->bindParam(4, $remark, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 200);

            $status = '';
            $remark = '';

            $statement->execute();

            if (strtolower($status) === 'success') {
                // Refresh user model to get updated BizUserID
                $user->refresh();

                Log::info('BIZ/FITSYS provisioning succeeded', [
                    'user_id' => $user->Id,
                    'status' => $status,
                    'remark' => $remark,
                    'biz_user_id' => $user->BizUserID,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "BIZ/FITSYS provisioning successful!\n\nEmployee Code: {$user->EmployeCode}\n{$remark}",
                ]);
            } else {
                Log::error('BIZ/FITSYS provisioning failed', [
                    'user_id' => $user->Id,
                    'status' => $status,
                    'remark' => $remark,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => "BIZ/FITSYS provisioning failed.\n\nStatus: {$status}\nRemark: {$remark}",
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('BIZ/FITSYS provisioning exception', [
                'user_id' => $user->Id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'BIZ/FITSYS provisioning failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retry FIT Express provisioning for a user
     */
    public function retryFITExpressProvisioning(AzureUser $user)
    {
        try {
            Log::info('Attempting FIT Express provisioning', [
                'user_id' => $user->Id,
                'azure_id' => $user->AzureId,
            ]);

            // Get the FIT Express application ID
            $fitExpressApp = DB::connection('sqlsrv')
                ->table('Azure_SoftwareMaster')
                ->where('Name', 'LIKE', '%FIT Express%')
                ->orWhere('Name', 'LIKE', '%FITEXPRESS%')
                ->first();

            if (! $fitExpressApp) {
                return response()->json([
                    'success' => false,
                    'message' => 'FIT Express application not found in system',
                ], 400);
            }

            // Call stored procedure (using same FITSYS SP with different ApplicationID)
            $statement = DB::connection('sqlsrv')->getPdo()->prepare(
                'EXEC sp_Azure_FITSYSUserCreation ?, ?, @Status OUTPUT, @Remark OUTPUT'
            );

            $statement->bindParam(1, $user->AzureId, \PDO::PARAM_STR);
            $statement->bindParam(2, $fitExpressApp->Id, \PDO::PARAM_STR);
            $statement->bindParam(3, $status, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 15);
            $statement->bindParam(4, $remark, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 200);

            $status = '';
            $remark = '';

            $statement->execute();

            if (strtolower($status) === 'success') {
                Log::info('FIT Express provisioning succeeded', [
                    'user_id' => $user->Id,
                    'status' => $status,
                    'remark' => $remark,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "FIT Express provisioning successful!\n\n{$remark}",
                ]);
            } else {
                Log::error('FIT Express provisioning failed', [
                    'user_id' => $user->Id,
                    'status' => $status,
                    'remark' => $remark,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => "FIT Express provisioning failed.\n\nStatus: {$status}\nRemark: {$remark}",
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('FIT Express provisioning exception', [
                'user_id' => $user->Id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'FIT Express provisioning failed: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Get user's current application access status
     * Used to determine which checkboxes should be enabled/disabled in the modal
     * 
     * @param AzureUser $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserApplicationStatus(AzureUser $user)
    {
        try {
            Log::info('=== GET USER APPLICATION STATUS ===', [
                'user_id' => $user->Id,
                'azure_id' => $user->AzureId,
                'email' => $user->userPrincipalName,
            ]);

            // Check Azure AD status
            $azureEnabled = ($user->Status === 'Active' && $user->accountEnabled);

            // Check D365 Live access
            $hasD365 = DB::connection('sqlsrv')
                ->table('Azure_Application_UserMapping')
                ->where('AzureUserID', $user->AzureId)
                ->where('ApplicationName', 'D365 LIVE')
                ->where('IsActive', 1)
                ->where('IsDelete', 0)
                ->exists();

            // Check OPS/SCM access
            $hasOPS = DB::connection('sqlsrv')
                ->table('Azure_Application_UserMapping')
                ->where('AzureUserID', $user->AzureId)
                ->where('ApplicationName', 'LIKE', '%OPS%')
                ->where('IsActive', 1)
                ->where('IsDelete', 0)
                ->exists();

            // Check BIZ PRO access
            $hasBIZ = !empty($user->BizUserID);

            // Check FIT Express access
            $hasFIT = DB::connection('sqlsrv')
                ->table('Azure_Application_UserMapping')
                ->where('AzureUserID', $user->AzureId)
                ->where('ApplicationName', 'LIKE', '%FIT Express%')
                ->where('IsActive', 1)
                ->where('IsDelete', 0)
                ->exists();

            $status = [
                'azure' => $azureEnabled,
                'd365' => $hasD365,
                'ops' => $hasOPS,
                'biz' => $hasBIZ,
                'fit' => $hasFIT,
            ];

            Log::info('User application status retrieved', [
                'user_id' => $user->Id,
                'status' => $status,
            ]);

            return response()->json([
                'success' => true,
                'data' => $status,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get user application status', [
                'user_id' => $user->Id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve application status',
            ], 500);
        }
    }

    /**
     * Manage user access across multiple applications using sp_Azure_UserDisable
     * 
     * @param Request $request
     * @param AzureUser $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function manageUserApplicationAccess(Request $request, AzureUser $user)
    {
        try {
            Log::info('=== MANAGE USER APPLICATION ACCESS ===', [
                'user_id' => $user->Id,
                'azure_id' => $user->AzureId,
                'email' => $user->userPrincipalName,
                'request_data' => $request->all(),
            ]);

            // Validate request
            $validated = $request->validate([
                'action' => 'required|in:Disable,Enable',
                'applications' => 'required|array|min:1',
                'applications.*' => 'required|in:azure,d365,ops,biz,fit',
            ]);

            $action = $validated['action'];
            $applications = $validated['applications'];

            // Check if Azure AD is included
            $includesAzureAD = in_array('azure', $applications);

            // Build module list for JSON payload
            $moduleList = [];

            // Map application codes to module names and user levels
            $applicationMapping = [
                'd365' => ['Module' => 'D365 Live', 'UserLevel' => 'Overall'],
                'ops' => ['Module' => 'OPS', 'UserLevel' => 'User'],
                'biz' => ['Module' => 'BIZ PRO', 'UserLevel' => 'Overall'],
                'fit' => ['Module' => 'FIT Express', 'UserLevel' => 'Overall'],
            ];

            foreach ($applications as $app) {
                if ($app === 'azure') {
                    continue; // Azure AD handled separately
                }

                if (isset($applicationMapping[$app])) {
                    $moduleList[] = [
                        'Module' => $applicationMapping[$app]['Module'],
                        'UserLevel' => $applicationMapping[$app]['UserLevel'],
                        'Status' => ($action === 'Disable') ? 1 : 0,
                    ];
                }
            }

            $results = [
                'azure' => null,
                'applications' => [],
            ];

            // Step 1: Handle Azure AD if included
            if ($includesAzureAD) {
                Log::info('→ Step 1: Processing Azure AD...');

                try {
                    if ($action === 'Disable') {
                        // Use existing disable method
                        $azureResponse = $this->performAzureDisable($user);
                    } else {
                        // Use existing enable method
                        $azureResponse = $this->performAzureEnable($user);
                    }

                    $results['azure'] = [
                        'success' => $azureResponse['success'],
                        'message' => $azureResponse['message'],
                    ];

                    Log::info('✓ Step 1: Azure AD processed', $results['azure']);
                } catch (\Exception $e) {
                    $results['azure'] = [
                        'success' => false,
                        'message' => 'Azure AD operation failed: ' . $e->getMessage(),
                    ];

                    Log::error('✗ Step 1: Azure AD failed', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Step 2: Handle other applications via stored procedure
            if (!empty($moduleList)) {
                Log::info('→ Step 2: Processing applications via SP...', [
                    'module_count' => count($moduleList),
                ]);

                try {
                    // Build JSON input for stored procedure
                    $inputJson = [
                        'OperationType' => $action . ' Users',
                        'SubmittedDatetime' => now()->format('YmdHis'),
                        'SubmittedBy' => Auth::user()->name ?? 'system',
                        'Action' => $action,
                        'UserID' => $user->Id,
                        'Modulelist' => $moduleList,
                    ];

                    $inputJsonString = json_encode($inputJson);

                    Log::info('Calling sp_Azure_UserDisable', [
                        'input_json' => $inputJsonString,
                    ]);

                    // Call stored procedure
                    $pdo = DB::connection('sqlsrv')->getPdo();
                    $stmt = $pdo->prepare("
                    SET NOCOUNT ON;
                    DECLARE @OutputJson NVARCHAR(MAX);
                    EXEC dbo.sp_Azure_UserDisable 
                        @InputJson = ?,
                        @OutputJson = @OutputJson OUTPUT;
                    SELECT @OutputJson as OutputJson;
                ");

                    $stmt->execute([$inputJsonString]);
                    $result = $stmt->fetch(\PDO::FETCH_OBJ);

                    // Clear any remaining result sets
                    while ($stmt->nextRowset()) {
                        // Just loop through to clear
                    }

                    $outputJson = $result->OutputJson ?? null;

                    Log::info('SP Response received', [
                        'output_json' => $outputJson,
                        'output_json_length' => strlen($outputJson ?? ''),
                    ]);

                    // Parse output JSON
                    if ($outputJson) {
                        $output = json_decode($outputJson, true);

                        if (json_last_error() === JSON_ERROR_NONE) {
                            Log::info('Parsed SP output', ['output' => $output]);

                            // ================================================================
                            // ENHANCED: Handle both old and new SP output formats
                            // ================================================================

                            // Check if output is in the old format (single result, no Modulelist array)
                            if (isset($output['status']) && !isset($output['Modulelist'])) {
                                // OLD FORMAT: Single object with status/Remark
                                // This means SP processed one or more modules and returned directly

                                Log::info('Detected old SP output format (single result)');

                                // Check if this is a global failure (user doesn't exist, etc.)
                                if (
                                    strtolower($output['status']) === 'failed' &&
                                    str_contains(strtolower($output['Remark'] ?? ''), 'does not exists')
                                ) {

                                    // User doesn't exist - mark all modules as failed
                                    foreach ($moduleList as $module) {
                                        $moduleName = $module['Module'];
                                        $results['applications'][$moduleName] = [
                                            'success' => false,
                                            'message' => $output['Remark'] ?? 'User not found in system',
                                        ];
                                    }
                                } else {
                                    // Apply result to all modules in the request
                                    foreach ($moduleList as $module) {
                                        $moduleName = $module['Module'];

                                        $results['applications'][$moduleName] = [
                                            'success' => strtolower($output['status']) === 'success',
                                            'message' => $output['Remark'] ?? $output['status'] ?? 'Unknown status',
                                        ];
                                    }
                                }
                            }
                            // Check if output has the expected Modulelist format
                            elseif (isset($output['Modulelist']) && is_array($output['Modulelist'])) {
                                // NEW FORMAT: Array of module results
                                Log::info('Detected new SP output format (Modulelist array)');

                                foreach ($moduleList as $module) {
                                    $moduleName = $module['Module'];

                                    // Find result for this module in output
                                    $moduleResult = collect($output['Modulelist'])->firstWhere('Module', $moduleName);

                                    if ($moduleResult) {
                                        $results['applications'][$moduleName] = [
                                            'success' => ($moduleResult['Status'] ?? 0) === 1,
                                            'message' => $moduleResult['Remark'] ?? 'No status returned',
                                        ];
                                    } else {
                                        // Module not found in output
                                        $results['applications'][$moduleName] = [
                                            'success' => false,
                                            'message' => 'No response from stored procedure for this module',
                                        ];
                                    }
                                }
                            }
                            // Fallback: unknown format
                            else {
                                Log::warning('Unknown SP output format', ['output' => $output]);

                                foreach ($moduleList as $module) {
                                    $moduleName = $module['Module'];
                                    $results['applications'][$moduleName] = [
                                        'success' => false,
                                        'message' => 'Unexpected response format from stored procedure',
                                    ];
                                }
                            }
                        } else {
                            throw new \Exception('Invalid JSON response from stored procedure: ' . json_last_error_msg());
                        }
                    } else {
                        throw new \Exception('No output from stored procedure');
                    }

                    Log::info('✓ Step 2: Applications processed', [
                        'results' => $results['applications'],
                    ]);
                } catch (\Exception $e) {
                    Log::error('✗ Step 2: Application processing failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    // Mark all applications as failed
                    foreach ($moduleList as $module) {
                        $moduleName = $module['Module'];

                        // Only mark as failed if not already processed
                        if (!isset($results['applications'][$moduleName])) {
                            $results['applications'][$moduleName] = [
                                'success' => false,
                                'message' => 'Failed: ' . $e->getMessage(),
                            ];
                        }
                    }
                }
            }

            // Build summary message
            $summary = $this->buildSummaryMessage($results, $action);

            Log::info('=== MANAGE USER APPLICATION ACCESS COMPLETED ===', [
                'user_id' => $user->Id,
                'summary' => $summary,
                'results' => $results,
            ]);

            return response()->json([
                'success' => true,
                'message' => $summary['message'],
                'results' => $results,
                'summary' => $summary,
                'reload' => $includesAzureAD, // Reload page if Azure AD was modified
            ]);
        } catch (\Exception $e) {
            Log::error('Unexpected error in manageUserApplicationAccess', [
                'user_id' => $user->Id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper: Perform Azure AD disable operation
     */
    private function performAzureDisable($user)
    {
        // Reuse existing disable logic
        $azureId = $user->AzureId;

        if (empty($azureId) || !$this->isValidUuid($azureId)) {
            return [
                'success' => false,
                'message' => 'Invalid Azure ID',
            ];
        }

        $azureService = app(\App\Services\AzureGraphService::class);

        try {
            $azureService->disableUser($azureId);

            DB::connection('sqlsrv')
                ->table('Azure_Users')
                ->where('Id', $user->Id)
                ->update([
                    'Status' => 'Inactive',
                    'accountEnabled' => 0,
                ]);

            return [
                'success' => true,
                'message' => 'Azure AD account disabled successfully',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Azure AD disable failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Helper: Perform Azure AD enable operation
     */
    private function performAzureEnable($user)
    {
        // Reuse existing enable logic
        $azureId = $user->AzureId;

        if (empty($azureId) || !$this->isValidUuid($azureId)) {
            return [
                'success' => false,
                'message' => 'Invalid Azure ID',
            ];
        }

        $azureService = app(\App\Services\AzureGraphService::class);

        try {
            $azureService->enableUser($azureId);

            DB::connection('sqlsrv')
                ->table('Azure_Users')
                ->where('Id', $user->Id)
                ->update([
                    'Status' => 'Active',
                    'accountEnabled' => 1,
                ]);

            return [
                'success' => true,
                'message' => 'Azure AD account enabled successfully',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Azure AD enable failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Helper: Build summary message from results
     */
    private function buildSummaryMessage($results, $action)
    {
        $successful = [];
        $failed = [];

        // Check Azure AD result
        if ($results['azure']) {
            if ($results['azure']['success']) {
                $successful[] = 'Azure AD';
            } else {
                $failed[] = 'Azure AD: ' . $results['azure']['message'];
            }
        }

        // Check application results
        foreach ($results['applications'] as $module => $result) {
            if ($result['success']) {
                $successful[] = $module;
            } else {
                $failed[] = $module . ': ' . $result['message'];
            }
        }

        $totalCount = count($successful) + count($failed);
        $successCount = count($successful);

        $message = "{$action} operation completed: {$successCount}/{$totalCount} successful";

        if (!empty($successful)) {
            $message .= "\n\n✅ Successful:\n• " . implode("\n• ", $successful);
        }

        if (!empty($failed)) {
            $message .= "\n\n❌ Failed:\n• " . implode("\n• ", $failed);
        }

        return [
            'message' => $message,
            'successful' => $successful,
            'failed' => $failed,
            'success_count' => $successCount,
            'total_count' => $totalCount,
        ];
    }

    /**
     * Get Azure ID with comprehensive fallback strategy
     * 
     * @param AzureUser $user
     * @return string|null
     */
    private function getAzureIdWithFallback(AzureUser $user): ?string
    {
        // Method 1: From model
        if (!empty($user->AzureId) && $this->isValidUuid($user->AzureId)) {
            return trim($user->AzureId);
        }

        // Method 2: Refresh and retry
        $user = $user->fresh();
        if ($user && !empty($user->AzureId) && $this->isValidUuid($user->AzureId)) {
            return trim($user->AzureId);
        }

        // Method 3: Direct DB query
        $azureId = DB::connection('sqlsrv')
            ->table('Azure_Users')
            ->where('Id', $user->Id)
            ->value('AzureId');

        if (!empty($azureId) && $this->isValidUuid($azureId)) {
            return trim($azureId);
        }

        // Method 4: Azure AD lookup
        try {
            $azureService = app(\App\Services\AzureGraphService::class);
            $azureUser = $azureService->findUserByUPN($user->userPrincipalName);

            if ($azureUser && isset($azureUser['id']) && $this->isValidUuid($azureUser['id'])) {
                $azureId = trim($azureUser['id']);

                // Update DB
                DB::connection('sqlsrv')
                    ->table('Azure_Users')
                    ->where('Id', $user->Id)
                    ->update(['AzureId' => $azureId]);

                return $azureId;
            }
        } catch (\Exception $e) {
            Log::error('Azure AD lookup failed', ['error' => $e->getMessage()]);
        }

        return null;
    }
}
