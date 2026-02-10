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
     * ENHANCED: Prevents duplicate users by email/UPN and ensures AzureId is always set
     *
     * Flow:
     * 1. CHECK FOR DUPLICATES (email/UPN in DB and Azure AD)
     * 2. Validate and decode modules JSON
     * 3. CREATE USER IN AZURE AD FIRST (to get AzureId)
     * 4. Create user in local DB with AzureId already populated (with race condition safety)
     * 5. Auto-assign EmployeeCode if not provided
     * 6. Expand ALL selections (locations, roles, functions) server-side
     * 7. Create all expanded module assignments
     * 8. Provision user to external systems (D365, OPS, BIZ, FIT)
     * 9. Rollback everything if ANY critical step fails (all-or-nothing)
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(AzureStoreUserRequest $request)
    {

        $azureService = app(\App\Services\AzureGraphService::class);

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
            // STEP 0: CHECK FOR DUPLICATE USERS (Enhanced with Azure AD check)
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
                ->where('userPrincipalName', '!=', $userEmail)
                ->first();

            if ($existingByMail) {
                Log::warning('Duplicate user detected by mail field', [
                    'email' => $userEmail,
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
                    ], 422);
                }

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['email' => strip_tags($errorMessage)]);
            }

            // ✅ NEW: Check 2.5: Azure AD - check if user already exists
            try {
                $azureService = app(\App\Services\AzureGraphService::class);
                $azureUser = $azureService->findUserByUPN($userEmail);

                if ($azureUser && isset($azureUser['id'])) {
                    Log::warning('Duplicate user detected in Azure AD', [
                        'email' => $userEmail,
                        'azure_id' => $azureUser['id'],
                        'display_name' => $azureUser['displayName'] ?? 'Unknown',
                    ]);

                    // Check if this Azure user is already in our DB (orphaned Azure account)
                    $linkedDBUser = DB::connection('sqlsrv')
                        ->table('Azure_Users')
                        ->where('AzureId', $azureUser['id'])
                        ->first();

                    if ($linkedDBUser) {
                        $errorMessage = sprintf(
                            'This user already exists in Azure AD and is linked to our system!<br>' .
                                '<strong>Name:</strong> %s<br>' .
                                '<strong>Email:</strong> %s<br>' .
                                '<strong>Azure ID:</strong> %s<br>' .
                                '<strong>Suggestion:</strong> Use the existing account instead of creating a new one.',
                            $linkedDBUser->displayName ?? 'Unknown',
                            $linkedDBUser->userPrincipalName ?? 'Unknown',
                            substr($azureUser['id'], 0, 8) . '...'
                        );
                    } else {
                        // Orphaned Azure account - exists in Azure but not in our DB
                        $errorMessage = sprintf(
                            'This email already exists in Azure AD but is not in our system!<br>' .
                                '<strong>Azure Name:</strong> %s<br>' .
                                '<strong>Email:</strong> %s<br>' .
                                '<strong>Suggestion:</strong> This appears to be an orphaned Azure account. Contact IT to resolve this conflict before creating a new user.',
                            $azureUser['displayName'] ?? 'Unknown',
                            $userEmail
                        );
                    }

                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => strip_tags($errorMessage),
                            'error' => 'Duplicate user in Azure AD',
                        ], 422);
                    }

                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['email' => strip_tags($errorMessage)]);
                }
            } catch (\Exception $e) {
                // Azure AD check failed - log but continue (network issues shouldn't block user creation)
                Log::warning('Azure AD duplicate check failed (non-blocking)', [
                    'email' => $userEmail,
                    'error' => $e->getMessage(),
                ]);
            }

            // Check 3: Employee ID uniqueness (if provided)
            if (!empty($request->employee_id)) {
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
                        ], 422);
                    }

                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['employee_id' => $errorMessage]);
                }
            }

            // Check 4: Odoo User ID uniqueness (if provided)
            $odooUserId = $request->input('odoo_user_id');

            if (!empty($odooUserId)) {
                $existingByOdooId = DB::connection('sqlsrv')
                    ->table('Azure_Users')
                    ->where('OdooUserId', $odooUserId)
                    ->first();

                if ($existingByOdooId) {
                    Log::warning('Duplicate Odoo User ID detected', [
                        'odoo_user_id' => $odooUserId,
                        'existing_user' => $existingByOdooId->displayName,
                    ]);

                    $errorMessage = sprintf(
                        'Odoo User ID "%s" is already assigned to: %s',
                        $odooUserId,
                        $existingByOdooId->displayName ?? 'Unknown'
                    );

                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => $errorMessage,
                        ], 422);
                    }

                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['odoo_user_id' => $errorMessage]);
                }
            }

            Log::info('✓ Step 0: No duplicates found - proceeding with creation');

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
                $modulesData = $request->input('modules');
                Log::info('Using legacy modules format', [
                    'assignments' => count($modulesData),
                ]);
            }

            if (empty($modulesData)) {
                throw new \Exception('No module assignments provided. Please assign at least one module.');
            }

            // ====================================================================
            // STEP 1.5: Validate Windows Sign-In module is present (MANDATORY)
            // ====================================================================
            $hasWindowsSignIn = collect($modulesData)->contains(function ($module) {
                $moduleName = strtoupper($module['application_name'] ?? '');
                return in_array($moduleName, ['WINDOWS SIGN IN', 'WINDOWS SIGN-IN', 'WINDOWS SIGNIN']);
            });

            if (!$hasWindowsSignIn) {
                throw new \Exception('Windows Sign-In module is mandatory for all new users. Please add it to the assignments.');
            }

            Log::info('✓ Windows Sign-In module validation passed');

            // ====================================================================
            // STEP 2: Prepare user data
            // ====================================================================

            // Split name into first and last name
            $nameParts = explode(' ', trim($request->name), 2);
            $givenName = $nameParts[0];
            $surname = $nameParts[1] ?? $nameParts[0];

            // Generate UPN as firstname.initial@domain (e.g., nahashon.m@freight-in-time.com)
            $companyDomain = config('azure.tenant_domain', 'freight-in-time.com');

            // Get first name and last name initial
            $firstNameLower = strtolower($givenName);
            $lastNameInitial = strtolower(substr($surname, 0, 1));

            // Build UPN
            $userPrincipalName = $firstNameLower . '.' . $lastNameInitial . '@' . $companyDomain;

            // Generate new GUID for user ID
            $userId = DB::connection('sqlsrv')
                ->selectOne('SELECT NEWID() as newId')->newId;

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
                'user_principal_name' => $userPrincipalName,
                'primary_location' => $primaryLocation,
            ]);

            // Start database transaction
            DB::connection('sqlsrv')->beginTransaction();

            // ====================================================================
            // STEP 3: CREATE USER IN AZURE AD FIRST (to get AzureId)
            // ====================================================================

            Log::info('→ Step 3: Creating user in Azure AD...', [
                'upn' => $userPrincipalName,
                'display_name' => $request->name,
            ]);

            // Prepare Azure AD user data
            $azureUserData = [
                'accountEnabled' => true,
                'displayName' => $request->name,
                'mailNickname' => $firstNameLower . '.' . $lastNameInitial,
                'userPrincipalName' => $userPrincipalName,
                'givenName' => $givenName,
                'surname' => $surname,
                'mail' => $userPrincipalName,
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

            // ✅ IMPROVED: Use robust helper method with validation
            $azureResult = $this->getOrCreateAzureId($userPrincipalName, $request->name, $azureUserData);

            if (!isset($azureResult['success']) || !$azureResult['success'] || empty($azureResult['azure_id'])) {
                throw new \Exception('Failed to obtain Azure ID: ' . ($azureResult['message'] ?? 'Unknown error'));
            }

            $azureId = $azureResult['azure_id'];

            // Final validation before proceeding
            if (!$this->isValidUuid($azureId)) {
                throw new \Exception('Invalid Azure ID format after all attempts: ' . $azureId);
            }

            Log::info('✓ Step 3: Azure ID obtained and validated', [
                'azure_id' => $azureId,
                'message' => $azureResult['message'],
            ]);

            // ====================================================================
            // STEP 4: Insert into Azure_Users WITH AzureId already populated
            // ✅ ENHANCED: Race condition safety + exception handling
            // ====================================================================

            Log::info('→ Step 4: Inserting user into database with AzureId...');

            // ✅ NEW: Final duplicate check right before insert (race condition safety)
            $finalDuplicateCheck = DB::connection('sqlsrv')
                ->table('Azure_Users')
                ->where('userPrincipalName', $userPrincipalName)
                ->exists();

            if ($finalDuplicateCheck) {
                // User was just created by another process!
                Log::error('RACE CONDITION DETECTED: User created by another process during transaction', [
                    'upn' => $userPrincipalName,
                ]);

                // Rollback transaction
                DB::connection('sqlsrv')->rollBack();

                // Delete Azure user we just created (cleanup)
                if ($azureId && $azureService) {
                    try {
                        $azureService->deleteUser($azureId);
                        Log::info('✓ Cleaned up Azure user after race condition');
                    } catch (\Exception $e) {
                        Log::critical('Failed to cleanup Azure user after race condition', [
                            'azure_id' => $azureId,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User was just created by another process. Please refresh and try again.',
                    ], 409); // 409 Conflict
                }

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['email' => 'User was just created by another process. Please refresh and try again.']);
            }

            // ✅ NEW: Proceed with insert wrapped in try-catch for constraint violations
            try {
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
                    GETDATE(), ?, ?, ?, ?
                )
            ', [
                    $userId,
                    $userPrincipalName,
                    $request->name,
                    $surname,
                    $userPrincipalName,
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
                    $azureId, // ← AzureId is now guaranteed to be valid!
                ]);

                Log::info('✓ Step 4: User created in database with AzureId', [
                    'user_id' => $userId,
                    'azure_id' => $azureId,
                    'upn' => $userPrincipalName,
                    'status' => 'Pending',
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // Check if it's a duplicate key error
                if (
                    str_contains($e->getMessage(), 'duplicate') ||
                    str_contains($e->getMessage(), 'unique') ||
                    str_contains($e->getMessage(), '2627')
                ) { // SQL Server unique constraint error

                    Log::error('Duplicate key detected during insert', [
                        'upn' => $userPrincipalName,
                        'error' => $e->getMessage(),
                    ]);

                    // Rollback transaction
                    DB::connection('sqlsrv')->rollBack();

                    // Cleanup Azure user
                    if ($azureId && $azureService) {
                        try {
                            $azureService->deleteUser($azureId);
                            Log::info('✓ Cleaned up Azure user after duplicate key error');
                        } catch (\Exception $cleanupError) {
                            Log::critical('Failed to cleanup Azure user after duplicate key error', [
                                'azure_id' => $azureId,
                                'error' => $cleanupError->getMessage(),
                            ]);
                        }
                    }

                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'A user with this email already exists in the database.',
                        ], 409);
                    }

                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['email' => 'A user with this email already exists in the database.']);
                }

                // Re-throw if it's a different database error
                throw $e;
            }

            // ====================================================================
            // STEP 5: Auto-assign EmployeeCode if not provided
            // ====================================================================

            if (empty($request->employee_id)) {
                Log::info('Auto-generating EmployeeCode...');
                $nextCode = DB::connection('sqlsrv')->select("
SELECT CAST(ISNULL(MAX(MaxCode), 0) + 1 AS VARCHAR(100)) as NextCode
FROM (
    SELECT MAX(CAST(CU.EmployeeCode AS BIGINT)) as MaxCode
    FROM Cust_User CU WITH (NOLOCK)
    WHERE CU.EmployeeCode IS NOT NULL 
        AND LEN(ISNULL(CU.EmployeeCode, '')) > 0
        AND ISNUMERIC(CU.EmployeeCode) = 1
        AND CU.EmployeeCode NOT LIKE '%[^0-9]%'
    
    UNION ALL
    
    SELECT MAX(CAST(AU.EmployeCode AS BIGINT)) as MaxCode
    FROM Azure_Users AU WITH (NOLOCK)
    WHERE AU.EmployeCode IS NOT NULL  
        AND LEN(ISNULL(AU.EmployeCode, '')) > 0
        AND ISNUMERIC(AU.EmployeCode) = 1
        AND AU.EmployeCode NOT LIKE '%[^0-9]%'
) AS Combined
");

                $generatedEmployeeCode = $nextCode[0]->NextCode ?? '1';

                DB::connection('sqlsrv')->update(
                    "UPDATE Azure_Users SET EmployeCode = ? WHERE Id = ?",
                    [$generatedEmployeeCode, $userId]
                );

                Log::info('✓ EmployeeCode auto-assigned', [
                    'user_id' => $userId,
                    'employee_code' => $generatedEmployeeCode,
                ]);
            } else {
                Log::info('Using provided EmployeeCode', [
                    'user_id' => $userId,
                    'employee_code' => $request->employee_id,
                ]);
            }

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

                    if (!$module) {
                        $expansionErrors[] = "Assignment {$index}: Module not found or inactive";
                        continue;
                    }

                    // ================================================================
                    // STEP 6.1: Check if this is Windows Sign-In module
                    // ================================================================
                    $applicationCode = $compactAssignment['application_name'];
                    $isWindowsSignIn = in_array(strtoupper($applicationCode), [
                        'WINDOWS SIGN IN',
                        'WINDOWS SIGN-IN',
                        'WINDOWS SIGNIN'
                    ]);

                    // ================================================================
                    // STEP 6.2: Expand LOCATIONS
                    // ================================================================
                    $locations = [];

                    if ($isWindowsSignIn) {
                        // Windows Sign-In: Use default location automatically
                        Log::info("  Processing Windows Sign-In module (no location required)");

                        $defaultLocation = DB::connection('sqlsrv')
                            ->selectOne("SELECT TOP 1 Id, code, Name FROM Cust_Office 
                             WHERE IsActive = 1 AND IsDeleted = 0 
                             ORDER BY code");

                        if (!$defaultLocation) {
                            $expansionErrors[] = "Assignment {$index}: Could not find default location for Windows Sign-In";
                            continue;
                        }

                        $locations = collect([$defaultLocation]);

                        Log::info("  ✓ Using default location for Windows Sign-In", [
                            'location_code' => $defaultLocation->code,
                            'location_name' => $defaultLocation->Name
                        ]);
                    } else {
                        // Normal modules: Process locations as usual
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
                    }

                    if ($locations->isEmpty()) {
                        $expansionErrors[] = "Assignment {$index}: No valid locations found";
                        continue;
                    }

                    // ================================================================
                    // STEP 6.3: Expand ROLES
                    // ================================================================
                    $roles = [];

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
                                return !empty($r->id) && !empty($r->code);
                            });

                            Log::info("  Expanded ALL roles to {$roles->count()} roles for {$applicationCode}");
                        } catch (\Exception $e) {
                            $expansionErrors[] = "Assignment {$index}: Failed to fetch roles - {$e->getMessage()}";
                            continue;
                        }
                    } else {
                        $roleIds = $compactAssignment['roleIds'] ?? [];
                        $roleCodes = $compactAssignment['roleCodes'] ?? [];

                        if (!empty($roleIds)) {
                            $roles = collect($roleIds)->map(function ($id, $idx) use ($roleCodes) {
                                return (object) [
                                    'id' => $id,
                                    'code' => $roleCodes[$idx] ?? '',
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

                    // ================================================================
                    // STEP 6.4: Expand FUNCTIONS (SCM modules only)
                    // ================================================================
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
                                    return !empty($f->id) && !empty($f->code);
                                });

                                Log::info("  Expanded ALL functions to {$functions->count()} functions");
                            } catch (\Exception $e) {
                                $expansionErrors[] = "Assignment {$index}: Failed to fetch functions - {$e->getMessage()}";
                                continue;
                            }
                        } else {
                            $functionIds = $compactAssignment['functionIds'] ?? [];
                            $functionCodes = $compactAssignment['functionCodes'] ?? [];

                            if (!empty($functionIds)) {
                                $functions = collect($functionIds)->map(function ($id, $idx) use ($functionCodes) {
                                    return (object) [
                                        'id' => $id,
                                        'code' => $functionCodes[$idx] ?? '',
                                    ];
                                })->filter(function ($f) {
                                    return !empty($f->id) && !empty($f->code);
                                });

                                Log::info("  Using {$functions->count()} specific functions");
                            }
                        }
                    }

                    // ================================================================
                    // STEP 6.5: Create all combinations
                    // ================================================================
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
                                'function_id' => null,
                                'function_code' => null,
                            ];
                        }

                        // Add functions separately (only for SCM)
                        if ($isSCM) {
                            foreach ($functions as $function) {
                                if ($function && $function->id) {
                                    $expandedAssignments[] = [
                                        'module_id' => $compactAssignment['module_id'],
                                        'application_id' => $compactAssignment['application_id'],
                                        'application_name' => $compactAssignment['application_name'],
                                        'location_id' => $location->Id,
                                        'location_code' => $location->code,
                                        'role_id' => null,
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

                if (!$validation['valid']) {
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

                if (!$dataCheck['valid']) {
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
            if (!empty($validationErrors)) {
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
                if (!empty($assignment['role_id'])) {
                    $roleAssignments[] = $assignment;
                }
                if (!empty($assignment['function_id'])) {
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

            if (!empty($roleAssignments)) {
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
                                'Id'          => $mappingId,
                                'AzureUserID' => $userId,
                                'user_id'     => $userPrincipalName,
                                'application_id'   => $roleData['application_id'],
                                'Username'         => $request->name,
                                'ApplicationName'  => $roleData['application_name'],
                                'RoleCode'         => $roleData['role_code'],
                                'RoleID'           => $roleData['role_id'],
                                'OfficeCode'       => $roleData['location_code'],
                                'OfficeID'         => $roleData['location_id'],
                                'IsActive'         => 1,
                                'IsDelete'         => 0,
                                'CreatedBy'        => Auth::user()->name ?? 'system',
                                'CreatedOn'        => DB::raw('GETDATE()'),
                            ]);

                        $insertedRolesCount++;
                        $spMap = config('azure.service_principal_map', []);
                        $spId = $spMap[$roleData['application_name']] ?? null;

                        if ($spId) {
                            $azureService->assignUserToApplication($azureId, $spId);
                        }
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

            if (!empty($functionAssignments)) {
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
                                'AzureUserID' => $userId,
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

            if (!empty($allInsertErrors)) {
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
                ', [$userId]);

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
            // STEP 12: Provision to OPS/SCM (if applicable)
            // ====================================================================

            // $hasOPSAssignment = collect($validatedAssignments)
            //     ->contains(function ($assignment) {
            //         $appName = strtoupper($assignment['application_name']);
            //         return in_array($appName, ['OPS', 'OPS UAT', 'OPSUAT']);
            //     });

            // if ($hasOPSAssignment) {
            //     Log::info('→ Step 12: Provisioning user to OPS/SCM...', [
            //         'user_id' => $userId,
            //     ]);

            //     try {
            //         $scmResult = DB::connection('sqlsrv')->select("
            //         SET NOCOUNT ON;
            //         DECLARE @Status VARCHAR(15), @Remark VARCHAR(200);
            //         EXEC sp_Azure_SCMUserCreation
            //             @AzureUserID = ?,
            //             @Status = @Status OUTPUT,
            //             @Remark = @Remark OUTPUT;
            //         SELECT @Status as Status, @Remark as Remark;
            //     ", [$userId]);

            //         $scmStatus = $scmResult[0]->Status ?? 'Failed';
            //         $scmRemark = $scmResult[0]->Remark ?? 'Unknown error';

            //         Log::info(strtoupper($scmStatus) === 'SUCCESS' ? '✓ Step 12: OPS provisioned' : '⚠ Step 12: OPS failed', [
            //             'status' => $scmStatus,
            //             'remark' => $scmRemark,
            //         ]);
            //     } catch (\Exception $e) {
            //         Log::error('✗ Step 12: OPS provisioning error', [
            //             'error' => $e->getMessage(),
            //         ]);
            //     }
            // } else {
            //     Log::info('⊘ Step 12: OPS provisioning skipped (no OPS assignments)');
            // }

            // // ====================================================================
            // // STEP 13: Provision to BIZ PRO (if applicable)
            // // ====================================================================

            // $hasBizAssignment = collect($validatedAssignments)
            //     ->contains(function ($assignment) {
            //         return strtoupper($assignment['application_name']) === 'BIZ PRO';
            //     });

            // if ($hasBizAssignment) {
            //     Log::info('→ Step 13: Provisioning user to BIZ PRO...', [
            //         'user_id' => $userId,
            //     ]);

            //     // Get fresh user data for validation
            //     $userForValidation = DB::connection('sqlsrv')
            //         ->table('Azure_Users')
            //         ->where('Id', $userId)
            //         ->first();

            //     // PRE-FLIGHT VALIDATION
            //     $validation = $this->validateForBizProvisioning((object) [
            //         'Id' => $userForValidation->Id,
            //         'EmployeCode' => $userForValidation->EmployeCode,
            //     ]);

            //     if (!$validation['valid']) {
            //         Log::warning('⊘ Step 13: BIZ PRO provisioning skipped - validation failed', [
            //             'user_id' => $userId,
            //             'errors' => $validation['errors'],
            //         ]);
            //     } else {
            //         try {
            //             $bizApp = DB::connection('sqlsrv')
            //                 ->table('Azure_SoftwareMaster')
            //                 ->where(function ($q) {
            //                     $q->where('Name', 'LIKE', '%FITSYS%')
            //                         ->orWhere('Name', 'LIKE', '%BIZ%');
            //                 })
            //                 ->first();

            //             if ($bizApp) {
            //                 $bizResult = DB::connection('sqlsrv')->select("
            //                 SET NOCOUNT ON;
            //                 DECLARE @Status VARCHAR(15), @Remark VARCHAR(200);
            //                 EXEC sp_Azure_FITSYSUserCreation
            //                     @AzureUserID = ?,
            //                     @ApplicationID = ?,
            //                     @Status = @Status OUTPUT,
            //                     @Remark = @Remark OUTPUT;
            //                 SELECT @Status as Status, @Remark as Remark;
            //             ", [$userId, $bizApp->ApplicationId]);

            //                 $bizStatus = $bizResult[0]->Status ?? 'Failed';
            //                 $bizRemark = $bizResult[0]->Remark ?? 'Unknown error';

            //                 Log::info(strtolower($bizStatus) === 'success' ? '✓ Step 13: BIZ PRO provisioned' : '⚠ Step 13: BIZ PRO failed', [
            //                     'status' => $bizStatus,
            //                     'remark' => $bizRemark,
            //                 ]);
            //             } else {
            //                 Log::error('✗ Step 13: BIZ PRO application not found');
            //             }
            //         } catch (\Exception $e) {
            //             Log::error('✗ Step 13: BIZ PRO provisioning error', [
            //                 'error' => $e->getMessage(),
            //             ]);
            //         }
            //     }
            // } else {
            //     Log::info('⊘ Step 13: BIZ PRO provisioning skipped (no BIZ PRO assignments)');
            // }

            // // ====================================================================
            // // STEP 14: FIT Express (if applicable)
            // // ====================================================================

            // $hasFitAssignment = collect($validatedAssignments)
            //     ->contains(function ($assignment) {
            //         return stripos($assignment['application_name'], 'FIT EXPRESS') !== false;
            //     });

            // if ($hasFitAssignment) {
            //     Log::info('→ Step 14: Provisioning user to FIT Express...', [
            //         'user_id' => $userId,
            //     ]);

            //     // Get fresh user data for validation
            //     $userForValidation = DB::connection('sqlsrv')
            //         ->table('Azure_Users')
            //         ->where('Id', $userId)
            //         ->first();

            //     // PRE-FLIGHT VALIDATION
            //     $validation = $this->validateForBizProvisioning((object) [
            //         'Id' => $userForValidation->Id,
            //         'EmployeCode' => $userForValidation->EmployeCode,
            //     ]);

            //     if (!$validation['valid']) {
            //         Log::warning('⊘ Step 14: FIT Express provisioning skipped - validation failed', [
            //             'user_id' => $userId,
            //             'errors' => $validation['errors'],
            //         ]);
            //     } else {
            //         try {
            //             $fitApp = DB::connection('sqlsrv')
            //                 ->table('Azure_SoftwareMaster')
            //                 ->where('Name', 'LIKE', '%FIT Express%')
            //                 ->first();

            //             if ($fitApp) {
            //                 $fitResult = DB::connection('sqlsrv')->select("
            //                 SET NOCOUNT ON;
            //                 DECLARE @Status VARCHAR(15), @Remark VARCHAR(200);
            //                 EXEC sp_Azure_FITSYSUserCreation
            //                     @AzureUserID = ?,
            //                     @ApplicationID = ?,
            //                     @Status = @Status OUTPUT,
            //                     @Remark = @Remark OUTPUT;
            //                 SELECT @Status as Status, @Remark as Remark;
            //             ", [$userId, $fitApp->ApplicationId]);

            //                 $fitStatus = $fitResult[0]->Status ?? 'Failed';
            //                 $fitRemark = $fitResult[0]->Remark ?? 'Unknown error';

            //                 Log::info(strtolower($fitStatus) === 'success' ? '✓ Step 14: FIT Express provisioned' : '⚠ Step 14: FIT Express failed', [
            //                     'status' => $fitStatus,
            //                     'remark' => $fitRemark,
            //                 ]);
            //             } else {
            //                 Log::error('✗ Step 14: FIT Express application not found');
            //             }
            //         } catch (\Exception $e) {
            //             Log::error('✗ Step 14: FIT Express provisioning error', [
            //                 'error' => $e->getMessage(),
            //             ]);
            //         }
            //     }
            // } else {
            //     Log::info('⊘ Step 14: FIT Express provisioning skipped (no FIT Express assignments)');
            // }

            // ====================================================================
            // STEP 11-14: Provision to ALL external systems
            // ====================================================================

            Log::info('→ Step 11-14: Provisioning to external systems...', [
                'user_id' => $userId,
                'method' => config('azure.use_master_provisioning_sp', false) ? 'master_sp' : 'individual_sps',
            ]);

            $provisioningResults = $this->provisionUser($userId, $validatedAssignments);

            // Build summary for success message
            $provisioningSummary = "\n\nProvisioning ({$provisioningResults['method']}):\n" . $provisioningResults['message'];

            if ($provisioningResults['activated']) {
                $provisioningSummary .= "\n\n✓ User auto-activated by provisioning system";
            }

            Log::info('✓ Step 11-14: Provisioning completed', [
                'user_id' => $userId,
                'success' => $provisioningResults['success'],
                'activated' => $provisioningResults['activated'],
                'method' => $provisioningResults['method'],
            ]);

            // ====================================================================
            // STEP 15: Commit transaction - everything succeeded!
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
            // $successMessage = sprintf(
            //     '✓ User created successfully!<br>' .
            //         'Azure ID: %s<br>' .
            //         'Status: <strong>Pending</strong><br>' .
            //         '%d role(s) and %d function(s) created across %d module(s): %s',
            //     substr($azureId, 0, 8) . '...',
            //     $insertedRolesCount,
            //     $insertedFunctionsCount,
            //     count($uniqueModules),
            //     implode(', ', $uniqueModules)
            // );

            // // Add D365 status to message
            // if ($d365ProvisioningStatus && $d365ProvisioningStatus['status'] !== 'Skipped') {
            //     if ($d365ProvisioningStatus['success']) {
            //         $successMessage .= '<br>✓ D365: User provisioned successfully';
            //     } else {
            //         $successMessage .= '<br>⚠ D365: Provisioning failed - ' . $d365ProvisioningStatus['message'];
            //     }
            // }


            // Refresh user to get latest status
            $userFresh = DB::connection('sqlsrv')
                ->table('Azure_Users')
                ->where('Id', $userId)
                ->first();

            $finalStatus = $userFresh->Status ?? 'Pending';

            $successMessage = sprintf(
                '✓ User created successfully!<br>' .
                    'Azure ID: %s<br>' .
                    'Status: <strong>%s</strong><br>' .
                    '%d role(s) and %d function(s) created across %d module(s): %s%s',
                substr($azureId, 0, 8) . '...',
                $finalStatus,
                $insertedRolesCount,
                $insertedFunctionsCount,
                count($uniqueModules),
                implode(', ', $uniqueModules),
                $provisioningSummary
            );

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
     * Ensure user has AzureId, fetch from Azure AD if missing
     * 
     * @param AzureUser $user
     * @return array ['success' => bool, 'azure_id' => string|null, 'message' => string]
     */
    private function ensureAzureIdExists(AzureUser $user): array
    {
        try {
            // Check if AzureId already exists in database
            $azureId = DB::connection('sqlsrv')
                ->table('Azure_Users')
                ->where('Id', $user->Id)
                ->value('AzureId');

            if (!empty($azureId) && $this->isValidUuid($azureId)) {
                return [
                    'success' => true,
                    'azure_id' => trim($azureId),
                    'message' => 'AzureId already exists',
                ];
            }

            // AzureId missing - attempt to fetch from Azure AD
            Log::warning('AzureId missing - attempting lookup in Azure AD', [
                'user_id' => $user->Id,
                'upn' => $user->userPrincipalName,
            ]);

            $azureService = app(\App\Services\AzureGraphService::class);
            $azureUser = $azureService->findUserByUPN($user->userPrincipalName);

            if (!$azureUser || !isset($azureUser['id'])) {
                return [
                    'success' => false,
                    'azure_id' => null,
                    'message' => 'User not found in Azure AD',
                ];
            }

            $fetchedAzureId = trim($azureUser['id']);

            // Validate format
            if (!$this->isValidUuid($fetchedAzureId)) {
                return [
                    'success' => false,
                    'azure_id' => null,
                    'message' => 'Invalid AzureId format received from Azure AD',
                ];
            }

            // Update database
            DB::connection('sqlsrv')
                ->table('Azure_Users')
                ->where('Id', $user->Id)
                ->update(['AzureId' => $fetchedAzureId]);

            Log::info('✓ AzureId fetched and saved from Azure AD', [
                'user_id' => $user->Id,
                'azure_id' => $fetchedAzureId,
            ]);

            return [
                'success' => true,
                'azure_id' => $fetchedAzureId,
                'message' => 'AzureId fetched from Azure AD',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to fetch AzureId from Azure AD', [
                'user_id' => $user->Id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            // ✅ CRITICAL: Always return array with 'success' key, even on exception
            return [
                'success' => false,
                'azure_id' => null,
                'message' => 'Azure AD lookup failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate user data before BIZ/FIT provisioning
     * 
     * @param AzureUser $user
     * @return array ['valid' => bool, 'errors' => array]
     */
    // private function validateForBizProvisioning(AzureUser $user): array
    // {
    //     $errors = [];

    //     // Check 1: EmployeCode must exist
    //     if (empty($user->EmployeCode)) {
    //         $errors[] = 'EmployeCode is required for BIZ/FIT provisioning. Please ensure EmployeeCode is set in Azure_Users table.';
    //     }
    //     // Removed all numeric validation checks

    //     return [
    //         'valid' => empty($errors),
    //         'errors' => $errors,
    //     ];
    // }

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
        $azureService = app(\App\Services\AzureGraphService::class);

        set_time_limit(120);
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        Log::info('=== UPDATE USER REQUEST RECEIVED ===', [
            'user_id' => $user->Id,
            'user_principal' => $user->userPrincipalName,
            'request_method' => $request->method(),
            'is_ajax' => $request->ajax(),
        ]);

        try {
            // ====================================================================
            // STEP 0.5: Handle Base Office Update
            // ====================================================================
            if ($request->has('scm_base_office')) {
                $newBaseOffice = trim($request->input('scm_base_office'));

                $office = DB::connection('sqlsrv')
                    ->table('Cust_Office')
                    ->where('Code', $newBaseOffice)
                    ->where('IsActive', 1)
                    ->where('IsDeleted', 0)
                    ->first();

                if (!$office) {
                    return response()->json([
                        'success' => false,
                        'message' => "Invalid base office code: {$newBaseOffice}",
                    ], 400);
                }

                DB::connection('sqlsrv')
                    ->table('Azure_Users')
                    ->where('Id', $user->Id)
                    ->update(['BaseOfficeCode' => $newBaseOffice]);

                Log::info('✓ Base office updated', ['new_base_office' => $newBaseOffice]);
            }

            // ====================================================================
            // STEP 0.6: CRITICAL - Always validate AzureId before allowing updates
            // ====================================================================
            $currentUserStatus = DB::connection('sqlsrv')
                ->table('Azure_Users')
                ->where('Id', $user->Id)
                ->value('Status');

            Log::info('→ Step 0.6: Validating AzureId', [
                'current_status' => $currentUserStatus,
                'has_azure_id' => !empty($user->AzureId),
            ]);

            $azureIdCheck = $this->ensureAzureIdExists($user);

            if (!$azureIdCheck['success']) {
                Log::error('✗ Step 0.6: AzureId validation failed', [
                    'user_id' => $user->Id,
                    'message' => $azureIdCheck['message'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => '❌ Cannot update user: ' . $azureIdCheck['message'] .
                        '\n\nThe user must be provisioned to Azure AD before updates.',
                ], 400);
            }

            Log::info('✓ Step 0.6: AzureId validated', ['azure_id' => $azureIdCheck['azure_id']]);

            // ====================================================================
            // STEP 1: Process modules data
            // ====================================================================
            $modulesData = $request->input('modules', []);

            if ($request->has('modules_empty')) {
                $modulesData = [];
            } elseif (is_string($modulesData)) {
                if (in_array($modulesData, ['[]', '{}', '', 'null'])) {
                    $modulesData = [];
                } else {
                    $decoded = json_decode($modulesData, true);
                    $modulesData = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
                }
            }

            if (!is_array($modulesData)) {
                $modulesData = [];
            }

            // ====================================================================
            // STEP 2: Validate modules if present
            // ====================================================================
            if (!empty($modulesData)) {
                try {
                    $request->validate([
                        'modules' => 'required|array|min:1',
                        'modules.*.module_id' => 'required|string',
                        'modules.*.application_id' => 'required|string',
                        'modules.*.location_id' => 'nullable|string',
                        'modules.*.location_code' => 'nullable|string',
                        'modules.*.role_id' => 'nullable|string',
                        'modules.*.role_code' => 'nullable|string',
                        'modules.*.function_id' => 'nullable|string',
                        'modules.*.function_code' => 'nullable|string',
                    ]);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $e->errors(),
                    ], 422);
                }
            }

            // ====================================================================
            // STEP 3: Start database transaction
            // ====================================================================
            DB::connection('sqlsrv')->beginTransaction();

            try {
                // ====================================================================
                // STEP 4: Delete existing assignments
                // ====================================================================
                $deletedRolesCount = DB::connection('sqlsrv')
                    ->table('Azure_Application_UserMapping')
                    ->where('user_id', $user->userPrincipalName)
                    ->whereNotIn('ApplicationName', [
                        'Windows Sign-In',
                        'Windows Sign In',    // ← ADD THIS (with space, no hyphen)
                        'Windows SignIn'      // ← ADD THIS (no space, no hyphen)
                    ])
                    ->delete();

                $deletedFunctionsCount = DB::connection('sqlsrv')
                    ->table('Azure_Application_UserFunction')
                    ->where('user_id', $user->userPrincipalName)
                    ->delete();

                $totalDeleted = $deletedRolesCount + $deletedFunctionsCount;

                Log::info('🗑️ Deleted existing assignments', [
                    'deleted_roles' => $deletedRolesCount,
                    'deleted_functions' => $deletedFunctionsCount,
                    'total_deleted' => $totalDeleted,
                ]);

                // ====================================================================
                // STEP 5: If no new modules, commit and return
                // ====================================================================
                if (empty($modulesData)) {
                    DB::connection('sqlsrv')->commit();

                    return response()->json([
                        'success' => true,
                        'message' => "All module assignments removed! {$totalDeleted} assignments deleted.",
                        'data' => ['deleted' => $totalDeleted, 'inserted' => 0],
                    ]);
                }

                // STEP 6: Separate roles and functions
                // ====================================================================
                $roleAssignments = [];
                $functionAssignments = [];

                $windowsSignInRecord = null;

                foreach ($modulesData as $moduleData) {
                    // Windows Sign-In: bare record, no role/function/location
                    $moduleName = strtoupper($moduleData['application_name'] ?? '');
                    if (in_array($moduleName, ['WINDOWS SIGN-IN', 'WINDOWS SIGN IN', 'WINDOWS SIGNIN'])) {
                        $windowsSignInRecord = $moduleData;
                        continue;
                    }

                    $hasRole = !empty($moduleData['role_id']);
                    $hasFunction = !empty($moduleData['function_id']);

                    if (!$hasRole && !$hasFunction) continue;

                    if ($hasRole) $roleAssignments[] = $moduleData;
                    if ($hasFunction) $functionAssignments[] = $moduleData;
                }

                // ====================================================================
                // STEP 7-8: Validate roles and functions
                // ====================================================================
                $validatedRoles = [];
                $roleValidationErrors = [];

                foreach ($roleAssignments as $index => $roleData) {
                    $validation = $this->validateAssignment($roleData, $index);
                    if (!$validation['valid']) {
                        $roleValidationErrors[] = $validation['error'];
                        continue;
                    }

                    $dataCheck = $this->verifyReferencedData($validation['data']);
                    if (!$dataCheck['valid']) {
                        $roleValidationErrors[] = "Role {$index}: " . implode(', ', $dataCheck['errors']);
                        continue;
                    }

                    $module = DB::connection('sqlsrv')
                        ->table('Azure_SoftwareMaster')
                        ->where('Id', $validation['data']['module_id'])
                        ->first();

                    $validation['data']['application_name'] = $module->Name;
                    $validatedRoles[] = $validation['data'];
                }

                if (!empty($roleValidationErrors)) {
                    DB::connection('sqlsrv')->rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Role validation failed: ' . implode(', ', array_slice($roleValidationErrors, 0, 3)),
                    ], 422);
                }

                $validatedFunctions = [];
                $functionValidationErrors = [];

                foreach ($functionAssignments as $index => $funcData) {
                    $validation = $this->validateAssignment($funcData, $index);
                    if (!$validation['valid']) {
                        $functionValidationErrors[] = $validation['error'];
                        continue;
                    }

                    $dataCheck = $this->verifyReferencedData($validation['data']);
                    if (!$dataCheck['valid']) {
                        $functionValidationErrors[] = "Function {$index}: " . implode(', ', $dataCheck['errors']);
                        continue;
                    }

                    $module = DB::connection('sqlsrv')
                        ->table('Azure_SoftwareMaster')
                        ->where('Id', $validation['data']['module_id'])
                        ->first();

                    $validation['data']['application_name'] = $module->Name;
                    $validatedFunctions[] = $validation['data'];
                }

                if (!empty($functionValidationErrors)) {
                    DB::connection('sqlsrv')->rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Function validation failed: ' . implode(', ', array_slice($functionValidationErrors, 0, 3)),
                    ], 422);
                }

                // ====================================================================
                // STEP 9-10: Insert roles and functions
                // ====================================================================
                $insertedRolesCount = 0;
                foreach ($validatedRoles as $roleData) {
                    DB::connection('sqlsrv')->table('Azure_Application_UserMapping')->insert([
                        'Id'              => \Illuminate\Support\Str::uuid()->toString(),
                        'AzureUserID'     => $user->Id,
                        'user_id'         => $user->userPrincipalName,
                        'application_id'  => $roleData['application_id'],
                        'ApplicationName' => $roleData['application_name'],
                        'RoleID'          => $roleData['role_id'],
                        'RoleCode'        => $roleData['role_code'],
                        'OfficeID'        => $roleData['location_id'],
                        'OfficeCode'      => $roleData['location_code'],
                        'IsActive'        => 1,
                        'IsDelete'        => 0,
                        'CreatedBy'       => Auth::user()->name ?? 'system',
                        'CreatedOn'       => DB::raw('GETDATE()'),
                    ]);

                    $insertedRolesCount++;

                    $spMap = config('azure.service_principal_map', []);
                    $spId = $spMap[$roleData['application_name']] ?? null;

                    if ($spId) {
                        $azureService->assignUserToApplication($user->AzureId, $spId);
                    }

                    $spMap = config('azure.service_principal_map', []);
                    $spId  = $spMap[$roleData['application_name']] ?? null;

                    if ($spId && $roleData['role_id']) {
                        $azureService->assignAppRole(
                            $user->AzureId,
                            $spId,
                            $roleData['role_id']
                        );
                    }
                }




                // ====================================================================
                // STEP 10.5: Ensure Windows Sign-In exists (idempotent insert)
                // ====================================================================
                if ($windowsSignInRecord) {
                    try {
                        $wsExists = DB::connection('sqlsrv')
                            ->table('Azure_Application_UserMapping')
                            ->where('user_id', $user->userPrincipalName)
                            ->where('ApplicationName', 'Windows Sign-In')
                            ->exists();

                        if (!$wsExists) {
                            DB::connection('sqlsrv')->table('Azure_Application_UserMapping')->insert([
                                'Id' => \Illuminate\Support\Str::uuid()->toString(),
                                'AzureUserID' => $user->Id,
                                'user_id' => $user->userPrincipalName,
                                'application_id' => $windowsSignInRecord['application_id'],
                                'ApplicationName' => 'Windows Sign-In',
                                'Username' => $user->displayName,
                                'RoleID' => null,
                                'RoleCode' => null,
                                'OfficeID' => null,
                                'OfficeCode' => null,
                                'IsActive' => 1,
                                'IsDelete' => 0,
                                'CreatedBy' => Auth::user()->name ?? 'system',
                                'CreatedOn' => DB::raw('GETDATE()'),
                            ]);

                            Log::info('✓ Step 10.5: Windows Sign-In inserted', [
                                'user_id' => $user->Id,
                                'application_id' => $windowsSignInRecord['application_id'],
                            ]);
                        } else {
                            Log::info('⊘ Step 10.5: Windows Sign-In already exists — skipped', [
                                'user_id' => $user->Id,
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('✗ Step 10.5: Windows Sign-In insert failed', [
                            'user_id' => $user->Id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                } else {
                    Log::debug('⊘ Step 10.5: No Windows Sign-In record to insert');
                }

                $insertedFunctionsCount = 0;
                foreach ($validatedFunctions as $funcData) {
                    DB::connection('sqlsrv')->table('Azure_Application_UserFunction')->insert([
                        'Id' => \Illuminate\Support\Str::uuid()->toString(),
                        'AzureUserID' => $user->Id,
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
                    ]);
                    $insertedFunctionsCount++;
                }

                // ====================================================================
                // STEP 11: Commit transaction
                // ====================================================================
                DB::connection('sqlsrv')->commit();

                $totalInserted = $insertedRolesCount + $insertedFunctionsCount;

                // ====================================================================
                // STEP 12: Auto-generate EmployeeCode if missing
                // ====================================================================
                $user->refresh();

                if (empty($user->EmployeCode)) {
                    // Line ~1035 - Auto-generation query (remove ISNUMERIC filters)
                    $nextCode = DB::connection('sqlsrv')->select("
                        SELECT CAST(ISNULL(MAX(MaxCode), 0) + 1 AS VARCHAR(100)) as NextCode
                        FROM (
                            SELECT MAX(CAST(CU.EmployeeCode AS BIGINT)) as MaxCode
                            FROM Cust_User CU WITH (NOLOCK)
                            WHERE CU.EmployeeCode IS NOT NULL 
                                AND LEN(ISNULL(CU.EmployeeCode, '')) > 0
                                AND ISNUMERIC(CU.EmployeeCode) = 1
                                AND CU.EmployeeCode NOT LIKE '%[^0-9]%'
    
                            UNION ALL
    
                            SELECT MAX(CAST(AU.EmployeCode AS BIGINT)) as MaxCode
                            FROM Azure_Users AU WITH (NOLOCK)
                            WHERE AU.EmployeCode IS NOT NULL  
                                AND LEN(ISNULL(AU.EmployeCode, '')) > 0
                                AND ISNUMERIC(AU.EmployeCode) = 1
                                AND AU.EmployeCode NOT LIKE '%[^0-9]%'
                            ) AS Combined
                        ");

                    $generatedEmployeeCode = $nextCode[0]->NextCode ?? '1';

                    DB::connection('sqlsrv')->update(
                        "UPDATE Azure_Users SET EmployeCode = ? WHERE Id = ?",
                        [$generatedEmployeeCode, $user->Id]
                    );

                    $user->refresh();
                }

                // // ====================================================================
                // // STEP 13: Provision to external systems
                // // ====================================================================
                // $provisioningResults = [];
                // $allValidatedAssignments = array_merge($validatedRoles, $validatedFunctions);

                // $assignedModules = collect($allValidatedAssignments)
                //     ->pluck('application_name')
                //     ->unique()
                //     ->map(fn($name) => strtoupper(trim($name)))
                //     ->filter()
                //     ->values();

                // $userId = $user->Id;

                // // D365
                // if ($assignedModules->contains('D365 LIVE')) {
                //     try {
                //         $d365Result = DB::connection('sqlsrv')->select("
                //         SET NOCOUNT ON;
                //         DECLARE @Status VARCHAR(15), @Remark VARCHAR(200);
                //         EXEC sp_Azure_D365UserCreation @AzureUserID = ?, @Status = @Status OUTPUT, @Remark = @Remark OUTPUT;
                //         SELECT @Status as Status, @Remark as Remark;
                //     ", [$userId]);

                //         $provisioningResults['D365 LIVE'] = [
                //             'success' => ($d365Result[0]->Status ?? 'Failed') === 'Success',
                //             'message' => $d365Result[0]->Remark ?? 'Unknown error',
                //         ];
                //     } catch (\Exception $e) {
                //         $provisioningResults['D365 LIVE'] = ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
                //     }
                // }

                // // OPS/SCM  
                // if ($assignedModules->contains('OPS') || $assignedModules->contains('OPS UAT') || $assignedModules->contains('OPSUAT')) {
                //     try {
                //         $scmResult = DB::connection('sqlsrv')->select("
                //         SET NOCOUNT ON;
                //         DECLARE @Status VARCHAR(15), @Remark VARCHAR(200);
                //         EXEC sp_Azure_SCMUserCreation @AzureUserID = ?, @Status = @Status OUTPUT, @Remark = @Remark OUTPUT;
                //         SELECT @Status as Status, @Remark as Remark;
                //     ", [$userId]);

                //         $provisioningResults['OPS'] = [
                //             'success' => strtoupper($scmResult[0]->Status ?? 'Failed') === 'SUCCESS',
                //             'message' => $scmResult[0]->Remark ?? 'Unknown error',
                //         ];
                //     } catch (\Exception $e) {
                //         $provisioningResults['OPS'] = ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
                //     }
                // }

                // // BIZ PRO
                // if ($assignedModules->contains('BIZ PRO') || $assignedModules->contains('FITSYS')) {
                //     $validation = $this->validateForBizProvisioning($user);

                //     if (!$validation['valid']) {
                //         $provisioningResults['BIZ PRO'] = [
                //             'success' => false,
                //             'message' => 'Validation failed: ' . implode('; ', $validation['errors']),
                //         ];
                //     } else {
                //         try {
                //             $bizApp = DB::connection('sqlsrv')->table('Azure_SoftwareMaster')
                //                 ->where(function ($q) {
                //                     $q->where('Name', 'LIKE', '%FITSYS%')->orWhere('Name', 'LIKE', '%BIZ%');
                //                 })->first();

                //             if ($bizApp) {
                //                 $bizResult = DB::connection('sqlsrv')->select("
                //                 SET NOCOUNT ON;
                //                 DECLARE @Status VARCHAR(15), @Remark VARCHAR(200);
                //                 EXEC sp_Azure_FITSYSUserCreation @AzureUserID = ?, @ApplicationID = ?, @Status = @Status OUTPUT, @Remark = @Remark OUTPUT;
                //                 SELECT @Status as Status, @Remark as Remark;
                //             ", [$userId, $bizApp->ApplicationId]);

                //                 $provisioningResults['BIZ PRO'] = [
                //                     'success' => strtolower($bizResult[0]->Status ?? 'Failed') === 'success',
                //                     'message' => $bizResult[0]->Remark ?? 'Unknown error',
                //                 ];
                //             }
                //         } catch (\Exception $e) {
                //             $provisioningResults['BIZ PRO'] = ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
                //         }
                //     }
                // }

                // // FIT Express
                // if ($assignedModules->contains('FIT EXPRESS')) {
                //     $validation = $this->validateForBizProvisioning($user);

                //     if (!$validation['valid']) {
                //         $provisioningResults['FIT Express'] = [
                //             'success' => false,
                //             'message' => 'Validation failed: ' . implode('; ', $validation['errors']),
                //         ];
                //     } else {
                //         try {
                //             $fitApp = DB::connection('sqlsrv')->table('Azure_SoftwareMaster')
                //                 ->where('Name', 'LIKE', '%FIT Express%')->first();

                //             if ($fitApp) {
                //                 $fitResult = DB::connection('sqlsrv')->select("
                //                 SET NOCOUNT ON;
                //                 DECLARE @Status VARCHAR(15), @Remark VARCHAR(200);
                //                 EXEC sp_Azure_FITSYSUserCreation @AzureUserID = ?, @ApplicationID = ?, @Status = @Status OUTPUT, @Remark = @Remark OUTPUT;
                //                 SELECT @Status as Status, @Remark as Remark;
                //             ", [$userId, $fitApp->ApplicationId]);

                //                 $provisioningResults['FIT Express'] = [
                //                     'success' => strtolower($fitResult[0]->Status ?? 'Failed') === 'success',
                //                     'message' => $fitResult[0]->Remark ?? 'Unknown error',
                //                 ];
                //             }
                //         } catch (\Exception $e) {
                //             $provisioningResults['FIT Express'] = ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
                //         }
                //     }
                // }

                // ====================================================================
                // STEP 13: Provision to ALL external systems using Master SP
                // ====================================================================

                Log::info('→ Step 13: Provisioning via Master SP...', [
                    'user_id' => $user->Id,
                ]);

                $provisioningSummary = '';
                $provisioningResults = []; // ✅ INITIALIZE THIS VARIABLE

                try {
                    $provisionResult = DB::connection('sqlsrv')->select("
        SET NOCOUNT ON;
        DECLARE @sp_Remark VARCHAR(MAX);

        EXEC sp_Azure_UserCreationorUpdate 
            @AzureUserID = ?,
            @sp_Remark = @sp_Remark OUTPUT;

        SELECT @sp_Remark as Remark;
    ", [$user->Id]);

                    $remarkJson = $provisionResult[0]->Remark ?? null;

                    Log::info('Master SP Raw Response', [
                        'user_id' => $user->Id,
                        'raw_json' => $remarkJson,
                        'raw_length' => strlen($remarkJson ?? ''),
                    ]);

                    if ($remarkJson) {
                        try {
                            $provisioningResults = $this->parseMasterSPResponse($remarkJson);

                            Log::info('✓ Step 13: Provisioning completed', [
                                'results' => $provisioningResults,
                            ]);

                            $provisioningSummary = $this->buildProvisioningSummary($provisioningResults);
                        } catch (\Exception $parseError) {
                            Log::error('Failed to parse provisioning results', [
                                'json_error' => $parseError->getMessage(),
                                'raw_json_preview' => substr($remarkJson, 0, 500),
                            ]);
                            $provisioningSummary = "\n\nProvisioning: Completed (details unavailable - parsing failed)";
                        }
                    } else {
                        $provisioningSummary = "\n\nProvisioning: No response from stored procedure";
                    }
                } catch (\Exception $e) {
                    Log::error('✗ Step 13: Provisioning failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $provisioningSummary = "\n\nProvisioning: Failed - " . $e->getMessage();
                }

                // ====================================================================
                // STEP 14: Build response
                // ====================================================================
                $provisioningSummary = '';
                if (!empty($provisioningResults)) {
                    // ✅ FIX: Count successes by checking Status field (not 'success' key)
                    $successCount = collect($provisioningResults)->filter(function ($result) {
                        // Handle both new format (Status) and old format (success) for safety
                        if (isset($result['Status'])) {
                            return strtolower($result['Status']) === 'success';
                        }
                        if (isset($result['success'])) {
                            return $result['success'] === true;
                        }
                        return false;
                    })->count();

                    $totalCount = count($provisioningResults);
                    $provisioningSummary = "\n\nProvisioning: {$successCount}/{$totalCount} successful";

                    foreach ($provisioningResults as $result) {
                        // ✅ FIX: Handle both formats when building summary
                        if (isset($result['Application'])) {
                            // New Master SP format
                            $icon = (strtolower($result['Status'] ?? '') === 'success') ? '✓' : '⚠';
                            $provisioningSummary .= "\n{$icon} {$result['Application']}: {$result['Remark']}";
                        } elseif (isset($result['success'])) {
                            // Old individual SP format (fallback)
                            $icon = $result['success'] ? '✓' : '⚠';
                            $module = $result['module'] ?? 'Unknown';
                            $message = $result['message'] ?? 'No details';
                            $provisioningSummary .= "\n{$icon} {$module}: {$message}";
                        } else {
                            // Unknown format - log it
                            $provisioningSummary .= "\n⚠ Unknown format: " . json_encode($result);
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => "User updated! {$insertedRolesCount} roles and {$insertedFunctionsCount} functions saved.{$provisioningSummary}",
                    'data' => [
                        'roles_inserted' => $insertedRolesCount,
                        'functions_inserted' => $insertedFunctionsCount,
                        'total_inserted' => $totalInserted,
                        'deleted' => $totalDeleted,
                        'provisioning_results' => $provisioningResults,
                    ],
                ]);
            } catch (\Exception $e) {
                if (DB::connection('sqlsrv')->transactionLevel() > 0) {
                    DB::connection('sqlsrv')->rollBack();
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage(),
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage(),
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

            // ✅ NEW: Validation 1.5: CRITICAL - Check if AzureId exists before activation
            if (empty($user->AzureId) || !$this->isValidUuid($user->AzureId)) {
                Log::error('Cannot activate user - AzureId missing or invalid', [
                    'user_id' => $user->Id,
                    'upn' => $user->userPrincipalName,
                    'azure_id' => $user->AzureId,
                ]);

                return redirect()->back()
                    ->with('error', 'Cannot activate user: Azure ID is missing or invalid. The user must be provisioned to Azure AD first.');
            }

            Log::info('✓ AzureId validated for activation', [
                'user_id' => $user->Id,
                'azure_id' => $user->AzureId,
            ]);

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
        try {
            Log::info('=== DISABLE USER REQUEST ===', [
                'user_id' => $user->Id,
                'email' => $user->userPrincipalName,
                'current_status' => $user->Status,
                'initiated_by' => Auth::id(),
            ]);

            // Check if user is already inactive
            if ($user->Status === 'Inactive') {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already disabled',
                ], 400);
            }

            // Ensure Azure ID exists
            $azureIdCheck = $this->ensureAzureIdExists($user);
            if (!isset($azureIdCheck['success']) || !$azureIdCheck['success']) {  // ← ADD isset()
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot disable: ' . ($azureIdCheck['message'] ?? 'Unknown error')  // ← ADD null coalescing
                ], 400);
            }
            $azureId = $azureIdCheck['azure_id'];
            // STEP 1: Attempt to disable in Azure AD
            $azureDisabled = false;
            $azureNotFound = false;
            $azureIdUpdated = false;

            Log::info('Step 1: Attempting to disable user in Azure AD...', [
                'user_id' => $user->Id,
                'azure_id' => $azureId,
            ]);

            try {
                $azureService = app(\App\Services\AzureGraphService::class);
                set_time_limit(120);

                try {
                    $azureService->disableUser($azureId);
                    $azureDisabled = true;
                    Log::info('✓ Step 1: User disabled in Azure AD successfully');
                } catch (\Exception $directError) {
                    if (
                        str_contains($directError->getMessage(), '404') ||
                        str_contains($directError->getMessage(), 'ResourceNotFound')
                    ) {
                        Log::warning('Azure ID not found, attempting lookup by email');

                        try {
                            $azureUserData = $azureService->findUserByUPN($user->userPrincipalName);

                            if ($azureUserData && isset($azureUserData['id'])) {
                                $correctAzureId = trim($azureUserData['id']);

                                Log::warning('Azure ID mismatch - updating', [
                                    'old' => $azureId,
                                    'new' => $correctAzureId,
                                ]);

                                DB::connection('sqlsrv')
                                    ->table('Azure_Users')
                                    ->where('Id', $user->Id)
                                    ->update(['AzureId' => $correctAzureId]);

                                $azureId = $correctAzureId;
                                $azureIdUpdated = true;

                                $azureService->disableUser($correctAzureId);
                                $azureDisabled = true;
                                Log::info('✓ User disabled after ID correction');
                            } else {
                                $azureNotFound = true;
                                Log::warning('✓ User not found in Azure AD (already deleted)');
                            }
                        } catch (\Exception $lookupError) {
                            $azureNotFound = true;
                            Log::warning('✓ User lookup failed (treating as already deleted)');
                        }
                    } else {
                        throw $directError;
                    }
                }
            } catch (\Exception $azureException) {
                if (str_contains($azureException->getMessage(), 'timeout')) {
                    Log::warning('Azure operation timed out - proceeding with local disable');
                } else {
                    Log::error('✗ Step 1 FAILED: ' . $azureException->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to disable in Azure AD: ' . $azureException->getMessage(),
                    ], 500);
                }
            }

            // STEP 2: Update database
            Log::info('Step 2: Updating database...');

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

                Log::info('✓ Step 2: Database updated successfully');
            } catch (\Exception $dbError) {
                Log::error('✗ Step 2 FAILED: ' . $dbError->getMessage());

                // Rollback if Azure was disabled
                if ($azureDisabled && !$azureNotFound) {
                    try {
                        $azureService->enableUser($azureId);
                        Log::warning('⚠ ROLLBACK: User re-enabled in Azure AD');

                        return response()->json([
                            'success' => false,
                            'message' => 'Database update failed. User remains enabled (rollback successful).',
                        ], 500);
                    } catch (\Exception $rollbackError) {
                        Log::critical('✗ ROLLBACK FAILED: Manual intervention required!', [
                            'user_id' => $user->Id,
                            'azure_id' => $azureId,
                        ]);

                        return response()->json([
                            'success' => false,
                            'message' => 'CRITICAL: User disabled in Azure but DB update failed. Contact IT immediately.',
                        ], 500);
                    }
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update database: ' . $dbError->getMessage(),
                ], 500);
            }

            // Log activity
            $this->logUserActivity($user->Id, 'user.disabled', 'User account disabled', [
                'disabled_by' => Auth::user()->name ?? 'system',
                'azure_disabled' => $azureDisabled,
                'azure_not_found' => $azureNotFound,
            ]);

            Log::info('=== USER DISABLED SUCCESSFULLY ===');

            $message = '✅ User has been disabled successfully!';
            if ($azureIdUpdated) $message .= ' Azure ID was corrected.';
            if ($azureNotFound) $message .= ' Note: User was not found in Azure AD.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'reload' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Unexpected error: ' . $e->getMessage());
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
     * Get or create Azure ID for a user with comprehensive error handling
     * This is the SINGLE SOURCE OF TRUTH for Azure ID management
     * 
     * @param string $userPrincipalName
     * @param string|null $displayName
     * @param array $userData Additional user data for Azure AD creation
     * @return array ['success' => bool, 'azure_id' => string|null, 'message' => string]
     */
    private function getOrCreateAzureId(string $userPrincipalName, ?string $displayName = null, array $userData = []): array
    {
        Log::info('→ Getting or creating Azure ID', [
            'upn' => $userPrincipalName,
            'display_name' => $displayName,
        ]);

        $azureService = app(\App\Services\AzureGraphService::class);

        // STEP 1: Try to find existing user in Azure AD
        try {
            $existingUser = $azureService->findUserByUPN($userPrincipalName);

            if ($existingUser && isset($existingUser['id'])) {
                $azureId = trim($existingUser['id']);

                // Validate UUID format
                if ($this->isValidUuid($azureId)) {
                    Log::info('✓ Found existing Azure user', [
                        'upn' => $userPrincipalName,
                        'azure_id' => $azureId,
                    ]);

                    return [
                        'success' => true,
                        'azure_id' => $azureId,
                        'message' => 'Existing Azure user found',
                    ];
                } else {
                    Log::error('Invalid Azure ID format from existing user', [
                        'upn' => $userPrincipalName,
                        'azure_id' => $azureId,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::info('User not found in Azure AD, will create new', [
                'upn' => $userPrincipalName,
                'error' => $e->getMessage(),
            ]);
        }
        // STEP 2: Create new user in Azure AD
        if (empty($userData)) {
            return [
                'success' => false,
                'azure_id' => null,
                'message' => 'User not found in Azure AD and no user data provided for creation',
            ];
        }

        try {
            Log::info('Creating new Azure AD user', [
                'upn' => $userPrincipalName,
            ]);

            $createResult = $azureService->createUser($userData);

            if (!$createResult || !isset($createResult['id'])) {
                throw new \Exception('Azure AD user creation returned no ID');
            }

            $azureId = trim($createResult['id']);

            // Validate UUID format
            if (!$this->isValidUuid($azureId)) {
                throw new \Exception('Invalid Azure ID format returned: ' . $azureId);
            }

            // ✅ NEW: Double-check the user was actually created (prevent ghost responses)
            try {
                $verifyUser = $azureService->getUser($azureId);
                if (!$verifyUser || !isset($verifyUser['id'])) {
                    throw new \Exception('User creation verification failed - user not found immediately after creation');
                }

                Log::info('✅ Azure user creation verified immediately', [
                    'azure_id' => $azureId,
                    'upn' => $verifyUser['userPrincipalName'] ?? 'unknown',
                ]);
            } catch (\Exception $verifyError) {
                // If immediate verification fails, log but continue - may be propagation delay
                Log::warning('⚠️ Immediate verification failed (may be propagation delay)', [
                    'azure_id' => $azureId,
                    'error' => $verifyError->getMessage(),
                ]);
            }

            Log::info('✓ Created new Azure AD user', [
                'upn' => $userPrincipalName,
                'azure_id' => $azureId,
            ]);

            // STEP 3: Verify creation with retry
            $maxRetries = 3;
            $retryDelay = 2; // seconds

            for ($i = 0; $i < $maxRetries; $i++) {
                try {
                    sleep($retryDelay);
                    $verifyUser = $azureService->getUser($azureId);

                    if ($verifyUser && isset($verifyUser['id'])) {
                        Log::info('✓ Azure user creation verified', [
                            'azure_id' => $azureId,
                            'attempt' => $i + 1,
                        ]);

                        return [
                            'success' => true,
                            'azure_id' => $azureId,
                            'message' => 'Azure user created and verified',
                        ];
                    }
                } catch (\Exception $e) {
                    Log::warning('Verification attempt failed', [
                        'attempt' => $i + 1,
                        'error' => $e->getMessage(),
                    ]);

                    if ($i === $maxRetries - 1) {
                        // Last attempt - return the ID anyway
                        return [
                            'success' => true,
                            'azure_id' => $azureId,
                            'message' => 'Azure user created (verification pending)',
                        ];
                    }
                }
            }

            return [
                'success' => true,
                'azure_id' => $azureId,
                'message' => 'Azure user created',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create Azure AD user', [
                'upn' => $userPrincipalName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'azure_id' => null,
                'message' => 'Azure AD user creation failed: ' . $e->getMessage(),
            ];
        }
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
                'initiated_by' => Auth::id(),
            ]);

            // Check if user is already active
            if ($user->Status === 'Active') {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already enabled',
                ], 400);
            }

            // Ensure Azure ID exists
            $azureIdCheck = $this->ensureAzureIdExists($user);
            if (!isset($azureIdCheck['success']) || !$azureIdCheck['success']) {  // ← ADD isset()
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot disable: ' . ($azureIdCheck['message'] ?? 'Unknown error')  // ← ADD null coalescing
                ], 400);
            }
            $azureId = $azureIdCheck['azure_id'];

            // STEP 1: Attempt to enable in Azure AD
            $azureEnabled = false;
            $azureNotFound = false;
            $azureIdUpdated = false;

            Log::info('Step 1: Attempting to enable user in Azure AD...', [
                'user_id' => $user->Id,
                'azure_id' => $azureId,
            ]);

            try {
                $azureService = app(\App\Services\AzureGraphService::class);
                set_time_limit(120);

                try {
                    $azureService->enableUser($azureId);
                    $azureEnabled = true;
                    Log::info('✓ Step 1: User enabled in Azure AD successfully');
                } catch (\Exception $directError) {
                    if (
                        str_contains($directError->getMessage(), '404') ||
                        str_contains($directError->getMessage(), 'ResourceNotFound')
                    ) {
                        Log::warning('Azure ID not found, attempting lookup by email');

                        try {
                            $azureUserData = $azureService->findUserByUPN($user->userPrincipalName);

                            if ($azureUserData && isset($azureUserData['id'])) {
                                $correctAzureId = trim($azureUserData['id']);

                                Log::warning('Azure ID mismatch - updating', [
                                    'old' => $azureId,
                                    'new' => $correctAzureId,
                                ]);

                                DB::connection('sqlsrv')
                                    ->table('Azure_Users')
                                    ->where('Id', $user->Id)
                                    ->update(['AzureId' => $correctAzureId]);

                                $azureId = $correctAzureId;
                                $azureIdUpdated = true;

                                $azureService->enableUser($correctAzureId);
                                $azureEnabled = true;
                                Log::info('✓ User enabled after ID correction');
                            } else {
                                Log::error('✗ User not found in Azure AD');
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Cannot enable: User not found in Azure AD. Contact IT support.',
                                ], 404);
                            }
                        } catch (\Exception $lookupError) {
                            Log::error('✗ User lookup failed');
                            return response()->json([
                                'success' => false,
                                'message' => 'Cannot enable: User not found in Azure AD.',
                            ], 404);
                        }
                    } else {
                        throw $directError;
                    }
                }
            } catch (\Exception $azureException) {
                if (str_contains($azureException->getMessage(), 'timeout')) {
                    Log::warning('Azure operation timed out - proceeding with local enable');
                } else {
                    Log::error('✗ Step 1 FAILED: ' . $azureException->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to enable in Azure AD: ' . $azureException->getMessage(),
                    ], 500);
                }
            }

            // STEP 2: Update database
            Log::info('Step 2: Updating database...');

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

                Log::info('✓ Step 2: Database updated successfully');
            } catch (\Exception $dbError) {
                Log::error('✗ Step 2 FAILED: ' . $dbError->getMessage());

                // Rollback if Azure was enabled
                if ($azureEnabled) {
                    try {
                        $azureService->disableUser($azureId);
                        Log::warning('⚠ ROLLBACK: User re-disabled in Azure AD');

                        return response()->json([
                            'success' => false,
                            'message' => 'Database update failed. User remains disabled (rollback successful).',
                        ], 500);
                    } catch (\Exception $rollbackError) {
                        Log::critical('✗ ROLLBACK FAILED: Manual intervention required!', [
                            'user_id' => $user->Id,
                            'azure_id' => $azureId,
                        ]);

                        return response()->json([
                            'success' => false,
                            'message' => 'CRITICAL: User enabled in Azure but DB update failed. Contact IT immediately.',
                        ], 500);
                    }
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update database: ' . $dbError->getMessage(),
                ], 500);
            }

            // Log activity
            $this->logUserActivity($user->Id, 'user.enabled', 'User account enabled', [
                'enabled_by' => Auth::user()->name ?? 'system',
                'azure_enabled' => $azureEnabled,
            ]);

            Log::info('=== USER ENABLED SUCCESSFULLY ===');

            $message = '✅ User has been enabled successfully!';
            if ($azureIdUpdated) $message .= ' Azure ID was corrected.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'reload' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Unexpected error: ' . $e->getMessage());
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
     * Provision user to all assigned applications using master SP
     * 
     * This SP automatically:
     * - Detects all modules assigned to user in Azure_Application_UserMapping
     * - Calls appropriate individual SPs (D365, SCM, BIZ, FIT)
     * - Auto-activates user if ANY provisioning succeeds
     * - Returns consolidated JSON results
     * 
     * CRITICAL: @AzureUserID parameter expects Azure_Users.Id (database PK), NOT AzureId!
     * 
     * @param string $userId - Azure_Users.Id (database primary key)
     * @return array ['success' => bool, 'results' => array, 'message' => string, 'activated' => bool]
     */
    private function provisionUserToApplications(string $userId): array
    {
        try {

            Log::info('→ Calling sp_Azure_UserCreationorUpdate (Master SP)', [
                'user_id' => $userId,
            ]);

            $spResult = DB::connection('sqlsrv')->select("
            SET NOCOUNT ON;
            DECLARE @Remark VARCHAR(MAX);
            
            EXEC sp_Azure_UserCreationorUpdate 
                @AzureUserID = ?,
                @sp_Remark = @Remark OUTPUT;
            
            SELECT @Remark as Remark;
        ", [$userId]);

            $remarkJson = $spResult[0]->Remark ?? null;

            if (empty($remarkJson)) {
                Log::warning('Master SP returned empty results', [
                    'user_id' => $userId,
                ]);

                return [
                    'success' => false,
                    'results' => [],
                    'message' => 'No applications provisioned (empty response)',
                    'activated' => false,
                ];
            }

            // ========================================================================
            // Parse invalid JSON format
            // ========================================================================
            $results = [];

            try {
                $cleanedJson = trim($remarkJson);
                $cleanedJson = preg_replace('/[\x00-\x1F\x7F]/u', '', $cleanedJson);

                if (substr($cleanedJson, 0, 1) === '{' && strpos($cleanedJson, '}{') !== false) {
                    $objects = preg_split('/\}\s*\{/', $cleanedJson);

                    $objects = array_map(function ($obj, $index) use ($objects) {
                        $obj = trim($obj);
                        if ($index > 0 && substr($obj, 0, 1) !== '{') {
                            $obj = '{' . $obj;
                        }
                        if ($index < count($objects) - 1 && substr($obj, -1) !== '}') {
                            $obj = $obj . '}';
                        }
                        return $obj;
                    }, $objects, array_keys($objects));

                    foreach ($objects as $objString) {
                        $parsed = json_decode($objString, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                            $results[] = $parsed;
                        } else {
                            Log::warning('Failed to parse individual JSON object', [
                                'object' => substr($objString, 0, 200),
                                'error' => json_last_error_msg(),
                            ]);
                        }
                    }
                } else {
                    $parsed = json_decode($cleanedJson, true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        $results = is_array($parsed) && isset($parsed[0]) ? $parsed : [$parsed];
                    } else {
                        throw new \Exception('Invalid JSON format: ' . json_last_error_msg());
                    }
                }

                if (empty($results)) {
                    throw new \Exception('No valid JSON objects found in response');
                }

                Log::info('Successfully parsed Master SP response', [
                    'user_id' => $userId,
                    'objects_parsed' => count($results),
                    'results' => $results,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to parse Master SP JSON', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                    'raw_response' => substr($remarkJson, 0, 500),
                ]);

                return [
                    'success' => false,
                    'results' => [],
                    'message' => 'Failed to parse provisioning results: ' . $e->getMessage(),
                    'activated' => false,
                ];
            }

            // ❌ DELETE LINES ~2093-2113 (duplicate old code below)
            // Don't keep this old code:
            // $results = json_decode($remarkJson, true);
            // if (json_last_error() !== JSON_ERROR_NONE) { ... }

            // ✅ Continue with existing logic
            $anySucceeded = collect($results)
                ->where('Status', '!=', 'Failed')
                ->isNotEmpty();

            $wasActivated = DB::connection('sqlsrv')
                ->table('Azure_Users')
                ->where('Id', $userId)
                ->value('Status') === 'Active';

            Log::info('✓ Master SP provisioning completed', [
                'user_id' => $userId,
                'any_succeeded' => $anySucceeded,
                'was_activated' => $wasActivated,
                'results_count' => count($results),
            ]);

            return [
                'success' => $anySucceeded,
                'results' => $results,
                'message' => $this->buildProvisioningSummary($results),
                'activated' => $wasActivated,
            ];
        } catch (\Exception $e) {
            Log::error('Master SP provisioning failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'results' => [],
                'message' => 'Provisioning failed: ' . $e->getMessage(),
                'activated' => false,
            ];
        }
    }

    /**
     * Parse Master SP JSON response (handles invalid multi-object format)
     */
    private function parseMasterSPResponse(string $remarkJson): array
    {
        $cleanedJson = trim($remarkJson);
        $cleanedJson = preg_replace('/[\x00-\x1F\x7F]/u', '', $cleanedJson);

        Log::debug('Parsing Master SP JSON', [
            'original_length' => strlen($remarkJson),
            'cleaned_length' => strlen($cleanedJson),
            'first_100_chars' => substr($cleanedJson, 0, 100),
        ]);

        // ✅ FIX: Check for concatenated JSON objects (}{)
        if (substr($cleanedJson, 0, 1) === '{' && strpos($cleanedJson, '},{') !== false) {
            // Multiple JSON objects concatenated - wrap in array brackets
            $wrappedJson = '[' . $cleanedJson . ']';

            Log::debug('Detected concatenated JSON objects - wrapping in array', [
                'wrapped_json_preview' => substr($wrappedJson, 0, 150),
            ]);

            $parsed = json_decode($wrappedJson, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                Log::info('✓ Successfully parsed concatenated JSON objects', [
                    'object_count' => count($parsed),
                ]);
                return $parsed;
            } else {
                Log::error('Failed to parse wrapped JSON', [
                    'json_error' => json_last_error_msg(),
                    'wrapped_preview' => substr($wrappedJson, 0, 300),
                ]);
                throw new \Exception('Invalid JSON format after wrapping: ' . json_last_error_msg());
            }
        } else {
            // Single JSON object or already valid array
            $parsed = json_decode($cleanedJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON format: ' . json_last_error_msg());
            }

            return is_array($parsed) && isset($parsed[0]) ? $parsed : [$parsed];
        }
    }

    /**
     * Build human-readable summary from Master SP results
     */
    private function buildProvisioningSummary(array $results): string
    {
        if (empty($results)) {
            return 'No applications provisioned';
        }

        $successful = [];
        $failed = [];

        foreach ($results as $result) {
            // ✅ FIX: Normalize both old and new SP formats
            // Old format: ['success' => bool, 'message' => string]
            // New format: ['Application' => string, 'Status' => string, 'Remark' => string]

            if (isset($result['Application'])) {
                // New Master SP format (current)
                $app = $result['Application'];
                $status = $result['Status'] ?? 'Unknown';
                $remark = $result['Remark'] ?? 'No details';

                // ✅ NORMALIZE: Check Status (case-insensitive) instead of 'success' key
                if (strtolower($status) === 'success') {
                    $successful[] = "{$app}: {$remark}";
                } else {
                    $failed[] = "{$app}: {$remark}";
                }
            } elseif (isset($result['success'])) {
                // Old individual SP format (fallback for legacy code)
                $success = $result['success'];
                $message = $result['message'] ?? 'No message';

                if ($success) {
                    $successful[] = $message;
                } else {
                    $failed[] = $message;
                }
            } else {
                // ✅ SAFETY: Handle unexpected format
                $failed[] = 'Unknown result format: ' . json_encode($result);
            }
        }

        $summary = [];
        $totalCount = count($successful) + count($failed);
        $successCount = count($successful);

        $summary[] = "Provisioning: {$successCount}/{$totalCount} successful";

        if (!empty($successful)) {
            foreach ($successful as $item) {
                $summary[] = "✓ {$item}";
            }
        }

        if (!empty($failed)) {
            foreach ($failed as $item) {
                $summary[] = "⚠ {$item}";
            }
        }

        return "\n\n" . implode("\n", $summary);
    }

    /**
     * Provision user to external systems
     * Uses master SP if enabled, falls back to individual SPs
     * 
     * @param string $userId - Azure_Users.Id (database PK)
     * @param array $validatedAssignments - Module assignments
     * @return array ['success' => bool, 'results' => array, 'message' => string, 'method' => string]
     */
    private function provisionUser(string $userId, array $validatedAssignments): array
    {
        $useMasterSP = config('azure.use_master_provisioning_sp', false);

        if ($useMasterSP) {
            Log::info('→ Using MASTER SP for provisioning', ['user_id' => $userId]);

            try {
                $result = $this->provisionUserToApplications($userId);
                $result['method'] = 'master_sp';

                // ✅ SAFETY: Only build summary if results exist
                if (!empty($result['results'])) {
                    $result['message'] = $this->buildProvisioningSummary($result['results']);
                } else {
                    $result['message'] = "\n\nProvisioning: No results returned from stored procedure";
                }

                Log::info('✓ Master SP provisioning completed', [
                    'success' => $result['success'],
                    'activated' => $result['activated'],
                ]);

                return $result;
            } catch (\Exception $e) {
                Log::error('✗ Master SP FAILED - Falling back to individual SPs', [
                    'error' => $e->getMessage(),
                ]);

                // FALLBACK: Use individual SPs
                return $this->provisionUserIndividual($userId, $validatedAssignments);
            }
        } else {
            Log::info('→ Using INDIVIDUAL SPs for provisioning (config disabled)', [
                'user_id' => $userId,
            ]);

            return $this->provisionUserIndividual($userId, $validatedAssignments);
        }
    }

    /**
     * Provision user using individual SPs (legacy method)
     * 
     * CRITICAL: All SPs expect @AzureUserID = Azure_Users.Id (database PK), NOT AzureId column!
     * 
     * @param string $userId - Azure_Users.Id (database primary key)
     * @param array $validatedAssignments
     * @return array
     */
    private function provisionUserIndividual(string $userId, array $validatedAssignments): array
    {
        $results = [];
        $anySucceeded = false;

        // Extract unique modules
        $assignedModules = collect($validatedAssignments)
            ->pluck('application_name')
            ->unique()
            ->map(fn($name) => strtoupper(trim($name)))
            ->filter()
            ->values();

        Log::info('→ Provisioning via individual SPs', [
            'user_id' => $userId,
            'modules' => $assignedModules->toArray(),
        ]);

        // ========================================================================
        // D365 LIVE
        // ========================================================================
        if ($assignedModules->contains('D365 LIVE')) {
            try {
                Log::info('  → Calling sp_Azure_D365UserCreation', ['user_id' => $userId]);

                $d365Result = DB::connection('sqlsrv')->select('
                SET NOCOUNT ON;
                DECLARE @Status VARCHAR(15), @Remark VARCHAR(200);
                EXEC sp_Azure_D365UserCreation
                    @AzureUserID = ?,
                    @Status = @Status OUTPUT,
                    @Remark = @Remark OUTPUT;
                SELECT @Status as Status, @Remark as Remark;
            ', [$userId]);

                $status = $d365Result[0]->Status ?? 'Failed';
                $remark = $d365Result[0]->Remark ?? 'Unknown error';

                $results[] = [
                    'Application' => 'D365 LIVE',
                    'Status' => $status,
                    'Remark' => $remark,
                ];

                if ($status === 'Success') {
                    $anySucceeded = true;
                }

                Log::info($status === 'Success' ? '  ✓ D365 provisioned' : '  ⚠ D365 failed', [
                    'status' => $status,
                    'remark' => $remark,
                ]);
            } catch (\Exception $e) {
                $results[] = [
                    'Application' => 'D365 LIVE',
                    'Status' => 'Failed',
                    'Remark' => 'Exception: ' . $e->getMessage(),
                ];

                Log::error('  ✗ D365 provisioning error', ['error' => $e->getMessage()]);
            }
        }

        // ========================================================================
        // OPS/SCM
        // ========================================================================
        if ($assignedModules->contains('OPS') || $assignedModules->contains('OPS UAT') || $assignedModules->contains('OPSUAT')) {
            try {
                Log::info('  → Calling sp_Azure_SCMUserCreation', ['user_id' => $userId]);

                // ✅ PRE-VALIDATION: Check if SCMResourceID exists in Cust_Resource
                $user = DB::connection('sqlsrv')->table('Azure_Users')->where('Id', $userId)->first();

                if (!empty($user->SCMResourceID)) {
                    $resourceExists = DB::connection('sqlsrv')
                        ->table('Cust_Resource')
                        ->where('Id', $user->SCMResourceID)
                        ->exists();

                    if (!$resourceExists) {
                        Log::warning('SCMResourceID orphaned - clearing it before provisioning', [
                            'user_id' => $userId,
                            'orphaned_resource_id' => $user->SCMResourceID,
                        ]);

                        // Clear orphaned IDs to force fresh creation
                        DB::connection('sqlsrv')->table('Azure_Users')
                            ->where('Id', $userId)
                            ->update([
                                'SCMResourceID' => null,
                                'SCMUserID' => null,
                                'SCMLoginId' => null,
                            ]);
                    }
                }

                $scmResult = DB::connection('sqlsrv')->select("
            SET NOCOUNT ON;
            DECLARE @Status VARCHAR(15), @Remark VARCHAR(200);
            EXEC sp_Azure_SCMUserCreation
                @AzureUserID = ?,
                @Status = @Status OUTPUT,
                @Remark = @Remark OUTPUT;
            SELECT @Status as Status, @Remark as Remark;
        ", [$userId]);

                $status = $scmResult[0]->Status ?? 'Failed';
                $remark = $scmResult[0]->Remark ?? 'Unknown error';

                $results[] = [
                    'Application' => 'OPS',
                    'Status' => $status,
                    'Remark' => $remark,
                ];

                if (strtoupper($status) === 'SUCCESS') {
                    $anySucceeded = true;
                }

                Log::info(strtoupper($status) === 'SUCCESS' ? '  ✓ OPS provisioned' : '  ⚠ OPS failed', [
                    'status' => $status,
                    'remark' => $remark,
                ]);
            } catch (\Exception $e) {
                $results[] = [
                    'Application' => 'OPS',
                    'Status' => 'Failed',
                    'Remark' => 'Exception: ' . $e->getMessage(),
                ];

                Log::error('  ✗ OPS provisioning error', ['error' => $e->getMessage()]);
            }
        }

        // ========================================================================
        // BIZ PRO
        // ========================================================================
        if ($assignedModules->contains('BIZ PRO') || $assignedModules->contains('FITSYS')) {
            try {
                $bizApp = DB::connection('sqlsrv')
                    ->table('Azure_SoftwareMaster')
                    ->where(function ($q) {
                        $q->where('Name', 'LIKE', '%FITSYS%')->orWhere('Name', 'LIKE', '%BIZ%');
                    })
                    ->first();

                if ($bizApp) {
                    Log::info('  → Calling sp_Azure_FITSYSUserCreation (BIZ PRO)', [
                        'user_id' => $userId,
                        'app_id' => $bizApp->ApplicationId,
                    ]);

                    $bizResult = DB::connection('sqlsrv')->select("
                SET NOCOUNT ON;
                DECLARE @Status VARCHAR(15), @Remark VARCHAR(200);
                EXEC sp_Azure_FITSYSUserCreation
                    @AzureUserID = ?,
                    @ApplicationID = ?,
                    @Status = @Status OUTPUT,
                    @Remark = @Remark OUTPUT;
                SELECT @Status as Status, @Remark as Remark;
            ", [$userId, $bizApp->ApplicationId]);

                    $status = $bizResult[0]->Status ?? 'Failed';
                    $remark = $bizResult[0]->Remark ?? 'Unknown error';

                    $results[] = [
                        'Application' => 'BIZ PRO',
                        'Status' => $status,
                        'Remark' => $remark,
                    ];

                    if (strtolower($status) === 'success') {
                        $anySucceeded = true;
                    }

                    Log::info(strtolower($status) === 'success' ? '  ✓ BIZ PRO provisioned' : '  ⚠ BIZ PRO failed', [
                        'status' => $status,
                        'remark' => $remark,
                    ]);
                } else {
                    $results[] = [
                        'Application' => 'BIZ PRO',
                        'Status' => 'Failed',
                        'Remark' => 'Application not found in Azure_SoftwareMaster',
                    ];

                    Log::error('  ✗ BIZ PRO application not found');
                }
            } catch (\Exception $e) {
                $results[] = [
                    'Application' => 'BIZ PRO',
                    'Status' => 'Failed',
                    'Remark' => 'Exception: ' . $e->getMessage(),
                ];

                Log::error('  ✗ BIZ PRO provisioning error', ['error' => $e->getMessage()]);
            }
        }

        // ========================================================================
        // FIT Express
        // ========================================================================
        if ($assignedModules->contains('FIT EXPRESS')) {
            try {
                $fitApp = DB::connection('sqlsrv')
                    ->table('Azure_SoftwareMaster')
                    ->where('Name', 'LIKE', '%FIT Express%')
                    ->first();

                if ($fitApp) {
                    Log::info('  → Calling sp_Azure_FITSYSUserCreation (FIT Express)', [
                        'user_id' => $userId,
                        'app_id' => $fitApp->ApplicationId,
                    ]);

                    $fitResult = DB::connection('sqlsrv')->select("
                SET NOCOUNT ON;
                DECLARE @Status VARCHAR(15), @Remark VARCHAR(200);
                EXEC sp_Azure_FITSYSUserCreation
                    @AzureUserID = ?,
                    @ApplicationID = ?,
                    @Status = @Status OUTPUT,
                    @Remark = @Remark OUTPUT;
                SELECT @Status as Status, @Remark as Remark;
            ", [$userId, $fitApp->ApplicationId]);

                    $status = $fitResult[0]->Status ?? 'Failed';
                    $remark = $fitResult[0]->Remark ?? 'Unknown error';

                    $results[] = [
                        'Application' => 'FIT Express',
                        'Status' => $status,
                        'Remark' => $remark,
                    ];

                    if (strtolower($status) === 'success') {
                        $anySucceeded = true;
                    }

                    Log::info(strtolower($status) === 'success' ? '  ✓ FIT Express provisioned' : '  ⚠ FIT Express failed', [
                        'status' => $status,
                        'remark' => $remark,
                    ]);
                } else {
                    $results[] = [
                        'Application' => 'FIT Express',
                        'Status' => 'Failed',
                        'Remark' => 'Application not found in Azure_SoftwareMaster',
                    ];

                    Log::error('  ✗ FIT Express application not found');
                }
            } catch (\Exception $e) {
                $results[] = [
                    'Application' => 'FIT Express',
                    'Status' => 'Failed',
                    'Remark' => 'Exception: ' . $e->getMessage(),
                ];

                Log::error('  ✗ FIT Express provisioning error', ['error' => $e->getMessage()]);
            }
        }

        Log::info('✓ Individual SP provisioning completed', [
            'user_id' => $userId,
            'total_apps' => count($results),
            'any_succeeded' => $anySucceeded,
        ]);

        return [
            'success' => $anySucceeded,
            'results' => $results,
            'message' => $this->buildProvisioningSummary($results),
            'activated' => false, // Individual SPs don't auto-activate
            'method' => 'individual_sps',
        ];
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
                    // ====================================================================
                    // ✅ NEW: Validation 1: CRITICAL - Check AzureId exists before activation
                    // ====================================================================
                    if (empty($user->AzureId) || !$this->isValidUuid($user->AzureId)) {
                        Log::warning('Skipping activation - AzureId missing or invalid', [
                            'user_id' => $user->Id,
                            'upn' => $user->userPrincipalName,
                            'azure_id' => $user->AzureId ?? 'NULL',
                        ]);

                        $stats['skipped']++;
                        $stats['errors'][] = [
                            'user' => $user->userPrincipalName ?? $user->Id,
                            'error' => 'AzureId missing or invalid - user not provisioned to Azure AD',
                        ];

                        continue; // Skip this user
                    }

                    Log::info('✓ AzureId validated for activation', [
                        'user_id' => $user->Id,
                        'azure_id' => $user->AzureId,
                    ]);

                    // ====================================================================
                    // Validation 2: Check eligibility for activation
                    // ====================================================================
                    $eligible = $this->checkUserEligibility($user);

                    if ($eligible['can_activate']) {

                        // ====================================================================
                        // D365 PROVISIONING (Before Activation)
                        // ====================================================================

                        $needsD365 = !empty($user->AzureId) && empty($user->D365UserID);

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
                                ', [$user->Id]);

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
                            if (empty($user->AzureId)) {
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

            if (!empty($stats['errors'])) {
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
        if (empty($user->AzureId)) {  // ✅ FIXED - removed isProvisionedToAzure() call
            return [
                'can_activate' => false,
                'reason' => 'Not provisioned to Azure AD',
            ];
        }

        // Check 2: External system synchronization
        $syncedSystems = [];
        if (!empty($user->OdooUserId)) {
            $syncedSystems[] = 'Odoo';
        }
        if (!empty($user->SCMUserID)) {
            $syncedSystems[] = 'SCM';
        }
        if (!empty($user->BizUserID)) {
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

        // ========================================================================
        // Check if this is Windows Sign-In (special case - no roles/functions needed)
        // ========================================================================
        $isWindowsSignIn = isset($assignment['application_name']) &&
            in_array(strtoupper(trim($assignment['application_name'])), [
                'WINDOWS SIGN-IN',
                'WINDOWS SIGN IN',
                'WINDOWS SIGNIN'
            ]);

        // ========================================================================
        // REQUIRED FIELDS (Always needed)
        // ========================================================================
        $requiredFields = ['module_id', 'application_id', 'location_id', 'location_code'];

        foreach ($requiredFields as $field) {
            if (empty($assignment[$field])) {
                $errors[] = "Missing required field: {$field}";
            }
        }

        // ========================================================================
        // ✅ FIX: Handle 1-to-1 method validation
        // Windows Sign-In: No role/function validation needed
        // Other modules: Must have EITHER role OR function
        // ========================================================================

        if (!$isWindowsSignIn) {
            $hasRole = !empty($assignment['role_id']) && !empty($assignment['role_code']);
            $hasFunction = !empty($assignment['function_id']) && !empty($assignment['function_code']);

            // At least ONE must be present (for non-Windows Sign-In modules)
            if (!$hasRole && !$hasFunction) {
                $errors[] = "Must have either role (role_id + role_code) OR function (function_id + function_code)";
            }
        } else {
            // Windows Sign-In: Set flags to false (no validation needed)
            $hasRole = false;
            $hasFunction = false;
        }

        if (!empty($errors)) {
            return [
                'valid' => false,
                'data' => null,
                'error' => "Assignment {$index}: " . implode(', ', $errors),
            ];
        }

        // ========================================================================
        // GUID format validation (only for non-null values)
        // ========================================================================
        $guidFields = [
            'module_id' => $assignment['module_id'],
            'application_id' => $assignment['application_id'],
            'location_id' => $assignment['location_id'],
        ];

        // Add role_id only if present
        if ($hasRole) {
            $guidFields['role_id'] = $assignment['role_id'];
        }

        // Add function_id only if present
        if ($hasFunction) {
            $guidFields['function_id'] = $assignment['function_id'];
        }

        foreach ($guidFields as $field => $value) {
            if (!$this->isValidGuid($value)) {
                return [
                    'valid' => false,
                    'data' => null,
                    'error' => "Assignment {$index}: Invalid GUID format for {$field}",
                ];
            }
        }

        // ========================================================================
        // Sanitize data - set missing fields to NULL
        // ========================================================================
        $sanitized = [
            'module_id' => $assignment['module_id'],
            'application_id' => $assignment['application_id'],
            'location_id' => $assignment['location_id'],
            'location_code' => $assignment['location_code'],
            'role_id' => $hasRole ? $assignment['role_id'] : null,
            'role_code' => $hasRole ? $assignment['role_code'] : null,
            'function_id' => $hasFunction ? $assignment['function_id'] : null,
            'function_code' => $hasFunction ? $assignment['function_code'] : null,
        ];

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

        // ========================================================================
        // ✅ FIX: Verify role by RoleCode instead of Id
        // ========================================================================
        // PROBLEM: The frontend gets role IDs from sp_Azure_UserResourceRole 
        //          which returns the RoleId column.
        //          But Azure_Users_Role table's primary key is Id (different value!)
        // SOLUTION: Match on RoleCode which is consistent across both sources
        // 
        // NOTE: In 1-to-1 method, role can be NULL (for function-only records)
        // ========================================================================
        if (!empty($assignment['role_code'])) {
            $roleExists = DB::connection('sqlsrv')
                ->table('Azure_Users_Role')
                ->where('RoleCode', $assignment['role_code'])
                ->where('IsActive', 1)
                ->where('IsDeleted', 0)
                ->exists();

            if (! $roleExists) {
                $errors[] = "Role not found or inactive: {$assignment['role_code']}";
            }
        }

        // ========================================================================
        // ✅ FIX: Verify function by Code
        // ========================================================================
        // NOTE: Functions come from sp_Azure_UserResourcefunction (not from a table)
        // The stored procedure returns: ID, Code, Name
        // We could cache and validate against SP output, but for now we'll skip
        // validation since the frontend already validates against the SP output
        // ========================================================================
        if (!empty($assignment['function_code'])) {
            // Functions are validated by the frontend against sp_Azure_UserResourcefunction
            // No table exists to validate against
            // The function will fail at provisioning time if invalid
            Log::debug('Function assignment detected', [
                'function_code' => $assignment['function_code'],
                'function_id' => $assignment['function_id'] ?? null,
            ]);
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

            // ✅ FIXED: Use $user->Id instead of $user->AzureId
            // sp_Azure_SCMUserCreation expects @AzureUserID = Azure_Users.Id (database PK)
            $result = DB::connection('sqlsrv')->select(
                'EXEC sp_Azure_SCMUserCreation ?, @Status = ? OUTPUT, @Remark = ? OUTPUT',
                [
                    $user->Id,  // ✅ CORRECT - pass database ID, not AzureId
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

            // ✅ FIX: Use $user->Id instead of $user->AzureId
            // Check D365 Live access
            $hasD365 = DB::connection('sqlsrv')
                ->table('Azure_Application_UserMapping')
                ->where('AzureUserID', $user->Id)  // ← Changed from $user->AzureId
                ->where('ApplicationName', 'D365 LIVE')
                ->where('IsActive', 1)
                ->where('IsDelete', 0)
                ->exists();

            // Check OPS/SCM access
            $hasOPS = DB::connection('sqlsrv')
                ->table('Azure_Application_UserMapping')
                ->where('AzureUserID', $user->Id)  // ← Changed from $user->AzureId
                ->where('ApplicationName', 'LIKE', '%OPS%')
                ->where('IsActive', 1)
                ->where('IsDelete', 0)
                ->exists();

            // Check BIZ PRO access
            $hasBIZ = !empty($user->BizUserID);

            // Check FIT Express access
            $hasFIT = DB::connection('sqlsrv')
                ->table('Azure_Application_UserMapping')
                ->where('AzureUserID', $user->Id)  // ← Changed from $user->AzureId
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

            // ================================================================
            // Auto-include all accessible applications when Azure AD is selected for disable
            // ================================================================
            if ($includesAzureAD && $action === 'Disable') {
                Log::info('→ Azure AD disable detected - auto-including all accessible applications');

                $allApps = ['d365', 'ops', 'biz', 'fit'];
                $addedApps = [];

                foreach ($allApps as $app) {
                    if (!in_array($app, $applications)) {
                        $hasAccess = $this->checkUserHasApplicationAccess($user, $app);

                        if ($hasAccess) {
                            $applications[] = $app;
                            $addedApps[] = $app;
                            Log::info("  ✓ Auto-added {$app} to disable list (user has access)");
                        } else {
                            Log::info("  ⊘ Skipped {$app} (user has no access)");
                        }
                    }
                }

                if (!empty($addedApps)) {
                    Log::info('Auto-added applications:', [
                        'count' => count($addedApps),
                        'apps' => $addedApps,
                    ]);
                }
            }

            // ================================================================
            // CRITICAL: Validate AzureId exists before proceeding
            // ================================================================
            if (!$includesAzureAD && !empty($applications)) {
                // Only validate AzureId if we're processing non-Azure applications
                if (empty($user->AzureId) || !$this->isValidUuid($user->AzureId)) {
                    Log::error('Cannot process - AzureId is missing or invalid', [
                        'user_id' => $user->Id,
                        'azure_id' => $user->AzureId,
                        'upn' => $user->userPrincipalName,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot process applications: User Azure ID is missing or invalid. Please ensure the user is properly provisioned to Azure AD.',
                    ], 400);
                }
            }

            // Build module list for JSON payload
            $moduleList = [];

            // Map application codes to module names (exact case match required)
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

            // ================================================================
            // Step 1: Handle Azure AD if included
            // ================================================================
            if ($includesAzureAD) {
                Log::info('→ Step 1: Processing Azure AD...');

                try {
                    if ($action === 'Disable') {
                        $azureResponse = $this->performAzureDisable($user);
                    } else {
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

            // ================================================================
            // Step 2: Handle other applications via stored procedure
            // ================================================================
            if (!empty($moduleList)) {
                Log::info('→ Step 2: Processing applications via SP...', [
                    'module_count' => count($moduleList),
                    'modules' => array_column($moduleList, 'Module'),
                ]);

                try {
                    // Build simplified JSON input (no outer wrapper)
                    $inputJson = [
                        'OperationType' => $action . ' Users',
                        'SubmittedDatetime' => now()->format('YmdHis'),
                        'SubmittedBy' => Auth::user()->name ?? 'system',
                        'Action' => $action,
                        'UserID' => $user->Id, // Use AzureId for consistency with your example
                        'Modulelist' => $moduleList,
                    ];

                    $inputJsonString = json_encode($inputJson);

                    Log::info('Calling sp_Azure_UserDisable', [
                        'input_json' => $inputJsonString,
                        'user_azure_id' => $user->AzureId,
                        'user_db_id' => $user->Id,
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

                    Log::info('SP Raw Response', [
                        'raw_output' => $outputJson,
                        'raw_length' => strlen($outputJson ?? ''),
                        'raw_hex' => bin2hex(substr($outputJson ?? '', 0, 100)),
                        'first_100_chars' => substr($outputJson ?? '', 0, 100),
                    ]);

                    // Parse output JSON
                    if ($outputJson) {
                        // Clean the JSON first
                        $cleanedJson = trim($outputJson);
                        $cleanedJson = preg_replace('/[\x00-\x1F\x7F]/u', '', $cleanedJson);

                        // Wrap multiple JSON objects in array brackets if needed
                        if (substr($cleanedJson, 0, 1) === '{' && strpos($cleanedJson, '},{') !== false) {
                            $cleanedJson = '[' . $cleanedJson . ']';
                            Log::info('Wrapped multiple JSON objects in array', ['wrapped' => true]);
                        }

                        Log::info('Cleaned JSON', [
                            'cleaned' => substr($cleanedJson, 0, 200) . '...',
                            'original_equals_cleaned' => ($outputJson === $cleanedJson),
                        ]);

                        $output = json_decode($cleanedJson, true);

                        if (json_last_error() === JSON_ERROR_NONE) {
                            Log::info('JSON parsed successfully', ['output' => $output]);

                            // Handle array of module results from SP
                            if (is_array($output) && isset($output[0]['status'])) {
                                Log::info('Detected array of module results');

                                foreach ($output as $moduleResult) {
                                    $remark = $moduleResult['Remark'] ?? '';

                                    // Extract module name from remark (e.g., "<< BIZ PRO >>")
                                    if (preg_match('/<< (.+?) >>/', $remark, $matches)) {
                                        $moduleName = trim($matches[1]);

                                        $results['applications'][$moduleName] = [
                                            'success' => strtolower($moduleResult['status']) === 'success',
                                            'message' => $remark,
                                        ];
                                    }
                                }
                            } else {
                                // Unknown format - mark all modules as failed
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
     * Helper: Check if user has access to a specific application
     * 
     * @param AzureUser $user
     * @param string $app - Application code (d365, ops, biz, fit)
     * @return bool
     */
    private function checkUserHasApplicationAccess(AzureUser $user, string $app): bool
    {
        switch ($app) {
            case 'd365':
                return DB::connection('sqlsrv')
                    ->table('Azure_Application_UserMapping')
                    ->where('AzureUserID', $user->Id)
                    ->where('ApplicationName', 'D365 LIVE')
                    ->where('IsActive', 1)
                    ->where('IsDelete', 0)
                    ->exists();

            case 'ops':
                return DB::connection('sqlsrv')
                    ->table('Azure_Application_UserMapping')
                    ->where('AzureUserID', $user->Id)
                    ->where('ApplicationName', 'LIKE', '%OPS%')
                    ->where('IsActive', 1)
                    ->where('IsDelete', 0)
                    ->exists();

            case 'biz':
                return !empty($user->BizUserID);

            case 'fit':
                return DB::connection('sqlsrv')
                    ->table('Azure_Application_UserMapping')
                    ->where('AzureUserID', $user->Id)
                    ->where('ApplicationName', 'LIKE', '%FIT Express%')
                    ->where('IsActive', 1)
                    ->where('IsDelete', 0)
                    ->exists();

            default:
                return false;
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

        // ✅ IMPROVED: Build more detailed message with action verb
        $actionVerb = strtolower($action) . 'd'; // "disabled" or "enabled"
        $message = "{$action} operation completed: {$successCount}/{$totalCount} successful";

        if (!empty($successful)) {
            $message .= "\n\n✅ Successfully {$actionVerb}:\n• " . implode("\n• ", $successful);
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


    /**
     * Repair AzureId by searching with alternate email patterns
     */
    private function findAndRepairAzureId(AzureUser $user): ?string
    {
        $azureService = app(\App\Services\AzureGraphService::class);

        // Try 1: Exact UPN match
        try {
            $azureUser = $azureService->findUserByUPN($user->userPrincipalName);
            if ($azureUser && isset($azureUser['id'])) {
                return trim($azureUser['id']);
            }
        } catch (\Exception $e) {
        }

        // Try 2: Search by display name
        try {
            $searchResults = $azureService->searchUsers($user->displayName);
            if (!empty($searchResults) && isset($searchResults[0]['id'])) {
                return trim($searchResults[0]['id']);
            }
        } catch (\Exception $e) {
        }

        return null;
    }
}
