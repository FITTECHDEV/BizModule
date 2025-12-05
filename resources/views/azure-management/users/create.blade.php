@extends('azure-management.layouts.app')

@section('title', 'Create New User')

@section('page-title', 'Create New User')

@push('styles')
<style>
    .main-content {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    /* Back Button Styles */
    .back-to-dashboard {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: #6c757d;
        color: white;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .back-to-dashboard:hover {
        background: #5a6268;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
        color: white;
        text-decoration: none;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .user-form-panel {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
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

    .section-title {
        font-size: 1.4em;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .form-group input,
    .form-group select {
        padding: 12px 15px;
        border: 2px solid #e1e8ed;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s ease;
        background: white;
        width: 100%;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .form-group input.is-invalid,
    .form-group select.is-invalid {
        border-color: #e74c3c;
        box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
    }

    .invalid-feedback {
        color: #e74c3c;
        font-size: 0.875em;
        margin-top: 5px;
        display: block;
    }

    .info-note {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        color: #1565c0;
        font-size: 0.9em;
        display: flex;
        align-items: center;
        gap: 10px;
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

    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 30px;
        padding-top: 20px;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: var(--primary-color);
        color: white;
    }

    .btn-primary:hover {
        background: #2980b9;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
    }

    .btn-success {
        background: var(--success);
        color: white;
    }

    .btn-success:hover {
        background: #229954;
    }

    .btn-danger {
        background: var(--danger);
        color: white;
    }

    .btn-danger:hover {
        background: #c0392b;
    }

    .btn-sm {
        padding: 6px 10px;
        font-size: 0.875rem;
    }

    .btn-ghost {
        background: transparent;
        color: var(--text);
        border: 2px solid var(--border-color);
    }

    .btn-ghost:hover {
        background: var(--hover-bg);
        border-color: var(--primary-color);
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .assignment-info {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="main-content">
    <div class="user-form-panel">
        <form id="userForm" method="POST" action="{{ route('azure.users.store') }}">
            @csrf
            
            <!-- Basic Information Section -->
            <div class="form-section">
                <div class="page-header">
                    <div class="section-title" style="margin-bottom: 0;">
                        <i class="fas fa-user-plus"></i>
                        Create User Data
                    </div>
                    <a href="{{ route('azure.dashboard.index') }}" class="back-to-dashboard">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </div>

                <div class="info-note">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> Fields marked with * are required.
                </div>
                
                <div class="form-grid">
                    <!-- Name -->
                    <div class="form-group">
                        <label for="name">
                            Full Name <span style="color: var(--danger);">*</span>
                        </label>
                        <input type="text" 
                                id="name" 
                                name="name" 
                                value="{{ old('name') }}" 
                                required 
                                placeholder="e.g., John Doe"
                                class="@error('name') is-invalid @enderror">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group" style="display: none;">
                        <label for="company_id">
                            Company
                        </label>
                        <select id="company_id" 
                                name="company_id"
                                class="@error('company_id') is-invalid @enderror">
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('company_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Employee ID -->
                    <div class="form-group">
                        <label for="employee_id">
                            Employee ID <span style="color: var(--danger);">*</span>
                        </label>
                        <input type="text" 
                                id="employee_id" 
                                name="employee_id" 
                                value="{{ old('employee_id') }}" 
                                required 
                                placeholder="e.g., EMP001"
                                class="@error('employee_id') is-invalid @enderror">
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">
                            Email Address <span style="color: var(--danger);">*</span>
                        </label>
                        <input type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required
                                placeholder="e.g., john.doe@company.com"
                                class="@error('email') is-invalid @enderror">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div class="form-group">
                        <label for="phone">
                            Phone Number <span style="color: var(--danger);">*</span>
                        </label>
                        <input type="tel" 
                                id="phone" 
                                name="phone" 
                                value="{{ old('phone') }}" 
                                required
                                placeholder="e.g., +254 712 345 678"
                                class="@error('phone') is-invalid @enderror">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Department -->
                    <div class="form-group">
                        <label for="department">
                            Department
                        </label>
                        <input type="text" 
                                id="department" 
                                name="department" 
                                value="{{ old('department') }}" 
                                placeholder="e.g., IT Department"
                                class="@error('department') is-invalid @enderror">
                        @error('department')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Module Assignments Section -->
            <div class="form-section">
                <div class="section-title" style="display: flex; justify-content: space-between; align-items: center;">
                    <div><i class="fas fa-th-large"></i> Module Assignments</div>
                    <button type="button" class="btn btn-sm btn-success" onclick="addNewModule()">
                        <i class="fas fa-plus"></i> Add Module
                    </button>
                </div>
                <div id="modulesGrid" class="modules-grid"></div>
            </div>

            <div id="hiddenInputsContainer"></div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button type="button" class="btn btn-secondary" onclick="cancelForm()">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i>
                    Create User
                </button>
            </div>
        </form>
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
            <div id="modalAssignmentsContainer"></div>
        </div>
        <div class="modal-footer">
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
    const csrfToken = "{{ csrf_token() }}";

    // Global state
    let moduleCards = {};
    let cardIdCounter = 0;
    let currentModalCardId = null;
    let originalCardState = null;
    let scmFunctionsCache = null;
    let rolesCache = {};
    let locationsCache = {}; // NEW: Cache for location details

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

    // Fetch SCM Functions (returns array of {ID, Code, Name})
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
                // Transform to consistent structure
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

    // Fetch Roles by Application Code (returns array of {RoleId, RoleCode, Role})
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
                
                // Transform to consistent structure
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

    // Render all module cards
    function renderAllCards() {
        const grid = document.getElementById('modulesGrid');
        grid.innerHTML = '';
        
        Object.values(moduleCards).forEach(card => {
            grid.appendChild(createModuleCard(card));
        });
        
        if (Object.keys(moduleCards).length === 0) {
            grid.innerHTML = '<div class="empty-grid"><i class="fas fa-inbox"></i><p>No modules assigned yet. Click "Add Module" to get started.</p></div>';
        }
        
        updateHiddenInputs();
    }

    // Create a module card element
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

    // Open modal for editing module assignments
    window.openModal = async (cardId) => {
        currentModalCardId = cardId;
        const card = moduleCards[cardId];
        originalCardState = deepClone(card);
        
        document.getElementById('modalModuleName').textContent = card.module_name;
        
        // Pre-fetch data if needed
        const module = modulesData.find(m => m.id == card.module_id);
        if (module) {
            const applicationCode = getApplicationCodeForSP(module);
            const isSCM = applicationCode === 'OPS' || applicationCode === 'OPS UAT';
            
            // Fetch roles and functions concurrently
            const promises = [fetchRolesByApplicationCode(applicationCode)];
            if (isSCM) {
                promises.push(fetchSCMFunctions());
            }
            
            await Promise.all(promises);
        }
        
        renderModalAssignments();
        document.getElementById('moduleModal').classList.add('active');
    };

    // Close modal (restore original state)
    window.closeModal = () => {
        if (originalCardState && currentModalCardId) {
            moduleCards[currentModalCardId] = deepClone(originalCardState);
        }
        document.getElementById('moduleModal').classList.remove('active');
        currentModalCardId = null;
        originalCardState = null;
        renderAllCards();
    };

    // Cancel modal (delete if new, restore if existing)
    window.cancelModal = () => {
        if (originalCardState && originalCardState.isNew && currentModalCardId) {
            delete moduleCards[currentModalCardId];
        } else if (originalCardState && currentModalCardId) {
            moduleCards[currentModalCardId] = deepClone(originalCardState);
        }
        document.getElementById('moduleModal').classList.remove('active');
        currentModalCardId = null;
        originalCardState = null;
        renderAllCards();
    };

    // Close modal when clicking overlay
    window.closeModalOnOverlay = (event) => {
        if (event.target.id === 'moduleModal') {
            cancelModal();
        }
    };

    // Save modal changes
    window.saveModalChanges = () => {
        originalCardState = null;
        if (currentModalCardId && moduleCards[currentModalCardId]) {
            moduleCards[currentModalCardId].isNew = false;
        }
        document.getElementById('moduleModal').classList.remove('active');
        currentModalCardId = null;
        renderAllCards();
        showAlert('success', 'Saved', 'Module assignment saved. Click "Create User" to save everything.');
    };

    // Render modal assignments
    function renderModalAssignments() {
        const card = moduleCards[currentModalCardId];
        const container = document.getElementById('modalAssignmentsContainer');
        
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

            return `
                <div class="assignment-item" id="${a.id}">
                    <div class="assignment-header">
                        <span style="font-weight: 600; color: var(--muted); font-size: 0.9em;">
                            Assignment for ${escapeHtml(module.name)} (${escapeHtml(applicationCode)})
                        </span>
                        <button type="button" class="btn btn-sm btn-ghost" style="color: var(--danger);" onclick="deleteAssignment('${a.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="assignment-info" style="grid-template-columns: ${isSCM ? '1fr 1fr 1fr' : '1fr 1fr'};">
                        <!-- OFFICE LOCATION -->
                        <div class="info-field">
                            <div class="field-label"><i class="fas fa-map-marker-alt"></i> Office Location</div>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" id="${a.id}_all_locations" name="${a.id}_location_type" class="all-locations-radio" ${isAllLocations ? 'checked' : ''} onchange="handleLocationRadioChange('${a.id}')">
                                    <label for="${a.id}_all_locations">All Locations</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" id="${a.id}_specific_locations" name="${a.id}_location_type" class="specific-locations-radio" ${!isAllLocations ? 'checked' : ''} onchange="handleLocationRadioChange('${a.id}')">
                                    <label for="${a.id}_specific_locations">Specific Location</label>
                                </div>
                            </div>
                            <div class="checkboxes-grid">
                                ${locationsData.map(l => {
                                    const locCode = l.code.trim();
                                    const isChecked = a.locationCodes && a.locationCodes.includes(locCode);
                                    return `
                                        <div class="checkbox-option ${isAllLocations ? 'disabled' : ''}">
                                            <input type="checkbox" 
                                                   id="${a.id}_loc_${locCode}" 
                                                   class="location-checkbox" 
                                                   data-location-id="${l.id}"
                                                   data-location-code="${locCode}"
                                                   data-location-name="${escapeHtml(l.name)}"
                                                   value="${locCode}" 
                                                   ${isChecked ? 'checked' : ''} 
                                                   ${isAllLocations ? 'disabled' : ''} 
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
                                <div class="radio-option">
                                    <input type="radio" id="${a.id}_all_functions" name="${a.id}_function_type" class="all-functions-radio" ${isAllFunctions ? 'checked' : ''} onchange="handleFunctionRadioChange('${a.id}')">
                                    <label for="${a.id}_all_functions">All Functions</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" id="${a.id}_specific_functions" name="${a.id}_function_type" class="specific-functions-radio" ${!isAllFunctions ? 'checked' : ''} onchange="handleFunctionRadioChange('${a.id}')">
                                    <label for="${a.id}_specific_functions">Specific Function</label>
                                </div>
                            </div>
                            <div class="checkboxes-grid">
                                ${availableFunctions.length > 0 ? availableFunctions.map(f => {
                                    const isChecked = a.functionIds && a.functionIds.includes(String(f.id));
                                    return `
                                        <div class="checkbox-option ${isAllFunctions ? 'disabled' : ''}">
                                            <input type="checkbox" 
                                                   id="${a.id}_func_${f.id}" 
                                                   class="function-checkbox" 
                                                   data-function-id="${f.id}"
                                                   data-function-code="${escapeHtml(f.code)}"
                                                   value="${f.id}" 
                                                   ${isChecked ? 'checked' : ''} 
                                                   ${isAllFunctions ? 'disabled' : ''} 
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
                                <div class="radio-option">
                                    <input type="radio" id="${a.id}_all_roles" name="${a.id}_role_type" class="all-roles-radio" ${isAllRoles ? 'checked' : ''} onchange="handleRoleRadioChange('${a.id}')">
                                    <label for="${a.id}_all_roles">All Roles</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" id="${a.id}_specific_roles" name="${a.id}_role_type" class="specific-roles-radio" ${!isAllRoles ? 'checked' : ''} onchange="handleRoleRadioChange('${a.id}')">
                                    <label for="${a.id}_specific_roles">Specific Role</label>
                                </div>
                            </div>
                            <div class="checkboxes-grid">
                                ${availableRoles.length > 0 ? availableRoles.map(r => {
                                    const isChecked = a.roleIds && a.roleIds.includes(String(r.id));
                                    return `
                                        <div class="checkbox-option ${isAllRoles ? 'disabled' : ''}">
                                            <input type="checkbox" 
                                                   id="${a.id}_role_${r.id}" 
                                                   class="role-checkbox" 
                                                   data-role-id="${r.id}"
                                                   data-role-code="${escapeHtml(r.code)}"
                                                   value="${r.id}" 
                                                   ${isChecked ? 'checked' : ''} 
                                                   ${isAllRoles ? 'disabled' : ''} 
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
        const container = document.getElementById(assignId);
        if (!container) return;
        
        const allRadio = container.querySelector('.all-locations-radio');
        const checkboxes = container.querySelectorAll('.location-checkbox');
        const checkboxOptions = container.querySelectorAll('.info-field:first-child .checkbox-option');
        const card = moduleCards[currentModalCardId];
        const assignment = card.assignments.find(a => a.id === assignId);
        
        if (allRadio && allRadio.checked) {
            // All locations selected
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
            // Specific locations
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

    // Handle location checkbox change
    window.handleLocationCheckboxChange = (assignId) => {
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

    // Handle function radio change (All vs Specific)
    window.handleFunctionRadioChange = (assignId) => {
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

    // Handle function checkbox change
    window.handleFunctionCheckboxChange = (assignId) => {
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

    // Handle role radio change (All vs Specific)
    window.handleRoleRadioChange = (assignId) => {
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

    // Handle role checkbox change
    window.handleRoleCheckboxChange = (assignId) => {
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

    // Add new module
    function addNewModule() {
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
                        selectModuleDirectly(moduleId);
                    });
                });
            }
        });
    }
    window.addNewModule = addNewModule;

    // Select module and create card
    function selectModuleDirectly(moduleId) {
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
    }
    window.selectModuleDirectly = selectModuleDirectly;

    // Delete assignment
    window.deleteAssignment = (assignId) => {
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

    // Update hidden inputs for form submission
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

    // Form validation
    function validateForm() {
        let isValid = true;
        const requiredFields = ['name', 'employee_id', 'email', 'phone'];
        
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        
        requiredFields.forEach(field => {
            const element = document.getElementById(field);
            if (!element.value.trim()) {
                element.classList.add('is-invalid');
                isValid = false;
                
                if (!element.nextElementSibling || !element.nextElementSibling.classList.contains('invalid-feedback')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = `The ${field.replace('_', ' ')} field is required.`;
                    element.parentNode.appendChild(errorDiv);
                }
            }
        });
        
        const emailField = document.getElementById('email');
        if (emailField.value.trim()) {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(emailField.value)) {
                emailField.classList.add('is-invalid');
                isValid = false;
                
                if (!emailField.nextElementSibling || !emailField.nextElementSibling.classList.contains('invalid-feedback')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'Please enter a valid email address.';
                    emailField.parentNode.appendChild(errorDiv);
                }
            }
        }
        
        if (!isValid) {
            const firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
            
            showAlert('error', 'Validation Error', 'Please fill in all required fields correctly.');
        }
        
        return isValid;
    }

    // Cancel form
    function cancelForm() {
        confirmAction(
            'Cancel Form',
            'Are you sure you want to cancel? All unsaved changes will be lost.',
            function() {
                window.location.href = '{{ route("azure.dashboard.index") }}';
            }
        );
    }

    // Form submission
    document.getElementById('userForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }

        updateHiddenInputs();
        
        // Debug: show what we're submitting
        console.log('=== FORM SUBMISSION ===');
        console.log('Module Cards:', moduleCards);
        console.log('Hidden Inputs Count:', document.querySelectorAll('#hiddenInputsContainer input').length);
        
        showLoading();
        
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
        
        this.submit();
    });

    // Email validation on blur
    document.getElementById('email').addEventListener('blur', function() {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (this.value.trim() && !emailPattern.test(this.value)) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Remove validation errors on input
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                this.classList.remove('is-invalid');
                const errorMsg = this.nextElementSibling;
                if (errorMsg && errorMsg.classList.contains('invalid-feedback')) {
                    errorMsg.remove();
                }
            }
        });
    });

    // ESC key to close modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && currentModalCardId) {
            cancelModal();
        }
    });

    // Helper functions
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

    function confirmAction(title, message, callback) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3498db',
                cancelButtonColor: '#e74c3c',
                confirmButtonText: 'Yes, proceed',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed && callback) {
                    callback();
                }
            });
        } else {
            if (confirm(message) && callback) {
                callback();
            }
        }
    }

    function showLoading() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while we create the user',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        renderAllCards();
    });
</script>
@endpush
