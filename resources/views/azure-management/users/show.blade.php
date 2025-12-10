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
                        $badgeClass = match(strtolower($status)) {
                            'active' => 'badge-success',
                            'pending' => 'badge-warning',
                            'inactive' => 'badge-danger',
                            default => 'badge-secondary'
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }} badge-lg">{{ $status }}</span>
                </div>

                <hr>

                <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                <p class="text-muted mb-2">
                    <a href="mailto:{{ $user->mail ?? $user->userPrincipalName }}">
                        {{ $user->mail ?? $user->userPrincipalName ?? 'N/A' }}
                    </a>
                </p>

                <strong><i class="fas fa-phone mr-1"></i> Business Phone</strong>
                <p class="text-muted mb-2">
                    @if(!empty($user->businessPhones))
                        @foreach(json_decode($user->businessPhones, true) ?? [] as $phone)
                            <a href="tel:{{ $phone }}">{{ $phone }}</a><br>
                        @endforeach
                    @else
                        N/A
                    @endif
                </p>

                <strong><i class="fas fa-mobile-alt mr-1"></i> Mobile Phone</strong>
                <p class="text-muted mb-2">
                    @if($user->mobilePhone)
                        <a href="tel:{{ $user->mobilePhone }}">{{ $user->mobilePhone }}</a>
                    @else
                        N/A
                    @endif
                </p>

                <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>
                <p class="text-muted mb-2">
                    {{ $user->officeLocation ?? $user->city ?? 'N/A' }}
                    @if($user->country)
                        <br><small>{{ $user->country }}</small>
                    @endif
                </p>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <!-- <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
            </div>
            <div class="card-body p-0">
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a href="mailto:{{ $user->mail ?? $user->userPrincipalName }}" class="nav-link">
                            <i class="fas fa-envelope"></i> Send Email
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('azure.users.edit', $user->Id) }}" class="nav-link">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        @if($user->Status === 'Active')
                            <a href="#" onclick="disableUser('{{ $user->Id }}')" class="nav-link text-danger">
                                <i class="fas fa-user-slash"></i> Disable Account
                            </a>
                        @else
                            <a href="#" onclick="enableUser('{{ $user->Id }}')" class="nav-link text-success">
                                <i class="fas fa-user-check"></i> Enable Account
                            </a>
                        @endif
                    </li>
                    <li class="nav-item">
                        <a href="#" onclick="resetPassword('{{ $user->Id }}')" class="nav-link">
                            <i class="fas fa-key"></i> Reset Password
                        </a>
                    </li>
                </ul>
            </div>
        </div> -->
    </div>

    <!-- Right Column - Detailed Information -->
    <div class="col-md-8">
        <!-- Module Assignments Card -->
        <div class="card card-success">
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
                @if($moduleAssignments->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>No module assignments found for this user.</p>
                    </div>
                @else
                    <!-- Accordion for Module Assignments -->
                    <div class="accordion" id="moduleAssignmentsAccordion">
                        @foreach($moduleAssignments as $index => $module)
                        <div class="card mb-2">
                            <div class="card-header p-0" id="heading{{ $index }}">
                                <button class="btn btn-link btn-block text-left d-flex justify-content-between align-items-center collapsed" 
                                        type="button" 
                                        data-toggle="collapse" 
                                        data-target="#collapse{{ $index }}" 
                                        aria-expanded="false" 
                                        aria-controls="collapse{{ $index }}">
                                    <div>
                                        <i class="fas fa-cube mr-2"></i>
                                        <strong>{{ $module['module_name'] }}</strong>
                                    </div>
                                    <div>
                                        <span class="badge badge-primary">{{ $module['locations_count'] }} Location(s)</span>
                                        <span class="badge badge-info">{{ $module['roles_count'] }} Role(s)</span>
                                        @if($module['functions_count'] > 0)
                                            <span class="badge badge-warning">{{ $module['functions_count'] }} Function(s)</span>
                                        @endif
                                        <i class="fas fa-chevron-down ml-2"></i>
                                    </div>
                                </button>
                            </div>

                            <div id="collapse{{ $index }}" 
                                class="collapse" 
                                aria-labelledby="heading{{ $index }}" 
                                data-parent="#moduleAssignmentsAccordion">
                                <div class="card-body">
                                    <!-- Locations -->
                                    <div class="mb-3">
                                        <h6 class="text-muted">
                                            <i class="fas fa-map-marker-alt mr-2"></i>Locations
                                        </h6>
                                        <div class="ml-4">
                                            @foreach($module['locations'] as $location)
                                                <span class="badge badge-primary mr-1">{{ $location }}</span>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Roles -->
                                    <div class="mb-3">
                                        <h6 class="text-muted">
                                            <i class="fas fa-user-tag mr-2"></i>Roles
                                        </h6>
                                        <ul class="ml-4">
                                            @foreach($module['roles'] as $role)
                                                <li>{{ $role['code'] }}</li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    <!-- Functions (Only for SCM modules) -->
                                    @if($module['functions_count'] > 0)
                                        <div class="mb-0">
                                            <h6 class="text-muted">
                                                <i class="fas fa-cog mr-2"></i>Functions
                                            </h6>
                                            <ul class="ml-4 mb-0">
                                                @foreach($module['functions'] as $function)
                                                    <li>{{ $function['code'] }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <!-- Total Assignments Count -->
                                    <hr>
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

        <!-- Account Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-id-card"></i> Account Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item">
                            <label>User Principal Name</label>
                            <p>{{ $user->userPrincipalName ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <label>Employee ID</label>
                            <p>{{ $user->EmployeCode ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <label>Azure AD Object ID</label>
                            <p><code>{{ $user->Id ?? 'N/A' }}</code></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <label>Account Enabled</label>
                            <p>
                                @if($user->accountEnabled ?? true)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-danger">No</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <label>Created Date</label>
                            <p>{{ $user->createdDateTime ? \Carbon\Carbon::parse($user->createdDateTime)->format('M d, Y H:i') : 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
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

        <!-- Additional Information -->
        <!-- <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Additional Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="info-item">
                            <label>About Me</label>
                            <p>{{ $user->aboutMe ?? 'No information available' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <label>Preferred Language</label>
                            <p>{{ $user->preferredLanguage ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <label>Mail Nickname</label>
                            <p>{{ $user->mailNickname ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Activity Log (Optional - if you track changes) -->
        <!-- <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Recent Activity</h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="time-label">
                        <span class="bg-primary">{{ now()->format('M d, Y') }}</span>
                    </div>
                    <div>
                        <i class="fas fa-eye bg-info"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> Just now</span>
                            <h3 class="timeline-header">Profile Viewed</h3>
                            <div class="timeline-body">
                                User profile was viewed by {{ auth()->user()->name ?? 'Administrator' }}
                            </div>
                        </div>
                    </div>
                    @if($user->updated_at && $user->updated_at != $user->created_at)
                    <div>
                        <i class="fas fa-edit bg-warning"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> {{ $user->updated_at->diffForHumans() }}</span>
                            <h3 class="timeline-header">Profile Updated</h3>
                            <div class="timeline-body">
                                User information was last modified
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($user->createdDateTime)
                    <div>
                        <i class="fas fa-user-plus bg-success"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($user->createdDateTime)->diffForHumans() }}</span>
                            <h3 class="timeline-header">Account Created</h3>
                            <div class="timeline-body">
                                User account was created in Azure AD
                            </div>
                        </div>
                    </div>
                    @endif
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div> -->
    </div>
</div>

@stop

@section('css')
<style>
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
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
    
    .nav-pills .nav-link {
        color: #333;
        border-radius: 0;
        padding: 12px 20px;
    }
    
    .nav-pills .nav-link:hover {
        background-color: #f8f9fa;
    }
    
    .nav-pills .nav-link i {
        width: 20px;
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    
    .card-header .card-title {
        font-weight: 600;
        color: #495057;
    }
    
    .timeline {
        position: relative;
        margin: 0 0 30px 0;
        padding: 0;
        list-style: none;
    }
    
    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 3px;
        background: #dee2e6;
        left: 31px;
        margin: 0;
    }
    
    .timeline > div {
        margin-bottom: 15px;
        position: relative;
    }
    
    .timeline > div > .fas,
    .timeline > div > .far,
    .timeline > div > .fab {
        width: 30px;
        height: 30px;
        font-size: 15px;
        line-height: 30px;
        position: absolute;
        color: #fff;
        background: #999;
        border-radius: 50%;
        text-align: center;
        left: 18px;
        top: 0;
    }
    
    .timeline > div > .timeline-item {
        margin-left: 60px;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 3px;
        padding: 10px;
    }
    
    .timeline-header {
        margin: 0;
        color: #495057;
        font-size: 16px;
        font-weight: 600;
    }
    
    .timeline-body {
        padding: 10px 0 0 0;
    }
    
    .time-label > span {
        font-weight: 600;
        padding: 5px 10px;
        display: inline-block;
        background-color: #fff;
        border-radius: 4px;
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
    function enableUser(userId) {
        toggleUserStatus(userId, 'enable');
    }

    function disableUser(userId) {
        toggleUserStatus(userId, 'disable');
    }

    function toggleUserStatus(userId, action) {
        const actionText = action === 'enable' ? 'enable' : 'disable';
        const actionTitle = action === 'enable' ? 'Enable User' : 'Disable User';
        const actionMessage = action === 'enable' ?
            'Are you sure you want to enable this user account?' :
            'Are you sure you want to disable this user account? They will lose access to all systems.';

        if (!confirm(`${actionTitle}\n\n${actionMessage}`)) {
            return;
        }

        const url = `/azure/users/${userId}/${actionText}`;
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || `User has been ${actionText}d successfully`);
                window.location.reload();
            } else {
                alert(data.message || `Failed to ${actionText} user`);
            }
        })
        .catch(error => {
            console.error(`Error ${actionText}ing user:`, error);
            alert(`Failed to ${actionText} user. ${error.message}`);
        });
    }

    function resetPassword(userId) {
        if (!confirm('Are you sure you want to reset this user\'s password?\n\nA temporary password will be generated and sent to their email.')) {
            return;
        }

        const url = `/azure/users/${userId}/reset-password`;
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Password reset successfully. Temporary password sent to user.');
            } else {
                alert(data.message || 'Failed to reset password');
            }
        })
        .catch(error => {
            console.error('Error resetting password:', error);
            alert('Failed to reset password. ' + error.message);
        });
    }

    console.log('User detail page loaded successfully');
</script>
@stop