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
    <div class="header-right">
        <a href="{{ route('azure.dashboard.index') }}" class="back-to-dashboard">
            <span>Back to Dashboard</span>
            <i class="fas fa-arrow-right"></i>
        </a>
    </div>
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
                                    Employee ID <span style="color: var(--danger);">*</span>
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
                                    Email Address <span style="color: var(--danger);">*</span>
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
                            <div class="form-group">
                                <label for="company_id" class="form-label">
                                    Company
                                </label>
                                @php
                                $userCompanyId = old('company_id', $user->company_id ?? '');
                                $userCompanyDisplay = '';
                                if ($userCompanyId) {
                                $companyObj = $companies->firstWhere('id', $userCompanyId);
                                if ($companyObj) {
                                $userCompanyDisplay = $companyObj->name;
                                }
                                }
                                @endphp
                                <input type="text"
                                    id="company_id"
                                    name="company_id"
                                    class="form-control @error('company_id') is-invalid @enderror"
                                    value="{{ $userCompanyDisplay }}"
                                    readonly>
                                @error('company_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Module Assignments Section -->
                    <div class="form-section">
                        <div class="form-section-title" style="display: flex; justify-content: space-between; align-items: center;">
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
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
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
    const updateRoute = "{{ route('users.update', $user) }}";
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
        return JSON.parse(JSON.stringify(obj));
    }

    function getApplicationCodeForSP(module) {
        return module.name;
    }

    // Show/Hide the Update User & Cancel buttons
    function toggleFormActions(show) {
        const moduleActionsSection = document.getElementById('moduleActionsSection');
        if (show) {
            moduleActionsSection.classList.add('visible');
            hasUnsavedChanges = true;
        } else {
            moduleActionsSection.classList.remove('visible');
            hasUnsavedChanges = false;
        }
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

            // Store the assignment with proper structure
            grouped[moduleId].assignments.push({
                id: `assign_${cardIdCounter++}`,
                location: m.location,
                locationIds: [m.location_id],
                locationCodes: [m.location],
                role_id: m.role_id,
                roleIds: [m.role_id],
                roleCodes: [m.role_code || ''],
                function_id: m.function_id || undefined,
                functionIds: m.function_id ? [m.function_id] : undefined,
                functionCodes: m.function_code ? [m.function_code] : undefined
            });
        });

        Object.values(grouped).forEach(data => {
            const cardId = `card_${cardIdCounter++}`;
            moduleCards[cardId] = {
                id: cardId,
                isNew: false,
                ...data
            };
        });

        // Store initial state for comparison
        initialModuleState = deepClone(moduleCards);

        renderAllCards();
    }

    function renderAllCards() {
        const grid = document.getElementById('modulesGrid');
        grid.innerHTML = '';
        Object.values(moduleCards).forEach(card => {
            grid.appendChild(createModuleCard(card));
        });
        if (Object.keys(moduleCards).length === 0) {
            grid.innerHTML = '<div class="empty-grid"><i class="fas fa-inbox"></i><p>No modules assigned yet. Click "Add New Assignment" to get started.</p></div>';
        }
        updateHiddenInputs();
    }

    function createModuleCard(card) {
        const div = document.createElement('div');
        div.className = `module-card ${card.isNew ? 'new-module' : ''}`;
        div.id = card.id;
        div.onclick = () => openModal(card.id);
        const assignmentCount = card.assignments ? card.assignments.length : 0;
        div.innerHTML = `
            <i class="fas ${card.isNew ? 'fa-plus-circle' : 'fa-cube'} card-icon"></i>
            <div class="card-title">${escapeHtml(card.module_name)}</div>
            <span class="assignment-count">
                <i class="fas fa-list"></i>
                ${assignmentCount} assignment${assignmentCount !== 1 ? 's' : ''}
            </span>
        `;
        return div;
    }

    window.openModal = async (cardId) => {
        currentModalCardId = cardId;
        const card = moduleCards[cardId];
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
            container.innerHTML = '<div class="empty-assignments"><i class="fas fa-spinner fa-spin"></i><p>Loading data...</p></div>';

            // Fetch roles and functions concurrently
            const promises = [fetchRolesByApplicationCode(applicationCode)];
            if (isSCM) {
                promises.push(fetchSCMFunctions());
            }

            await Promise.all(promises);
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
        if (!isEditMode) return;

        const card = moduleCards[currentModalCardId];

        // For NEW modules: just close the modal (no Update User button needed)
        if (card && card.isNew) {
            card.isNew = false;
            document.getElementById('moduleModal').classList.remove('active');
            currentModalCardId = null;
            isEditMode = false;
            originalCardState = null;
            renderAllCards();
            showAlert('success', 'Saved', 'New module assignment added. The changes are ready.');
            return;
        }

        // For EXISTING modules: show the Update User & Cancel buttons
        if (currentModalCardId && moduleCards[currentModalCardId]) {
            moduleCards[currentModalCardId].isNew = false;
        }

        // Update the original state to the current state
        originalCardState = deepClone(moduleCards[currentModalCardId]);

        // Close modal
        document.getElementById('moduleModal').classList.remove('active');
        currentModalCardId = null;
        isEditMode = false;

        renderAllCards();

        // Show the Update User & Cancel buttons
        toggleFormActions(true);

        showAlert('success', 'Saved', 'Changes saved. Click "Update User" to save to database.');
    };

    function renderModalAssignments() {
        const card = moduleCards[currentModalCardId];
        const container = document.getElementById('modalAssignmentsContainer');

        // Update modal header - REMOVED the extra "Delete Module" button
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
            container.innerHTML = '<div class="empty-assignments"><i class="fas fa-clipboard-list"></i><p>No assignments yet.</p></div>';
            return;
        }

        const module = modulesData.find(m => m.id == card.module_id);

        if (!module) {
            container.innerHTML = '<div class="empty-assignments"><i class="fas fa-exclamation-triangle"></i><p>Module not found.</p></div>';
            return;
        }

        const applicationCode = getApplicationCodeForSP(module);
        const isSCM = applicationCode === 'OPS' || applicationCode === 'OPS UAT';
        const cacheKey = `${applicationCode}_all`;

        // Check if data is loaded
        if (!rolesCache[cacheKey]) {
            container.innerHTML = '<div class="empty-assignments"><i class="fas fa-spinner fa-spin"></i><p>Loading roles...</p></div>';
            return;
        }

        if (isSCM && !scmFunctionsCache) {
            container.innerHTML = '<div class="empty-assignments"><i class="fas fa-spinner fa-spin"></i><p>Loading functions...</p></div>';
            return;
        }

        const availableRoles = rolesCache[cacheKey] || [];
        const availableFunctions = isSCM ? (scmFunctionsCache || []) : [];

        container.innerHTML = card.assignments.map(a => {
            const isAllLocations = a.location === 'ALL';
            const isAllRoles = a.role_id === 'ALL';
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
                        <button type="button" class="btn btn-sm btn-ghost" style="color: #dc3545;" onclick="deleteAssignment('${a.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="assignment-info" style="grid-template-columns: ${isSCM ? '1fr 1fr 1fr' : '1fr 1fr'};">
                    <!-- OFFICE LOCATION -->
                    <div class="info-field">
                        <div class="field-label"><i class="fas fa-map-marker-alt"></i> Office Location</div>
                        <div class="radio-group">
                            <div class="radio-option ${disabledClass}">
                                <input type="radio" id="${a.id}_all_locations" name="${a.id}_location_type" class="all-locations-radio" ${isAllLocations ? 'checked' : ''} ${disabled} onchange="handleLocationRadioChange('${a.id}')">
                                <label for="${a.id}_all_locations">All Locations</label>
                            </div>
                            <div class="radio-option ${disabledClass}">
                                <input type="radio" id="${a.id}_specific_locations" name="${a.id}_location_type" class="specific-locations-radio" ${!isAllLocations ? 'checked' : ''} ${disabled} onchange="handleLocationRadioChange('${a.id}')">
                                <label for="${a.id}_specific_locations">Specific Location</label>
                            </div>
                        </div>
                        <div class="checkboxes-grid">
                            ${locationsData.map(l => {
                                const locCode = l.code.trim();
                                const isChecked = a.locationCodes && a.locationCodes.includes(locCode);
                                return `
                                    <div class="checkbox-option ${isAllLocations || !isEditMode ? 'disabled' : ''}">
                                        <input type="checkbox" 
                                               id="${a.id}_loc_${locCode}" 
                                               class="location-checkbox" 
                                               data-location-id="${l.id}"
                                               data-location-code="${locCode}"
                                               data-location-name="${escapeHtml(l.name)}"
                                               value="${locCode}" 
                                               ${isChecked || isAllLocations ? 'checked' : ''} 
                                               ${isAllLocations || !isEditMode ? 'disabled' : ''} 
                                               onchange="handleLocationCheckboxChange('${a.id}')">
                                        <label for="${a.id}_loc_${locCode}">${escapeHtml(l.name)}</label>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                    
                    <!-- SCM FUNCTIONS (Only for OPS/OPSUAT) -->
                    ${isSCM ? `
                    <div class="info-field">
                        <div class="field-label"><i class="fas fa-cog"></i> Function (SCM)</div>
                        <div class="radio-group">
                            <div class="radio-option ${disabledClass}">
                                <input type="radio" id="${a.id}_all_functions" name="${a.id}_function_type" class="all-functions-radio" ${isAllFunctions ? 'checked' : ''} ${disabled} onchange="handleFunctionRadioChange('${a.id}')">
                                <label for="${a.id}_all_functions">All Functions</label>
                            </div>
                            <div class="radio-option ${disabledClass}">
                                <input type="radio" id="${a.id}_specific_functions" name="${a.id}_function_type" class="specific-functions-radio" ${!isAllFunctions ? 'checked' : ''} ${disabled} onchange="handleFunctionRadioChange('${a.id}')">
                                <label for="${a.id}_specific_functions">Specific Function</label>
                            </div>
                        </div>
                        <div class="checkboxes-grid">
                            ${availableFunctions.length > 0 ? availableFunctions.map(f => {
                                const isChecked = a.functionIds && a.functionIds.includes(String(f.id));
                                return `
                                    <div class="checkbox-option ${isAllFunctions || !isEditMode ? 'disabled' : ''}">
                                        <input type="checkbox" 
                                               id="${a.id}_func_${f.id}" 
                                               class="function-checkbox" 
                                               data-function-id="${f.id}"
                                               data-function-code="${escapeHtml(f.code)}"
                                               value="${f.id}" 
                                               ${isChecked || isAllFunctions ? 'checked' : ''} 
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
                        <div class="radio-group">
                            <div class="radio-option ${disabledClass}">
                                <input type="radio" id="${a.id}_all_roles" name="${a.id}_role_type" class="all-roles-radio" ${isAllRoles ? 'checked' : ''} ${disabled} onchange="handleRoleRadioChange('${a.id}')">
                                <label for="${a.id}_all_roles">All Roles</label>
                            </div>
                            <div class="radio-option ${disabledClass}">
                                <input type="radio" id="${a.id}_specific_roles" name="${a.id}_role_type" class="specific-roles-radio" ${!isAllRoles ? 'checked' : ''} ${disabled} onchange="handleRoleRadioChange('${a.id}')">
                                <label for="${a.id}_specific_roles">Specific Role</label>
                            </div>
                        </div>
                        <div class="checkboxes-grid">
                            ${availableRoles.length > 0 ? availableRoles.map(r => {
                                const isChecked = a.roleIds && a.roleIds.includes(String(r.id));
                                return `
                                    <div class="checkbox-option ${isAllRoles || !isEditMode ? 'disabled' : ''}">
                                        <input type="checkbox" 
                                               id="${a.id}_role_${r.id}" 
                                               class="role-checkbox" 
                                               data-role-id="${r.id}"
                                               data-role-code="${escapeHtml(r.code)}"
                                               value="${r.id}" 
                                               ${isChecked || isAllRoles ? 'checked' : ''} 
                                               ${isAllRoles || !isEditMode ? 'disabled' : ''} 
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
        }).join('');
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
                const locCode = cb.getAttribute('data-location-code');

                assignment.locationIds.push(locId);
                assignment.locationCodes.push(locCode);
            });

            assignment.location = assignment.locationCodes.length > 0 ? assignment.locationCodes[0] : '';
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
                const funcId = cb.getAttribute('data-function-id');
                const funcCode = cb.getAttribute('data-function-code');

                assignment.functionIds.push(funcId);
                assignment.functionCodes.push(funcCode);
            });

            assignment.function_id = assignment.functionIds.length > 0 ? assignment.functionIds[0] : '';
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

        const checkboxes = container.querySelectorAll('.role-checkbox:checked:not(:disabled)');
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
        const moduleSelectorHTML = `
            <div class="module-selector-grid">
                ${modulesData.map(m => `
                    <div class="module-selector-card" data-module-id="${escapeHtml(String(m.id))}">
                        <i class="fas fa-cube"></i>
                        <div class="module-name">${escapeHtml(m.name)}</div>
                    </div>
                `).join('')}
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
                        const moduleId = this.getAttribute('data-module-id');
                        selectModule(moduleId);
                    });
                });
            }
        });
    };

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
        const isSCM = applicationCode === 'OPS' || applicationCode === 'OPS UAT';

        moduleCards[cardId] = {
            id: cardId,
            isNew: true,
            module_id: moduleId,
            module_name: module.name,
            application_id: module.application_id,
            assignments: [{
                id: assignId,
                location: '',
                locationIds: [],
                locationCodes: [],
                role_id: '',
                roleIds: [],
                roleCodes: [],
                function_id: isSCM ? '' : undefined,
                functionIds: isSCM ? [] : undefined,
                functionCodes: isSCM ? [] : undefined
            }]
        };

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

        console.log('Card before delete:', {
            id: card.id,
            module: card.module_name,
            assignments: card.assignments.length
        });

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
            text: 'This will revert all unsaved changes to module assignments',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, cancel changes',
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

    function updateHiddenInputs() {
        const container = document.getElementById('hiddenInputsContainer');
        container.innerHTML = '';
        let index = 0;

        Object.values(moduleCards).forEach(card => {
            if (!card.module_id || !card.assignments) return;

            const module = modulesData.find(m => m.id == card.module_id);
            if (!module) return;

            const applicationCode = getApplicationCodeForSP(module);
            const isSCM = applicationCode === 'OPS' || applicationCode === 'OPS UAT';

            card.assignments.forEach(a => {
                // Determine what to expand
                const locations = a.location === 'ALL' ?
                    locationsData.map(l => ({
                        id: l.id,
                        code: l.code.trim()
                    })) :
                    (a.locationCodes || []).map(code => ({
                        id: locationsCache[code]?.id || '',
                        code: code
                    }));

                const roles = a.role_id === 'ALL' ?
                    (rolesCache[`${applicationCode}_all`] || []).map(r => ({
                        id: r.id,
                        code: r.code
                    })) :
                    (a.roleIds || []).map((id, idx) => ({
                        id: id,
                        code: a.roleCodes[idx] || ''
                    }));

                const functions = isSCM ? (
                    a.function_id === 'ALL' ?
                    (scmFunctionsCache || []).map(f => ({
                        id: f.id,
                        code: f.code
                    })) :
                    (a.functionIds || []).map((id, idx) => ({
                        id: id,
                        code: a.functionCodes[idx] || ''
                    }))
                ) : [];

                // Create combinations
                if (locations.length > 0 && roles.length > 0) {
                    locations.forEach(loc => {
                        roles.forEach(role => {
                            if (isSCM && functions.length > 0) {
                                functions.forEach(func => {
                                    container.innerHTML += `
                                        <input type="hidden" name="modules[${index}][module_id]" value="${card.module_id}">
                                        <input type="hidden" name="modules[${index}][application_id]" value="${module.application_id}">
                                        <input type="hidden" name="modules[${index}][location_id]" value="${loc.id}">
                                        <input type="hidden" name="modules[${index}][location_code]" value="${loc.code}">
                                        <input type="hidden" name="modules[${index}][role_id]" value="${role.id}">
                                        <input type="hidden" name="modules[${index}][role_code]" value="${role.code}">
                                        <input type="hidden" name="modules[${index}][function_id]" value="${func.id}">
                                        <input type="hidden" name="modules[${index}][function_code]" value="${func.code}">
                                    `;
                                    index++;
                                });
                            } else {
                                container.innerHTML += `
                                    <input type="hidden" name="modules[${index}][module_id]" value="${card.module_id}">
                                    <input type="hidden" name="modules[${index}][application_id]" value="${module.application_id}">
                                    <input type="hidden" name="modules[${index}][location_id]" value="${loc.id}">
                                    <input type="hidden" name="modules[${index}][location_code]" value="${loc.code}">
                                    <input type="hidden" name="modules[${index}][role_id]" value="${role.id}">
                                    <input type="hidden" name="modules[${index}][role_code]" value="${role.code}">
                                `;
                                index++;
                            }
                        });
                    });
                }
            });
        });

        console.log('✅ Created', index, 'hidden inputs for', Object.keys(moduleCards).length, 'module cards');
    }

    window.updateUser = () => {
        updateHiddenInputs();
        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('_method', 'PUT');
        document.querySelectorAll('#hiddenInputsContainer input').forEach(input => {
            formData.append(input.name, input.value);
        });
        showLoading();
        fetch(updateRoute, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showAlert('success', 'Success', 'User updated successfully!');

                    // Hide the Update User & Cancel buttons
                    toggleFormActions(false);

                    // Update initial state
                    initialModuleState = deepClone(moduleCards);

                    setTimeout(() => {
                        window.location.href = "{{ route('azure.dashboard.index') }}";
                    }, 1500);
                } else {
                    showAlert('error', 'Error', data.message || 'Failed to update user');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                showAlert('error', 'Error', 'An error occurred while updating the user');
            });
    };

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

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeModules();
    });
</script>
@stop