@extends('azure-management.layouts.app')

@section('title', 'Create New User')

@section('page-title', 'Azure User Management System')

@section('header-actions')
    <a href="{{ route('azure.dashboard.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Back to Dashboard
    </a>
@endsection

@section('additional-styles')
<style>
    .main-content {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        align-items: start;
    }

    .user-form-panel {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .form-section {
        margin-bottom: 30px;
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
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        font-size: 16px;
        transition: all 0.3s ease;
        background: white;
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
    }

    .invalid-feedback {
        color: #e74c3c;
        font-size: 0.875em;
        margin-top: 5px;
        display: block;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #e1e8ed;
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
        background: #3498db;
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

    .info-note {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        color: #1565c0;
        font-size: 0.9em;
    }

    @media (max-width: 768px) {
        .main-content {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
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
    }
</style>
@endsection

@section('content')
    <div class="main-content">
        <!-- User Form Panel -->
        <div class="user-form-panel">
            <form id="userForm" method="POST" action="{{ route('azure.users.store') }}">
                @csrf
                
                <!-- User Information Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-user-plus"></i>
                        Create New Azure User
                    </div>

                    <div class="info-note">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Fields marked with * are required. This will create a new user in the Azure Users system.
                    </div>
                    
                    <div class="form-grid">
                        <!-- Name -->
                        <div class="form-group">
                            <label for="name">
                                <i class="fas fa-user"></i> Full Name *
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
                        
                        <!-- Email -->
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email Address *
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
                                <i class="fas fa-phone"></i> Phone Number
                            </label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone') }}" 
                                   placeholder="e.g., +254 712 345 678"
                                   class="@error('phone') is-invalid @enderror">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Job Title -->
                        <div class="form-group">
                            <label for="job_title">
                                <i class="fas fa-briefcase"></i> Job Title
                            </label>
                            <input type="text" 
                                   id="job_title" 
                                   name="job_title" 
                                   value="{{ old('job_title') }}" 
                                   placeholder="e.g., Software Engineer"
                                   class="@error('job_title') is-invalid @enderror">
                            @error('job_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Department -->
                        <div class="form-group">
                            <label for="department">
                                <i class="fas fa-building"></i> Department
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
                        
                        <!-- Location -->
                        <div class="form-group">
                            <label for="location">
                                <i class="fas fa-map-marker-alt"></i> Office Location
                            </label>
                            <input type="text" 
                                   id="location" 
                                   name="location" 
                                   value="{{ old('location') }}" 
                                   placeholder="e.g., Nairobi Office"
                                   class="@error('location') is-invalid @enderror">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Company -->
                        <div class="form-group">
                            <label for="company_id">
                                <i class="fas fa-building"></i> Company Name
                            </label>
                            <input type="text" 
                                   id="company_id" 
                                   name="company_id" 
                                   value="{{ old('company_id') }}" 
                                   placeholder="e.g., ACME Corporation"
                                   class="@error('company_id') is-invalid @enderror">
                            @error('company_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Mobile Phone (Additional) -->
                        <div class="form-group">
                            <label for="mobile_phone">
                                <i class="fas fa-mobile-alt"></i> Mobile Phone
                            </label>
                            <input type="tel" 
                                   id="mobile_phone" 
                                   name="mobile_phone" 
                                   value="{{ old('mobile_phone') }}" 
                                   placeholder="e.g., +254 700 000 000"
                                   class="@error('mobile_phone') is-invalid @enderror">
                            @error('mobile_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

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
@endsection

@section('additional-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('userForm');
        
        // Form validation on submit
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateForm()) {
                // Show loading if function exists
                if (typeof showLoading === 'function') {
                    showLoading();
                }
                
                // Disable submit button to prevent double submission
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
                
                // Submit the form
                this.submit();
            }
        });
    });

    function validateForm() {
        let isValid = true;
        const requiredFields = ['name', 'email'];
        
        // Clear previous validation states
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        
        // Validate required fields
        requiredFields.forEach(field => {
            const element = document.getElementById(field);
            if (!element.value.trim()) {
                element.classList.add('is-invalid');
                isValid = false;
                
                // Add error message if it doesn't exist
                if (!element.nextElementSibling || !element.nextElementSibling.classList.contains('invalid-feedback')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = `The ${field} field is required.`;
                    element.parentNode.appendChild(errorDiv);
                }
            }
        });
        
        // Validate email format
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
            // Scroll to first error
            const firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
            
            // Show alert if function exists
            if (typeof showAlert === 'function') {
                showAlert('error', 'Validation Error', 'Please fill in all required fields correctly.');
            } else {
                alert('Please fill in all required fields correctly.');
            }
        }
        
        return isValid;
    }

    function cancelForm() {
        if (typeof confirmAction === 'function') {
            confirmAction(
                'Cancel Form',
                'Are you sure you want to cancel? All unsaved changes will be lost.',
                function() {
                    window.location.href = '{{ route("azure.dashboard.index") }}';
                }
            );
        } else {
            if (confirm('Are you sure you want to cancel? All unsaved changes will be lost.')) {
                window.location.href = '{{ route("azure.dashboard.index") }}';
            }
        }
    }

    // Real-time validation for email
    document.getElementById('email').addEventListener('blur', function() {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (this.value.trim() && !emailPattern.test(this.value)) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Remove validation error on input
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
</script>
@endsection