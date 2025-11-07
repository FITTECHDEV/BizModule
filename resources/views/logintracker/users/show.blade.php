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

    {{-- Static Date Range Banner (Always shows current month) --}}
    @php
        // For filtering sign-in history (dynamic)
        $displayStart = isset($start) ? $start : \Carbon\Carbon::now()->startOfMonth();
        $displayEnd = isset($end) ? $end : \Carbon\Carbon::now();
        
        // For "Applications Used This Month" and top banner - always current month
        $currentMonthStart = \Carbon\Carbon::now()->startOfMonth();
        $currentMonthEnd = \Carbon\Carbon::now()->endOfMonth();
        
        // Color classes for cycling through cards
        $colorClasses = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary'];
    @endphp
    <div class="alert mb-4" style="background-color: #513D6C; color: white; border: none; padding: 12px 20px;">
        <strong style="font-size: 1.1rem;">Showing data from {{ $currentMonthStart->format('M j, Y') }} to {{ $currentMonthEnd->format('M j, Y') }}</strong>
    </div>

    {{-- Recent Applications Used (Current Month - Static) --}}
    <div class="card mb-4">
        <div class="card-header" style="background-color: #f8f9fa;">
            <h5 class="mb-0" style="font-size: 1.1rem;">Applications Used This Month</h5>
            <small class="text-muted" style="font-size: 0.95rem;">{{ $currentMonthStart->format('M j, Y') }} to {{ $currentMonthEnd->format('M j, Y') }} (does not change with date filter)</small>
        </div>
        <div class="card-body">
            @if ($currentMonthApplications->isEmpty())
                <div class="alert mb-0" style="background-color: #513D6C; color: white; border: none; font-size: 1rem; padding: 12px 16px;">
                    No application usage found for the current month.
                </div>
            @else
                <div class="position-relative">
                    <button class="carousel-control-prev" type="button" onclick="scrollCarousel('currentMonthCarousel', -1)" style="position: absolute; left: -15px; top: 50%; transform: translateY(-50%); z-index: 10; background-color: rgba(81, 61, 108, 0.8); border: none; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                        <span style="color: white; font-size: 20px; font-weight: bold;">‹</span>
                    </button>
                    <div id="currentMonthCarousel" class="carousel-container" style="overflow-x: auto; scroll-behavior: smooth; -ms-overflow-style: none; scrollbar-width: none;">
                        <div class="row g-3 flex-nowrap" style="min-width: max-content;">
                        @foreach ($currentMonthApplications as $index => $app)
                            @php
                                $colorClass = $colorClasses[$index % count($colorClasses)];
                                $textColor = ($colorClass === 'bg-warning') ? 'text-dark' : 'text-white';
                            @endphp
                            <div class="col-auto" style="min-width: 290px; max-width: 140px;">
                                <div class="card h-100 shadow-sm border-0 {{ $colorClass }} card-hover">
                                    <div class="card-body text-center py-2 px-2">
                                        <h6 class="card-title mb-2 fw-bold {{ $textColor }}" style="font-size: 1.29rem; line-height: 1.2;">
                                            {{ $app->application ?? 'Unknown App' }}
                                        </h6>
                                        <p class="card-text mb-0">
                                            <span class="badge bg-white text-dark" style="font-size: 0.99rem; padding: 0.25rem 0.5rem;">
                                                {{ $app->usage_count }} {{ $app->usage_count == 1 ? 'login' : 'logins' }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        </div>
                    </div>
                    <button class="carousel-control-next" type="button" onclick="scrollCarousel('currentMonthCarousel', 1)" style="position: absolute; right: -15px; top: 50%; transform: translateY(-50%); z-index: 10; background-color: rgba(81, 61, 108, 0.8); border: none; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                        <span style="color: white; font-size: 20px; font-weight: bold;">›</span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Sign In History Section --}}
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <h4 class="mb-2 mb-md-0 fw-semibold">Sign In History</h4>
            
            <form method="GET" 
            action="{{ route('reporting.users.show', $user->userPrincipalName ?? $user->id) }}" 
            id="filterForm"
            class="d-flex align-items-center flex-wrap"
            style="gap: 1.5rem;">

    {{-- Date Range Dropdown --}}
    <div class="d-flex align-items-center" style="gap: 0.75rem;">
        <label for="date_preset" class="mb-0" style="font-size: 0.95rem; white-space: nowrap;">Date Range:</label>
        <select name="date_preset" id="date_preset" class="form-select" style="width: 180px; font-size: 0.95rem;">
            <option value="this-month" {{ (request('date_preset', 'this-month') == 'this-month') ? 'selected' : '' }}>This Month</option>
            <option value="last-month" {{ (request('date_preset') == 'last-month') ? 'selected' : '' }}>Last Month</option>
            <option value="last-3-months" {{ (request('date_preset') == 'last-3-months') ? 'selected' : '' }}>Last 3 Months</option>
            <option value="custom" {{ (request('date_preset') == 'custom') ? 'selected' : '' }}>Custom Range</option>
        </select>
    </div>

    {{-- Custom Date Range Pickers (Hidden by default) --}}
    <div id="customDateInputs" class="align-items-center flex-wrap" style="display: none; gap: 0.75rem;">
        <label for="start_date" class="mb-0" style="font-size: 0.95rem; white-space: nowrap;">From:</label>
        <input type="date" 
               name="start_date" 
               id="start_date" 
               class="form-control"
               style="width: 160px; font-size: 0.95rem;"
               value="{{ request('start_date') ?? $displayStart->format('Y-m-d') }}">
        <label for="end_date" class="mb-0" style="font-size: 0.95rem; white-space: nowrap;">To:</label>
        <input type="date" 
               name="end_date" 
               id="end_date" 
               class="form-control"
               style="width: 160px; font-size: 0.95rem;"
               value="{{ request('end_date') ?? $displayEnd->format('Y-m-d') }}">
    </div>

    {{-- Buttons --}}
    <div class="d-flex" style="gap: 0.75rem;">
        <button type="submit" class="btn" style="background-color: #513D6C; color: white; padding: 0.6rem 2rem; min-width: 100px; font-size: 1rem;">
            Apply
        </button>
        <a href="{{ route('reporting.users.show', $user->userPrincipalName ?? $user->id) }}" 
           class="btn btn-outline-secondary" style="padding: 0.6rem 2rem; min-width: 100px; font-size: 1rem;">
            Reset
        </a>
    </div>
</form>
        </div>

        {{-- Recent Applications for Selected Period (Dynamic - Updates with ALL date filters) --}}
        @if (isset($recentApplications) && !$recentApplications->isEmpty())
            <div class="card mb-4" style="border-left: 4px solid #513D6C;">
                <div class="card-header" style="background-color: #513D6C; color: white;">
                    <h5 class="mb-0" style="font-size: 1.1rem;">Applications Used in Selected Period</h5>
                    <small style="color: rgba(255, 255, 255, 0.9); font-size: 0.95rem;">{{ $displayStart->format('M j, Y') }} to {{ $displayEnd->format('M j, Y') }}</small>
                </div>
                <div class="card-body">
                    <div class="position-relative">
                        <button class="carousel-control-prev" type="button" onclick="scrollCarousel('selectedPeriodCarousel', -1)" style="position: absolute; left: -15px; top: 50%; transform: translateY(-50%); z-index: 10; background-color: rgba(81, 61, 108, 0.8); border: none; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                            <span style="color: white; font-size: 20px; font-weight: bold;">‹</span>
                        </button>
                        <div id="selectedPeriodCarousel" class="carousel-container" style="overflow-x: auto; scroll-behavior: smooth; -ms-overflow-style: none; scrollbar-width: none;">
                            <div class="row g-3 flex-nowrap" style="min-width: max-content;">
                                @foreach ($recentApplications as $index => $app)
                                    @php
                                        $colorClass = $colorClasses[$index % count($colorClasses)];
                                        $textColor = ($colorClass === 'bg-warning') ? 'text-dark' : 'text-white';
                                    @endphp
                                    <div class="col-auto" style="min-width: 290px; max-width: 140px;">
                                        <div class="card h-100 shadow-sm border-0 {{ $colorClass }} card-hover">
                                            <div class="card-body text-center py-2 px-2">
                                                <h6 class="card-title mb-2 fw-bold {{ $textColor }}" style="font-size: 1.29rem; line-height: 1.2;">
                                                    {{ $app->application ?? 'Unknown App' }}
                                                </h6>
                                                <p class="card-text mb-0">
                                                    <span class="badge bg-white text-dark" style="font-size: 0.99rem; padding: 0.25rem 0.5rem;">
                                                        {{ $app->usage_count }} {{ $app->usage_count == 1 ? 'login' : 'logins' }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <button class="carousel-control-next" type="button" onclick="scrollCarousel('selectedPeriodCarousel', 1)" style="position: absolute; right: -15px; top: 50%; transform: translateY(-50%); z-index: 10; background-color: rgba(81, 61, 108, 0.8); border: none; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                            <span style="color: white; font-size: 20px; font-weight: bold;">›</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- LOGIN SUMMARY TABLE --}}
        @if(isset($monthlySummary) && !empty($monthlySummary))
            @php
                // Extract headers from the first row (LoginDate + all application names)
                $firstRow = $monthlySummary[0];
                $headers = array_keys($firstRow);
                
                // Ensure LoginDate is first
                if (($key = array_search('LoginDate', $headers)) !== false) {
                    unset($headers[$key]);
                    array_unshift($headers, 'LoginDate');
                }
                
                // Calculate totals for each day
                $dailyTotals = [];
                foreach($monthlySummary as $row) {
                    $total = 0;
                    foreach($headers as $header) {
                        if($header !== 'LoginDate') {
                            $total += intval($row[$header] ?? 0);
                        }
                    }
                    $dailyTotals[$row['LoginDate']] = $total;
                }
            @endphp

            <div class="mb-4">
                <div class="alert mb-3" style="background-color: #513D6C; color: white; border: none; padding: 12px 20px;">
                    <strong style="font-size: 1.1rem;">Login Activity by System</strong>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover mb-0" style="background-color: white;">
                        <thead>
                            <tr style="background-color: #e9ecef;">
                                @foreach($headers as $header)
                                    <th class="text-center align-middle" 
                                        style="padding: 14px 10px; font-size: 0.95rem; font-weight: 600; white-space: nowrap; min-width: {{ $header === 'LoginDate' ? '170px' : '100px' }}; {{ $header === 'LoginDate' ? 'background-color: #d1d1d1;' : '' }}">
                                        {{ $header === 'LoginDate' ? 'Login Date' : $header }}
                                    </th>
                                @endforeach
                                <th class="text-center align-middle" 
                                    style="padding: 14px 10px; font-size: 0.95rem; font-weight: 600; white-space: nowrap; min-width: 120px; background-color: #624889ff; color: white;">
                                    Total Logins
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlySummary as $row)
                                <tr>
                                    @foreach($headers as $header)
                                        @if($header === 'LoginDate')
                                            @php
                                                // Parse the date and format with day of week
                                                try {
                                                    $date = \Carbon\Carbon::parse($row[$header]);
                                                    $formattedDate = $date->format('D d M Y'); // e.g., "Mon 06 Nov 2025"
                                                } catch (\Exception $e) {
                                                    $formattedDate = $row[$header] ?? '';
                                                }
                                            @endphp
                                            <td class="text-center align-middle" 
                                                style="padding: 12px 10px; font-size: 0.95rem; font-weight: 500; background-color: #e9ecef;">
                                                {{ $formattedDate }}
                                            </td>
                                        @else
                                            <td class="text-center align-middle" 
                                                style="padding: 12px 10px; font-size: 0.95rem;">
                                                {{ $row[$header] ?? '' }}
                                            </td>
                                        @endif
                                    @endforeach
                                    <td class="text-center align-middle" 
                                        style="padding: 12px 10px; font-size: 0.95rem; font-weight: 600; background-color: #f8f9fa;">
                                        <span class="badge" style="background-color: #cfc9d8ff; font-size: 0.9rem; padding: 0.4rem 0.7rem;">
                                            {{ $dailyTotals[$row['LoginDate']] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="alert alert-info" style="font-size: 1rem;">
                <i class="fas fa-info-circle me-2"></i>
                <strong>No login history available</strong><br>
                No sign-ins found for the selected date range.
            </div>
        @endif
    </div>

    {{-- Back to Dashboard --}}
    <div class="mt-4 mb-5">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary" style="font-size: 1rem; padding: 0.6rem 1.5rem;">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

</div>
@stop

@section('css')
    <style>
        /* Base font size improvements */
        body {
            font-size: 16px;
        }
        
        .card-body {
            font-size: 1rem;
        }
        
        .card-hover {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2) !important;
        }
        
        /* Hover effect for custom purple button */
        .btn[style*="#513D6C"]:hover {
            background-color: #3f2f54 !important;
            border-color: #3f2f54 !important;
        }
        
        /* Hide scrollbar but keep functionality */
        .carousel-container::-webkit-scrollbar {
            display: none;
        }
        
        /* Carousel button hover effects */
        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            background-color: rgba(81, 61, 108, 1) !important;
        }
        
        /* Custom dropdown styling - Remove default arrow since browser adds one */
        #date_preset {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding: 0.6rem 0.75rem;
            appearance: auto;
            -webkit-appearance: menulist;
            -moz-appearance: menulist;
        }
        
        #date_preset:focus {
            border-color: #513D6C;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(81, 61, 108, 0.25);
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
            }
            
            .row.mb-3 .col-md-5,
            .row.mb-3 .col-md-7 {
                font-size: 0.95rem;
            }
            
            h4 {
                font-size: 1.3rem;
            }
            
            h5 {
                font-size: 1.1rem;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
            
            .carousel-control-prev,
            .carousel-control-next {
                width: 35px;
                height: 35px;
            }
            
            .form-select {
                width: 150px !important;
                font-size: 0.9rem !important;
            }
        }
        
        @media (max-width: 576px) {
            .d-flex.gap-3 {
                gap: 0.5rem !important;
            }
            
            input[type="date"] {
                width: 140px !important;
                font-size: 0.9rem !important;
            }
            
            .btn {
                padding: 0.5rem 1rem !important;
                font-size: 0.9rem !important;
                min-width: 80px !important;
            }

            
        }
    </style>
@stop

@section('js')
<script>
// Carousel scroll function
function scrollCarousel(carouselId, direction) {
    const carousel = document.getElementById(carouselId);
    const scrollAmount = 300;
    carousel.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const filterForm = document.getElementById('filterForm');
    const customDateInputs = document.getElementById('customDateInputs');
    const datePresetSelect = document.getElementById('date_preset');
    
    // Function to calculate date ranges
    function getDateRange(preset) {
        const today = new Date();
        let startDate, endDate;
        
        switch(preset) {
            case 'this-month':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                break;
            case 'last-month':
                startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                break;
            case 'last-3-months':
                startDate = new Date(today.getFullYear(), today.getMonth() - 3, 1);
                endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                break;
            default:
                return null;
        }
        
        return {
            start: startDate.toISOString().split('T')[0],
            end: endDate.toISOString().split('T')[0]
        };
    }
    
    // Function to toggle custom date inputs visibility
    function toggleCustomDateInputs() {
        const selectedPreset = datePresetSelect.value;
        
        if (selectedPreset === 'custom') {
            // Show custom date inputs
            customDateInputs.classList.add('d-flex');
            customDateInputs.style.display = '';
            startDateInput.required = true;
            endDateInput.required = true;
        } else {
            // Hide custom date inputs
            customDateInputs.classList.remove('d-flex');
            customDateInputs.style.display = 'none';
            startDateInput.required = false;
            endDateInput.required = false;
            
            // Auto-fill dates for preset ranges
            const dateRange = getDateRange(selectedPreset);
            if (dateRange) {
                startDateInput.value = dateRange.start;
                endDateInput.value = dateRange.end;
            }
        }
    }
    
    // Handle dropdown change
    datePresetSelect.addEventListener('change', function() {
        toggleCustomDateInputs();
        
        // Auto-submit for preset ranges (not custom)
        if (this.value !== 'custom') {
            filterForm.submit();
        }
    });
    
    // Initialize on page load
    toggleCustomDateInputs();
    
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
            if (datePresetSelect.value === 'custom' && !validateDates()) {
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