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

            <h5 class="card-title mb-4">{{ $user->displayName }}</h5>

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

    {{-- Dynamic Current Month Banner (Always shows current month) --}}
    <div class="alert mb-4" style="background-color: #17a2b8; color: white; border: none; padding: 10px 15px;">
        <strong>Showing data for {{ \Carbon\Carbon::now()->format('M j, Y') }}</strong>
    </div>

    {{-- Recent Applications Used (ALWAYS CURRENT MONTH DATA) --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Recent Applications Used</h5>
        </div>
        <div class="card-body">
            @if ($currentMonthApplications->isEmpty())
                <div class="alert alert-info">No recent application usage found for this month.</div>
            @else
                <div class="row">
                @foreach ($currentMonthApplications as $app)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title fs-5">{{ $app->application ?? 'Unknown App' }}</h5>
                                <p class="card-text fs-6">
                                    @if($app->system && $app->system != $app->application)
                                        <span class="text-muted">System: {{ $app->system }}</span><br>
                                    @endif
                                    <span class="badge bg-info text-dark fs-6">{{ $app->usage_count }} logins</span><br>
                                    <span class="text-muted">Last used: {{ \Carbon\Carbon::parse($app->last_used)->format('M j, Y g:i A') }}</span>
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

                {{-- Date Range Dropdown --}}
                <select name="range" id="range" class="form-select form-select-sm" style="width: auto;">
                    @php
                        $selectedRange = request('range', 'this_month');
                    @endphp
                    <option value="this_month" {{ $selectedRange == 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $selectedRange == 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="last_3_months" {{ $selectedRange == 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
                    <option value="custom" {{ $selectedRange == 'custom' ? 'selected' : '' }}>Custom Date</option>
                </select>

                {{-- Date Range Pickers (From - To) --}}
                <div id="dateRangeContainer" style="{{ $selectedRange == 'custom' ? '' : 'display: none;' }}" class="d-flex gap-1 align-items-center">
                    <input type="date" 
                           name="start_date" 
                           id="start_date" 
                           class="form-control form-control-sm"
                           style="width: 150px;"
                           value="{{ request('start_date') ?? ($selectedRange == 'custom' && isset($start) ? $start->format('Y-m-d') : '') }}">
                    <span class="text-muted small">to</span>
                    <input type="date" 
                           name="end_date" 
                           id="end_date" 
                           class="form-control form-control-sm"
                           style="width: 150px;"
                           value="{{ request('end_date') ?? ($selectedRange == 'custom' && isset($end) ? $end->format('Y-m-d') : '') }}">
                </div>

                {{-- Buttons --}}
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                <a href="{{ route('reporting.users.show', $user->userPrincipalName ?? $user->id) }}" 
                   class="btn btn-outline-secondary btn-sm">Reset</a>
            </form>
        </div>

        {{-- Date Range Display Banner (Shows selected range) --}}
        @if(isset($start) && isset($end))
            <div class="alert mb-3" style="background-color: #17a2b8; color: white; border: none; padding: 10px 15px;">
                <strong>
                    @if($selectedRange === 'custom')
                        Custom Date - {{ $start->format('d-m-Y') }} to {{ $end->format('d-m-Y') }}
                    @else
                        {{ $rangeLabel ?? 'Selected Period' }} - {{ $start->format('d-m-Y') }} to {{ $end->format('d-m-Y') }}
                    @endif
                </strong>
            </div>
        @endif

        {{-- Recent Applications for Selected Period (Updates with date change) --}}
        @if (isset($recentApplications) && !$recentApplications->isEmpty())
            <div class="mb-4">
                <h6 class="mb-3" style="color: #6c757d;"></h6>
                <div class="row g-3">
                    @foreach ($recentApplications as $app)
                        <div class="col-md-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $app->application ?? 'Unknown App' }}</h6>
                                    @if($app->system && $app->system != $app->application)
                                        <p class="text-muted small mb-1">System: {{ $app->system }}</p>
                                    @endif
                                    <span class="badge bg-info text-dark">{{ $app->usage_count }} logins</span>
                                    <p class="text-muted small mb-0 mt-2">Last used: {{ \Carbon\Carbon::parse($app->last_used)->format('M j, Y g:i A') }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Login Summary Table - ALWAYS HORIZONTAL LAYOUT --}}
        @if(isset($monthlySummary) && !empty($monthlySummary))
            @php
                // Check if data is already pivoted (has LoginDate column)
                $firstRow = (array)$monthlySummary[0];
                $hasPivotedData = isset($firstRow['LoginDate']);
                $headers = [];
                $transformedData = [];
                
                if (!$hasPivotedData) {
                    // Transform vertical data (ApplicationName/NoofLogins) to horizontal
                    $headers = ['LoginDate'];
                    $dataRow = new stdClass();
                    // Use the start date from the selected range
                    $dataRow->LoginDate = isset($start) ? $start->format('Y-m-d') : \Carbon\Carbon::now()->format('Y-m-d');
                    
                    foreach($monthlySummary as $row) {
                        $appName = $row->ApplicationName ?? 'Unknown';
                        $loginCount = $row->NoofLogins ?? 0;
                        $headers[] = $appName;
                        $dataRow->$appName = $loginCount;
                    }
                    
                    // Create a single row summary
                    $transformedData = [$dataRow];
                } else {
                    // Data is already pivoted, use as-is
                    $headers = array_keys($firstRow);
                    $transformedData = $monthlySummary;
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
                            @foreach($transformedData as $row)
                                <tr>
                                    @foreach($headers as $header)
                                        @php
                                            $cellValue = $row->$header ?? '';
                                        @endphp
                                        @if($header === 'LoginDate')
                                            <td class="text-center align-middle" 
                                                style="padding: 10px 8px; font-size: 13px; font-weight: 500; background-color: #e9ecef;">
                                                {{ $cellValue ? \Carbon\Carbon::parse($cellValue)->format('d-M-Y') : '' }}
                                            </td>
                                        @else
                                            <td class="text-center align-middle" 
                                                style="padding: 10px 8px; font-size: 13px;">
                                                {{ $cellValue && $cellValue > 0 ? $cellValue : '' }}
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Summary Stats --}}
                @php
                    $totalLogins = 0;
                    $systemTotals = [];
                    
                    foreach($transformedData as $row) {
                        foreach($headers as $header) {
                            if ($header !== 'LoginDate') {
                                $value = $row->$header ?? 0;
                                if (!isset($systemTotals[$header])) {
                                    $systemTotals[$header] = 0;
                                }
                                $systemTotals[$header] += $value;
                                $totalLogins += $value;
                            }
                        }
                    }
                    
                    // Get top 3 systems
                    arsort($systemTotals);
                    $topSystems = array_slice($systemTotals, 0, 3, true);
                    $activeDays = $hasPivotedData ? count($transformedData) : 1;
                    $systemCount = count($systemTotals);
                @endphp

                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-2 small">Total Logins</h6>
                                <h3 class="mb-0 text-primary fw-bold">{{ $totalLogins }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-2 small">{{ $hasPivotedData ? 'Active Days' : 'Systems Used' }}</h6>
                                <h3 class="mb-0 text-success fw-bold">{{ $hasPivotedData ? $activeDays : $systemCount }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-3 small">Most Used Systems</h6>
                                @if(count($topSystems) > 0)
                                    @foreach($topSystems as $system => $count)
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="small fw-medium">{{ $system }}</span>
                                            <span class="badge bg-primary rounded-pill">{{ $count }} {{ $count == 1 ? 'login' : 'logins' }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted small mb-0">No data available</p>
                                @endif
                            </div>
                        </div>
                    </div>
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
{{-- Enhanced JavaScript with date range support --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rangeSelect = document.getElementById('range');
    const dateRangeContainer = document.getElementById('dateRangeContainer');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const filterForm = document.getElementById('filterForm');
    
    // Handle range selection change
    if (rangeSelect) {
        rangeSelect.addEventListener('change', function() {
            const selectedRange = this.value;
            
            if (selectedRange === 'custom') {
                // Show date range pickers
                dateRangeContainer.style.display = 'flex';
                startDateInput.setAttribute('required', 'required');
                endDateInput.setAttribute('required', 'required');
            } else {
                // Hide date pickers and clear values
                dateRangeContainer.style.display = 'none';
                startDateInput.removeAttribute('required');
                endDateInput.removeAttribute('required');
                startDateInput.value = '';
                endDateInput.value = '';
                // Auto-submit for predefined ranges
                filterForm.submit();
            }
        });
    }
    
    // Handle date input changes for custom range
    if (startDateInput && endDateInput) {
        const validateAndSubmit = function() {
            if (rangeSelect.value === 'custom' && startDateInput.value && endDateInput.value) {
                // Validate that end date is not before start date
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);
                
                if (endDate < startDate) {
                    alert('End date cannot be before start date');
                    endDateInput.value = '';
                    return;
                }
                
                // Auto-submit when both dates are selected and valid
                filterForm.submit();
            }
        };
        
        startDateInput.addEventListener('change', validateAndSubmit);
        endDateInput.addEventListener('change', validateAndSubmit);
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
        filterForm.addEventListener('submit', function() {
            sessionStorage.setItem(SCROLL_STORAGE_KEY, window.pageYOffset.toString());
        });
        
        // Loading states for better UX
        filterForm.addEventListener('submit', function(e) {
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