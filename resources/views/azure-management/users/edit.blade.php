@extends('adminlte::page')

@section('title', 'Edit User')

@section('content_header')
    <div class="page-header-container">
        <div class="header-left">
            <div class="page-title-icon">
                <i class="fas fa-user-edit"></i>
            </div>
            <h1 class="page-main-title">Edit User</h1>
            <div class="current-status status-{{ $user->status }}">
                <i class="fas fa-circle"></i>
                {{ ucfirst($user->status) }}
            </div>
        </div>
        <a href="{{ route('azure.users.show', $user->Id) }}" class="back-to-dashboard">
            <span>Back to User Details</span>
            <i class="fas fa-arrow-right"></i>
        </a>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
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
                                        Full Name <span style="color: var(--danger);">*</span>
                                    </label>
                                    <input type="text" id="name" name="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $user->displayName ?? ($user->name ?? '')) }}" readonly>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="employee_id" class="form-label">
                                        Employee ID <span style="color: var(--danger);">*</span>
                                    </label>
                                    <input type="text" id="employee_id" name="employee_id"
                                        class="form-control @error('employee_id') is-invalid @enderror"
                                        value="{{ old('employee_id', $user->EmployeCode ?? ($user->employeeId ?? '')) }}"
                                        readonly>
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        Email Address <span style="color: var(--danger);">*</span>
                                    </label>
                                    <input type="email" id="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $user->mail ?? ($user->userPrincipalName ?? '')) }}"
                                        readonly>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        Phone Number
                                    </label>
                                    <input type="tel" id="phone" name="phone"
                                        class="form-control @error('phone') is-invalid @enderror"
                                        value="{{ old('phone', $user->telephoneNumber ?? ($user->mobilePhone ?? '')) }}"
                                        readonly>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="company_name" class="form-label">
                                        Company
                                    </label>
                                    @php
                                        $userCompanyDisplay = old('company_name', $user->companyName ?? 'Not Assigned');
                                    @endphp
                                    <input type="text" id="company_name" name="company_name" class="form-control"
                                        value="{{ $userCompanyDisplay }}" readonly
                                        style="background-color: #f8f9fa; cursor: not-allowed;">

                                    <small class="form-text text-muted">
                                        Company assignment cannot be changed after user creation
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="job_title" class="form-label">
                                        Job Title
                                    </label>
                                    <input type="text" id="job_title" name="job_title" class="form-control"
                                        value="{{ old('job_title', $user->jobTitle ?? '') }}" readonly
                                        style="background-color: #f8f9fa; cursor: not-allowed;">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fas fa-building"></i>
                                Base Office
                            </div>

                            <div class="form-row single-column">
                                <div class="form-group">
                                    <label for="scm_base_office" class="form-label">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Primary Office<span style="color: var(--danger);">*</span>
                                    </label>

                                    @php
                                        // ✅ FIX: Trim whitespace from BaseOfficeCode
                                        $currentBaseOffice = $user->BaseOfficeCode ? trim($user->BaseOfficeCode) : null;

                                        // Debug log
                                        // \Log::info('Edit page - Base Office Debug', [
                                        //     'raw_value' => $user->BaseOfficeCode,
                                        //     'trimmed_value' => $currentBaseOffice,
                                        //     'is_null' => is_null($currentBaseOffice),
                                        // ]);

                                    @endphp

                                    <div style="display: flex; gap: 12px; align-items: flex-start;">
                                        <select name="scm_base_office" id="scm_base_office"
                                            class="form-control @error('scm_base_office') is-invalid @enderror"
                                            style="flex: 1;">

                                            @foreach ($offices as $office)
                                                @php
                                                    $officeCode = trim($office->Code);
                                                    $currentBaseOffice = $user->BaseOfficeCode
                                                        ? trim($user->BaseOfficeCode)
                                                        : null;
                                                    $isSelected = $currentBaseOffice === $officeCode;
                                                @endphp

                                                <option value="{{ $officeCode }}" {{ $isSelected ? 'selected' : '' }}>
                                                    {{ $office->Name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        {{-- <button type="button" class="btn btn-primary" id="saveBaseOfficeBtn"
                                            onclick="saveBaseOffice()"
                                            style="white-space: nowrap; min-width: 150px; display: none;">
                                            <i class="fas fa-save"></i>
                                            Save Base Office
                                        </button>

                                        <button type="button" class="btn btn-secondary" id="cancelBaseOfficeBtn"
                                            onclick="cancelBaseOfficeChange()"
                                            style="white-space: nowrap; min-width: 120px; display: none;">
                                            <i class="fas fa-times"></i>
                                            Cancel
                                        </button> --}}
                                    </div>

                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Current: <strong>{{ $currentBaseOffice ?? 'Not Set' }}</strong> |
                                        Changes will be saved when you click "Update User".
                                    </small>

                                    @error('scm_base_office')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Module Assignments Section -->
                        <div class="form-section">
                            <div class="form-section-title"
                                style="display: flex; justify-content: space-between; align-items: center;">
                                <div><i class="fas fa-th-large"></i> Module Assignments</div>
                                <button type="button" class="btn btn-sm btn-success" onclick="addNewModule()">
                                    <i class="fas fa-plus"></i> Add New Assignment
                                </button>
                            </div>
                            <div id="modulesGrid" class="modules-grid"></div>

                            <!-- Form Actions - Inside Module Assignments, only visible when editing existing modules -->
                            <div class="module-actions-section" id="moduleActionsSection">
                                <div class="form-actions-info">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>You have unsaved changes to module assignments</span>
                                </div>
                                <div class="form-actions-buttons">
                                    <button type="button" class="btn btn-ghost" onclick="cancelAllChanges()">
                                        <i class="fas fa-times"></i>
                                        Cancel Changes
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="updateUser()">
                                        <i class="fas fa-save"></i>
                                        Update User
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="hiddenInputsContainer"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Module Details -->
    <div id="moduleModal" class="modal-overlay" onclick="closeModalOnOverlay(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <div class="modal-title">
                    <i class="fas fa-cube" style="color: var(--primary-color)"></i>
                    <span id="modalModuleName">Module Name</span>
                </div>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="viewModeBanner" class="view-mode-banner" style="display: none;">
                    <div class="info">
                        <i class="fas fa-eye"></i>
                        <span>View Mode - Click Edit to make changes</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" onclick="enableEditMode()">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
                <div id="modalAssignmentsContainer"></div>
            </div>
            <div class="modal-footer" id="modalFooter">
                <button type="button" class="btn btn-ghost" onclick="cancelModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="saveModalChanges()">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* Fix for Base Office Dropdown */
        #scm_base_office {
            appearance: auto;
            -webkit-appearance: menulist;
            -moz-appearance: menulist;
            background-color: white;
            background-image: none;
            cursor: pointer;
            height: auto;
            min-height: 44px;
        }

        #scm_base_office:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        /* Button styling for Save Base Office */
        #saveBaseOfficeBtn {
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        #saveBaseOfficeBtn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* ---------- Full Screen Container ---------- */
        .full-screen-container {
            width: 100%;
            max-width: none;
            padding: 0;
            margin: 0;
        }

        .page-content-wrapper {
            padding: 0 20px;
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        /* ---------- Page Header Styles ---------- */
        .page-header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px 0;
            border-bottom: 2px solid #e1e8ed;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-title-icon {
            font-size: 1.8em;
            color: #007bff;
            display: flex;
            align-items: center;
        }

        .page-main-title {
            font-size: 1.9em;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
            letter-spacing: -0.02em;
        }

        .current-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffeaa7;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }

        .header-right {
            display: flex;
            align-items: center;
        }

        .back-to-dashboard {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #f8f9fa;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            color: #2c3e50;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-to-dashboard:hover {
            background: #e8f0ff;
            border-color: #007bff;
            color: #007bff;
            transform: translateX(2px);
        }

        .back-to-dashboard i {
            font-size: 1.1em;
        }

        /* ---------- Edit Form Styles ---------- */
        .edit-form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
            overflow: hidden;
            position: relative;
        }

        .edit-form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #3498db, #8e44ad);
            border-radius: 20px 20px 0 0;
        }

        .form-section {
            margin-bottom: 40px;
            padding-bottom: 32px;
            border-bottom: 2px solid #f0f3f7;
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .form-section-title {
            font-size: 1.3em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 12px;
            border-bottom: 3px solid #e8f0ff;
        }

        .form-section-title i {
            color: #007bff;
            font-size: 1.1em;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            margin-bottom: 24px;
        }

        /* Single column override for full-width fields */
        .form-row.single-column {
            grid-template-columns: 1fr;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-label .required {
            color: #e74c3c;
            margin-left: 2px;
        }

        .form-label i {
            color: #007bff;
            font-size: 0.9em;
        }

        .form-control {
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
            width: 100%;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            background: #fafbfc;
        }

        .form-control:read-only,
        .form-control[readonly] {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
            border-color: #ced4da;
        }

        .form-control.is-invalid {
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }

        .invalid-feedback {
            color: #e74c3c;
            font-size: 0.875em;
            margin-top: 5px;
            display: block;
            font-weight: 500;
        }

        /* Module Cards Grid */
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .module-card {
            background: white;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            padding: 24px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 12px;
        }

        .module-card:hover {
            border-color: #007bff;
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .module-card.new-module {
            border-color: #28a745;
            border-style: dashed;
            background: #f0fff4;
        }

        .card-icon {
            font-size: 2.5em;
            color: #007bff;
            margin-bottom: 8px;
        }

        .module-card.new-module .card-icon {
            color: #28a745;
        }

        .card-title {
            font-size: 1.1em;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .assignment-count {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: #e8f0ff;
            color: #007bff;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85em;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3em;
            margin-bottom: 15px;
            opacity: 0.3;
        }

        .empty-grid {
            grid-column: 1/-1;
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-grid i {
            font-size: 3em;
            opacity: 0.3;
            margin-bottom: 15px;
            display: block;
        }

        /* Form Actions - Inside Module Assignments, hidden by default */
        .module-actions-section {
            display: none;
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 15px 20px;
            margin-top: 20px;
            animation: slideDown 0.3s ease;
        }

        .module-actions-section.visible {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-actions-info {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #856404;
            font-weight: 600;
        }

        .form-actions-info i {
            font-size: 1.3em;
        }

        .form-actions-buttons {
            display: flex;
            gap: 12px;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1050;
            backdrop-filter: blur(4px);
        }

        .modal-overlay.active {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 900px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-header {
            padding: 24px 30px;
            border-bottom: 2px solid #e1e8ed;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.4em;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5em;
            color: #6c757d;
            cursor: pointer;
            padding: 4px 8px;
            transition: all 0.2s;
        }

        .modal-close:hover {
            color: #2c3e50;
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 30px;
        }

        .modal-footer {
            padding: 20px 30px;
            border-top: 2px solid #e1e8ed;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: #f8f9fa;
            border-radius: 0 0 16px 16px;
        }

        .modal-footer.hidden {
            display: none;
        }

        .assignment-item {
            background: #fafbfc;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 12px;
            transition: all 0.2s ease;
        }

        .assignment-item:hover {
            border-color: #dee2e6;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .assignment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .assignment-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .info-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field-label {
            font-size: 0.85em;
            color: #6c757d;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .radio-group {
            margin-bottom: 12px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: white;
            border: 2px solid #e1e8ed;
            border-radius: 6px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .radio-option:hover {
            border-color: #007bff;
            background: #f8faff;
        }

        .radio-option input[type="radio"] {
            cursor: pointer;
        }

        .radio-option label {
            cursor: pointer;
            font-weight: 500;
            margin: 0;
            flex: 1;
        }

        .radio-option input[type="radio"]:disabled {
            cursor: not-allowed;
        }

        .radio-option.disabled {
            opacity: 0.6;
            background: #f8f9fa;
            cursor: not-allowed;
        }

        .radio-option.disabled:hover {
            border-color: #e1e8ed;
            background: #f8f9fa;
        }

        .checkboxes-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-top: 10px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .checkbox-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            background: white;
            border: 1px solid #e1e8ed;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .checkbox-option:hover {
            border-color: #007bff;
        }

        .checkbox-option input[type="checkbox"] {
            cursor: pointer;
        }

        .checkbox-option label {
            cursor: pointer;
            margin: 0;
            font-size: 0.9em;
            flex: 1;
        }

        .checkbox-option input[type="checkbox"]:disabled {
            cursor: not-allowed;
        }

        .checkbox-option.disabled {
            opacity: 0.6;
            background: #f1f3f5;
        }

        .empty-assignments {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }

        .empty-assignments i {
            font-size: 2.5em;
            opacity: 0.3;
            margin-bottom: 10px;
        }

        /* Module Selector Styles for SweetAlert */
        .module-selector-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
            padding: 10px;
            max-height: 400px;
            overflow-y: auto;
        }

        .module-selector-card {
            background: white;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            min-height: 120px;
            justify-content: center;
        }

        .module-selector-card:hover {
            border-color: #007bff;
            background: #f8faff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(11, 102, 255, 0.15);
        }

        .module-selector-card i {
            font-size: 2em;
            color: #007bff;
            margin-bottom: 5px;
        }

        .module-selector-card .module-name {
            font-weight: 600;
            font-size: 0.95em;
            color: #2c3e50;
        }

        /* View Mode Styles */
        .view-mode-banner {
            background: #e8f0ff;
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .view-mode-banner .info {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #007bff;
            font-weight: 600;
        }

        /* Button Styles */
        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .btn-primary {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            color: #fff;
            background-color: #0069d9;
            border-color: #0062cc;
        }

        .btn-success {
            color: #fff;
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-success:hover {
            color: #fff;
            background-color: #218838;
            border-color: #1e7e34;
        }

        .btn-danger {
            color: #fff;
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            color: #fff;
            background-color: #c82333;
            border-color: #bd2130;
        }

        .btn-warning {
            color: #212529;
            background-color: #ffc107;
            border-color: #ffc107;
        }

        .btn-warning:hover {
            color: #212529;
            background-color: #e0a800;
            border-color: #d39e00;
        }

        .btn-ghost {
            color: #6c757d;
            background-color: transparent;
            border-color: #6c757d;
        }

        .btn-ghost:hover {
            color: #fff;
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }

        @media (max-width: 768px) {
            .page-content-wrapper {
                padding: 0 15px;
            }

            .page-header-container {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .header-left {
                flex-wrap: wrap;
            }

            .page-main-title {
                font-size: 1.5em;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .edit-form-container {
                padding: 20px;
            }

            .assignment-info {
                grid-template-columns: 1fr;
            }

            .module-actions-section.visible {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }

            .form-actions-buttons {
                width: 100%;
                flex-direction: column;
            }

            .form-actions-buttons .btn {
                width: 100%;
            }
        }

        /* Disabled module card in selector */
        .module-selector-card.disabled {
            opacity: 0.5;
            background: #f5f5f5;
            cursor: not-allowed;
            border-color: #ddd;
            position: relative;
        }

        .module-selector-card.disabled:hover {
            border-color: #ddd;
            background: #f5f5f5;
            transform: none;
            box-shadow: none;
        }

        .module-selector-card.disabled i {
            color: #999;
        }

        .module-selector-card.disabled .module-name {
            color: #999;
        }

        /* Badge for already assigned modules */
        .already-assigned-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #e74c3c;
            color: white;
            font-size: 0.7em;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Visual indicator on module selector */
        .module-selector-card.disabled::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 3em;
            color: #27ae60;
            opacity: 0.2;
            font-weight: bold;
        }

        /* Roles Checkboxes Grid for OPS Module */
        .roles-checkboxes-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 10px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
            width: 100%;
        }

        .roles-checkboxes-grid.ops-layout {
            grid-template-columns: repeat(3, minmax(120px, 1fr));
        }

        /* Responsive adjustments for roles */
        @media (max-width: 992px) {
            .roles-checkboxes-grid.ops-layout {
                grid-template-columns: repeat(2, 1fr);
            }

            .assignment-info {
                grid-template-columns: 1fr 1fr !important;
            }
        }

        @media (max-width: 768px) {
            .roles-checkboxes-grid.ops-layout {
                grid-template-columns: 1fr;
            }

            .assignment-info {
                grid-template-columns: 1fr !important;
            }

            .roles-checkboxes-grid {
                padding: 8px;
            }
        }

        /* Better checkbox layout for OPS */
        .ops-roles-field .checkbox-option {
            min-height: 40px;
            display: flex;
            align-items: center;
            padding: 8px 12px;
            margin: 0;
        }

        .ops-roles-field .field-label {
            margin-bottom: 10px;
        }

        /* Ensure modal body uses full width */
        .modal-body {
            padding: 25px;
        }

        /* Make assignment info section wider */
        .assignment-info {
            gap: 20px !important;
        }

        /* Specific styling for OPS module */
        .ops-roles-container {
            width: 100%;
        }

        /* OPS Module Specific Styles */
        .ops-module-layout {
            display: flex;
            flex-direction: column;
            gap: 25px;
            width: 100%;
        }

        .ops-top-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            width: 100%;
        }

        .ops-form-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #e1e8ed;
        }

        .ops-roles-section {
            width: 100%;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #e1e8ed;
        }

        .roles-grid-3col {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 10px;
            width: 100%;
        }

        .roles-grid-3col .checkbox-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            background: white;
            border: 1px solid #e1e8ed;
            border-radius: 4px;
            transition: all 0.2s;
            width: 100%;
            box-sizing: border-box;
        }

        .roles-grid-3col .checkbox-option:hover {
            border-color: #007bff;
            background: #f8faff;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .roles-grid-3col {
                grid-template-columns: repeat(2, 1fr);
            }

            .ops-top-section {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .roles-grid-3col {
                grid-template-columns: 1fr;
            }

            .ops-module-layout {
                gap: 15px;
            }

            .ops-form-section,
            .ops-roles-section {
                padding: 12px;
            }
        }

        /* Make modal content wider */
        .modal-content {
            max-width: 1000px !important;
            /* Increased from 900px */
            width: 95% !important;
        }

        /* Ensure modal body uses full width */
        .modal-body {
            padding: 25px 30px;
        }

        /* Adjust assignment item padding */
        .assignment-item {
            padding: 20px;
        }

        /* ============================================================================
                                                                                       WINDOWS SIGN-IN MODULE STYLES
                                                                                       ============================================================================ */

        /* Highlight Windows Sign-In in module selector */
        .windows-signin-highlight {
            border-color: #0066cc !important;
            background: linear-gradient(135deg, #e8f4fd 0%, #f0f8ff 100%) !important;
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.2) !important;
            position: relative;
        }

        .windows-signin-highlight:hover {
            border-color: #004080 !important;
            transform: translateY(-6px) !important;
            box-shadow: 0 8px 20px rgba(0, 102, 204, 0.3) !important;
        }

        .windows-signin-highlight i {
            color: #0066cc !important;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }
        }

        /* Required badge for mandatory modules */
        .required-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            font-size: 0.65em;
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(255, 107, 107, 0.4);
            animation: blink 1.5s infinite;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        /* Windows Sign-In info box in modal */
        .windows-signin-info-box {
            padding: 16px 20px;
            background: linear-gradient(135deg, #e8f4fd 0%, #f0f8ff 100%);
            border: 2px solid #0066cc;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .windows-signin-info-box i {
            color: #0066cc;
            font-size: 1.5em;
        }

        .windows-signin-info-box span {
            color: #004080;
            font-weight: 600;
            font-size: 0.95em;
            line-height: 1.5;
        }

        /* Hide location field for Windows Sign-In */
        .windows-signin-layout .location-field {
            display: none !important;
        }

        /* Special layout for Windows Sign-In modal */
        .windows-signin-layout {
            background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);
            padding: 20px;
            border-radius: 10px;
        }

        /* ============================================================================
                   LOCKED CARD STYLES (Windows Sign-In)
                   ============================================================================ */

        /* Locked card appearance */
        .locked-card {
            position: relative;
            opacity: 0.95;
            background: linear-gradient(135deg, #e8f4fd 0%, #d4e9f7 100%) !important;
            border: 2px solid #0066cc !important;
        }

        .locked-card:hover {
            transform: none !important;
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.3) !important;
        }

        /* Lock badge */
        .lock-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: linear-gradient(135deg, #0066cc 0%, #004080 100%);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75em;
            box-shadow: 0 2px 8px rgba(0, 102, 204, 0.4);
            z-index: 10;
        }

        .lock-badge i {
            color: white !important;
        }

        /* Windows Sign-In card icon */
        .windows-signin-card .card-icon {
            color: #0066cc !important;
        }

        /* Disabled state visual */
        .locked-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            pointer-events: none;
            border-radius: 12px;
        }

        /* Remove pointer events from delete button inside locked cards */
        .locked-card .btn-ghost {
            pointer-events: none !important;
            opacity: 0.3 !important;
        }
    </style>
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Data from Laravel
        const modulesData = @json($modules);
        const locationsData = @json($locations);
        const rolesData = @json($roles);
        const existingModules = @json($userModuleAssignments ?? []);
        const updateRoute = "{{ route('azure.users.update', $user) }}";
        const csrfToken = "{{ csrf_token() }}";

        let moduleCards = {};
        let cardIdCounter = 0;
        let currentModalCardId = null;
        let isEditMode = false;
        let originalCardState = null;
        let hasUnsavedChanges = false; // Track if there are unsaved changes to existing modules
        let initialModuleState = {}; // Store initial state of all modules

        // Caches for dynamic data
        let scmFunctionsCache = null;
        let rolesCache = {};
        let locationsCache = {};

        // Initialize locations cache
        locationsData.forEach(loc => {
            locationsCache[loc.code.trim()] = {
                id: loc.id,
                code: loc.code.trim(),
                name: loc.name
            };
        });

        console.log('=== INITIAL DATA ===');
        console.log('Modules:', modulesData);
        console.log('Locations Cache:', locationsCache);

        // Helper function to escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        function deepClone(obj) {
            const cloned = JSON.parse(JSON.stringify(obj));
            console.log('🔄 Deep clone:', {
                original: obj,
                cloned: cloned,
                same: JSON.stringify(obj) === JSON.stringify(cloned)
            });
            return cloned;
        }

        function getApplicationCodeForSP(module) {
            return module.name;
        }

        // Show/Hide the Update User & Cancel buttons
        // Show/Hide the Update User & Cancel buttons
        function toggleFormActions(show) {
            console.log('🔘 toggleFormActions called with:', show);
            const moduleActionsSection = document.getElementById('moduleActionsSection');

            if (!moduleActionsSection) {
                console.error('❌ moduleActionsSection element not found!');
                return;
            }

            console.log('📍 moduleActionsSection element found:', moduleActionsSection);

            if (show) {
                console.log('✅ Adding visible class');
                moduleActionsSection.classList.add('visible');
                hasUnsavedChanges = true;
            } else {
                console.log('❌ Removing visible class');
                moduleActionsSection.classList.remove('visible');
                hasUnsavedChanges = false;
            }

            console.log('📊 Current classes:', moduleActionsSection.className);
        }

        // Fetch SCM Functions
        async function fetchSCMFunctions() {
            if (scmFunctionsCache) {
                return scmFunctionsCache;
            }

            try {
                const response = await fetch('/azure/modules/scm-functions', {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success && result.data) {
                    scmFunctionsCache = result.data.map(f => ({
                        id: f.ID || f.Id || f.id,
                        code: f.Code || f.code,
                        name: f.Name || f.name
                    }));
                    console.log('✅ Cached', scmFunctionsCache.length, 'SCM functions');
                    return scmFunctionsCache;
                } else {
                    console.error('Failed to fetch SCM functions:', result.message);
                    return [];
                }
            } catch (error) {
                console.error('Error fetching SCM functions:', error);
                return [];
            }
        }

        // Fetch Roles by Application Code
        async function fetchRolesByApplicationCode(applicationCode) {
            const cacheKey = `${applicationCode}_all`;

            if (rolesCache[cacheKey]) {
                return rolesCache[cacheKey];
            }

            try {
                const url = `/azure/modules/roles?application_code=${encodeURIComponent(applicationCode)}`;

                const response = await fetch(url, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success && result.data && result.data.length > 0) {
                    // Filter out error responses
                    const validRoles = result.data.filter(role => {
                        const hasError = Object.values(role).some(val =>
                            typeof val === 'string' && (
                                val.toLowerCase().includes('failed') ||
                                val.toLowerCase().includes('does not exist') ||
                                val.toLowerCase().includes('error')
                            )
                        );
                        return !hasError;
                    });

                    rolesCache[cacheKey] = validRoles.map(r => ({
                        id: r.RoleId || r.Id || r.id,
                        code: r.RoleCode || r.Code || r.code,
                        name: r.Role || r.Name || r.name
                    }));

                    console.log('✅ Cached', rolesCache[cacheKey].length, 'roles for', applicationCode);
                    return rolesCache[cacheKey];
                } else {
                    console.warn('⚠️ No roles found for:', applicationCode);
                    rolesCache[cacheKey] = [];
                    return [];
                }
            } catch (error) {
                console.error('❌ Error fetching roles:', error);
                rolesCache[cacheKey] = [];
                return [];
            }
        }

        function initializeModules() {
            moduleCards = {};

            console.log('=== EXISTING MODULES FROM BACKEND ===');
            console.log('Total count:', existingModules.length);
            console.log('Data:', existingModules);

            const grouped = {};
            existingModules.forEach(m => {
                const moduleId = m.module_id;
                if (!grouped[moduleId]) {
                    grouped[moduleId] = {
                        module_id: moduleId,
                        module_name: m.module_name,
                        application_id: m.application_id,
                        assignments: []
                    };
                }

                // ← IMPORTANT: Check if this module is SCM
                const module = modulesData.find(mod => mod.id == moduleId);
                const applicationCode = module ? getApplicationCodeForSP(module) : '';
                const isSCM = applicationCode === 'OPS' || applicationCode === 'OPS UAT' || applicationCode ===
                    'OPSUAT';

                const existingAssignment = grouped[moduleId].assignments[0];

                if (!existingAssignment) {
                    // First assignment for this module
                    grouped[moduleId].assignments.push({
                        id: `assign_${cardIdCounter++}`,
                        location: m.location,
                        locationIds: [m.location_id],
                        locationCodes: [m.location],
                        role_id: m.role_id,
                        roleIds: [m.role_id],
                        roleCodes: [m.role_code || ''],
                        // ← FIX: Always initialize function fields for SCM modules
                        function_id: isSCM ? (m.function_id || '') : undefined,
                        functionIds: isSCM ? (m.function_id ? [String(m.function_id)] : []) : undefined,
                        functionCodes: isSCM ? (m.function_code ? [m.function_code] : []) : undefined
                    });
                } else {
                    // Add location if not already present
                    if (!existingAssignment.locationIds.includes(m.location_id)) {
                        existingAssignment.locationIds.push(m.location_id);
                        existingAssignment.locationCodes.push(m.location);
                    }

                    // Add role if not already present
                    if (!existingAssignment.roleIds.includes(m.role_id)) {
                        existingAssignment.roleIds.push(m.role_id);
                        existingAssignment.roleCodes.push(m.role_code || '');
                    }

                    // ← FIX: Add function if not already present (for SCM modules)
                    if (isSCM && m.function_id) {
                        // Initialize function arrays if they don't exist
                        if (!existingAssignment.functionIds) {
                            existingAssignment.functionIds = [];
                        }
                        if (!existingAssignment.functionCodes) {
                            existingAssignment.functionCodes = [];
                        }
                        if (!existingAssignment.function_id) {
                            existingAssignment.function_id = '';
                        }

                        const funcIdStr = String(m.function_id);
                        if (!existingAssignment.functionIds.map(String).includes(funcIdStr)) {
                            existingAssignment.functionIds.push(funcIdStr);
                            existingAssignment.functionCodes.push(m.function_code);

                            // Set function_id to first one if not set
                            if (!existingAssignment.function_id) {
                                existingAssignment.function_id = funcIdStr;
                            }
                        }
                    }
                }
            });

            Object.values(grouped).forEach(data => {
                const cardId = `card_${cardIdCounter++}`;
                moduleCards[cardId] = {
                    id: cardId,
                    isNew: false,
                    ...data
                };
            });

            // ← ADD DEBUG: Log what was created
            console.log('📦 Initialized module cards:', Object.values(moduleCards).map(c => ({
                module: c.module_name,
                assignments: c.assignments.map(a => ({
                    id: a.id,
                    functionIds: a.functionIds,
                    locationCodes: a.locationCodes
                }))
            })));

            // Store initial state for comparison
            initialModuleState = deepClone(moduleCards);

            renderAllCards();
        }

        // Clean up invalid cards after initialization
        function cleanupInvalidCards() {
            const invalidCards = [];

            Object.entries(moduleCards).forEach(([cardId, card]) => {
                if (!card.module_id) {
                    invalidCards.push(cardId);
                    return;
                }

                const module = modulesData.find(m => m.id == card.module_id);
                if (!module) {
                    console.warn('🗑️ Removing invalid card on load:', cardId, card.module_id);
                    invalidCards.push(cardId);
                }
            });

            invalidCards.forEach(cardId => {
                delete moduleCards[cardId];
            });

            if (invalidCards.length > 0) {
                console.log(`✅ Cleaned up ${invalidCards.length} invalid card(s)`);
                renderAllCards();
            }
        }

        // Call cleanup after initialization
        document.addEventListener('DOMContentLoaded', function() {
            initializeModules();
            cleanupInvalidCards(); // Add this line
        });

        function renderAllCards() {
            const grid = document.getElementById('modulesGrid');
            grid.innerHTML = '';
            Object.values(moduleCards).forEach(card => {
                grid.appendChild(createModuleCard(card));
            });
            if (Object.keys(moduleCards).length === 0) {
                grid.innerHTML =
                    '<div class="empty-grid"><i class="fas fa-inbox"></i><p>No modules assigned yet. Click "Add New Assignment" to get started.</p></div>';
            }
            updateHiddenInputs();
        }

        function createModuleCard(card) {
            const div = document.createElement('div');

            // ✅ Check if this is Windows Sign-In
            const isWindowsSignIn = card.module_name.toUpperCase().includes('WINDOWS SIGN') ||
                card.module_name.toUpperCase().includes('WINDOWS SIGNIN');

            // ✅ Add special classes for Windows Sign-In
            const cardClasses =
                `module-card ${card.isNew ? 'new-module' : ''} ${isWindowsSignIn ? 'windows-signin-card locked-card' : ''}`;

            div.className = cardClasses;
            div.id = card.id;

            // ✅ Only make it clickable if NOT Windows Sign-In
            if (!isWindowsSignIn) {
                div.onclick = () => openModal(card.id);
                div.style.cursor = 'pointer';
            } else {
                div.style.cursor = 'not-allowed';
                div.onclick = (e) => {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'info',
                        title: 'Windows Sign-In is Locked',
                        html: `
                    <p><strong>Windows Sign-In</strong> is a mandatory module and cannot be modified.</p>
                    ${card.isNew ? `
                                                    <p style="color: #dc3545; font-weight: 600; margin-top: 15px;">
                                                        To remove it, click the "Cancel Changes" button.
                                                    </p>
                                                ` : ''}
                `,
                        confirmButtonColor: '#3498db',
                        confirmButtonText: 'OK'
                    });
                };
            }

            const assignmentCount = card.assignments ? card.assignments.length : 0;

            // ✅ Add lock icon for Windows Sign-In
            const iconClass = isWindowsSignIn ? 'fa-shield-alt' : (card.isNew ? 'fa-plus-circle' : 'fa-cube');
            const lockBadge = isWindowsSignIn ? '<span class="lock-badge"><i class="fas fa-lock"></i></span>' : '';

            div.innerHTML = `
        <i class="fas ${iconClass} card-icon"></i>
        <div class="card-title">${escapeHtml(card.module_name)}</div>
        <span class="assignment-count">
            <i class="fas fa-list"></i>
            ${assignmentCount} assignment${assignmentCount !== 1 ? 's' : ''}
        </span>
        ${lockBadge}
    `;

            return div;
        }

        window.openModal = async (cardId) => {
            currentModalCardId = cardId;
            const card = moduleCards[cardId];

            // ← ADD DETAILED LOGGING
            console.log('🔍 Opening modal for card:', cardId);
            console.log('📦 Full card data:', JSON.stringify(card, null, 2));
            console.log('📋 Assignments detail:', card.assignments.map(a => ({
                id: a.id,
                functionIds: a.functionIds,
                functionCodes: a.functionCodes,
                locationCodes: a.locationCodes,
                roleIds: a.roleIds
            })));

            originalCardState = deepClone(card);

            // Set edit mode based on whether it's a new module
            isEditMode = card.isNew;

            document.getElementById('modalModuleName').textContent = card.module_name;

            // Pre-fetch data if needed
            const module = modulesData.find(m => m.id == card.module_id);
            if (module) {
                const applicationCode = getApplicationCodeForSP(module);
                const isSCM = applicationCode === 'OPS' || applicationCode === 'OPS UAT';

                // Show loading state
                const container = document.getElementById('modalAssignmentsContainer');
                container.innerHTML =
                    '<div class="empty-assignments"><i class="fas fa-spinner fa-spin"></i><p>Loading data...</p></div>';

                // Fetch roles and functions concurrently
                const promises = [fetchRolesByApplicationCode(applicationCode)];
                if (isSCM) {
                    promises.push(fetchSCMFunctions());
                }

                await Promise.all(promises);

                // ← ADD THIS: Log data AFTER fetching
                console.log('✅ Data fetched, card assignments:', card.assignments);
            }

            renderModalAssignments();
            updateModalViewState();
            document.getElementById('moduleModal').classList.add('active');
        };

        function updateModalViewState() {
            const viewBanner = document.getElementById('viewModeBanner');
            const footer = document.getElementById('modalFooter');
            const card = moduleCards[currentModalCardId];

            // Never show view banner for new modules
            if (card && card.isNew) {
                viewBanner.style.display = 'none';
                footer.classList.remove('hidden');
            } else if (isEditMode) {
                viewBanner.style.display = 'none';
                footer.classList.remove('hidden');
            } else {
                viewBanner.style.display = 'flex';
                footer.classList.add('hidden');
            }
        }

        window.enableEditMode = () => {
            isEditMode = true;
            renderModalAssignments();
            updateModalViewState();
        };

        window.closeModal = () => {
            // Revert changes if needed
            if (originalCardState && currentModalCardId) {
                moduleCards[currentModalCardId] = deepClone(originalCardState);
            }
            document.getElementById('moduleModal').classList.remove('active');
            currentModalCardId = null;
            isEditMode = false;
            originalCardState = null;
            renderAllCards();
        };

        window.cancelModal = () => {
            const card = moduleCards[currentModalCardId];

            // Different behavior for new vs existing modules
            if (card && card.isNew) {
                // For new modules: delete the card and close modal
                delete moduleCards[currentModalCardId];
                document.getElementById('moduleModal').classList.remove('active');
                currentModalCardId = null;
                isEditMode = false;
                originalCardState = null;
                renderAllCards();
            } else {
                // For existing modules: revert changes and switch to view mode
                if (originalCardState && currentModalCardId) {
                    moduleCards[currentModalCardId] = deepClone(originalCardState);
                }
                isEditMode = false;
                renderModalAssignments();
                updateModalViewState();
            }
        };

        window.closeModalOnOverlay = (event) => {
            if (event.target.id === 'moduleModal') {
                closeModal();
            }
        };

        window.saveModalChanges = () => {
            console.log('💾 saveModalChanges called');

            if (!isEditMode) {
                console.log('❌ Not in edit mode, returning');
                return;
            }

            const card = moduleCards[currentModalCardId];
            console.log('📦 Current card:', card);
            console.log('🆕 Is new module?', card?.isNew);

            // ✅ ADD: Deep clone to ensure data integrity
            const cardData = deepClone(card);
            console.log('📊 Assignment data BEFORE save:', JSON.stringify(cardData.assignments, null, 2));

            // ✅ CRITICAL: Update the moduleCards with the cloned data
            moduleCards[currentModalCardId] = cardData;

            // For NEW modules: mark as not new and show Update User button
            if (cardData && cardData.isNew) {
                console.log('✅ Processing NEW module');
                cardData.isNew = false;
                moduleCards[currentModalCardId].isNew = false;

                console.log('📊 Assignment data AFTER marking as not new:', JSON.stringify(cardData.assignments, null,
                    2));

                // Close modal
                document.getElementById('moduleModal').classList.remove('active');
                currentModalCardId = null;
                isEditMode = false;
                originalCardState = null;

                // Re-render cards
                renderAllCards();

                // Show the Update User button
                console.log('🔘 Calling toggleFormActions(true)');
                toggleFormActions(true);

                showAlert('success', 'Saved', 'New module assignment added. Click "Update User" to save to database.');
                return;
            }

            console.log('✅ Processing EXISTING module');

            // Update the original state to the current state
            originalCardState = deepClone(cardData);

            // Close modal
            document.getElementById('moduleModal').classList.remove('active');
            currentModalCardId = null;
            isEditMode = false;

            renderAllCards();

            // Show the Update User & Cancel buttons
            console.log('🔘 Calling toggleFormActions(true) for existing module');
            toggleFormActions(true);

            showAlert('success', 'Saved', 'Changes saved. Click "Update User" to save to database.');
        };

        function renderModalAssignments() {
            const card = moduleCards[currentModalCardId];
            const container = document.getElementById('modalAssignmentsContainer');

            // Update modal header
            const modalHeader = document.querySelector('.modal-header');
            if (modalHeader) {
                modalHeader.innerHTML = `
            <div class="modal-title">
                <i class="fas fa-cube" style="color: #007bff"></i>
                <span id="modalModuleName">${escapeHtml(card.module_name)}</span>
            </div>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        `;
            }

            if (!card.assignments || card.assignments.length === 0) {
                container.innerHTML =
                    '<div class="empty-assignments"><i class="fas fa-clipboard-list"></i><p>No assignments yet.</p></div>';
                return;
            }

            const module = modulesData.find(m => m.id == card.module_id);

            if (!module) {
                container.innerHTML =
                    '<div class="empty-assignments"><i class="fas fa-exclamation-triangle"></i><p>Module not found.</p></div>';
                return;
            }

            const applicationCode = getApplicationCodeForSP(module);

            // ✅ CHECK IF THIS IS WINDOWS SIGN-IN MODULE
            const isWindowsSignIn = module.name.toUpperCase().includes('WINDOWS SIGN') ||
                module.name.toUpperCase().includes('WINDOWS SIGNIN');

            const isSCM = applicationCode === 'OPS' || applicationCode === 'OPS UAT';
            const cacheKey = `${applicationCode}_all`;
            const isOPSModule = isSCM;

            // Check if data is loaded
            if (!rolesCache[cacheKey]) {
                container.innerHTML =
                    '<div class="empty-assignments"><i class="fas fa-spinner fa-spin"></i><p>Loading roles...</p></div>';
                return;
            }

            if (isSCM && !scmFunctionsCache) {
                container.innerHTML =
                    '<div class="empty-assignments"><i class="fas fa-spinner fa-spin"></i><p>Loading functions...</p></div>';
                return;
            }

            const availableRoles = rolesCache[cacheKey] || [];
            const availableFunctions = isSCM ? (scmFunctionsCache || []) : [];

            // ✅ RENDER ASSIGNMENTS BASED ON MODULE TYPE
            container.innerHTML = card.assignments.map(a => {
                // ✅ IF WINDOWS SIGN-IN: Use special layout
                if (isWindowsSignIn) {
                    return renderWindowsSignInAssignment(a, module, applicationCode, availableRoles);
                }

                // ✅ IF OPS MODULE: Use OPS layout
                if (isOPSModule) {
                    return renderOPSAssignment(a, module, applicationCode, availableRoles, availableFunctions);
                }

                // ✅ ELSE: Use standard layout
                return renderStandardAssignment(a, module, applicationCode, availableRoles, isSCM,
                    availableFunctions);
            }).join('');
        }

        /**
         * Render Windows Sign-In assignment (no location dropdown, no delete)
         */
        function renderWindowsSignInAssignment(a, module, applicationCode, availableRoles) {
            const disabled = !isEditMode ? 'disabled' : '';
            const disabledClass = !isEditMode ? 'disabled' : '';

            return `
        <div class="assignment-item windows-signin-layout" id="${a.id}">
            <div class="assignment-header">
                <span style="font-weight: 600; color: #0066cc; font-size: 0.95em; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-shield-alt"></i>
                    ${escapeHtml(module.name)} - Windows Domain Access
                    <span class="lock-badge" style="position: relative; top: 0; right: 0; margin-left: auto;">
                        <i class="fas fa-lock"></i>
                    </span>
                </span>
                <!-- ✅ NO DELETE BUTTON FOR WINDOWS SIGN-IN -->
            </div>

            <!-- INFO BOX -->
            <div class="windows-signin-info-box">
                <i class="fas fa-info-circle"></i>
                <span>
                    <strong>Mandatory Module:</strong> This module grants Windows domain authentication and cannot be removed. 
                    It is required for user activation.
                </span>
            </div>

            <!-- ROLES (if applicable) -->
            ${availableRoles.length > 0 ? `
                                        <div class="info-field">
                                            <div class="field-label" style="margin-bottom: 12px;">
                                                <i class="fas fa-user-tag"></i> Access Roles (Optional)
                                            </div>
                                            <div class="checkboxes-grid">
                                                ${availableRoles.map(r => {
                                                    const isChecked = a.roleIds && a.roleIds.map(String).includes(String(r.id));
                                                    return `
                                <div class="checkbox-option ${!isEditMode ? 'disabled' : ''}">
                                    <input type="checkbox"
                                           id="${a.id}_role_${r.id}"
                                           class="role-checkbox"
                                           data-role-id="${r.id}"
                                           data-role-code="${escapeHtml(r.code)}"
                                           value="${r.id}"
                                           ${isChecked ? 'checked' : ''}
                                           ${disabled}
                                           onchange="handleRoleCheckboxChange('${a.id}')">
                                    <label for="${a.id}_role_${r.id}">${escapeHtml(r.name)}</label>
                                </div>
                            `;
                                                }).join('')}
                                            </div>
                                        </div>
                                    ` : '<p style="padding: 12px; color: #666; text-align: center; background: #f8f9fa; border-radius: 6px;"><em>No roles available for this module</em></p>'}
        </div>
    `;
        }


        /**
         * Render OPS module assignment (OPS-specific layout)
         */
        function renderOPSAssignment(a, module, applicationCode, availableRoles, availableFunctions) {
            const isAllLocations = a.location === 'ALL';
            const isAllFunctions = a.function_id === 'ALL';
            const disabled = !isEditMode ? 'disabled' : '';
            const disabledClass = !isEditMode ? 'disabled' : '';

            return `
        <div class="assignment-item" id="${a.id}">
            <div class="assignment-header">
                <span style="font-weight: 600; color: #6c757d; font-size: 0.9em;">
                    Assignment for ${escapeHtml(module.name)} (${escapeHtml(applicationCode)})
                </span>
                <div style="display: flex; gap: 8px; ${!isEditMode ? 'display: none;' : ''}">
                    <button type="button" class="btn btn-sm btn-ghost" style="color: #dc3545;" 
                            onclick="deleteAssignment('${a.id}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>

            <!-- OPS MODULE LAYOUT -->
            <div class="ops-module-layout">
                <div class="ops-top-section">
                    <!-- OFFICE LOCATION -->
                    <div class="ops-form-section">
                        <div class="field-label"><i class="fas fa-map-marker-alt"></i> Office Location</div>
                        <div class="radio-group">
                            <div class="radio-option ${disabledClass}">
                                <input type="radio" id="${a.id}_all_locations" name="${a.id}_location_type" 
                                       class="all-locations-radio" ${isAllLocations ? 'checked' : ''} ${disabled} 
                                       onchange="handleLocationRadioChange('${a.id}')">
                                <label for="${a.id}_all_locations">All Locations</label>
                            </div>
                            <div class="radio-option ${disabledClass}">
                                <input type="radio" id="${a.id}_specific_locations" name="${a.id}_location_type" 
                                       class="specific-locations-radio" ${!isAllLocations ? 'checked' : ''} ${disabled} 
                                       onchange="handleLocationRadioChange('${a.id}')">
                                <label for="${a.id}_specific_locations">Specific Location</label>
                            </div>
                        </div>
                        <div class="checkboxes-grid">
                            ${locationsData.map(l => {
                                const locCode = l.code.trim();
                                const isChecked = isAllLocations || (a.locationCodes && a.locationCodes.map(c => c.trim()).includes(locCode));
                                return `
                                                                        <div class="checkbox-option ${isAllLocations || !isEditMode ? 'disabled' : ''}">
                                                                            <input type="checkbox"
                                                                                   id="${a.id}_loc_${locCode}"
                                                                                   class="location-checkbox"
                                                                                   data-location-id="${l.id}"
                                                                                   data-location-code="${locCode}"
                                                                                   data-location-name="${escapeHtml(l.name)}"
                                                                                   value="${locCode}"
                                                                                   ${isChecked ? 'checked' : ''}
                                                                                   ${isAllLocations || !isEditMode ? 'disabled' : ''}
                                                                                   onchange="handleLocationCheckboxChange('${a.id}')">
                                                                            <label for="${a.id}_loc_${locCode}">${escapeHtml(l.name)}</label>
                                                                        </div>
                                                                    `;
                            }).join('')}
                        </div>
                    </div>

                    <!-- SCM FUNCTIONS -->
                    <div class="ops-form-section">
                        <div class="field-label"><i class="fas fa-cog"></i> Function (SCM)</div>
                        <div class="radio-group">
                            <div class="radio-option ${disabledClass}">
                                <input type="radio" id="${a.id}_all_functions" name="${a.id}_function_type" 
                                       class="all-functions-radio" ${isAllFunctions ? 'checked' : ''} ${disabled} 
                                       onchange="handleFunctionRadioChange('${a.id}')">
                                <label for="${a.id}_all_functions">All Functions</label>
                            </div>
                            <div class="radio-option ${disabledClass}">
                                <input type="radio" id="${a.id}_specific_functions" name="${a.id}_function_type" 
                                       class="specific-functions-radio" ${!isAllFunctions ? 'checked' : ''} ${disabled} 
                                       onchange="handleFunctionRadioChange('${a.id}')">
                                <label for="${a.id}_specific_functions">Specific Function</label>
                            </div>
                        </div>
                        <div class="checkboxes-grid">
                            ${availableFunctions.length > 0 ? availableFunctions.map(f => {
                                const functionId = String(f.id).toUpperCase();
                                const normalizedFunctionIds = (a.functionIds || []).map(id => String(id).toUpperCase());
                                const isChecked = isAllFunctions || normalizedFunctionIds.includes(functionId);
                                return `
                                                                        <div class="checkbox-option ${isAllFunctions || !isEditMode ? 'disabled' : ''}">
                                                                            <input type="checkbox"
                                                                                   id="${a.id}_func_${f.id}"
                                                                                   class="function-checkbox"
                                                                                   data-function-id="${f.id}"
                                                                                   data-function-code="${escapeHtml(f.code)}"
                                                                                   value="${f.id}"
                                                                                   ${isChecked ? 'checked' : ''}
                                                                                   ${isAllFunctions || !isEditMode ? 'disabled' : ''}
                                                                                   onchange="handleFunctionCheckboxChange('${a.id}')">
                                                                            <label for="${a.id}_func_${f.id}">${escapeHtml(f.name)}</label>
                                                                        </div>
                                                                    `;
                            }).join('') : '<p style="padding: 10px; color: #666;">No functions available</p>'}
                        </div>
                    </div>
                </div>

                <!-- ROLES - FULL WIDTH FOR OPS MODULES -->
                <div class="ops-roles-section">
                    <div class="field-label"><i class="fas fa-user-tag"></i> Roles</div>
                    <div class="roles-grid-3col">
                        ${availableRoles.length > 0 ? availableRoles.map(r => {
                            const isChecked = a.roleIds && a.roleIds.map(String).includes(String(r.id));
                            return `
                                                                    <div class="checkbox-option ${!isEditMode ? 'disabled' : ''}">
                                                                        <input type="checkbox"
                                                                               id="${a.id}_role_${r.id}"
                                                                               class="role-checkbox"
                                                                               data-role-id="${r.id}"
                                                                               data-role-code="${escapeHtml(r.code)}"
                                                                               value="${r.id}"
                                                                               ${isChecked ? 'checked' : ''}
                                                                               ${!isEditMode ? 'disabled' : ''}
                                                                               onchange="handleRoleCheckboxChange('${a.id}')">
                                                                        <label for="${a.id}_role_${r.id}">${escapeHtml(r.name)}</label>
                                                                    </div>
                                                                `;
                        }).join('') : '<p style="padding: 10px; color: #666; grid-column: 1/-1;">No roles available for ' + escapeHtml(applicationCode) + '</p>'}
                    </div>
                </div>
            </div>
        </div>
    `;
        }


        /**
         * Render standard module assignment (non-OPS, non-Windows-SignIn layout)
         */
        function renderStandardAssignment(a, module, applicationCode, availableRoles, isSCM, availableFunctions) {
            const isAllLocations = a.location === 'ALL';
            const isAllFunctions = a.function_id === 'ALL';
            const disabled = !isEditMode ? 'disabled' : '';
            const disabledClass = !isEditMode ? 'disabled' : '';

            return `
        <div class="assignment-item" id="${a.id}">
            <div class="assignment-header">
                <span style="font-weight: 600; color: #6c757d; font-size: 0.9em;">
                    Assignment for ${escapeHtml(module.name)} (${escapeHtml(applicationCode)})
                </span>
                <div style="display: flex; gap: 8px; ${!isEditMode ? 'display: none;' : ''}">
                    <button type="button" class="btn btn-sm btn-ghost" style="color: #dc3545;" 
                            onclick="deleteAssignment('${a.id}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>

            <!-- STANDARD LAYOUT FOR NON-OPS MODULES -->
            <div class="assignment-info" style="grid-template-columns: ${isSCM ? '1fr 1fr 1fr' : '1fr 1fr'};">
                <!-- OFFICE LOCATION -->
                <div class="info-field">
                    <div class="field-label"><i class="fas fa-map-marker-alt"></i> Office Location</div>
                    <div class="radio-group">
                        <div class="radio-option ${disabledClass}">
                            <input type="radio" id="${a.id}_all_locations" name="${a.id}_location_type" 
                                   class="all-locations-radio" ${isAllLocations ? 'checked' : ''} ${disabled} 
                                   onchange="handleLocationRadioChange('${a.id}')">
                            <label for="${a.id}_all_locations">All Locations</label>
                        </div>
                        <div class="radio-option ${disabledClass}">
                            <input type="radio" id="${a.id}_specific_locations" name="${a.id}_location_type" 
                                   class="specific-locations-radio" ${!isAllLocations ? 'checked' : ''} ${disabled} 
                                   onchange="handleLocationRadioChange('${a.id}')">
                            <label for="${a.id}_specific_locations">Specific Location</label>
                        </div>
                    </div>
                    <div class="checkboxes-grid">
                        ${locationsData.map(l => {
                            const locCode = l.code.trim();
                            const isChecked = isAllLocations || (a.locationCodes && a.locationCodes.map(c => c.trim()).includes(locCode));
                            return `
                                                                <div class="checkbox-option ${isAllLocations || !isEditMode ? 'disabled' : ''}">
                                                                    <input type="checkbox"
                                                                           id="${a.id}_loc_${locCode}"
                                                                           class="location-checkbox"
                                                                           data-location-id="${l.id}"
                                                                           data-location-code="${locCode}"
                                                                           data-location-name="${escapeHtml(l.name)}"
                                                                           value="${locCode}"
                                                                           ${isChecked ? 'checked' : ''}
                                                                           ${isAllLocations || !isEditMode ? 'disabled' : ''}
                                                                           onchange="handleLocationCheckboxChange('${a.id}')">
                                                                    <label for="${a.id}_loc_${locCode}">${escapeHtml(l.name)}</label>
                                                                </div>
                                                            `;
                        }).join('')}
                    </div>
                </div>

                <!-- SCM FUNCTIONS (Only for non-OPS SCM modules) -->
                ${isSCM ? `
                                                    <div class="info-field">
                                                        <div class="field-label"><i class="fas fa-cog"></i> Function (SCM)</div>
                                                        <div class="radio-group">
                                                            <div class="radio-option ${disabledClass}">
                                                                <input type="radio" id="${a.id}_all_functions" name="${a.id}_function_type" 
                                                                       class="all-functions-radio" ${isAllFunctions ? 'checked' : ''} ${disabled} 
                                                                       onchange="handleFunctionRadioChange('${a.id}')">
                                                                <label for="${a.id}_all_functions">All Functions</label>
                                                            </div>
                                                            <div class="radio-option ${disabledClass}">
                                                                <input type="radio" id="${a.id}_specific_functions" name="${a.id}_function_type" 
                                                                       class="specific-functions-radio" ${!isAllFunctions ? 'checked' : ''} ${disabled} 
                                                                       onchange="handleFunctionRadioChange('${a.id}')">
                                                                <label for="${a.id}_specific_functions">Specific Function</label>
                                                            </div>
                                                        </div>
                                                        <div class="checkboxes-grid">
                                                            ${availableFunctions.length > 0 ? availableFunctions.map(f => {
                                                                const functionId = String(f.id).toUpperCase();
                                                                const normalizedFunctionIds = (a.functionIds || []).map(id => String(id).toUpperCase());
                                                                const isChecked = isAllFunctions || normalizedFunctionIds.includes(functionId);
                                                                return `
                                    <div class="checkbox-option ${isAllFunctions || !isEditMode ? 'disabled' : ''}">
                                        <input type="checkbox"
                                               id="${a.id}_func_${f.id}"
                                               class="function-checkbox"
                                               data-function-id="${f.id}"
                                               data-function-code="${escapeHtml(f.code)}"
                                               value="${f.id}"
                                               ${isChecked ? 'checked' : ''}
                                               ${isAllFunctions || !isEditMode ? 'disabled' : ''}
                                               onchange="handleFunctionCheckboxChange('${a.id}')">
                                        <label for="${a.id}_func_${f.id}">${escapeHtml(f.name)}</label>
                                    </div>
                                `;
                                                            }).join('') : '<p style="padding: 10px; color: #666;">No functions available</p>'}
                                                        </div>
                                                    </div>
                                                ` : ''}

                <!-- ROLES -->
                <div class="info-field">
                    <div class="field-label"><i class="fas fa-user-tag"></i> Role</div>
                    <div class="checkboxes-grid">
                        ${availableRoles.length > 0 ? availableRoles.map(r => {
                            const isChecked = a.roleIds && a.roleIds.map(String).includes(String(r.id));
                            return `
                                                                <div class="checkbox-option ${!isEditMode ? 'disabled' : ''}">
                                                                    <input type="checkbox"
                                                                           id="${a.id}_role_${r.id}"
                                                                           class="role-checkbox"
                                                                           data-role-id="${r.id}"
                                                                           data-role-code="${escapeHtml(r.code)}"
                                                                           value="${r.id}"
                                                                           ${isChecked ? 'checked' : ''}
                                                                           ${!isEditMode ? 'disabled' : ''}
                                                                           onchange="handleRoleCheckboxChange('${a.id}')">
                                                                    <label for="${a.id}_role_${r.id}">${escapeHtml(r.name)}</label>
                                                                </div>
                                                            `;
                        }).join('') : '<p style="padding: 10px; color: #666;">No roles available for ' + escapeHtml(applicationCode) + '</p>'}
                    </div>
                </div>
            </div>
        </div>
    `;
        }

        // Handle location radio change (All vs Specific)
        window.handleLocationRadioChange = (assignId) => {
            if (!isEditMode) return;
            const container = document.getElementById(assignId);
            if (!container) return;

            const allRadio = container.querySelector('.all-locations-radio');
            const checkboxes = container.querySelectorAll('.location-checkbox');
            const checkboxOptions = container.querySelectorAll('.info-field:first-child .checkbox-option');
            const card = moduleCards[currentModalCardId];
            const assignment = card.assignments.find(a => a.id === assignId);

            if (allRadio && allRadio.checked) {
                checkboxes.forEach(cb => {
                    cb.checked = true;
                    cb.disabled = true;
                });
                checkboxOptions.forEach(opt => opt.classList.add('disabled'));
                if (assignment) {
                    assignment.location = 'ALL';
                    assignment.locationIds = ['ALL'];
                    assignment.locationCodes = ['ALL'];
                }
            } else {
                checkboxes.forEach(cb => {
                    cb.disabled = false;
                    cb.checked = false;
                });
                checkboxOptions.forEach(opt => opt.classList.remove('disabled'));
                if (assignment) {
                    assignment.location = '';
                    assignment.locationIds = [];
                    assignment.locationCodes = [];
                }
            }
        };

        window.handleLocationCheckboxChange = (assignId) => {
            if (!isEditMode) return;
            const container = document.getElementById(assignId);
            if (!container) return;

            const checkboxes = container.querySelectorAll('.location-checkbox:checked:not(:disabled)');
            const card = moduleCards[currentModalCardId];
            const assignment = card.assignments.find(a => a.id === assignId);

            if (assignment) {
                assignment.locationIds = [];
                assignment.locationCodes = [];

                checkboxes.forEach(cb => {
                    const locId = cb.getAttribute('data-location-id');
                    const locCode = cb.getAttribute('data-location-code').trim(); // ← Add trim()

                    assignment.locationIds.push(locId);
                    assignment.locationCodes.push(locCode);
                });

                // Update the main location field
                assignment.location = assignment.locationCodes.length > 0 ? assignment.locationCodes[0] : '';

                console.log('✅ Updated locations:', assignment.locationCodes); // ← Add logging
            }
        };

        window.handleFunctionRadioChange = (assignId) => {
            if (!isEditMode) return;
            const container = document.getElementById(assignId);
            if (!container) return;

            const allRadio = container.querySelector('.all-functions-radio');
            const checkboxes = container.querySelectorAll('.function-checkbox');
            const checkboxOptions = container.querySelectorAll('.info-field:nth-child(2) .checkbox-option');
            const card = moduleCards[currentModalCardId];
            const assignment = card.assignments.find(a => a.id === assignId);

            if (allRadio && allRadio.checked) {
                checkboxes.forEach(cb => {
                    cb.checked = true;
                    cb.disabled = true;
                });
                checkboxOptions.forEach(opt => opt.classList.add('disabled'));
                if (assignment) {
                    assignment.function_id = 'ALL';
                    assignment.functionIds = ['ALL'];
                    assignment.functionCodes = ['ALL'];
                }
            } else {
                checkboxes.forEach(cb => {
                    cb.disabled = false;
                    cb.checked = false;
                });
                checkboxOptions.forEach(opt => opt.classList.remove('disabled'));
                if (assignment) {
                    assignment.function_id = '';
                    assignment.functionIds = [];
                    assignment.functionCodes = [];
                }
            }
        };


        window.handleFunctionCheckboxChange = (assignId) => {
            if (!isEditMode) return;
            const container = document.getElementById(assignId);
            if (!container) return;

            const checkboxes = container.querySelectorAll('.function-checkbox:checked:not(:disabled)');
            const card = moduleCards[currentModalCardId];
            const assignment = card.assignments.find(a => a.id === assignId);

            if (assignment) {
                assignment.functionIds = [];
                assignment.functionCodes = [];

                checkboxes.forEach(cb => {
                    // ← Store as strings (consistent with how they're stored initially)
                    const funcId = String(cb.getAttribute('data-function-id'));
                    const funcCode = cb.getAttribute('data-function-code');

                    assignment.functionIds.push(funcId);
                    assignment.functionCodes.push(funcCode);
                });

                assignment.function_id = assignment.functionIds.length > 0 ? assignment.functionIds[0] : '';

                console.log('✅ Updated functions:', {
                    functionIds: assignment.functionIds,
                    functionCodes: assignment.functionCodes
                });
            }
        };

        window.handleRoleRadioChange = (assignId) => {
            if (!isEditMode) return;
            const container = document.getElementById(assignId);
            if (!container) return;

            const allRadio = container.querySelector('.all-roles-radio');
            const checkboxes = container.querySelectorAll('.role-checkbox');
            const checkboxOptions = container.querySelectorAll('.info-field:last-child .checkbox-option');
            const card = moduleCards[currentModalCardId];
            const assignment = card.assignments.find(a => a.id === assignId);

            if (allRadio && allRadio.checked) {
                checkboxes.forEach(cb => {
                    cb.checked = true;
                    cb.disabled = true;
                });
                checkboxOptions.forEach(opt => opt.classList.add('disabled'));
                if (assignment) {
                    assignment.role_id = 'ALL';
                    assignment.roleIds = ['ALL'];
                    assignment.roleCodes = ['ALL'];
                }
            } else {
                checkboxes.forEach(cb => {
                    cb.disabled = false;
                    cb.checked = false;
                });
                checkboxOptions.forEach(opt => opt.classList.remove('disabled'));
                if (assignment) {
                    assignment.role_id = '';
                    assignment.roleIds = [];
                    assignment.roleCodes = [];
                }
            }
        };

        window.handleRoleCheckboxChange = (assignId) => {
            if (!isEditMode) return;
            const container = document.getElementById(assignId);
            if (!container) return;

            const checkboxes = container.querySelectorAll('.role-checkbox:checked');
            const card = moduleCards[currentModalCardId];
            const assignment = card.assignments.find(a => a.id === assignId);

            if (assignment) {
                assignment.roleIds = [];
                assignment.roleCodes = [];

                checkboxes.forEach(cb => {
                    const roleId = cb.getAttribute('data-role-id');
                    const roleCode = cb.getAttribute('data-role-code');

                    assignment.roleIds.push(roleId);
                    assignment.roleCodes.push(roleCode);
                });

                assignment.role_id = assignment.roleIds.length > 0 ? assignment.roleIds[0] : '';
            }
        };

        window.addNewModule = () => {
            // Get list of already-assigned module IDs
            const assignedModuleIds = Object.values(moduleCards)
                .map(card => card.module_id)
                .filter(Boolean);

            console.log('📋 Already assigned modules:', assignedModuleIds);

            const moduleSelectorHTML = `
        <div class="module-selector-grid">
            ${modulesData.map(m => {
                const isAssigned = assignedModuleIds.includes(m.id);
                const isWindowsSignIn = m.name.toUpperCase().includes('WINDOWS SIGN') || 
                                       m.name.toUpperCase().includes('WINDOWS SIGNIN');
                
                const disabledClass = isAssigned ? 'disabled' : '';
                const disabledAttr = isAssigned ? 'data-disabled="true"' : '';
                const highlightClass = isWindowsSignIn && !isAssigned ? 'windows-signin-highlight' : '';

                return `
                                                                                        <div class="module-selector-card ${disabledClass} ${highlightClass}"
                                                                                             data-module-id="${escapeHtml(String(m.id))}"
                                                                                             ${disabledAttr}>
                                                                                            <i class="fas ${isWindowsSignIn ? 'fa-shield-alt' : 'fa-cube'}"></i>
                                                                                            <div class="module-name">${escapeHtml(m.name)}</div>
                                                                                            ${isWindowsSignIn && !isAssigned ? '<div class="required-badge">Required</div>' : ''}
                                                                                            ${isAssigned ? '<div class="already-assigned-badge">Already Assigned</div>' : ''}
                                                                                        </div>
                                                                                    `;
            }).join('')}
        </div>
    `;

            Swal.fire({
                title: 'Select Module',
                html: moduleSelectorHTML,
                showCancelButton: true,
                showConfirmButton: false,
                cancelButtonText: 'Close',
                width: '700px',
                didOpen: () => {
                    document.querySelectorAll('.module-selector-card').forEach(card => {
                        card.addEventListener('click', function() {
                            // Check if disabled
                            if (this.getAttribute('data-disabled') === 'true') {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Module Already Assigned',
                                    text: 'This module is already assigned. Please edit the existing card or delete it first.',
                                    confirmButtonColor: '#3498db'
                                });
                                return;
                            }

                            const moduleId = this.getAttribute('data-module-id');
                            selectModule(moduleId);
                        });
                    });
                }
            });
        };

        // Replace lines ~1450-1510 in your JavaScript section with:

        window.selectModule = (moduleId) => {
            Swal.close();
            const module = modulesData.find(m => m.id == moduleId);

            if (!module) {
                showAlert('error', 'Error', 'Module not found');
                return;
            }

            const cardId = `card_${cardIdCounter++}`;
            const assignId = `assign_${cardIdCounter++}`;
            const applicationCode = getApplicationCodeForSP(module);

            const isWindowsSignIn = module.name.toUpperCase().includes('WINDOWS SIGN') ||
                module.name.toUpperCase().includes('WINDOWS SIGNIN');
            const isSCM = applicationCode === 'OPS' || applicationCode === 'OPS UAT';

            // Create the module card
            moduleCards[cardId] = {
                id: cardId,
                isNew: true,
                module_id: moduleId,
                module_name: module.name,
                application_id: module.application_id,
                assignments: [{
                    id: assignId,
                    location: isWindowsSignIn ? 'ALL' : '',
                    locationIds: isWindowsSignIn ? ['ALL'] : [],
                    locationCodes: isWindowsSignIn ? ['ALL'] : [],
                    role_id: '',
                    roleIds: [],
                    roleCodes: [],
                    function_id: isSCM ? '' : undefined,
                    functionIds: isSCM ? [] : undefined,
                    functionCodes: isSCM ? [] : undefined
                }]
            };

            // ✅ WINDOWS SIGN-IN: Auto-save immediately, no modal
            if (isWindowsSignIn) {
                moduleCards[cardId].isNew = false;
                renderAllCards();
                toggleFormActions(true);

                Swal.fire({
                    icon: 'success',
                    title: 'Windows Sign-In Added!',
                    html: `<p><strong>${escapeHtml(module.name)}</strong> has been added.</p>
                   <p style="color: #007bff; font-weight: 600; margin-top: 15px;">
                       Click "Update User" to save.
                   </p>`,
                    confirmButtonColor: '#3498db',
                    timer: 2000,
                    timerProgressBar: true
                });
                return;
            }

            // ✅ OTHER MODULES: Open modal as usual
            renderAllCards();
            openModal(cardId);
        };

        window.deleteAssignment = (assignId) => {
            console.log('🗑️ Delete assignment clicked:', assignId);
            console.log('Current modal card ID:', currentModalCardId);

            if (!currentModalCardId) {
                console.error('❌ No modal card ID - cannot delete');
                showAlert('error', 'Error', 'Cannot delete assignment - modal context lost');
                return;
            }

            const card = moduleCards[currentModalCardId];

            if (!card) {
                console.error('❌ Card not found:', currentModalCardId);
                showAlert('error', 'Error', 'Cannot delete assignment - card not found');
                return;
            }

            // ✅ CHECK IF THIS IS WINDOWS SIGN-IN - BLOCK DELETION
            const isWindowsSignIn = card.module_name.toUpperCase().includes('WINDOWS SIGN') ||
                card.module_name.toUpperCase().includes('WINDOWS SIGNIN');

            if (isWindowsSignIn) {
                Swal.fire({
                    icon: 'error',
                    title: 'Cannot Delete Mandatory Module',
                    html: `
                <p><strong>Windows Sign-In</strong> is a mandatory module and cannot be deleted.</p>
                <p style="color: #666; font-size: 0.9em; margin-top: 10px;">
                    <i class="fas fa-lock"></i> This module is required for user activation.
                </p>
                ${card.isNew ? `
                                            <p style="color: #007bff; font-weight: 600; margin-top: 15px;">
                                                To remove it, click "Cancel Changes" to discard all unsaved changes.
                                            </p>
                                        ` : ''}
            `,
                    confirmButtonColor: '#3498db',
                    confirmButtonText: 'OK'
                });
                return; // ✅ Stop deletion
            }

            console.log('Card before delete:', {
                id: card.id,
                module: card.module_name,
                assignments: card.assignments.length
            });

            // ... rest of your existing delete logic for other modules ...
            Swal.fire({
                title: 'Delete Module?',
                html: `
            <p>Are you sure you want to delete <strong>${escapeHtml(card.module_name)}</strong>?</p>
            <p style="color: #666; font-size: 0.9em; margin-top: 10px;">
                This will remove all assignments for this module.
            </p>
        `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete module',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log('✓ Delete confirmed for entire module');

                    // Delete the entire module card
                    delete moduleCards[currentModalCardId];

                    // If this was an existing module (not new), show the Update User button
                    if (!card.isNew) {
                        toggleFormActions(true);
                    }

                    // Close the modal
                    document.getElementById('moduleModal').classList.remove('active');
                    currentModalCardId = null;
                    isEditMode = false;
                    originalCardState = null;

                    // Re-render all cards
                    renderAllCards();

                    showAlert('success', 'Deleted', 'Module has been deleted');
                } else {
                    console.log('✗ Delete cancelled');
                }
            });
        };

        // DELETE THIS ENTIRE FUNCTION
        window.deleteModuleCard = (cardId) => {
            const card = moduleCards[cardId];
            if (!card) return;

            Swal.fire({
                title: 'Delete Module?',
                html: `
        <p>Are you sure you want to delete <strong>${escapeHtml(card.module_name)}</strong>?</p>
        <p style="color: #666; font-size: 0.9em; margin-top: 10px;">
            This will remove all assignments for this module.
        </p>
    `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete module',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Delete the entire card
                    delete moduleCards[cardId];

                    // If we're currently viewing this card's modal, close it
                    if (currentModalCardId === cardId) {
                        document.getElementById('moduleModal').classList.remove('active');
                        currentModalCardId = null;
                        isEditMode = false;
                        originalCardState = null;
                    }

                    // Re-render all cards
                    renderAllCards();

                    // If this was an existing module (not new), show the Update User button
                    if (!card.isNew) {
                        toggleFormActions(true);
                    }

                    showAlert('success', 'Deleted', 'Module has been deleted');
                }
            });
        };

        // Cancel all changes and revert to initial state
        window.cancelAllChanges = () => {
            Swal.fire({
                title: 'Cancel All Changes?',
                html: `
            <p>This will revert all unsaved changes to module assignments.</p>
            <p style="color: #dc3545; font-weight: 600; margin-top: 10px;">
                <i class="fas fa-exclamation-triangle"></i> 
                This includes removing newly added modules like Windows Sign-In.
            </p>
        `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, cancel all changes',
                cancelButtonText: 'Keep editing'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Revert to initial state
                    moduleCards = deepClone(initialModuleState);
                    renderAllCards();
                    toggleFormActions(false);
                    showAlert('info', 'Changes Reverted', 'All changes have been cancelled');
                }
            });
        };

        function debugFormData() {
            const formData = new FormData(document.getElementById('editUserForm'));
            console.log('=== FORM DEBUG ===');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
            console.log('==================');
        }

        // Call this before submitting to verify
        debugFormData();


        function testSubmit() {
            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('_method', 'PUT');
            formData.append('modules', JSON.stringify([]));

            console.log('Testing with empty modules array...');

            fetch(updateRoute, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(data => console.log('Test response:', data))
                .catch(err => console.error('Test error:', err));
        }


        function updateHiddenInputs() {
            const container = document.getElementById('hiddenInputsContainer');
            container.innerHTML = '';

            let validAssignments = [];

            // If no cards, add empty marker and return
            if (Object.keys(moduleCards).length === 0) {
                container.innerHTML = '<input type="hidden" name="modules_empty" value="1">';
                console.log('✅ No modules - adding empty marker');
                return;
            }

            Object.values(moduleCards).forEach(card => {
                console.log('🔍 Processing card:', {
                    id: card.id,
                    module_name: card.module_name,
                    module_id: card.module_id,
                    assignments: card.assignments
                });

                if (!card.module_id || !card.assignments) {
                    console.warn('⚠️ Skipping card - missing module_id or assignments:', card);
                    return;
                }

                const module = modulesData.find(m => m.id == card.module_id);
                if (!module) {
                    console.warn('⚠️ Skipping card - module not found:', card.module_id);
                    return;
                }

                const applicationCode = getApplicationCodeForSP(module);

                // ✅ CHECK IF THIS IS WINDOWS SIGN-IN
                const isWindowsSignIn = applicationCode.toUpperCase().includes('WINDOWS SIGN') ||
                    card.module_name.toUpperCase().includes('WINDOWS SIGN');

                // ✅ FOR WINDOWS SIGN-IN: Create minimal record and skip role/function processing
                if (isWindowsSignIn) {
                    console.log('✅ Processing Windows Sign-In (special handling)');

                    validAssignments.push({
                        module_id: card.module_id,
                        application_id: module.application_id,
                        application_name: module.name,
                        location_id: locationsData[0]?.id || null,
                        location_code: locationsData[0]?.code || null,
                        role_id: null,
                        role_code: null,
                        function_id: null,
                        function_code: null
                    });

                    return; // ← Skip normal processing for Windows Sign-In
                }

                // ✅ CONTINUE WITH NORMAL PROCESSING FOR OTHER MODULES
                const isSCM = applicationCode === 'OPS' || applicationCode === 'OPS UAT' || applicationCode ===
                    'OPSUAT';


                card.assignments.forEach(a => {
                    // ✅ ADD THIS DEBUG
                    console.log('🔍 Processing assignment:', {
                        id: a.id,
                        location: a.location,
                        locationCodes: a.locationCodes,
                        locationIds: a.locationIds,
                        roleIds: a.roleIds,
                        roleCodes: a.roleCodes,
                        functionIds: a.functionIds,
                        functionCodes: a.functionCodes
                    });

                    // ✅ FIX: Ensure locationCodes is an array and has values
                    const locationCodes = Array.isArray(a.locationCodes) ? a.locationCodes :
                        (a.location ? [a.location] : []);

                    // ✅ FIX: Handle 'ALL' locations properly
                    const locations = a.location === 'ALL' || locationCodes.includes('ALL') ?
                        locationsData.map(l => ({
                            id: l.id,
                            code: l.code.trim()
                        })) :
                        locationCodes.map(code => {
                            const trimmedCode = String(code).trim();
                            const loc = locationsCache[trimmedCode];
                            if (!loc) {
                                console.warn('⚠️ Location not found in cache:', trimmedCode);
                            }
                            return loc ? {
                                id: loc.id,
                                code: loc.code
                            } : null;
                        }).filter(Boolean);

                    console.log('📍 Resolved locations:', locations);

                    // ✅ FIX: Ensure roleIds is an array
                    const roleIds = Array.isArray(a.roleIds) ? a.roleIds :
                        (a.role_id ? [a.role_id] : []);

                    const roles = a.role_id === 'ALL' || roleIds.includes('ALL') ?
                        (rolesCache[`${applicationCode}_all`] || []).map(r => ({
                            id: r.id,
                            code: r.code
                        })) :
                        roleIds.map((id, idx) => ({
                            id: id,
                            code: (a.roleCodes || [])[idx] || ''
                        }));

                    console.log('👤 Resolved roles:', roles);

                    // ✅ FIX: Ensure functionIds is an array for SCM
                    const functionIds = isSCM ? (Array.isArray(a.functionIds) ? a.functionIds :
                        (a.function_id ? [a.function_id] : [])) : [];

                    const functions = isSCM ? (
                        a.function_id === 'ALL' || functionIds.includes('ALL') ?
                        (scmFunctionsCache || []).map(f => ({
                            id: f.id,
                            code: f.code
                        })) :
                        functionIds.map((id, idx) => ({
                            id: id,
                            code: (a.functionCodes || [])[idx] || ''
                        }))
                    ) : [];

                    console.log('⚙️ Resolved functions:', functions);

                    // ✅ CRITICAL FIX: Check if we have valid data before creating assignments
                    if (locations.length === 0) {
                        console.error('❌ No valid locations found for assignment:', a.id);
                        return;
                    }

                    if (roles.length === 0 && (!isSCM || functions.length === 0)) {
                        console.error('❌ No valid roles or functions found for assignment:', a.id);
                        return;
                    }

                    // ✅ CREATE 1-to-1 RECORDS
                    if (locations.length > 0 && roles.length > 0) {
                        locations.forEach(loc => {
                            // Add ROLE records
                            roles.forEach(role => {
                                validAssignments.push({
                                    module_id: card.module_id,
                                    application_id: module.application_id,
                                    location_id: loc.id,
                                    location_code: loc.code,
                                    role_id: role.id,
                                    role_code: role.code,
                                    function_id: null,
                                    function_code: null
                                });
                            });

                            // Add FUNCTION records separately (only for SCM)
                            if (isSCM && functions.length > 0) {
                                functions.forEach(func => {
                                    if (func && func.id) {
                                        validAssignments.push({
                                            module_id: card.module_id,
                                            application_id: module.application_id,
                                            location_id: loc.id,
                                            location_code: loc.code,
                                            role_id: null,
                                            role_code: null,
                                            function_id: func.id,
                                            function_code: func.code
                                        });
                                    }
                                });
                            }
                        });
                    }
                });
            });

            // Create hidden inputs for each valid assignment
            validAssignments.forEach((assignment, idx) => {
                Object.entries(assignment).forEach(([key, value]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `modules[${idx}][${key}]`;
                    input.value = value || '';
                    container.appendChild(input);
                });
            });

            console.log(`✅ Created ${validAssignments.length} valid assignments using 1-to-1 method`);
            console.log('Assignment breakdown:', {
                total: validAssignments.length,
                with_roles: validAssignments.filter(a => a.role_id).length,
                with_functions: validAssignments.filter(a => a.function_id).length
            });

            // If no valid assignments, add empty marker
            if (validAssignments.length === 0) {
                container.innerHTML = '<input type="hidden" name="modules_empty" value="1">';
                console.log('⚠️ No valid assignments - sending empty marker');
            }
        }

        function showLoading() {
            const baseOffice = document.getElementById('scm_base_office')?.value || 'Not set';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Updating User...',
                    html: `
                <div style="text-align: left;">
                    <p>Please wait while we update the user:</p>
                    <ul style="text-align: left; padding-left: 20px;">
                        <li>Base Office: <strong>${baseOffice}</strong></li>
                        <li>Module assignments: Processing...</li>
                    </ul>
                </div>
            `,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
        }

        function showAlert(type, title, message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: type,
                    title: title,
                    text: message,
                    confirmButtonColor: '#3498db'
                });
            } else {
                alert(title + ': ' + message);
            }
        }

        function showLoading() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we update the user',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
        }

        function hideLoading() {
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && currentModalCardId) closeModal();
        });

        function saveBaseOffice() {
            const baseOffice = $('#scm_base_office').val();
            const officeName = $('#scm_base_office option:selected').text();

            // Validation
            if (!baseOffice) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a Base Office!',
                    confirmButtonColor: '#3498db'
                });
                return;
            }

            const csrfToken = $('input[name="_token"]').val() || '{{ csrf_token() }}';
            const saveBtn = $('#saveBaseOfficeBtn');
            const cancelBtn = $('#cancelBaseOfficeBtn');
            const originalHtml = saveBtn.html();

            // Disable both buttons
            saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            cancelBtn.prop('disabled', true);

            // Show loading
            Swal.fire({
                title: 'Saving Base Office...',
                html: `Setting <strong>${officeName}</strong> as SCM Base Office...`,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('_method', 'PUT');
            formData.append('scm_base_office', baseOffice);

            console.log('📤 Sending request at:', new Date().toISOString());

            let requestCompleted = false;
            let pollAttempts = 0;
            const maxPollAttempts = 10;

            $.ajax({
                    url: `/azure/users/{{ $user->Id }}/update-base-office`,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    timeout: 15000
                })
                .done(function(response) {
                    console.log('✅ Response received:', response);
                    requestCompleted = true;

                    if (response.success) {
                        showSuccessAndReload(officeName, baseOffice);
                    } else {
                        showError(response.message || 'Failed to save Base Office.');
                        saveBtn.prop('disabled', false).html(originalHtml);
                        cancelBtn.prop('disabled', false);
                    }
                })
                .fail(function(xhr, status, error) {
                    console.warn('⚠️ Request failed/timeout, but checking if save succeeded...');

                    if (!requestCompleted) {
                        pollForSaveCompletion();
                    }
                });

            function pollForSaveCompletion() {
                pollAttempts++;

                if (pollAttempts > maxPollAttempts) {
                    console.error('❌ Polling timeout - assuming failure');
                    Swal.close();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Save Status Unknown',
                        html: 'Unable to confirm if the base office was saved.<br>' +
                            '<strong>Please refresh the page to check.</strong>',
                        confirmButtonColor: '#3498db',
                        confirmButtonText: 'Refresh Page'
                    }).then(() => {
                        window.location.reload();
                    });
                    return;
                }

                console.log(`🔄 Polling attempt ${pollAttempts}/${maxPollAttempts}...`);

                setTimeout(() => {
                    if (pollAttempts >= 5) {
                        console.log('✅ Assuming save succeeded after 5 seconds');
                        showSuccessAndReload(officeName, baseOffice);
                    } else {
                        pollForSaveCompletion();
                    }
                }, 1000);
            }

            function showSuccessAndReload(officeName, newValue) {
                Swal.close();
                Swal.fire({
                    icon: 'success',
                    title: 'Base Office Saved!',
                    html: `<p><strong>${officeName}</strong> has been set as the SCM Base Office.</p>
                    <p class="text-muted mb-0" style="font-size: 0.9em;">Refreshing page...</p>`,
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                    allowOutsideClick: false
                }).then(() => {
                    $('#scm_base_office').data('original-value', newValue);
                    window.location.reload();
                });
            }

            function showError(message) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Save Failed',
                    html: message,
                    confirmButtonColor: '#d33'
                });
            }
        }

        // ✅ Initialize on page load
        $(document).ready(function() {
            const originalValue = $('#scm_base_office').val();
            $('#scm_base_office').data('original-value', originalValue);

            // Hide both buttons initially
            $('#saveBaseOfficeBtn').hide();
            $('#cancelBaseOfficeBtn').hide();

            // Show/hide buttons when value changes
            $('#scm_base_office').on('change', function() {
                checkBaseOfficeChange();
            });
        });

        // ✅ Initialize on page load
        $(document).ready(function() {
            // Store original value
            const originalValue = $('#scm_base_office').val();
            $('#scm_base_office').data('original-value', originalValue);

            // Hide button initially
            $('#saveBaseOfficeBtn').hide();

            // Show/hide button when value changes
            $('#scm_base_office').on('change', function() {
                checkBaseOfficeChange();
            });
        });


        // ============================================================================
        // FIXED: updateUser() function - ADD BASE OFFICE TO REQUEST
        // ============================================================================

        window.updateUser = async () => {
            console.log('🔄 Update User clicked');

            try {
                // ====================================================================
                // ✅ STEP 0: VALIDATE MANDATORY FIELDS
                // ====================================================================
                const validation = validateMandatoryFields();

                if (!validation.valid) {
                    console.error('❌ Validation failed:', validation.errors);

                    // Build error message
                    const errorList = validation.errors
                        .map(err => `• <strong>${err.field}:</strong> ${err.message}`)
                        .join('<br>');

                    Swal.fire({
                        icon: 'error',
                        title: 'Required Fields Missing',
                        html: `
                    <p style="margin-bottom: 15px;">Please fix the following issues before updating:</p>
                    <div style="text-align: left; padding: 10px 20px; background: #fff3cd; border-radius: 6px; border-left: 4px solid #ffc107;">
                        ${errorList}
                    </div>
                    <p style="margin-top: 15px; color: #666; font-size: 0.9em;">
                        <i class="fas fa-info-circle"></i> These fields are mandatory for user activation.
                    </p>
                `,
                        confirmButtonColor: '#3498db',
                        confirmButtonText: 'OK, I\'ll fix it',
                        width: '600px'
                    });

                    // Highlight missing fields
                    validation.errors.forEach(err => {
                        if (err.field === 'Base Office') {
                            const baseOfficeSelect = document.getElementById('scm_base_office');
                            if (baseOfficeSelect) {
                                baseOfficeSelect.style.border = '2px solid #dc3545';
                                baseOfficeSelect.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });

                                // Remove highlight after 3 seconds
                                setTimeout(() => {
                                    baseOfficeSelect.style.border = '';
                                }, 3000);
                            }
                        }

                        if (err.field === 'Windows Sign-In') {
                            const modulesGrid = document.getElementById('modulesGrid');
                            if (modulesGrid) {
                                modulesGrid.style.border = '2px solid #dc3545';
                                modulesGrid.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });

                                // Remove highlight after 3 seconds
                                setTimeout(() => {
                                    modulesGrid.style.border = '';
                                }, 3000);
                            }
                        }
                    });

                    return; // ✅ Stop execution
                }

                console.log('✅ Validation passed - proceeding with update');

                // Show loading state
                Swal.fire({
                    title: 'Updating User...',
                    html: 'Please wait while we save your changes...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // ========================================================================
                // ✅ STEP 1: Get the base office value from the form
                // ========================================================================
                const baseOfficeSelect = document.getElementById('scm_base_office');
                const baseOfficeValue = baseOfficeSelect ? baseOfficeSelect.value : null;

                console.log('📍 Base Office Value:', baseOfficeValue);

                // ========================================================================
                // Build form data with modules AND base office
                // ========================================================================
                const formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('_method', 'PUT');

                // ✅ Add base office to form data
                formData.append('scm_base_office', baseOfficeValue);

                // Get hidden inputs (modules)
                const hiddenInputs = document.querySelectorAll('#hiddenInputsContainer input[type="hidden"]');

                console.log('📦 Hidden inputs found:', hiddenInputs.length);

                if (hiddenInputs.length === 0) {
                    // Check if this is intentional (clearing all modules)
                    const confirmClear = await Swal.fire({
                        title: 'No Module Assignments',
                        text: 'You are about to save with no module assignments. Continue?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3498db',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, save anyway',
                        cancelButtonText: 'Cancel'
                    });

                    if (!confirmClear.isConfirmed) {
                        return;
                    }

                    // Add empty marker
                    formData.append('modules_empty', '1');
                } else {
                    // Add all hidden inputs to form data
                    hiddenInputs.forEach((input, index) => {
                        formData.append(input.name, input.value);
                        console.log(`Input ${index}: ${input.name} = ${input.value}`);
                    });
                }

                // ========================================================================
                // Send AJAX request
                // ========================================================================
                console.log('🚀 Sending update request...');

                const response = await fetch(updateRoute, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                console.log('📡 Response status:', response.status);

                const data = await response.json();
                console.log('📨 Response data:', data);

                Swal.close();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        html: data.message,
                        confirmButtonColor: '#3498db'
                    }).then(() => {
                        // Redirect to user details page
                        window.location.href = "{{ route('azure.users.show', $user) }}";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        html: data.message || 'An error occurred while updating the user.',
                        confirmButtonColor: '#3498db'
                    });
                }

            } catch (error) {
                console.error('❌ Update error:', error);

                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An unexpected error occurred: ' + error.message,
                    confirmButtonColor: '#3498db'
                });
            }
        };
        // ============================================================================
        // BONUS: Add validation when base office changes
        // ============================================================================
        document.addEventListener('DOMContentLoaded', function() {
            // ====================================================================
            // ✅ 1. BASE OFFICE - Change notification (existing code)
            // ====================================================================
            const baseOfficeSelect = document.getElementById('scm_base_office');

            if (baseOfficeSelect) {
                baseOfficeSelect.addEventListener('change', function() {
                    console.log('📍 Base Office changed to:', this.value);

                    // Show notification that changes need to be saved
                    const notification = document.createElement('div');
                    notification.className = 'alert alert-info alert-dismissible fade show';
                    notification.style.cssText =
                        'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
                    notification.innerHTML = `
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-info-circle"></i> Base office changed. Click "Update User" to save.
            `;

                    document.body.appendChild(notification);

                    // Auto-remove after 5 seconds
                    setTimeout(() => {
                        notification.remove();
                    }, 5000);

                    // ✅ Show Update User button when base office changes
                    toggleFormActions(true);
                });
            }

            // ====================================================================
            // ✅ 2. MARK BASE OFFICE AS REQUIRED (NEW - Add this)
            // ====================================================================
            const baseOfficeLabel = document.querySelector('label[for="scm_base_office"]');
            if (baseOfficeLabel && !baseOfficeLabel.querySelector('.required-indicator')) {
                // Check if asterisk already exists in label text
                if (!baseOfficeLabel.innerHTML.includes('*')) {
                    const requiredSpan = document.createElement('span');
                    requiredSpan.className = 'required-indicator';
                    requiredSpan.style.cssText = 'color: #dc3545; margin-left: 4px; font-weight: bold;';
                    requiredSpan.textContent = '*';
                    baseOfficeLabel.appendChild(requiredSpan);
                }
            }

            // ====================================================================
            // ✅ 3. ADD MANDATORY MODULE BANNER (NEW - Add this)
            // ====================================================================
            const modulesSection = document.querySelector('.form-section:has(#modulesGrid)');
            if (modulesSection) {
                const existingBanner = modulesSection.querySelector('.mandatory-module-banner');
                if (!existingBanner) {
                    const banner = document.createElement('div');
                    banner.className = 'mandatory-module-banner';
                    banner.style.cssText = `
                background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
                border: 2px solid #ffc107;
                border-radius: 8px;
                padding: 12px 16px;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                gap: 12px;
            `;
                    banner.innerHTML = `
                <i class="fas fa-exclamation-triangle" style="color: #856404; font-size: 1.3em;"></i>
                <span style="color: #856404; font-weight: 600;">
                    <strong>Required:</strong> Windows Sign-In module must be assigned before saving.
                </span>
            `;

                    const formSectionTitle = modulesSection.querySelector('.form-section-title');
                    if (formSectionTitle) {
                        formSectionTitle.insertAdjacentElement('afterend', banner);
                    }
                }
            }

            // ====================================================================
            // ✅ 4. PREVENT FORM SUBMISSION ON ENTER KEY (NEW - Add this)
            // ====================================================================
            const editForm = document.getElementById('editUserForm');
            if (editForm) {
                editForm.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        console.log('⚠️ Form submission prevented (use Update User button)');
                        return false;
                    }
                });
            }

            // ====================================================================
            // ✅ 5. CHECK IF WINDOWS SIGN-IN IS ALREADY ASSIGNED (NEW - Add this)
            // ====================================================================
            function checkWindowsSignInStatus() {
                const hasWindowsSignIn = Object.values(moduleCards).some(card => {
                    const moduleName = card.module_name.toUpperCase();
                    return moduleName.includes('WINDOWS SIGN') || moduleName.includes('WINDOWS SIGNIN');
                });

                const banner = document.querySelector('.mandatory-module-banner');
                if (banner) {
                    if (hasWindowsSignIn) {
                        // ✅ Windows Sign-In is assigned - change banner to success
                        banner.style.background = 'linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%)';
                        banner.style.borderColor = '#28a745';
                        banner.innerHTML = `
                    <i class="fas fa-check-circle" style="color: #155724; font-size: 1.3em;"></i>
                    <span style="color: #155724; font-weight: 600;">
                        <strong>✓ Windows Sign-In assigned</strong> - Required module is present.
                    </span>
                `;
                    } else {
                        // ⚠️ Windows Sign-In is NOT assigned - warning banner
                        banner.style.background = 'linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%)';
                        banner.style.borderColor = '#ffc107';
                        banner.innerHTML = `
                    <i class="fas fa-exclamation-triangle" style="color: #856404; font-size: 1.3em;"></i>
                    <span style="color: #856404; font-weight: 600;">
                        <strong>Required:</strong> Windows Sign-In module must be assigned before saving.
                    </span>
                `;
                    }
                }
            }

            // ====================================================================
            // ✅ 6. MONITOR MODULE CHANGES (NEW - Add this)
            // ====================================================================
            // Override renderAllCards to check Windows Sign-In status after rendering
            const originalRenderAllCards = window.renderAllCards;
            if (originalRenderAllCards) {
                window.renderAllCards = function() {
                    originalRenderAllCards.call(this);
                    checkWindowsSignInStatus();
                };
            }

            // ====================================================================
            // ✅ 7. INITIAL CHECK (NEW - Add this)
            // ====================================================================
            // Wait a bit for modules to load, then check status
            setTimeout(() => {
                checkWindowsSignInStatus();
            }, 500);
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeModules();
        });

        // ✅ NEW: Detect base office changes and show Update User button
        $('#scm_base_office').on('change', function() {
            console.log('📍 Base Office changed to:', $(this).val());
            toggleFormActions(true); // Show the Update User button
        });

        /**
         * Validate mandatory fields before submission
         * @returns {object} { valid: boolean, errors: array }
         */
        function validateMandatoryFields() {
            const errors = [];

            // ✅ Check 1: Windows Sign-In module is assigned
            const hasWindowsSignIn = Object.values(moduleCards).some(card => {
                const moduleName = card.module_name.toUpperCase();
                return moduleName.includes('WINDOWS SIGN') || moduleName.includes('WINDOWS SIGNIN');
            });

            if (!hasWindowsSignIn) {
                errors.push({
                    field: 'Windows Sign-In',
                    message: 'Windows Sign-In module is mandatory and must be assigned before saving.'
                });
            }

            // ✅ Check 2: Base Office is selected
            const baseOfficeSelect = document.getElementById('scm_base_office');
            const baseOfficeValue = baseOfficeSelect ? baseOfficeSelect.value : null;

            if (!baseOfficeValue || baseOfficeValue.trim() === '') {
                errors.push({
                    field: 'Base Office',
                    message: 'Base Office must be selected before saving.'
                });
            }

            return {
                valid: errors.length === 0,
                errors: errors
            };
        }
    </script>
@stop
