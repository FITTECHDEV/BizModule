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
            $status = request('status', 'active');
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
                            ->orWhereRaw('LOWER(department) LIKE ?', ['% ' . $searchLower . ' %'])
                            ->orWhereRaw('LOWER(department) LIKE ?', [$searchLower . ' %'])
                            ->orWhereRaw('LOWER(department) LIKE ?', ['% ' . $searchLower]);
                    } elseif ($searchType === 'all') {
                        // All fields search (with department priority)
                        $q->whereRaw('LOWER(department) = ?', [$searchLower])
                            ->orWhereRaw('LOWER(department) LIKE ?', ['% ' . $searchLower . ' %'])
                            ->orWhereRaw('LOWER(department) LIKE ?', [$searchLower . ' %'])
                            ->orWhereRaw('LOWER(department) LIKE ?', ['% ' . $searchLower])
                            ->orWhereRaw('LOWER(companyName) LIKE ?', ['%' . $searchLower . '%'])
                            ->orWhereRaw('LOWER(displayName) LIKE ?', ['%' . $searchLower . '%'])
                            ->orWhereRaw('LOWER(officeLocation) LIKE ?', ['%' . $searchLower . '%'])
                            ->orWhere('EmployeCode', 'LIKE', "%{$search}%");

                        if (!str_contains($search, '@')) {
                            $q->orWhere('mail', 'LIKE', "{$search}%@%")->orWhere(
                                'userPrincipalName',
                                'LIKE',
                                "{$search}%@%",
                            );
                        }
                    } elseif ($searchType === 'name') {
                        $q->whereRaw('LOWER(displayName) LIKE ?', ['%' . $searchLower . '%']);
                    } elseif ($searchType === 'company') {
                        $q->whereRaw('LOWER(companyName) LIKE ?', ['%' . $searchLower . '%']);
                    } elseif ($searchType === 'email') {
                        if (!str_contains($search, '@')) {
                            $q->where('mail', 'LIKE', "{$search}%@%")->orWhere(
                                'userPrincipalName',
                                'LIKE',
                                "{$search}%@%",
                            );
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

            return view(
                'azure-management.dashboard.index',
                compact('stats', 'users', 'departments', 'companies', 'locations'),
            );
        } catch (\Exception $e) {
            Log::error('Azure Dashboard Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to load dashboard: ' . $e->getMessage()]);
        }
    }

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

            return response()->json(
                [
                    'total' => 0,
                    'active' => 0,
                    'pending' => 0,
                    'inactive' => 0,
                ],
                500,
            );
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
            $usersByStatus = AzureUser::selectRaw('Status, count(*) as count')->groupBy('Status')->get();

            return response()->json([
                'users_by_location' => $usersByLocation,
                'users_by_status' => $usersByStatus,
            ]);
        } catch (\Exception $e) {
            Log::error('Azure Dashboard Reports Error: ' . $e->getMessage());

            return response()->json(
                [
                    'users_by_location' => [],
                    'users_by_status' => [],
                ],
                500,
            );
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

    /**
     * Get users for AJAX requests (No partials needed)
     */
    public function getUsers(Request $request)
    {
        try {
            $search = $request->input('search', '');
            $status = $request->input('status', 'active'); // CHANGE: Default to 'active'
            $perPage = $request->input('per_page', 15);

            $query = AzureUser::query();

            if ($search) {
                $searchLower = strtolower($search);
                $query->where(function ($q) use ($search, $searchLower) {
                    $q->whereRaw('LOWER(displayName) LIKE ?', ['%' . $searchLower . '%'])
                        ->orWhereRaw('LOWER(department) LIKE ?', ['%' . $searchLower . '%'])
                        ->orWhereRaw('LOWER(companyName) LIKE ?', ['%' . $searchLower . '%'])
                        ->orWhere('EmployeCode', 'LIKE', "%{$search}%")
                        ->orWhere('mail', 'LIKE', "%{$search}%")
                        ->orWhere('userPrincipalName', 'LIKE', "%{$search}%");
                });
            }

            if ($status) {
                $query->where('Status', ucfirst(strtolower($status)));
            }

            $users = $query->orderBy('Id', 'desc')->paginate($perPage);

            // Build table HTML - PASS $status to buildUserRow
            $tableRows = '';
            foreach ($users as $user) {
                $tableRows .= $this->buildUserRow($user, $status); // ADD $status parameter
            }

            if ($users->isEmpty()) {
                $tableRows = '<tr><td colspan="9" class="text-center text-muted py-4">
                <i class="fas fa-users fa-3x mb-3"></i>
                <p>No users found</p></td></tr>';
            }

            // Build pagination HTML
            $paginationHtml = $this->buildPagination($users);

            return response()->json([
                'success' => true,
                'table' => $tableRows,
                'pagination' => $paginationHtml,
                'total' => $users->total(),
                'from' => $users->firstItem() ?? 0,
                'to' => $users->lastItem() ?? 0,
            ]);
        } catch (\Exception $e) {
            Log::error('Get Users AJAX Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Build user row HTML
     */
    private function buildUserRow($user, $currentStatus = 'active')
    {
        // ADD parameter with default
        $displayName = $user->displayName ?? 'N/A';
        $initials = strtoupper(substr($displayName, 0, 2));
        $email = $user->mail ?? ($user->userPrincipalName ?? 'N/A');
        $loginId = $user->userPrincipalName ?? 'N/A';
        $employeeCode = $user->EmployeCode ?? 'N/A';
        $department = $user->department ?? 'N/A';
        $company = $user->companyName ?? 'N/A';
        $location = $user->officeLocation ?? ($user->city ?? 'N/A');
        $status = $user->Status ?? 'Inactive';

        $badgeClass = match (strtolower($status)) {
            'active' => 'badge-success',
            'pending' => 'badge-warning',
            'inactive' => 'badge-danger',
            default => 'badge-secondary',
        };

        $actions = '';
        if ($user->Id) {
            $viewUrl = route('azure.users.show', $user->Id);
            $editUrl = route('azure.users.edit', $user->Id);
            $isInactive = strtolower($user->Status ?? 'inactive') === 'inactive';
            $isActive = strtolower($user->Status ?? 'inactive') === 'active';
            $userIdEscaped = e($user->Id);
            $displayNameEscaped = e(addslashes($displayName));
            $statusEscaped = e($status);

            // USE the passed $currentStatus parameter instead of request()
            $showManageButton = $isActive && $currentStatus === 'active';

            $editButton = $isInactive
                ? '<button class="btn btn-warning btn-sm btn-disabled" disabled title="Enable user first to edit" style="cursor: not-allowed; opacity: 0.6;"><i class="fas fa-edit"></i></button>'
                : '<a href="' .
                    $editUrl .
                    '" class="btn btn-warning btn-sm" title="Edit User"><i class="fas fa-edit"></i></a>';

            $manageButton = $showManageButton
                ? '<button class="btn btn-danger btn-sm" onclick="openManageAccessModal(\'' .
                    $userIdEscaped .
                    '\', \'' .
                    $displayNameEscaped .
                    '\', \'' .
                    $statusEscaped .
                    '\')" title="Manage Access"><i class="fas fa-cog"></i></button>'
                : '';

            $actions =
                '<div class="btn-group btn-group-sm" role="group">
            <a href="' .
                $viewUrl .
                '" class="btn btn-info btn-sm" title="View User"><i class="fas fa-eye"></i></a>
            ' .
                $editButton .
                '
            ' .
                $manageButton .
                '
            </div>';
        } else {
            $upnEscaped = e($user->userPrincipalName);
            $actions =
                '<button class="btn btn-warning btn-sm" onclick="fixUserRecord(\'' .
                $upnEscaped .
                '\')" title="Fix missing ID"><i class="fas fa-wrench"></i> Fix Record</button>';
        }

        return '<tr>
        <td>
            <div class="d-flex align-items-center">
                <div class="user-avatar mr-2">' .
            $initials .
            '</div>
                <span>' .
            e($displayName) .
            '</span>
            </div>
        </td>
        <td>' .
            e($email) .
            '</td>
        <td>' .
            e($loginId) .
            '</td>
        <td>' .
            e($employeeCode) .
            '</td>
        <td>' .
            e($department) .
            '</td>
        <td>' .
            e($company) .
            '</td>
        <td>' .
            e($location) .
            '</td>
        <td><span class="badge ' .
            $badgeClass .
            '">' .
            e($status) .
            '</span></td>
        <td>' .
            $actions .
            '</td>
        </tr>';
    }

    /**
     * Build pagination HTML
     */
    private function buildPagination($users)
    {
        if (!$users->hasPages()) {
            return '';
        }

        $html =
            '<div class="row align-items-center">
        <div class="col-md-6">
            <span class="text-muted">
                Showing ' .
            ($users->firstItem() ?? 0) .
            ' to ' .
            ($users->lastItem() ?? 0) .
            ' of ' .
            $users->total() .
            ' users
            </span>
        </div>
        <div class="col-md-6">
            <ul class="pagination pagination-sm m-0 float-right">';

        // First button
        $html .= $users->onFirstPage()
            ? '<li class="page-item disabled"><span class="page-link">First</span></li>'
            : '<li class="page-item"><a class="page-link page-nav" href="#" data-page="1">First</a></li>';

        // Previous button
        $html .= $users->onFirstPage()
            ? '<li class="page-item disabled"><span class="page-link">&laquo;</span></li>'
            : '<li class="page-item"><a class="page-link page-nav" href="#" data-page="' .
                ($users->currentPage() - 1) .
                '">&laquo;</a></li>';

        // Page numbers
        foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url) {
            if ($page == $users->currentPage()) {
                $html .= '<li class="page-item active"><span class="page-link">' . $page . '</span></li>';
            } else {
                $html .=
                    '<li class="page-item"><a class="page-link page-nav" href="#" data-page="' .
                    $page .
                    '">' .
                    $page .
                    '</a></li>';
            }
        }

        // Next button
        $html .= $users->hasMorePages()
            ? '<li class="page-item"><a class="page-link page-nav" href="#" data-page="' .
                ($users->currentPage() + 1) .
                '">&raquo;</a></li>'
            : '<li class="page-item disabled"><span class="page-link">&raquo;</span></li>';

        // Last button
        $html .=
            $users->currentPage() == $users->lastPage()
                ? '<li class="page-item disabled"><span class="page-link">Last</span></li>'
                : '<li class="page-item"><a class="page-link page-nav" href="#" data-page="' .
                    $users->lastPage() .
                    '">Last</a></li>';

        $html .= '</ul></div></div>';

        return $html;
    }
}
