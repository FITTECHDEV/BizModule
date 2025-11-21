<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>Edit User</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js" defer></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    <!-- Styles -->
    <style>
        /* ---------- Theme Variables ---------- */
        :root {
            --primary-color: #0b66ff;
            --primary-contrast: #ffffff;
            --secondary-color: #0ea5a4;
            --bg: #e9edf2;          /* softer background */
            --surface: #ffffff;
            --muted: #6b7280;
            --text: #0f172a;
            --danger: #ef4444;
            --success: #10b981;
            --radius: 10px;
            --shadow-sm: 0 2px 8px rgba(15, 23, 42, 0.06);
            --shadow-md: 0 8px 30px rgba(15, 23, 42, 0.08);
            --focus-ring: 3px rgba(11, 102, 255, 0.15);
            --max-width: 1320px;
        }

        /* ---------- Reset + Defaults ---------- */
        * { box-sizing: border-box; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
        html, body { height: 100%; margin: 0; }
        body {
            font-family: "Inter", "Figtree", "Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 16px;
            line-height: 1.45;
        }

        /* ---------- Layout ---------- */
        .app-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ---------- Main Content ---------- */
        .main {
            padding: 28px;
            max-width: var(--max-width);
            margin: 0 auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .page-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text);
        }

        /* ---------- Buttons ---------- */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: transform .08s ease, box-shadow .12s ease;
        }
        .btn:focus-visible { outline: none; box-shadow: 0 0 0 4px var(--focus-ring); }
        .btn-primary { background: var(--primary-color); color: var(--primary-contrast); box-shadow: var(--shadow-sm); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .btn-secondary { background: var(--secondary-color); color: var(--primary-contrast); }
        .btn-ghost { background: transparent; color: var(--text); border: 1px solid rgba(15,23,42,0.06); }
        .btn-danger { background: var(--danger); color: var(--primary-contrast); }
        .btn-success { background: var(--success); color: var(--primary-contrast); }
        .btn-sm { padding: 6px 10px; font-size: 0.875rem; }

        /* ---------- Cards ---------- */
        .card-surface {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 18px;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(15,23,42,0.04);
        }

        /* ---------- Loading Spinner ---------- */
        .loading-spinner {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255,255,255,0.98);
            padding: 16px 18px;
            border-radius: 10px;
            box-shadow: var(--shadow-md);
            text-align: center;
            min-width: 160px;
        }
        .loading-spinner[aria-hidden="false"] { display: block; }
        .spinner {
            width: 36px; height: 36px;
            border-radius: 50%;
            border: 4px solid rgba(15,23,42,0.06);
            border-top-color: var(--primary-color);
            margin: 0 auto 10px;
            animation: spin 0.9s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ---------- Responsive ---------- */
        @media (max-width: 991px) {
            .main { padding: 18px; }
        }

        .sr-only {
            position: absolute; width: 1px; height: 1px;
            padding: 0; margin: -1px; overflow: hidden;
            clip: rect(0,0,0,0); white-space: nowrap; border: 0;
        }

        /* ---------- Edit Form Styles ---------- */
        .edit-form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        

        .form-section {
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 2px solid #e1e8ed;
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .form-section-title {
            font-size: 1.3em;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .form-control {
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .form-control.is-invalid {
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }

        .form-control:read-only,
        .form-control[readonly] {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
            border-color: #ced4da;
        }

        .form-control:read-only:focus,
        .form-control[readonly]:focus {
            border-color: #ced4da;
            box-shadow: none;
        }

        select.form-control[disabled] {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
            border-color: #ced4da;
        }

        .invalid-feedback {
            color: #e74c3c;
            font-size: 0.875em;
            margin-top: 5px;
        }

        .current-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .required {
            color: #e74c3c;
        }

        #modulesTableContainer {
            display: block;
        }

        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-height: 200px;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #e1e8ed;
            border-radius: 8px;
        }

        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 16px;
            height: 16px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .edit-form-container {
                padding: 20px;
            }
        }

        /* Assignment Cards */
        .assignments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .assignment-card {
            background: #e9ecef;
            border-radius: 10px;
            padding: 16px;
            color: #2c3e50;
            box-shadow: var(--shadow-sm);
            border: 2px solid #dee2e6;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            cursor: pointer;
        }
        
        .assignment-card:hover {
            background: #dfe3e7;
            border-color: #adb5bd;
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        
        .assignment-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            font-size: 1.1em;
            font-weight: 600;
            border-bottom: 2px solid #ced4da;
            padding-bottom: 8px;
            color: #2c3e50;
        }
        
        .assignment-card-body {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .assignment-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9em;
            color: #495057;
        }
        
        
        .assignment-item {
            margin-bottom: 12px;
        }
        
        .assignment-item:last-child {
            margin-bottom: 0;
        }
        
        .assignment-divider {
            border: 0;
            border-top: 1px solid #ced4da;
            margin: 12px 0;
            opacity: 0.5;
        }
        
        .assignment-card-body .assignment-detail {
            margin-bottom: 4px;
        }
        
        .assignment-detail i {
            color: #6c757d;
            width: 16px;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--muted);
        }

        .empty-state i {
            font-size: 3em;
            margin-bottom: 15px;
            opacity: 0.3;
        }

        .empty-state p {
            font-size: 1.1em;
            margin: 0;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            backdrop-filter: blur(4px);
        }

        .modal-overlay.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e1e8ed;
        }

        .modal-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.4em;
            font-weight: 600;
            color: #2c3e50;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5em;
            color: #6c757d;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background 0.2s ease;
        }

        .modal-close:hover {
            background: #f8f9fa;
            color: #2c3e50;
        }

        .modal-body {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .assignment-details-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            border-left: 4px solid var(--primary-color);
        }

        .detail-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 10px;
        }

        .detail-row:last-child {
            margin-bottom: 0;
        }

        .detail-label {
            font-weight: 600;
            color: #2c3e50;
            min-width: 100px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-value {
            color: #495057;
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="app-shell" role="application">
        <!-- Main -->
        <main class="main" role="main">
            <header class="page-header">
                <div class="page-title">
                    <i class="fas fa-folder-open" style="color:var(--primary-color);"></i>
                    <div>
                        <div>Edit User</div>
                    </div>
                </div>
            </header>

            <section>
                <div class="edit-form-container">
                    <form id="editUserForm" action="{{ route('users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information Section -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fas fa-user"></i>
                                Basic Information
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name" class="form-label">
                                        Full Name <span class="required">*</span>
                                    </label>
                                    <input type="text" 
                                        id="name" 
                                        name="name" 
                                        class="form-control @error('name') is-invalid @enderror" 
                                        value="{{ old('name', $user->name) }}" 
                                        readonly>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="employee_id" class="form-label">
                                        Employee ID <span class="required">*</span>
                                    </label>
                                    <input type="text" 
                                        id="employee_id" 
                                        name="employee_id" 
                                        class="form-control @error('employee_id') is-invalid @enderror" 
                                        value="{{ old('employee_id', $user->employee_id) }}" 
                                        readonly>
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        Email Address <span class="required">*</span>
                                    </label>
                                    <input type="email" 
                                        id="email" 
                                        name="email" 
                                        class="form-control @error('email') is-invalid @enderror" 
                                        value="{{ old('email', $user->email) }}" 
                                        readonly>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        Phone Number
                                    </label>
                                    <input type="tel" 
                                        id="phone" 
                                        name="phone" 
                                        class="form-control @error('phone') is-invalid @enderror" 
                                        value="{{ old('phone', $user->phone) }}"
                                        readonly>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Location and Company Section -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fas fa-map-marker-alt"></i>
                                Location & Company
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="location" class="form-label">
                                        Location <span class="required">*</span>
                                    </label>
                                    <select id="location" 
                                            name="location" 
                                            class="form-control @error('location') is-invalid @enderror" 
                                            required>
                                        <option value="">Select Location</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->code }}" 
                                                    {{ (old('location', $user->location) == $location->code) ? 'selected' : '' }}>
                                                {{ $location->name }} ({{ $location->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    
                                    <small class="text-muted">Current value: {{ $user->location ?? 'null' }}</small>
                                </div>

                                <div class="form-group">
                                    <label for="company_id" class="form-label">
                                        Company <span class="required">*</span>
                                    </label>
                                    <select id="company_id" 
                                            name="company_id" 
                                            class="form-control @error('company_id') is-invalid @enderror" 
                                            required>
                                        <option value="">Select Company</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" 
                                                    {{ (old('company_id', $user->company_id) == $company->id) ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select transparently
                                    @error('company_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    
                                    <small class="text-muted">Current value: {{ $user->company_id ?? 'null' }}</small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Current Status</label>
                                    <div class="current-status status-{{ $user->status }}">
                                        <i class="fas fa-circle"></i>
                                        {{ ucfirst($user->status) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Module Assignments Section -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-th-large"></i>
                            Module Assignments
                        </div>

                    <!-- Summary Cards View (Default) -->
                    <div id="assignmentsSummary">
                        @if($userModuleAssignments && $userModuleAssignments->count() > 0)
                            @php
                                // Group assignments by module
                                $groupedAssignments = $userModuleAssignments->groupBy('module_name');
                            @endphp
                            
                            <div class="assignments-grid">
                                @foreach($groupedAssignments as $moduleName => $assignments)
                                    <div class="assignment-card" onclick="showModuleDetails('{{ addslashes($moduleName) }}', {{ json_encode($assignments) }})">
                                        <div class="assignment-card-header">
                                            <i class="fas fa-cube"></i>
                                            <strong>{{ $moduleName }}</strong>
                                        </div>
                                        <div class="assignment-card-body">
                                            @foreach($assignments as $assignment)
                                                <div class="assignment-item">
                                                    <div class="assignment-detail">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        <span>{{ $assignment['location_name'] }}</span>
                                                    </div>
                                                    <div class="assignment-detail">
                                                        <i class="fas fa-user-tag"></i>
                                                        <span>{{ $assignment['role_name'] }}</span>
                                                    </div>
                                                    @if(!$loop->last)
                                                        <hr class="assignment-divider">
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>No module assignments yet</p>
                            </div>
                        @endif
                        
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-primary" onclick="toggleEditMode()">
                                <i class="fas fa-edit"></i> Edit Assignments
                            </button>
                        </div>
                    </div>

                            <!-- Edit Form (Hidden by default) -->
                            <div id="assignmentsEditForm" style="display: none;">
                                <div class="alert alert-info mb-3">
                                    <i class="fas fa-info-circle"></i>
                                    Make your changes below and click "Update User" to save.
                                </div>
                                
                                <div class="mb-3">
                                    <button type="button" class="btn btn-sm btn-success" onclick="addModuleRow()">
                                        <i class="fas fa-plus"></i> Add New Assignment
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEditMode()">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </div>

                                <div id="modulesTableContainer" class="table-responsive">

                                    <table class="table table-bordered align-middle" id="modulesTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 25%">Office</th>
                                                <th style="width: 25%">Module</th>
                                                <th style="width: 25%">Role</th>
                                                <th style="width: 15%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($user->modules as $assignmentIndex => $assignment)
                                            
                                                <tr>
                                                    <td>
                                                        <div class="checkbox-group">
                                                            <label>
                                                                <input type="checkbox" name="modules[{{ $assignmentIndex }}][location][]" value="all" onchange="toggleAllCheckboxes(this, 'location', {{ $assignmentIndex }})">
                                                                All Locations
                                                            </label>
                                                            @foreach($locations as $location)
                                                                <label>
                                                                    <input type="checkbox" name="modules[{{ $assignmentIndex }}][location][]" value="{{ $location->code }}" {{ (string)$assignment->pivot->location === (string)$location->code ? 'checked' : '' }} onchange="validateCheckboxes(this, 'location', {{ $assignmentIndex }})">
                                                                    {{ $location->name }}
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="checkbox-group">
                                                            <label>
                                                                <input type="checkbox" name="modules[{{ $assignmentIndex }}][module_id][]" value="all" onchange="toggleAllCheckboxes(this, 'module_id', {{ $assignmentIndex }})">
                                                                All Modules
                                                            </label>
                                                            @foreach($modules as $module)
                                                                <label>
                                                                    <input type="checkbox" name="modules[{{ $assignmentIndex }}][module_id][]" value="{{ $module->id }}" {{ $assignment->id == $module->id ? 'checked' : '' }} onchange="validateCheckboxes(this, 'module_id', {{ $assignmentIndex }})">
                                                                    {{ $module->name }}
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="checkbox-group">
                                                            <label>
                                                                <input type="checkbox" name="modules[{{ $assignmentIndex }}][role_id][]" value="all" onchange="toggleAllCheckboxes(this, 'role_id', {{ $assignmentIndex }})">
                                                                All Roles
                                                            </label>
                                                            @foreach($roles as $role)
                                                                <label>
                                                                    <input type="checkbox" name="modules[{{ $assignmentIndex }}][role_id][]" value="{{ $role->id }}" {{ $assignment->pivot->role_id == $role->id ? 'checked' : '' }} onchange="validateCheckboxes(this, 'role_id', {{ $assignmentIndex }})">
                                                                    {{ $role->name }}
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <!-- No existing assignments -->
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- Form Actions -->
                        <div class="form-section">
                            <div style="display: flex; gap: 15px; justify-content: space-between; align-items: center;">
                                <!-- Left side - Disable/Enable button -->
                                <div>
                                    @if($user->status === 'inactive')
                                        <button type="button" class="btn btn-success" onclick="toggleUserStatus('{{ $user->id }}', 'enable')">
                                            <i class="fas fa-user-check"></i>
                                            Enable User
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-danger" onclick="toggleUserStatus('{{ $user->id }}', 'disable')">
                                            <i class="fas fa-user-slash"></i>
                                            Disable User
                                        </button>
                                    @endif
                                </div>
                                <!-- Right side - Save and Cancel buttons -->
                                <div style="display: flex; gap: 15px;">
                                    <a href="{{ route('dashboard.index') }}" class="btn btn-ghost">
                                        <i class="fas fa-times"></i>
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        Update User
                                    </button>
                                </div>
                            </div>
                        </div> 
                    </form>
                </div>
            </section>
        </main>
    </div>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="loading-spinner" aria-hidden="true">
        <div class="spinner"></div>
        <div id="loadingText">Processing…</div>
    </div>

    <!-- Modal for Module Details -->
    <div id="moduleModal" class="modal-overlay" onclick="closeModalOnOutsideClick(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <div class="modal-title">
                    <i class="fas fa-cube"></i>
                    <span id="modalModuleName"></span>
                </div>
                <button class="modal-close" onclick="closeModuleModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Details will be inserted here -->
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js" defer></script>

<script>
// ============= GLOBAL VARIABLES =============
let rowIndex = {{ count($user->modules ?? []) }};

// ============= UTILITY FUNCTIONS =============

/**
 * Show loading spinner
 */
window.showLoading = () => {
    const el = document.getElementById('loadingSpinner');
    if (el) {
        el.setAttribute('aria-hidden', 'false');
        el.style.display = 'block';
    }
};

/**
 * Hide loading spinner
 */
window.hideLoading = () => {
    const el = document.getElementById('loadingSpinner');
    if (el) {
        el.setAttribute('aria-hidden', 'true');
        el.style.display = 'none';
    }
};

/**
 * Show alert message
 */
window.showAlert = (type, title, message) => {
    if (window.Swal) {
        Swal.fire({ 
            icon: type, 
            title, 
            text: message, 
            toast: true, 
            position: 'top-end', 
            showConfirmButton: false, 
            timer: 3000
        });
    } else {
        alert(title + "\n\n" + message);
    }
};

/**
 * Confirm action dialog
 */
window.confirmAction = (title, text, callback) => {
    if (window.Swal) {
        Swal.fire({
            title, 
            text, 
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim(),
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, proceed!'
        }).then(result => {
            if (result.isConfirmed && typeof callback === 'function') {
                callback();
            }
        });
    } else if (confirm(title + "\n\n" + text)) {
        if (typeof callback === 'function') {
            callback();
        }
    }
};

// ============= CHECKBOX MANAGEMENT FUNCTIONS =============

/**
 * Initialize "All" checkboxes based on current state
 * CRITICAL: This ensures "All" checkboxes reflect the actual selected items
 */
function initializeAllCheckboxes() {
    console.log('=== Initializing All Checkboxes ===');
    const rows = document.querySelectorAll('#modulesTable tbody tr');
    
    rows.forEach((row, rowIndex) => {
        ['location', 'module_id', 'role_id'].forEach(field => {
            const checkboxGroup = row.querySelector(
                `.checkbox-group:has(input[name^="modules[${rowIndex}][${field}][]"])`
            );
            if (!checkboxGroup) return;
            
            const allCheckbox = checkboxGroup.querySelector(`input[value="all"]`);
            const individualCheckboxes = Array.from(
                checkboxGroup.querySelectorAll(
                    `input[name^="modules[${rowIndex}][${field}][]"]:not([value="all"])`
                )
            );
            
            if (individualCheckboxes.length > 0 && allCheckbox) {
                const checkedCount = individualCheckboxes.filter(cb => cb.checked).length;
                const totalCount = individualCheckboxes.length;
                
                if (checkedCount === totalCount) {
                    // All individual items are checked
                    allCheckbox.checked = true;
                    allCheckbox.indeterminate = false;
                    console.log(`✓ All ${field} selected in row ${rowIndex} (${checkedCount}/${totalCount})`);
                } else if (checkedCount === 0) {
                    // No items are checked
                    allCheckbox.checked = false;
                    allCheckbox.indeterminate = false;
                    console.log(`✗ No ${field} selected in row ${rowIndex}`);
                } else {
                    // Some items are checked (partial selection)
                    allCheckbox.checked = false;
                    allCheckbox.indeterminate = true;
                    console.log(`◐ ${checkedCount}/${totalCount} ${field} selected in row ${rowIndex}`);
                }
            }
        });
    });
    console.log('=== Initialization Complete ===');
}

/**
 * Toggle all checkboxes when "All" is clicked
 * FIXED: Properly handles the "All" checkbox behavior
 */
window.toggleAllCheckboxes = function(allCheckbox, field, index) {
    const checkboxGroup = allCheckbox.closest('.checkbox-group');
    if (!checkboxGroup) return;
    
    const checkboxes = checkboxGroup.querySelectorAll(
        `input[name^="modules[${index}][${field}][]"]:not([value="all"])`
    );
    
    if (allCheckbox.checked) {
        // Check all individual checkboxes
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        allCheckbox.indeterminate = false;
        console.log(`✓ Checked all ${field} for row ${index} (${checkboxes.length} items)`);
    } else {
        // Uncheck all individual checkboxes
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        allCheckbox.indeterminate = false;
        console.log(`✗ Unchecked all ${field} for row ${index}`);
    }
};

/**
 * Validate checkboxes and sync with "All" checkbox
 * FIXED: Properly syncs individual checkboxes with "All" checkbox
 */
window.validateCheckboxes = function(checkbox, field, index) {
    const checkboxGroup = checkbox.closest('.checkbox-group');
    if (!checkboxGroup) return;
    
    const allCheckbox = checkboxGroup.querySelector(
        `input[name="modules[${index}][${field}][]"][value="all"]`
    );
    const individualCheckboxes = Array.from(
        checkboxGroup.querySelectorAll(
            `input[name^="modules[${index}][${field}][]"]:not([value="all"])`
        )
    );
    
    if (checkbox.value !== 'all' && allCheckbox && individualCheckboxes.length > 0) {
        const checkedCount = individualCheckboxes.filter(cb => cb.checked).length;
        const totalCount = individualCheckboxes.length;
        
        if (checkedCount === totalCount) {
            // All items are now checked
            allCheckbox.checked = true;
            allCheckbox.indeterminate = false;
            console.log(`✓ All ${field} now selected in row ${index}`);
        } else if (checkedCount === 0) {
            // No items are checked
            allCheckbox.checked = false;
            allCheckbox.indeterminate = false;
            console.log(`✗ No ${field} selected in row ${index}`);
        } else {
            // Some items are checked (partial)
            allCheckbox.checked = false;
            allCheckbox.indeterminate = true;
            console.log(`◐ ${checkedCount}/${totalCount} ${field} selected in row ${index}`);
        }
    }
};

// ============= UI TOGGLE FUNCTIONS =============

/**
 * Toggle between summary and edit mode
 * FIXED: Always re-initialize checkboxes when entering edit mode
 */
window.toggleEditMode = function() {
    const summary = document.getElementById('assignmentsSummary');
    const editForm = document.getElementById('assignmentsEditForm');
    
    if (summary && editForm) {
        if (summary.style.display === 'none') {
            // Switch back to summary view
            summary.style.display = 'block';
            editForm.style.display = 'none';
        } else {
            // Switch to edit mode
            summary.style.display = 'none';
            editForm.style.display = 'block';
            
            // 🔥 CRITICAL: Always initialize checkboxes when entering edit mode
            setTimeout(() => {
                console.log('Entering edit mode - initializing checkboxes');
                initializeAllCheckboxes();
            }, 100);
        }
    }
};

/**
 * Add a new module assignment row
 */
window.addModuleRow = function() {
    const tableBody = document.querySelector('#modulesTable tbody');
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td>
            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="modules[${rowIndex}][location][]" value="all" onchange="toggleAllCheckboxes(this, 'location', ${rowIndex})">
                    All Locations
                </label>
                @foreach($locations as $location)
                    <label>
                        <input type="checkbox" name="modules[${rowIndex}][location][]" value="{{ $location->code }}" onchange="validateCheckboxes(this, 'location', ${rowIndex})">
                        {{ $location->name }}
                    </label>
                @endforeach
            </div>
        </td>
        <td>
            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="modules[${rowIndex}][module_id][]" value="all" onchange="toggleAllCheckboxes(this, 'module_id', ${rowIndex})">
                    All Modules
                </label>
                @foreach($modules as $module)
                    <label>
                        <input type="checkbox" name="modules[${rowIndex}][module_id][]" value="{{ $module->id }}" onchange="validateCheckboxes(this, 'module_id', ${rowIndex})">
                        {{ $module->name }}
                    </label>
                @endforeach
            </div>
        </td>
        <td>
            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="modules[${rowIndex}][role_id][]" value="all" onchange="toggleAllCheckboxes(this, 'role_id', ${rowIndex})">
                    All Roles
                </label>
                @foreach($roles as $role)
                    <label>
                        <input type="checkbox" name="modules[${rowIndex}][role_id][]" value="{{ $role->id }}" onchange="validateCheckboxes(this, 'role_id', ${rowIndex})">
                        {{ $role->name }}
                    </label>
                @endforeach
            </div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tableBody.appendChild(newRow);
    rowIndex++;
};

/**
 * Remove a table row
 */
window.removeRow = function(button) {
    const row = button.closest('tr');
    if (row) {
        row.remove();
    }
};

// ============= USER STATUS FUNCTIONS =============

/**
 * Toggle user status (enable/disable)
 */
window.toggleUserStatus = function(userId, action) {
    const actionText = action === 'enable' ? 'enable' : 'disable';
    const actionTitle = action === 'enable' ? 'Enable User' : 'Disable User';
    const actionMessage = action === 'enable'
        ? 'Are you sure you want to enable this user? This will restore access to all systems.'
        : 'Are you sure you want to disable this user? This will revoke access to all systems.';
    
    confirmAction(actionTitle, actionMessage, function() {
        showLoading();
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            showAlert('error', 'Error', 'CSRF token not found. Please refresh the page.');
            hideLoading();
            return;
        }
    
        axios.post(`/api/users/${userId}/${actionText}`, {}, {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken.content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.data.success) {
                const statusMessage = action === 'enable' 
                    ? 'User has been enabled successfully' 
                    : 'User has been disabled successfully';
                showAlert('success', action === 'enable' ? 'User Enabled' : 'User Disabled', response.data.message || statusMessage);
            
                const statusBadge = document.querySelector('.current-status');
                if (statusBadge) {
                    const newStatus = action === 'enable' ? 'active' : 'inactive';
                    statusBadge.className = `current-status status-${newStatus}`;
                    statusBadge.innerHTML = `
                        <i class="fas fa-circle"></i>
                        ${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}
                    `;
                }
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert('error', 'Error', response.data.message || `Failed to ${actionText} user`);
            }
            hideLoading();
        })
        .catch(error => {
            console.error(`Error ${actionText}ing user:`, error);
            hideLoading();
            const errorMessage = error.response?.data?.message || `Failed to ${actionText} user: ` + error.message;
            showAlert('error', 'Error', errorMessage);
        });
    });
};

/**
 * Provision user to Azure AD
 */
window.provisionUserToAzure = function(userId) {
    confirmAction('Provision to Azure', 'This will create an Azure AD account for this user. Continue?', function() {
        showLoading();
        axios.post(`/users/${userId}/provision-azure`)
            .then(response => {
                if (response.data.success) {
                    showAlert('success', 'Success', response.data.message);
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showAlert('error', 'Error', response.data.message);
                }
                hideLoading();
            })
            .catch(error => {
                console.error('Provision error:', error);
                hideLoading();
                showAlert('error', 'Error', error.response?.data?.message || 'Failed to provision user');
            });
    });
};

// ============= MODAL FUNCTIONS =============

/**
 * Show module details modal
 */
window.showModuleDetails = function(moduleName, assignments) {
    const modal = document.getElementById('moduleModal');
    const modalTitle = document.getElementById('modalModuleName');
    const modalBody = document.getElementById('modalBody');
    
    modalTitle.textContent = moduleName;
    modalBody.innerHTML = '';
    
    assignments.forEach((assignment, index) => {
        const detailsCard = document.createElement('div');
        detailsCard.className = 'assignment-details-card';
        
        detailsCard.innerHTML = `
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 15px; font-weight: 600; color: var(--primary-color);">
                <i class="fas fa-list-ul"></i>
                <span>Assignment ${index + 1}</span>
            </div>
            <div class="detail-row">
                <div class="detail-label">
                    <i class="fas fa-map-marker-alt"></i>
                    Location:
                </div>
                <div class="detail-value">${assignment.location_name}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">
                    <i class="fas fa-user-tag"></i>
                    Role:
                </div>
                <div class="detail-value">${assignment.role_name}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">
                    <i class="fas fa-cube"></i>
                    Module:
                </div>
                <div class="detail-value">${moduleName}</div>
            </div>
        `;
        
        modalBody.appendChild(detailsCard);
    });
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
};

/**
 * Close module details modal
 */
window.closeModuleModal = function() {
    const modal = document.getElementById('moduleModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
};

/**
 * Close modal when clicking outside
 */
window.closeModalOnOutsideClick = function(event) {
    if (event.target.id === 'moduleModal') {
        closeModuleModal();
    }
};

// ============= FORM SUBMISSION =============

/**
 * Handle form submission
 * CRITICAL FIX: Excludes "all" values and only sends actual IDs
 */
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.innerHTML : '';
    
    const rows = document.querySelectorAll('#modulesTable tbody tr');
    let isValid = true;
    let validationErrors = [];
    
    // Validate that each row has at least one selection in each column
    rows.forEach((row, index) => {
        ['location', 'module_id', 'role_id'].forEach(field => {
            // Check ONLY non-"all" checkboxes
            const checkboxes = row.querySelectorAll(`input[name^="modules[${index}][${field}][]"]:not([value="all"])`);
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            if (!anyChecked) {
                isValid = false;
                const fieldName = field.replace('_id', '').replace('_', ' ');
                validationErrors.push(`Row ${index + 1}: Select at least one ${fieldName}`);
            }
        });
    });
    
    if (!isValid) {
        showAlert('error', 'Validation Error', validationErrors.join('\n'));
        return;
    }
    
    console.log(`Validating ${rows.length} rows...`);
    
    // Show loading state
    showLoading();
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    }
    
    // Build FormData
    const formData = new FormData();
    
    const csrfToken = document.querySelector('input[name="_token"]');
    if (csrfToken) {
        formData.append('_token', csrfToken.value);
    }
    formData.append('_method', 'PUT');
    
    // Helper function to add field ONLY if it exists AND is not readonly
    const addFieldIfEditable = (fieldId, fieldName) => {
        const field = document.getElementById(fieldId);
        if (field && !field.hasAttribute('readonly') && !field.disabled) {
            formData.append(fieldName, field.value);
            console.log(`Added editable field: ${fieldName} = ${field.value}`);
        } else if (field && (field.hasAttribute('readonly') || field.disabled)) {
            console.log(`Skipped readonly/disabled field: ${fieldName}`);
        }
    };
    
    // Only add editable fields (phone, location, company_id)
    addFieldIfEditable('phone', 'phone');
    addFieldIfEditable('location', 'location');
    addFieldIfEditable('company_id', 'company_id');
    
    // ===== CRITICAL FIX: Process module assignments and EXCLUDE "all" =====
    console.log('=== Processing Module Assignments ===');
    
    rows.forEach((row, rowIndex) => {
        // Get all checked checkboxes that are NOT "all"
        const locationCheckboxes = Array.from(
            row.querySelectorAll(`input[name^="modules[${rowIndex}][location][]"]:checked`)
        ).filter(cb => cb.value !== 'all');  // CRITICAL: Filter out "all"
        
        const moduleCheckboxes = Array.from(
            row.querySelectorAll(`input[name^="modules[${rowIndex}][module_id][]"]:checked`)
        ).filter(cb => cb.value !== 'all');  // CRITICAL: Filter out "all"
        
        const roleCheckboxes = Array.from(
            row.querySelectorAll(`input[name^="modules[${rowIndex}][role_id][]"]:checked`)
        ).filter(cb => cb.value !== 'all');  // CRITICAL: Filter out "all"
        
        console.log(`Row ${rowIndex}:`, {
            locations: locationCheckboxes.map(cb => cb.value),
            modules: moduleCheckboxes.map(cb => cb.value),
            roles: roleCheckboxes.map(cb => cb.value)
        });
        
        // Only add if we have actual values (not "all")
        if (locationCheckboxes.length > 0 && moduleCheckboxes.length > 0 && roleCheckboxes.length > 0) {
            locationCheckboxes.forEach(checkbox => {
                formData.append(`modules[${rowIndex}][location][]`, checkbox.value);
            });
            
            moduleCheckboxes.forEach(checkbox => {
                formData.append(`modules[${rowIndex}][module_id][]`, checkbox.value);
            });
            
            roleCheckboxes.forEach(checkbox => {
                formData.append(`modules[${rowIndex}][role_id][]`, checkbox.value);
            });
        }
    });

    // Debug logging
    console.log('=== FormData Contents (Only Real Values) ===');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    console.log('=========================');

    // Submit form
    axios.post(this.action, formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        }
    })
    .then(response => {
        console.log('Success response:', response.data);
        showAlert('success', 'Success', 'User updated successfully');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
        hideLoading();
        
        setTimeout(() => {
            window.location.href = '{{ route("dashboard.index") }}';
        }, 1500);
    })
    .catch(error => {
        console.error('Update error:', error);
        console.error('Error response:', error.response?.data);
        
        let errorMessage = 'Failed to update user';
        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            const errorList = Object.entries(errors)
                .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
                .join('\n');
            errorMessage = errorList;
            console.error('Validation errors:', errors);
        } else if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        }
        
        showAlert('error', 'Validation Error', errorMessage);
        
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
        hideLoading();
    });
});

// ============= EVENT LISTENERS =============

/**
 * Escape key to close modal
 */
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModuleModal();
    }
});

/**
 * DOM Content Loaded - Initialize everything
 * FIXED: Always initialize "All" checkboxes on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing...');
    
    // Setup Axios defaults
    if (window.axios) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        }
    }

    // Show validation errors from server
    @if ($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            html: `<ul style="text-align:left;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>`
        });
    @endif
    
    // Show success message
    @if (session('success'))
        showAlert('success', 'Success', '{{ session("success") }}');
    @endif
    
    // Show error message
    @if (session('error'))
        showAlert('error', 'Error', '{{ session("error") }}');
    @endif

    // 🔥 CRITICAL FIX: Initialize "All" checkboxes whenever rows exist
    // This ensures "All" checkboxes reflect the actual state of checked items
    const moduleTable = document.getElementById('modulesTable');
    if (moduleTable) {
        const rows = moduleTable.querySelectorAll('tbody tr');
        if (rows.length > 0) {
            console.log(`Found ${rows.length} existing module rows - initializing checkboxes`);
            setTimeout(() => {
                initializeAllCheckboxes();
            }, 100);
        }
    }
});
</script>
</body>
</html>