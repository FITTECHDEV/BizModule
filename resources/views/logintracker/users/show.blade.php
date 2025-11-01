{{-- @extends('layouts.app') --}}
@extends('adminlte::page')

@section('title', 'Employee Details')

@section('content_header')
    <h1>Employee Details</h1>
@stop

@section('content')
<div class="container-fluid">

    {{-- User Details Card --}}
    <div class="card mb-4">
        <div class="card-body">

            <div class="row">
                <!-- First Column -->
                <div class="col-md-6">
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">User Principal Name</div>
                        <div class="col-md-7">{{ $user->userPrincipalName ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Display Name</div>
                        <div class="col-md-7">{{ $user->displayName ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Email</div>
                        <div class="col-md-7">{{ $user->mail ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Job Title</div>
                        <div class="col-md-7">{{ $user->jobTitle ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Department</div>
                        <div class="col-md-7">{{ $user->department ?? 'N/A' }}</div>
                    </div>
                </div>

                <!-- Second Column -->
                <div class="col-md-6">
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Office Location</div>
                        <div class="col-md-7">{{ $user->officeLocation ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Account Enabled</div>
                        <div class="col-md-7">
                            <span class="badge {{ $user->accountEnabled ? 'bg-success' : 'bg-danger' }}">
                                {{ $user->accountEnabled ? 'Yes' : 'No' }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Directory Synced</div>
                        <div class="col-md-7">{{ $user->directorySynced ? 'Yes' : 'No' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-5 fw-bold">Created At</div>
                        <div class="col-md-7">{{ $user->createdDateTime ? \Carbon\Carbon::parse($user->createdDateTime)->format('Y-m-d') : 'N/A' }}</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Dynamic Date Range Banner --}}
    @php
        $displayStart = isset($start) ? $start : \Carbon\Carbon::now()->startOfMonth();
        $displayEnd = isset($end) ? $end : \Carbon\Carbon::now();
    @endphp
    <div class="alert mb-4" style="background-color: #17a2b8; color: white; border: none; padding: 10px 15px;">
        <strong>Showing data from {{ $displayStart->format('M j, Y') }} to {{ $displayEnd->format('M j, Y') }}</strong>
    </div>

    {{-- Recent Applications Used --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Recent Applications Used</h5>
        </div>
        <div class="card-body">
            @if ($currentMonthApplications->isEmpty())
                <div class="alert alert-info">No recent application usage found for this period.</div>
            @else
                <div class="row">
                @foreach ($currentMonthApplications as $app)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title fs-5">{{ $app->application ?? 'Unknown App' }}</h5>
                                <p class="card-text fs-6">
                                    <span class="badge bg-info text-dark fs-6">{{ $app->usage_count }} logins</span>
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Sign In History Section --}}
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0 fw-semibold">Sign In History</h4>
            
            <form method="GET" 
                  action="{{ route('reporting.users.show', $user->userPrincipalName ?? $user->id) }}" 
                  id="filterForm"
                  class="d-flex gap-2 align-items-center">

                {{-- Date Range Pickers (From - To) --}}
                <div class="d-flex gap-1 align-items-center">
                    <label for="start_date" class="me-1 small">From:</label>
                    <input type="date" 
                           name="start_date" 
                           id="start_date" 
                           class="form-control form-control-sm"
                           style="width: 150px;"
                           value="{{ request('start_date') ?? $displayStart->format('Y-m-d') }}"
                           required>
                    <label for="end_date" class="mx-1 small">To:</label>
                    <input type="date" 
                           name="end_date" 
                           id="end_date" 
                           class="form-control form-control-sm"
                           style="width: 150px;"
                           value="{{ request('end_date') ?? $displayEnd->format('Y-m-d') }}"
                           required>
                </div>

                {{-- Buttons --}}
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                <a href="{{ route('reporting.users.show', $user->userPrincipalName ?? $user->id) }}" 
                   class="btn btn-outline-secondary btn-sm">Reset</a>
            </form>
        </div>

        {{-- Recent Applications for Selected Period (without last used timestamp) --}}
        @if (isset($recentApplications) && !$recentApplications->isEmpty())
            <div class="mb-4">
                <div class="row g-3">
                    @foreach ($recentApplications as $app)
                        <div class="col-md-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $app->application ?? 'Unknown App' }}</h6>
                                    <span class="badge bg-info text-dark">{{ $app->usage_count }} logins</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Login Summary Table - Show All 10 Applications --}}
        @if(isset($monthlySummary) && !empty($monthlySummary))
            @php
                // Extract headers from the first row (LoginDate + all application names)
                $firstRow = (array)$monthlySummary[0];
                $headers = array_keys($firstRow);
                
                // Ensure LoginDate is first
                if (($key = array_search('LoginDate', $headers)) !== false) {
                    unset($headers[$key]);
                    array_unshift($headers, 'LoginDate');
                }
            @endphp

            <div class="mb-4">
                <div class="alert mb-3" style="background-color: #17a2b8; color: white; border: none; padding: 10px 15px;">
                    <strong>Login Activity by System</strong>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover mb-0" style="background-color: white;">
                        <thead>
                            <tr style="background-color: #e9ecef;">
                                @foreach($headers as $header)
                                    <th class="text-center align-middle" 
                                        style="padding: 12px 8px; font-size: 13px; font-weight: 600; white-space: nowrap; min-width: {{ $header === 'LoginDate' ? '120px' : '90px' }}; {{ $header === 'LoginDate' ? 'background-color: #d1d1d1;' : '' }}">
                                        {{ $header === 'LoginDate' ? 'Login Date' : $header }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlySummary as $row)
                                <tr>
                                    @foreach($headers as $header)
                                        @php
                                            $cellValue = $row->$header ?? 0;
                                        @endphp
                                        @if($header === 'LoginDate')
                                            <td class="text-center align-middle" 
                                                style="padding: 10px 8px; font-size: 13px; font-weight: 500; background-color: #e9ecef;">
                                                {{ $cellValue ? \Carbon\Carbon::parse($cellValue)->format('d-M-Y') : '' }}
                                            </td>
                                        @else
                                            <td class="text-center align-middle" 
                                                style="padding: 10px 8px; font-size: 13px;">
                                                {{ $cellValue > 0 ? $cellValue : '0' }}
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>No login history available</strong><br>
                No sign-ins found for the selected date range.
            </div>
        @endif
    </div>

    {{-- Back to Dashboard --}}
    <div class="mt-4 mb-5">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

</div>
@stop

@section('css')
    <style>
        .card-hover {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2) !important;
        }
    </style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const filterForm = document.getElementById('filterForm');
    
    // Validate that end date is not before start date
    const validateDates = function() {
        if (startDateInput.value && endDateInput.value) {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            
            if (endDate < startDate) {
                alert('End date cannot be before start date');
                endDateInput.value = '';
                return false;
            }
        }
        return true;
    };
    
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', validateDates);
        endDateInput.addEventListener('change', validateDates);
    }
    
    // Enhanced scroll position restoration
    const SCROLL_STORAGE_KEY = 'userShowScrollPosition';
    const savedScrollPos = sessionStorage.getItem(SCROLL_STORAGE_KEY);
    
    if (savedScrollPos) {
        setTimeout(() => {
            window.scrollTo({
                top: parseInt(savedScrollPos),
                behavior: 'smooth'
            });
        }, 100);
        sessionStorage.removeItem(SCROLL_STORAGE_KEY);
    }
    
    // Store scroll position before form submission
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            if (!validateDates()) {
                e.preventDefault();
                return false;
            }
            
            sessionStorage.setItem(SCROLL_STORAGE_KEY, window.pageYOffset.toString());
            
            // Loading states for better UX
            const submitBtn = filterForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...';
                submitBtn.disabled = true;
                
                // Re-enable after 5 seconds as fallback
                setTimeout(function() {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
            }
        });
    }
    
    // Reset loading state if form submission fails or page shows
    window.addEventListener('pageshow', function(event) {
        const submitBtn = filterForm ? filterForm.querySelector('button[type="submit"]') : null;
        if (submitBtn) {
            submitBtn.innerHTML = 'Apply';
            submitBtn.disabled = false;
        }
    });
});
</script>
@stop