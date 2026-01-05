@extends('adminlte::page')

@section('title', 'User Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-user-circle"></i> User Details</h1>
            <p class="text-muted mb-0">View complete user information</p>
        </div>
        <div>
            <a href="{{ route('azure.dashboard.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
            <a href="{{ route('azure.users.edit', $user->Id) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit User
            </a>
        </div>
    </div>
@stop

@section('content')

    @php

        $fitKenyaOffice = DB::connection('sqlsrv')->table('Cust_Office')->where('Code', 'KE')->first();

        // ============================================================================
        // Fetch offices and modules once for use in both cards and modals
        // ============================================================================
        $offices = DB::connection('sqlsrv')
            ->table('Cust_Office')
            ->where('IsActive', 1)
            ->where('IsDeleted', 0)
            ->where('code', '!=', 'ALL')
            ->orderBy('Name')
            ->get();

        $modules = DB::connection('sqlsrv')
            ->table('Azure_SoftwareMaster')
            ->where('IsActive', 1)
            ->where('IsDeleted', 0)
            ->whereNotIn('Name', ['Odoo'])
            ->where('Name', 'NOT LIKE', '%UAT%')
            ->orderBy('Name')
            ->get();
    @endphp

    <div class="row">
        <!-- Left Column - Profile & Personal Info -->
        <div class="col-md-4">
            <!-- Profile Card -->
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <div class="user-avatar-large mx-auto mb-3">
                            {{ strtoupper(substr($user->displayName ?? 'NA', 0, 2)) }}
                        </div>
                        <h3 class="profile-username text-center">{{ $user->displayName ?? 'N/A' }}</h3>
                        <p class="text-muted text-center">{{ $user->jobTitle ?? 'No Job Title' }}</p>

                        @php
                            $status = $user->Status ?? 'Unknown';
                            $badgeClass = match (strtolower($status)) {
                                'active' => 'badge-success',
                                'pending' => 'badge-warning',
                                'inactive' => 'badge-danger',
                                default => 'badge-secondary',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }} badge-lg">{{ $status }}</span>
                    </div>

                    <hr>

                    <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                    <p class="text-muted mb-2">
                        <a href="mailto:{{ $user->mail ?? $user->userPrincipalName }}">
                            {{ $user->mail ?? ($user->userPrincipalName ?? 'N/A') }}
                        </a>
                    </p>

                    <strong><i class="fas fa-phone mr-1"></i> Business Phone</strong>
                    <p class="text-muted mb-2">
                        @if (!empty($user->businessPhones))
                            @foreach (json_decode($user->businessPhones, true) ?? [] as $phone)
                                <a href="tel:{{ $phone }}">{{ $phone }}</a><br>
                            @endforeach
                        @else
                            N/A
                        @endif
                    </p>

                    <strong><i class="fas fa-mobile-alt mr-1"></i> Mobile Phone</strong>
                    <p class="text-muted mb-2">
                        @if ($user->mobilePhone)
                            <a href="tel:{{ $user->mobilePhone }}">{{ $user->mobilePhone }}</a>
                        @else
                            N/A
                        @endif
                    </p>

                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>
                    <p class="text-muted mb-2">
                        {{ $user->officeLocation ?? ($user->city ?? 'N/A') }}
                        @if ($user->country)
                            <br><small>{{ $user->country }}</small>
                        @endif
                    </p>
                </div>
            </div>

            {{-- SCM Base Office Card --}}
            {{-- <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-building"></i> SCM Base Office
                    </h3>
                </div>
                <div class="card-body">
                    <form id="baseOfficeForm">
                        @csrf
                        <div class="form-group">
                            <label for="scm_base_office">
                                SCM Base Office <span class="text-danger">*</span>
                            </label>
                            <select name="scm_base_office" id="scm_base_office" class="form-control" required>
                                @foreach ($offices as $office)
                                    @if ($office->Code == 'KE')
                                        <option value="{{ $office->Code }}" selected>
                                            {{ $office->Name }} (Default)
                                        </option>
                                    @else
                                        <option value="{{ $office->Code }}"
                                            {{ ($user->SCMBaseOfficeCode ?? 'KE') == $office->Code ? 'selected' : '' }}>
                                            {{ $office->Name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="text-muted">Primary office for all SCM operations</small>
                        </div>

                        <button type="button" class="btn btn-warning btn-block" onclick="saveBaseOffice()"
                            id="saveBaseOfficeBtn" style="display: none;">
                            <i class="fas fa-save"></i> Save Base Office
                        </button>
                    </form>
                </div>
            </div> --}}

            {{-- Quick Module Assignment Card --}}
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cubes"></i> Quick Module Assignment
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3" style="font-size: 0.875rem;">
                        Select modules and search for users to assign
                    </p>

                    {{-- Module List with Search/Dropdown --}}
                    <div class="module-assignment-list">
                        {{-- SCM/OPS --}}
                        <div class="module-row mb-3">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="module_scm">
                                        <label class="custom-control-label font-weight-bold" for="module_scm">
                                            SCM
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="row">

                                        <div class="col-md-6">
                                            <select class="form-control form-control-sm">
                                                <option value="">-- Select User --</option>
                                                <option>Brian Munene</option>
                                                <option>Caroline Ibrahim</option>
                                                <option>Luciana Lopez</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-2">

                        {{-- D365 --}}
                        <div class="module-row mb-3">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="module_d365">
                                        <label class="custom-control-label font-weight-bold" for="module_d365">
                                            D365
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="row">

                                        <div class="col-md-6">
                                            <select class="form-control form-control-sm">
                                                <option value="">-- Select User --</option>
                                                <option>Brian Munene</option>
                                                <option>Caroline Ibrahim</option>
                                                <option>Luciana Lopez</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-2">

                        {{-- Odoo --}}
                        <div class="module-row mb-3">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="module_odoo">
                                        <label class="custom-control-label font-weight-bold" for="module_odoo">
                                            Odoo
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="row">

                                        <div class="col-md-6">
                                            <select class="form-control form-control-sm">
                                                <option value="">-- Select User --</option>
                                                <option>Brian Munene</option>
                                                <option>Caroline Ibrahim</option>
                                                <option>Luciana Lopez</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-2">

                        {{-- BIZ --}}
                        <div class="module-row mb-3">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="module_b12">
                                        <label class="custom-control-label font-weight-bold" for="module_b12">
                                            BIZ
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="row">

                                        <div class="col-md-6">
                                            <select class="form-control form-control-sm">
                                                <option value="">-- Select User --</option>
                                                <option>Brian Munene</option>
                                                <option>Caroline Ibrahim</option>
                                                <option>Luciana Lopez</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-2">

                        {{-- FIT Express --}}
                        <div class="module-row mb-3">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="module_fitexpress">
                                        <label class="custom-control-label font-weight-bold" for="module_fitexpress">
                                            FIT Express
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="row">

                                        <div class="col-md-6">
                                            <select class="form-control form-control-sm">
                                                <option value="">-- Select User --</option>
                                                <option>Brian Munene</option>
                                                <option>Caroline Ibrahim</option>
                                                <option>Luciana Lopez</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <button type="button" class="btn btn-success btn-block">
                        <i class="fas fa-save"></i> Assign Selected Modules
                    </button>
                </div>
            </div>


        </div>

        <!-- Right Column - Detailed Information -->
        <div class="col-md-8">
            <!-- Module Assignments Card -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cubes"></i> Module Assignments
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-light">
                            {{ $moduleAssignments->count() }} Module(s)
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @if ($moduleAssignments->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No module assignments found for this user.</p>
                        </div>
                    @else
                        <!-- Accordion for Module Assignments -->
                        <div class="accordion" id="moduleAssignmentsAccordion">
                            @foreach ($moduleAssignments as $index => $module)
                                <div class="card mb-2">
                                    <div class="card-header p-0" id="heading{{ $index }}">
                                        <button
                                            class="btn btn-link btn-block text-left d-flex justify-content-between align-items-center collapsed"
                                            type="button" data-toggle="collapse"
                                            data-target="#collapse{{ $index }}" aria-expanded="false"
                                            aria-controls="collapse{{ $index }}">
                                            <div>
                                                <i class="fas fa-cube mr-2"></i>
                                                <strong>{{ $module['module_name'] }}</strong>
                                            </div>
                                            <div>
                                                <span class="badge badge-primary">{{ $module['locations_count'] }}
                                                    Location(s)</span>
                                                <span class="badge badge-info">{{ $module['roles_count'] }}
                                                    Role(s)</span>
                                                @if ($module['functions_count'] > 0)
                                                    <span class="badge badge-warning">{{ $module['functions_count'] }}
                                                        Function(s)</span>
                                                @endif
                                                <i class="fas fa-chevron-down ml-2"></i>
                                            </div>
                                        </button>
                                    </div>

                                    <div id="collapse{{ $index }}" class="collapse"
                                        aria-labelledby="heading{{ $index }}"
                                        data-parent="#moduleAssignmentsAccordion">
                                        <div class="card-body">
                                            <!-- Horizontal Layout for Locations, Roles, Functions -->
                                            <div class="row">
                                                <!-- Locations Column -->
                                                <div class="col-md-4">
                                                    <div class="module-section">
                                                        <h6 class="section-title">
                                                            <i class="fas fa-map-marker-alt mr-2"></i>Locations
                                                        </h6>
                                                        <div class="section-content">
                                                            @foreach ($module['locations'] as $location)
                                                                <span
                                                                    class="badge badge-primary mr-1 mb-1">{{ $location }}</span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Roles Column -->
                                                <div class="col-md-4">
                                                    <div class="module-section">
                                                        <h6 class="section-title">
                                                            <i class="fas fa-user-tag mr-2"></i>Roles
                                                        </h6>
                                                        <div class="section-content">
                                                            <ul class="list-unstyled mb-0">
                                                                @foreach ($module['roles'] as $role)
                                                                    <li><i
                                                                            class="fas fa-check-circle text-info mr-1"></i>{{ $role['code'] }}
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Functions Column (Only for SCM modules) -->
                                                @if ($module['functions_count'] > 0)
                                                    <div class="col-md-4">
                                                        <div class="module-section">
                                                            <h6 class="section-title">
                                                                <i class="fas fa-cog mr-2"></i>Functions
                                                            </h6>
                                                            <div class="section-content">
                                                                <ul class="list-unstyled mb-0">
                                                                    @foreach ($module['functions'] as $function)
                                                                        <li><i
                                                                                class="fas fa-check-circle text-warning mr-1"></i>{{ $function['code'] }}
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Total Assignments Count -->
                                            <hr class="mt-3 mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Total: {{ $module['total_assignments'] }} individual assignment(s)
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            {{--
=============================================================================
PROVISIONING CARDS - DISABLED (External system provisioning removed)
=============================================================================

D365 Provisioning Status Card
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-server"></i> D365 Provisioning Status
        </h3>
    </div>
    <div class="card-body">
        @if (empty($user->D365UserID))
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>D365 Not Provisioned</strong>
                <p class="mb-0">This user has not been provisioned to D365 yet.</p>
            </div>

            @php
                $hasD365Assignment = DB::connection('sqlsrv')
                    ->table('Azure_Application_UserMapping')
                    ->where('AzureUserID', $user->AzureId)
                    ->where('ApplicationName', 'D365 LIVE')
                    ->where('IsActive', 1)
                    ->where('IsDelete', 0)
                    ->exists();
            @endphp

            @if ($hasD365Assignment && !empty($user->AzureId))
                <button type="button" class="btn btn-primary mt-2"
                    onclick="retryD365Provisioning('{{ $user->Id }}')" id="d365ProvisionBtn">
                    <i class="fas fa-sync"></i> Provision to D365 Now
                </button>
            @elseif(empty($user->AzureId))
                <div class="alert alert-danger mt-2 mb-0">
                    <i class="fas fa-times-circle"></i>
                    Cannot provision to D365: User not provisioned to Azure AD
                </div>
            @else
                <div class="alert alert-info mt-2 mb-0">
                    <i class="fas fa-info-circle"></i>
                    No D365 LIVE module assignments found. D365 provisioning not required.
                </div>
            @endif
        @else
            <div class="alert alert-success mb-0">
                <i class="fas fa-check-circle"></i>
                <strong>D365 Provisioned Successfully</strong>
                <div class="mt-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-0">
                                <label>D365 User ID</label>
                                <p><code class="text-success">{{ $user->D365UserID }}</code></p>
                            </div>
                        </div>
                        @if ($user->D365LoginID)
                            <div class="col-md-6">
                                <div class="info-item mb-0">
                                    <label>D365 Login ID</label>
                                    <p><code>{{ $user->D365LoginID }}</code></p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

BIZ/FITSYS Provisioning Status Card
<div class="card card-success">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-server"></i> BIZ/FITSYS Provisioning Status
        </h3>
    </div>
    <div class="card-body">
        @if (empty($user->BizUserID))
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>BIZ/FITSYS Not Provisioned</strong>
                <p class="mb-0">This user has not been provisioned to BIZ/FITSYS yet.</p>
            </div>

            @php
                $hasBIZAssignment = DB::connection('sqlsrv')
                    ->table('Azure_Application_UserMapping')
                    ->where('AzureUserID', $user->AzureId)
                    ->where('IsActive', 1)
                    ->where('IsDelete', 0)
                    ->exists();
            @endphp

            @if ($hasBIZAssignment && !empty($user->AzureId))
                <button type="button" class="btn btn-success mt-2"
                    onclick="retryBIZProvisioning('{{ $user->Id }}')" id="bizProvisionBtn">
                    <i class="fas fa-sync"></i> Provision to BIZ/FITSYS Now
                </button>
            @elseif(empty($user->AzureId))
                <div class="alert alert-danger mt-2 mb-0">
                    <i class="fas fa-times-circle"></i>
                    Cannot provision to BIZ/FITSYS: User not provisioned to Azure AD
                </div>
            @else
                <div class="alert alert-info mt-2 mb-0">
                    <i class="fas fa-info-circle"></i>
                    No module assignments found. BIZ/FITSYS provisioning requires role and office mapping.
                </div>
            @endif
        @else
            <div class="alert alert-success mb-0">
                <i class="fas fa-check-circle"></i>
                <strong>BIZ/FITSYS Provisioned Successfully</strong>
                <div class="mt-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-0">
                                <label>BIZ User ID</label>
                                <p><code class="text-success">{{ $user->BizUserID }}</code></p>
                            </div>
                        </div>
                        @if ($user->EmployeCode)
                            <div class="col-md-6">
                                <div class="info-item mb-0">
                                    <label>Employee Code</label>
                                    <p><code>{{ $user->EmployeCode }}</code></p>
                                </div>
                            </div>
                        @endif
                        @if ($user->BizLoginId)
                            <div class="col-md-12 mt-2">
                                <div class="info-item mb-0">
                                    <label>BIZ Login ID</label>
                                    <p><code>{{ $user->BizLoginId }}</code></p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

FIT Express Provisioning Status Card
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-server"></i> FIT Express Provisioning Status
        </h3>
    </div>
    <div class="card-body">
        @php
            $hasFITExpressAssignment = DB::connection('sqlsrv')
                ->table('Azure_Application_UserMapping')
                ->where('AzureUserID', $user->AzureId)
                ->where('ApplicationName', 'LIKE', '%FIT Express%')
                ->where('IsActive', 1)
                ->where('IsDelete', 0)
                ->exists();
        @endphp

        @if (!$hasFITExpressAssignment)
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle"></i>
                No FIT Express module assignments found. FIT Express provisioning not required.
            </div>
        @elseif(empty($user->AzureId))
            <div class="alert alert-danger mb-0">
                <i class="fas fa-times-circle"></i>
                Cannot provision to FIT Express: User not provisioned to Azure AD
            </div>
        @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>FIT Express Provisioning Available</strong>
                <p class="mb-0">This user has FIT Express assignments and can be provisioned.</p>
            </div>
            <button type="button" class="btn btn-primary"
                onclick="retryFITExpressProvisioning('{{ $user->Id }}')" id="fitExpressProvisionBtn">
                <i class="fas fa-sync"></i> Provision to FIT Express Now
            </button>
        @endif
    </div>
</div>

END OF DISABLED PROVISIONING CARDS
=============================================================================
--}}

            <!-- Account Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-id-card"></i> Account Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Row 1: User Principal Name | Employee ID | SCM Base Office -->
                        <div class="col-md-4">
                            <div class="info-item">
                                <label>User Principal Name</label>
                                <p>{{ $user->userPrincipalName ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-item">
                                <label>Employee ID</label>
                                <p>{{ $user->EmployeCode ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-item">
                                <label>SCM Base Office</label>
                                <p>
                                    @if ($user->SCMBaseOfficeCode)
                                        <span class="badge badge-warning">{{ $user->SCMBaseOfficeCode }}</span>
                                    @else
                                        <span class="text-muted">Not Set</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Row 2: Azure AD Object ID | Account Enabled -->
                        <div class="col-md-4">
                            <div class="info-item">
                                <label>Azure AD Object ID</label>
                                <p><code>{{ $user->Id ?? 'N/A' }}</code></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-item">
                                <label>Account Enabled</label>
                                <p>
                                    @if ($user->accountEnabled ?? true)
                                        <span class="badge badge-success">Yes</span>
                                    @else
                                        <span class="badge badge-danger">No</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-item">
                                <label>Created Date</label>
                                <p>{{ $user->createdDateTime ? \Carbon\Carbon::parse($user->createdDateTime)->format('M d, Y H:i') : 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <!-- Row 3: Created Date | Last Modified -->

                        <div class="col-md-4">
                            <div class="info-item">
                                <label>Last Modified</label>
                                <p>{{ $user->updated_at ? $user->updated_at->format('M d, Y H:i') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employment Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-briefcase"></i> Employment Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>Job Title</label>
                                <p>{{ $user->jobTitle ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>Department</label>
                                <p>{{ $user->department ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>Company</label>
                                <p>{{ $user->companyName ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>Office Location</label>
                                <p>{{ $user->officeLocation ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>Manager</label>
                                <p>{{ $user->manager ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>Employee Type</label>
                                <p>{{ $user->employeeType ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact & Location Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-map-marked-alt"></i> Contact & Location Details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>Street Address</label>
                                <p>{{ $user->streetAddress ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>City</label>
                                <p>{{ $user->city ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>State/Province</label>
                                <p>{{ $user->state ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>Postal Code</label>
                                <p>{{ $user->postalCode ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>Country</label>
                                <p>{{ $user->country ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>Usage Location</label>
                                <p>{{ $user->usageLocation ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Office Configuration Modal --}}
    <div class="modal fade" id="officeConfigModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">
                        <i class="fas fa-cog"></i> Configure Office - <span id="modalOfficeName"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="selectedOfficeCode">

                    {{-- Copy from Office --}}
                    <div class="form-group">
                        <label>📋 Copy from another office (optional)</label>
                        <div class="input-group">
                            <select class="form-control" id="copyFromOffice">
                                <option value="">-- Select office to copy --</option>
                                @foreach ($offices as $office)
                                    <option value="{{ $office->Code ?? $office->Code }}">{{ $office->Name }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-secondary" type="button" onclick="copyOfficeAssignments()">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Module Selection --}}
                    <div class="form-group">
                        <label>Select Module</label>
                        <select class="form-control" id="moduleSelect">
                            <option value="">-- Select Module --</option>
                            @foreach ($modules as $module)
                                <option value="{{ $module->Id }}" data-app-id="{{ $module->ApplicationId }}"
                                    data-name="{{ $module->Name }}">
                                    {{ $module->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Roles Section --}}
                    <div class="form-group">
                        <label>Roles</label>
                        <div class="roles-container"
                            style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px;">
                            <table class="table table-sm mb-0" id="rolesTable">
                                <thead>
                                    <tr>
                                        <th>Role</th>
                                        <th width="80">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="rolesTableBody">
                                    <tr id="noRolesRow">
                                        <td colspan="2" class="text-center text-muted">No roles added</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="input-group mt-2">
                            <select class="form-control" id="roleSelect" disabled>
                                <option value="">-- Select role --</option>
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-info" type="button" onclick="addRole()" id="addRoleBtn"
                                    disabled>
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Functions Section (SCM only) --}}
                    <div class="form-group" id="functionsSection" style="display: none;">
                        <label>Functions (SCM)</label>
                        <div class="functions-container"
                            style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px;">
                            <table class="table table-sm mb-0" id="functionsTable">
                                <thead>
                                    <tr>
                                        <th>Function</th>
                                        <th width="80">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="functionsTableBody">
                                    <tr id="noFunctionsRow">
                                        <td colspan="2" class="text-center text-muted">No functions added</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="input-group mt-2">
                            <select class="form-control" id="functionSelect">
                                <option value="">-- Select function --</option>
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-warning" type="button" onclick="addFunction()">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="saveOfficeConfig()">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Module Assignment Modal --}}
    <div class="modal fade" id="moduleAssignModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title">
                        <i class="fas fa-cubes"></i> Quick Assign - <span id="modalModuleName"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="selectedModuleId">
                    <input type="hidden" id="selectedModuleAppId">

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> This will assign the same roles to all selected offices
                    </div>

                    {{-- Office Selection --}}
                    <div class="form-group">
                        <label>Select Offices</label>
                        <div
                            style="max-height: 150px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px;">
                            @foreach ($offices as $office)
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input office-checkbox"
                                        id="office_{{ $office->Code ?? $office->Code }}"
                                        value="{{ $office->Code ?? $office->Code }}">
                                    <label class="custom-control-label"
                                        for="office_{{ $office->Code ?? $office->Code }}">
                                        {{ $office->Name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="toggleAllOffices()">
                            <i class="fas fa-check-double"></i> Select All / None
                        </button>
                    </div>

                    {{-- Roles Section --}}
                    <div class="form-group">
                        <label>Roles (same for all offices)</label>
                        <div class="roles-container-quick"
                            style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px;">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Role</th>
                                        <th width="80">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="quickRolesTableBody">
                                    <tr id="noQuickRolesRow">
                                        <td colspan="2" class="text-center text-muted">No roles added</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="input-group mt-2">
                            <select class="form-control" id="quickRoleSelect">
                                <option value="">-- Select role --</option>
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-info" type="button" onclick="addQuickRole()">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Functions Section (SCM only) --}}
                    <div class="form-group" id="quickFunctionsSection" style="display: none;">
                        <label>Functions (SCM)</label>
                        <div class="functions-container-quick"
                            style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px;">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Function</th>
                                        <th width="80">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="quickFunctionsTableBody">
                                    <tr id="noQuickFunctionsRow">
                                        <td colspan="2" class="text-center text-muted">No functions added</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="input-group mt-2">
                            <select class="form-control" id="quickFunctionSelect">
                                <option value="">-- Select function --</option>
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-warning" type="button" onclick="addQuickFunction()">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div class="alert alert-secondary mt-3">
                        <strong>Summary:</strong>
                        <span id="assignmentSummary">Select offices and roles</span>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="saveQuickAssignment()">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>

@stop

@section('css')
    <style>
        /* Module Section Styling */
        .module-section {
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 6px;
            height: 100%;
            border: 1px solid #e9ecef;
        }

        .module-section .section-title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #dee2e6;
            font-size: 0.9rem;
        }

        .module-section .section-content {
            max-height: 200px;
            overflow-y: auto;
        }

        .module-section .section-content ul li {
            padding: 0.25rem 0;
            font-size: 0.85rem;
        }

        /* Scrollbar styling */
        .section-content::-webkit-scrollbar,
        .roles-container::-webkit-scrollbar,
        .functions-container::-webkit-scrollbar,
        .roles-container-quick::-webkit-scrollbar,
        .functions-container-quick::-webkit-scrollbar {
            width: 8px;
        }

        .section-content::-webkit-scrollbar-track,
        .roles-container::-webkit-scrollbar-track,
        .functions-container::-webkit-scrollbar-track,
        .roles-container-quick::-webkit-scrollbar-track,
        .functions-container-quick::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .section-content::-webkit-scrollbar-thumb,
        .roles-container::-webkit-scrollbar-thumb,
        .functions-container::-webkit-scrollbar-thumb,
        .roles-container-quick::-webkit-scrollbar-thumb,
        .functions-container-quick::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .section-content::-webkit-scrollbar-thumb:hover,
        .roles-container::-webkit-scrollbar-thumb:hover,
        .functions-container::-webkit-scrollbar-thumb:hover,
        .roles-container-quick::-webkit-scrollbar-thumb:hover,
        .functions-container-quick::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .module-section {
                margin-bottom: 1rem;
            }
        }

        .user-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 48px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .badge-lg {
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 20px;
        }

        .info-item {
            margin-bottom: 20px;
        }

        .info-item label {
            font-weight: 600;
            color: #6c757d;
            font-size: 13px;
            text-transform: uppercase;
            margin-bottom: 5px;
            display: block;
        }

        .info-item p {
            font-size: 15px;
            color: #333;
            margin-bottom: 0;
        }

        .info-item code {
            font-size: 13px;
            padding: 4px 8px;
            background-color: #f4f4f4;
            border-radius: 4px;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .card-header .card-title {
            font-weight: 600;
            color: #495057;
        }

        /* Module Assignments Styles */
        .accordion .card {
            border: 1px solid #dee2e6;
            box-shadow: none;
        }

        .accordion .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .accordion .btn-link {
            color: #333;
            text-decoration: none;
            padding: 1rem;
        }

        .accordion .btn-link:hover {
            background-color: #e9ecef;
            text-decoration: none;
        }

        .accordion .btn-link.collapsed .fa-chevron-down {
            transform: rotate(0deg);
            transition: transform 0.3s ease;
        }

        .accordion .btn-link:not(.collapsed) .fa-chevron-down {
            transform: rotate(180deg);
            transition: transform 0.3s ease;
        }
    </style>
@stop

@section('js')
    <script>
        // ============================================================================
        // OFFICE CONFIGURATION MODAL
        // ============================================================================
        let selectedRoles = [];
        let selectedFunctions = [];

        function openOfficeModal(officeCode, officeName) {
            $('#selectedOfficeCode').val(officeCode);
            $('#modalOfficeName').text(officeName);
            $('#officeConfigModal').modal('show');

            // Reset
            selectedRoles = [];
            selectedFunctions = [];
            $('#rolesTableBody').html(
                '<tr id="noRolesRow"><td colspan="2" class="text-center text-muted">No roles added</td></tr>');
            $('#functionsTableBody').html(
                '<tr id="noFunctionsRow"><td colspan="2" class="text-center text-muted">No functions added</td></tr>');
            $('#moduleSelect').val('');
            $('#roleSelect').prop('disabled', true).html('<option value="">-- Select role --</option>');
            $('#functionsSection').hide();
        }

        $('#moduleSelect').on('change', function() {
            const moduleId = $(this).val();
            const appId = $(this).find(':selected').data('app-id');

            if (!moduleId) {
                $('#roleSelect').prop('disabled', true).html('<option value="">-- Select role --</option>');
                $('#functionsSection').hide();
                return;
            }

            const isSCM = appId === 'OPS' || appId === 'OPSUAT';
            if (isSCM) {
                $('#functionsSection').show();
                loadFunctions();
            } else {
                $('#functionsSection').hide();
            }

            loadRoles(appId);
        });

        function loadRoles(appId) {
            $.get(`/azure/modules/roles?application_code=${appId}`)
                .done(function(response) {
                    if (response.success) {
                        let options = '<option value="">-- Select role --</option>';
                        response.data.forEach(role => {
                            options +=
                                `<option value="${role.RoleId}" data-code="${role.RoleCode}">${role.RoleCode}</option>`;
                        });
                        $('#roleSelect').html(options).prop('disabled', false);
                        $('#addRoleBtn').prop('disabled', false);
                    }
                });
        }

        function loadFunctions() {
            $.get('/azure/modules/scm-functions')
                .done(function(response) {
                    if (response.success) {
                        let options = '<option value="">-- Select function --</option>';
                        response.data.forEach(func => {
                            options +=
                                `<option value="${func.ID}" data-code="${func.Code}">${func.Code}</option>`;
                        });
                        $('#functionSelect').html(options);
                    }
                });
        }

        function addRole() {
            const roleId = $('#roleSelect').val();
            const roleCode = $('#roleSelect').find(':selected').data('code');

            if (!roleId) return;

            if (selectedRoles.find(r => r.id === roleId)) {
                alert('Role already added');
                return;
            }

            selectedRoles.push({
                id: roleId,
                code: roleCode
            });

            $('#noRolesRow').remove();
            $('#rolesTableBody').append(`
                <tr data-role-id="${roleId}">
                    <td>${roleCode}</td>
                    <td><button class="btn btn-sm btn-danger" onclick="removeRole('${roleId}')"><i class="fas fa-times"></i></button></td>
                </tr>
            `);
        }

        function removeRole(roleId) {
            selectedRoles = selectedRoles.filter(r => r.id !== roleId);
            $(`tr[data-role-id="${roleId}"]`).remove();

            if (selectedRoles.length === 0) {
                $('#rolesTableBody').html(
                    '<tr id="noRolesRow"><td colspan="2" class="text-center text-muted">No roles added</td></tr>');
            }
        }

        function addFunction() {
            const funcId = $('#functionSelect').val();
            const funcCode = $('#functionSelect').find(':selected').data('code');

            if (!funcId) return;

            if (selectedFunctions.find(f => f.id === funcId)) {
                alert('Function already added');
                return;
            }

            selectedFunctions.push({
                id: funcId,
                code: funcCode
            });

            $('#noFunctionsRow').remove();
            $('#functionsTableBody').append(`
                <tr data-func-id="${funcId}">
                    <td>${funcCode}</td>
                    <td><button class="btn btn-sm btn-danger" onclick="removeFunction('${funcId}')"><i class="fas fa-times"></i></button></td>
                </tr>
            `);
        }

        function removeFunction(funcId) {
            selectedFunctions = selectedFunctions.filter(f => f.id !== funcId);
            $(`tr[data-func-id="${funcId}"]`).remove();

            if (selectedFunctions.length === 0) {
                $('#functionsTableBody').html(
                    '<tr id="noFunctionsRow"><td colspan="2" class="text-center text-muted">No functions added</td></tr>'
                );
            }
        }

        function saveOfficeConfig() {
            const officeCode = $('#selectedOfficeCode').val();
            const moduleId = $('#moduleSelect').val();
            const moduleName = $('#moduleSelect').find(':selected').data('name');

            if (!moduleId || selectedRoles.length === 0) {
                alert('Please select module and at least one role');
                return;
            }

            const data = {
                office_code: officeCode,
                modules: [{
                    module_id: moduleId,
                    application_name: moduleName,
                    roles: selectedRoles,
                    functions: selectedFunctions.length > 0 ? selectedFunctions : null
                }]
            };

            $.post({
                    url: `/azure/users/{{ $user->Id }}/configure-office`,
                    data: JSON.stringify(data),
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                })
                .done(function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#officeConfigModal').modal('hide');
                        location.reload();
                    }
                })
                .fail(function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Unknown error'));
                });
        }

        function copyOfficeAssignments() {
            const fromOffice = $('#copyFromOffice').val();
            if (!fromOffice) {
                alert('Please select an office to copy from');
                return;
            }

            $.get(`/azure/users/{{ $user->Id }}/offices/${fromOffice}/assignments`)
                .done(function(response) {
                    if (response.success && response.data.length > 0) {
                        alert(`Copied ${response.data.length} module(s) from ${fromOffice}`);
                    } else {
                        alert('No assignments found for that office');
                    }
                });
        }

        // ============================================================================
        // QUICK MODULE ASSIGNMENT MODAL
        // ============================================================================
        let quickSelectedRoles = [];
        let quickSelectedFunctions = [];

        function openModuleModal(moduleId, moduleName, appId) {
            $('#selectedModuleId').val(moduleId);
            $('#selectedModuleAppId').val(appId);
            $('#modalModuleName').text(moduleName);
            $('#moduleAssignModal').modal('show');

            // Reset
            quickSelectedRoles = [];
            quickSelectedFunctions = [];
            $('.office-checkbox').prop('checked', false);
            $('#quickRolesTableBody').html(
                '<tr id="noQuickRolesRow"><td colspan="2" class="text-center text-muted">No roles added</td></tr>');
            $('#quickFunctionsTableBody').html(
                '<tr id="noQuickFunctionsRow"><td colspan="2" class="text-center text-muted">No functions added</td></tr>'
            );

            const isSCM = appId === 'OPS' || appId === 'OPSUAT';
            if (isSCM) {
                $('#quickFunctionsSection').show();
                loadQuickFunctions();
            } else {
                $('#quickFunctionsSection').hide();
            }

            loadQuickRoles(appId);
            updateSummary();
        }

        function loadQuickRoles(appId) {
            $.get(`/azure/modules/roles?application_code=${appId}`)
                .done(function(response) {
                    if (response.success) {
                        let options = '<option value="">-- Select role --</option>';
                        response.data.forEach(role => {
                            options +=
                                `<option value="${role.RoleId}" data-code="${role.RoleCode}">${role.RoleCode}</option>`;
                        });
                        $('#quickRoleSelect').html(options);
                    }
                });
        }

        function loadQuickFunctions() {
            $.get('/azure/modules/scm-functions')
                .done(function(response) {
                    if (response.success) {
                        let options = '<option value="">-- Select function --</option>';
                        response.data.forEach(func => {
                            options +=
                                `<option value="${func.ID}" data-code="${func.Code}">${func.Code}</option>`;
                        });
                        $('#quickFunctionSelect').html(options);
                    }
                });
        }

        function toggleAllOffices() {
            const allChecked = $('.office-checkbox:checked').length === $('.office-checkbox').length;
            $('.office-checkbox').prop('checked', !allChecked);
            updateSummary();
        }

        function addQuickRole() {
            const roleId = $('#quickRoleSelect').val();
            const roleCode = $('#quickRoleSelect').find(':selected').data('code');

            if (!roleId) return;

            if (quickSelectedRoles.find(r => r.id === roleId)) {
                alert('Role already added');
                return;
            }

            quickSelectedRoles.push({
                id: roleId,
                code: roleCode
            });

            $('#noQuickRolesRow').remove();
            $('#quickRolesTableBody').append(`
                <tr data-role-id="${roleId}">
                    <td>${roleCode}</td>
                    <td><button class="btn btn-sm btn-danger" onclick="removeQuickRole('${roleId}')"><i class="fas fa-times"></i></button></td>
                </tr>
            `);

            updateSummary();
        }

        function removeQuickRole(roleId) {
            quickSelectedRoles = quickSelectedRoles.filter(r => r.id !== roleId);
            $(`#quickRolesTableBody tr[data-role-id="${roleId}"]`).remove();

            if (quickSelectedRoles.length === 0) {
                $('#quickRolesTableBody').html(
                    '<tr id="noQuickRolesRow"><td colspan="2" class="text-center text-muted">No roles added</td></tr>');
            }

            updateSummary();
        }

        function addQuickFunction() {
            const funcId = $('#quickFunctionSelect').val();
            const funcCode = $('#quickFunctionSelect').find(':selected').data('code');

            if (!funcId) return;

            if (quickSelectedFunctions.find(f => f.id === funcId)) {
                alert('Function already added');
                return;
            }

            quickSelectedFunctions.push({
                id: funcId,
                code: funcCode
            });

            $('#noQuickFunctionsRow').remove();
            $('#quickFunctionsTableBody').append(`
                <tr data-func-id="${funcId}">
                    <td>${funcCode}</td>
                    <td><button class="btn btn-sm btn-danger" onclick="removeQuickFunction('${funcId}')"><i class="fas fa-times"></i></button></td>
                </tr>
            `);

            updateSummary();
        }

        function removeQuickFunction(funcId) {
            quickSelectedFunctions = quickSelectedFunctions.filter(f => f.id !== funcId);
            $(`#quickFunctionsTableBody tr[data-func-id="${funcId}"]`).remove();

            if (quickSelectedFunctions.length === 0) {
                $('#quickFunctionsTableBody').html(
                    '<tr id="noQuickFunctionsRow"><td colspan="2" class="text-center text-muted">No functions added</td></tr>'
                );
            }

            updateSummary();
        }

        function updateSummary() {
            const officeCount = $('.office-checkbox:checked').length;
            const roleCount = quickSelectedRoles.length;
            const funcCount = quickSelectedFunctions.length;
            const isSCM = $('#quickFunctionsSection').is(':visible');

            let total = 0;
            if (isSCM && funcCount > 0) {
                total = officeCount * roleCount * funcCount;
            } else {
                total = officeCount * roleCount;
            }

            let text = `${officeCount} office(s) × ${roleCount} role(s)`;
            if (isSCM && funcCount > 0) {
                text += ` × ${funcCount} function(s)`;
            }
            text += ` = ${total} assignment(s)`;

            $('#assignmentSummary').text(text);
        }

        $(document).on('change', '.office-checkbox', updateSummary);

        function saveQuickAssignment() {
            const moduleId = $('#selectedModuleId').val();
            const moduleName = $('#modalModuleName').text();
            const officeCodes = $('.office-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (officeCodes.length === 0) {
                alert('Please select at least one office');
                return;
            }

            if (quickSelectedRoles.length === 0) {
                alert('Please add at least one role');
                return;
            }

            const data = {
                module_id: moduleId,
                application_name: moduleName,
                office_codes: officeCodes,
                roles: quickSelectedRoles,
                functions: quickSelectedFunctions.length > 0 ? quickSelectedFunctions : null
            };

            $.post({
                    url: `/azure/users/{{ $user->Id }}/quick-assign-module`,
                    data: JSON.stringify(data),
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                })
                .done(function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#moduleAssignModal').modal('hide');
                        location.reload();
                    }
                })
                .fail(function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Unknown error'));
                });
        }

        // ============================================================================
        // D365 PROVISIONING
        // ============================================================================
        function retryD365Provisioning(userId) {
            if (!confirm(
                    'Provision this user to D365?\n\nThis will create the user in the D365 system and assign roles based on their module assignments.'
                )) {
                return;
            }

            const button = document.getElementById('d365ProvisionBtn');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Provisioning...';

            fetch(`/azure/users/${userId}/retry-d365`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Success!\n\n${data.message}`);
                        window.location.reload();
                    } else {
                        alert(`D365 Provisioning Failed\n\n${data.message}`);
                        button.disabled = false;
                        button.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to provision to D365. Please try again.\n\nError: ' + error.message);
                    button.disabled = false;
                    button.innerHTML = originalText;
                });
        }

        // ============================================================================
        // BIZ/FITSYS PROVISIONING
        // ============================================================================
        function retryBIZProvisioning(userId) {
            if (!confirm(
                    'Provision this user to BIZ/FITSYS?\n\nThis will create the user in the BIZ/FITSYS system and assign roles based on their module assignments.'
                )) {
                return;
            }

            const button = document.getElementById('bizProvisionBtn');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Provisioning...';

            fetch(`/azure/users/${userId}/retry-biz`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Success!\n\n${data.message}`);
                        window.location.reload();
                    } else {
                        alert(`BIZ/FITSYS Provisioning Failed\n\n${data.message}`);
                        button.disabled = false;
                        button.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to provision to BIZ/FITSYS. Please try again.\n\nError: ' + error.message);
                    button.disabled = false;
                    button.innerHTML = originalText;
                });
        }

        // ============================================================================
        // FIT EXPRESS PROVISIONING
        // ============================================================================
        function retryFITExpressProvisioning(userId) {
            if (!confirm('Provision this user to FIT Express?\n\nThis will create the user in the FIT Express system.')) {
                return;
            }

            const button = document.getElementById('fitExpressProvisionBtn');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Provisioning...';

            fetch(`/azure/users/${userId}/retry-fitexpress`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Success!\n\n${data.message}`);
                        window.location.reload();
                    } else {
                        alert(`FIT Express Provisioning Failed\n\n${data.message}`);
                        button.disabled = false;
                        button.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to provision to FIT Express. Please try again.\n\nError: ' + error.message);
                    button.disabled = false;
                    button.innerHTML = originalText;
                });
        }

        // ============================================================================
        // BASE OFFICE MANAGEMENT
        // ============================================================================
        function saveBaseOffice() {
            const baseOffice = $('#scm_base_office').val();

            if (!baseOffice) {
                alert('⚠️ Please select a Base Office!');
                return;
            }

            $.post({
                    url: `/azure/users/{{ $user->Id }}/update-base-office`,
                    data: JSON.stringify({
                        scm_base_office: baseOffice
                    }),
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                })
                .done(function(response) {
                    if (response.success) {
                        alert('✅ Base Office saved successfully!');
                        location.reload();
                    }
                })
                .fail(function(xhr) {
                    alert('❌ Error: ' + (xhr.responseJSON?.message || 'Failed to save'));
                });
        }

        // ============================================================================
        // BASE OFFICE SAVE BUTTON VISIBILITY
        // ============================================================================
        // Get the default/base value
        const defaultBaseOffice = 'KE'; // FIT KENYA

        // Show save button only when a different office is selected
        $('#scm_base_office').on('change', function() {
            const selectedValue = $(this).val();
            const currentValue = '{{ $user->SCMBaseOfficeCode ?? 'KE' }}';

            // Show button only if:
            // 1. A value is selected AND
            // 2. It's different from the current/initial value
            if (selectedValue && selectedValue !== currentValue) {
                $('#saveBaseOfficeBtn').show();
            } else {
                $('#saveBaseOfficeBtn').hide();
            }
        });

        // Initialize button visibility on page load
        $(document).ready(function() {
            const selectedValue = $('#scm_base_office').val();
            const currentValue = '{{ $user->SCMBaseOfficeCode ?? 'KE' }}';

            if (selectedValue && selectedValue !== currentValue) {
                $('#saveBaseOfficeBtn').show();
            } else {
                $('#saveBaseOfficeBtn').hide();
            }
        });

        console.log('User detail page loaded successfully');
    </script>
@stop
