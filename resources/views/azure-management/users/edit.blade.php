@extends('azure-management.layouts.app')

@section('title', 'Edit User')

@section('page-title', 'Edit User')

@push('styles')
<style>
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
        width: 100%;
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

    .invalid-feedback {
        color: #e74c3c;
        font-size: 0.875em;
        margin-top: 5px;
        display: block;
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
        box-shadow: var(--shadow-sm);
        text-align: center;
        min-height: 140px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 12px;
    }

    .module-card:hover {
        border-color: var(--primary-color);
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }

    .module-card.new-module {
        border-color: var(--success);
        border-style: dashed;
        background: #f0fff4;
    }

    .card-icon {
        font-size: 2.5em;
        color: var(--primary-color);
        margin-bottom: 8px;
    }

    .module-card.new-module .card-icon {
        color: var(--success);
    }

    .card-title {
        font-size: 1.1em;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 4px;
    }

    .assignment-count {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        background: #e8f0ff;
        color: var(--primary-color);
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85em;
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

    .empty-grid {
        grid-column: 1/-1;
        text-align: center;
        padding: 60px 20px;
        color: var(--muted);
    }

    .empty-grid i {
        font-size: 3em;
        opacity: 0.3;
        margin-bottom: 15px;
        display: block;
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
        z-index: 1000;
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
        color: var(--muted);
        cursor: pointer;
        padding: 4px 8px;
        transition: all 0.2s;
    }

    .modal-close:hover {
        color: var(--text);
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
        color: var(--muted);
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
        border-color: var(--primary-color);
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
        border-color: var(--primary-color);
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
        color: var(--muted);
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
        border-color: var(--primary-color);
        background: #f8faff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(11, 102, 255, 0.15);
    }

    .module-selector-card i {
        font-size: 2em;
        color: var(--primary-color);
        margin-bottom: 5px;
    }

    .module-selector-card .module-name {
        font-weight: 600;
        font-size: 0.95em;
        color: var(--text);
    }

    /* View Mode Styles */
    .view-mode-banner {
        background: #e8f0ff;
        border: 2px solid var(--primary-color);
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
        color: var(--primary-color);
        font-weight: 600;
    }

    .btn-danger {
        background: var(--danger);
        color: var(--primary-contrast);
    }

    .btn-success {
        background: var(--success);
        color: var(--primary-contrast);
    }

    .btn-sm {
        padding: 6px 10px;
        font-size: 0.875rem;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .edit-form-container {
            padding: 20px;
        }

        .assignment-info {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
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
                        Location
                    </label>
                    @php
                        $userLocationCode = old('location', $user->location ?? '');
                        $userLocationDisplay = '';
                        if ($userLocationCode) {
                            $locationObj = $locations->firstWhere('code', $userLocationCode);
                            if ($locationObj) {
                                $userLocationDisplay = $locationObj->name . ' (' . $locationObj->code . ')';
                            }
                        }
                    @endphp
                    <input type="text" 
                        id="location" 
                        name="location" 
                        class="form-control @error('location') is-invalid @enderror" 
                        value="{{ $userLocationDisplay }}" 
                        readonly>
                    @error('location')
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
            <div class="form-section-title" style="display: flex; justify-content: space-between; align-items: center;">
                <div><i class="fas fa-th-large"></i> Module Assignments</div>
                <button type="button" class="btn btn-sm btn-success" onclick="addNewModule()">
                    <i class="fas fa-plus"></i> Add New Assignment
                </button>
            </div>
            <div id="modulesGrid" class="modules-grid"></div>
        </div>

        <div id="hiddenInputsContainer"></div>

        <!-- Form Actions -->
        <div class="form-section">
            <div style="display: flex; gap: 15px; justify-content: flex-end; align-items: center;">
                <a href="{{ route('azure.dashboard.index') }}" class="btn btn-ghost">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
                <button type="button" class="btn btn-primary" onclick="updateUser()">
                    <i class="fas fa-save"></i>
                    Update User
                </button>
            </div>
        </div>
    </form>
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
@endsection

@push('scripts')
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

    function deepClone(obj) {
        return JSON.parse(JSON.stringify(obj));
    }

    function initializeModules() {
        const grouped = {};
        existingModules.forEach(m => {
            const moduleId = m.module_id;
            if (!grouped[moduleId]) {
                grouped[moduleId] = {
                    module_id: moduleId,
                    module_name: m.module_name,
                    assignments: []
                };
            }
            grouped[moduleId].assignments.push({
                id: `assign_${cardIdCounter++}`,
                location: m.location,
                role_id: m.role_id
            });
        });

        Object.values(grouped).forEach(data => {
            const cardId = `card_${cardIdCounter++}`;
            moduleCards[cardId] = { id: cardId, isNew: false, ...data };
        });

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
            <div class="card-title">${card.module_name}</div>
            <span class="assignment-count">
                <i class="fas fa-list"></i>
                ${assignmentCount} assignment${assignmentCount !== 1 ? 's' : ''}
            </span>
        `;
        return div;
    }

    window.openModal = (cardId) => {
        currentModalCardId = cardId;
        const card = moduleCards[cardId];
        originalCardState = deepClone(card);
        isEditMode = card.isNew;
        document.getElementById('modalModuleName').textContent = card.module_name;
        renderModalAssignments();
        updateModalViewState();
        document.getElementById('moduleModal').classList.add('active');
    };

    function updateModalViewState() {
        const viewBanner = document.getElementById('viewModeBanner');
        const footer = document.getElementById('modalFooter');
        if (isEditMode) {
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
        if (originalCardState && originalCardState.isNew && currentModalCardId) {
            delete moduleCards[currentModalCardId];
        } else if (originalCardState && currentModalCardId) {
            moduleCards[currentModalCardId] = deepClone(originalCardState);
        }
        document.getElementById('moduleModal').classList.remove('active');
        currentModalCardId = null;
        isEditMode = false;
        originalCardState = null;
        renderAllCards();
    };

    window.closeModalOnOverlay = (event) => {
        if (event.target.id === 'moduleModal') {
            cancelModal();
        }
    };

    window.saveModalChanges = () => {
        if (!isEditMode) return;
        originalCardState = null;
        if (currentModalCardId && moduleCards[currentModalCardId]) {
            moduleCards[currentModalCardId].isNew = false;
        }
        document.getElementById('moduleModal').classList.remove('active');
        currentModalCardId = null;
        isEditMode = false;
        renderAllCards();
        showAlert('success', 'Saved', 'Changes saved. Click "Update User" to save to database.');
    };

    function renderModalAssignments() {
        const card = moduleCards[currentModalCardId];
        const container = document.getElementById('modalAssignmentsContainer');
        if (!card.assignments || card.assignments.length === 0) {
            container.innerHTML = '<div class="empty-assignments"><i class="fas fa-clipboard-list"></i><p>No assignments yet.</p></div>';
            return;
        }
        container.innerHTML = card.assignments.map(a => {
            const isAllLocations = a.location === 'ALL';
            const isAllRoles = a.role_id === 'ALL';
            const disabled = !isEditMode ? 'disabled' : '';
            const disabledClass = !isEditMode ? 'disabled' : '';

            return `
                <div class="assignment-item" id="${a.id}">
                    <div class="assignment-header">
                        <span style="font-weight: 600; color: var(--muted); font-size: 0.9em;">Assignment</span>
                        <div style="display: flex; gap: 8px; ${!isEditMode ? 'display: none;' : ''}">
                            <button type="button" class="btn btn-sm btn-ghost" style="color: var(--danger);" onclick="deleteAssignment('${a.id}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="assignment-info">
                        <div class="info-field">
                            <div class="field-label"><i class="fas fa-map-marker-alt"></i> Office</div>
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
                                ${locationsData.map(l => `
                                    <div class="checkbox-option ${isAllLocations || !isEditMode ? 'disabled' : ''}">
                                        <input type="checkbox" id="${a.id}_loc_${l.code}" class="location-checkbox" value="${l.code}" ${a.location === l.code || isAllLocations ? 'checked' : ''} ${isAllLocations || !isEditMode ? 'disabled' : ''} onchange="handleLocationCheckboxChange('${a.id}', this)">
                                        <label for="${a.id}_loc_${l.code}">${l.name}</label>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
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
                                ${rolesData.map(r => `
                                    <div class="checkbox-option ${isAllRoles || !isEditMode ? 'disabled' : ''}">
                                        <input type="checkbox" id="${a.id}_role_${r.id}" class="role-checkbox" value="${r.id}" ${a.role_id == r.id || isAllRoles ? 'checked' : ''} ${isAllRoles || !isEditMode ? 'disabled' : ''} onchange="handleRoleCheckboxChange('${a.id}', this)">
                                        <label for="${a.id}_role_${r.id}">${r.name}</label>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

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
            checkboxes.forEach(cb => { cb.checked = true; cb.disabled = true; });
            checkboxOptions.forEach(opt => opt.classList.add('disabled'));
            if (assignment) assignment.location = 'ALL';
        } else {
            checkboxes.forEach(cb => { cb.disabled = false; cb.checked = false; });
            checkboxOptions.forEach(opt => opt.classList.remove('disabled'));
            if (assignment) assignment.location = '';
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
            checkboxes.forEach(cb => { cb.checked = true; cb.disabled = true; });
            checkboxOptions.forEach(opt => opt.classList.add('disabled'));
            if (assignment) assignment.role_id = 'ALL';
        } else {
            checkboxes.forEach(cb => { cb.disabled = false; cb.checked = false; });
            checkboxOptions.forEach(opt => opt.classList.remove('disabled'));
            if (assignment) assignment.role_id = '';
        }
    };

    window.handleLocationCheckboxChange = (assignId, checkbox) => {
        if (!isEditMode) return;
        const card = moduleCards[currentModalCardId];
        const assignment = card.assignments.find(a => a.id === assignId);
        if (assignment) assignment.location = checkbox.value;
    };

    window.handleRoleCheckboxChange = (assignId, checkbox) => {
        if (!isEditMode) return;
        const card = moduleCards[currentModalCardId];
        const assignment = card.assignments.find(a => a.id === assignId);
        if (assignment) assignment.role_id = checkbox.value;
    };

    window.addNewModule = () => {
        const moduleSelectorHTML = `
            <div class="module-selector-grid">
                ${modulesData.map(m => `
                    <div class="module-selector-card" onclick="selectModule(${m.id})">
                        <i class="fas fa-cube"></i>
                        <div class="module-name">${m.name}</div>
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
            width: '700px'
        });
    };

    window.selectModule = (moduleId) => {
        Swal.close();
        const module = modulesData.find(m => m.id == moduleId);
        const cardId = `card_${cardIdCounter++}`;
        const assignId = `assign_${cardIdCounter++}`;
        moduleCards[cardId] = {
            id: cardId, isNew: true, module_id: moduleId, module_name: module.name,
            assignments: [{id: assignId, location: '', role_id: ''}]
        };
        renderAllCards();
        openModal(cardId);
    };

    window.deleteAssignment = (assignId) => {
        if (!isEditMode) return;
        Swal.fire({
            title: 'Delete Assignment?',
            text: 'This action cannot be undone',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                const card = moduleCards[currentModalCardId];
                card.assignments = card.assignments.filter(a => a.id !== assignId);
                if (card.assignments.length === 0 && card.isNew) {
                    delete moduleCards[currentModalCardId];
                    closeModal();
                } else {
                    renderModalAssignments();
                }
                showAlert('success', 'Deleted', 'Assignment removed');
            }
        });
    };

    function updateHiddenInputs() {
        const container = document.getElementById('hiddenInputsContainer');
        container.innerHTML = '';
        let index = 0;
        Object.values(moduleCards).forEach(card => {
            if (card.module_id && card.assignments) {
                card.assignments.forEach(a => {
                    if (a.location && a.role_id) {
                        container.innerHTML += `
                            <input type="hidden" name="modules[${index}][module_id]" value="${card.module_id}">
                            <input type="hidden" name="modules[${index}][location]" value="${a.location}">
                            <input type="hidden" name="modules[${index}][role_id]" value="${a.role_id}">
                        `;
                        index++;
                    }
                });
            }
        });
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
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showAlert('success', 'Success', 'User updated successfully!');
                setTimeout(() => { window.location.href = "{{ route('azure.dashboard.index') }}"; }, 1500);
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

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && currentModalCardId) cancelModal();
    });

    // Initialize on page load
    initializeModules();
</script>
@endpush