@extends('adminlte::page')

@section('title', 'User Activity Report')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>User Activity Report</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary" style="font-size: 1rem; padding: 0.6rem 1.5rem;">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
@stop

@section('content')
    <div class="container-fluid">

        {{-- Date Range Info Banner --}}
        <div class="alert mb-4" style="background-color: #513D6C; color: white; border: none; padding: 12px 20px;">
            <strong style="font-size: 1.1rem;">Showing activity from {{ $startDate->format('M j, Y') }} to
                {{ $endDate->format('M j, Y') }}</strong>
        </div>

        {{-- Date Filter and Search Form --}}
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <h4 class="mb-2 mb-md-0 fw-semibold">Filter Activity</h4>

                <form method="GET" action="{{ route('activity.report') }}" id="filterForm"
                    class="d-flex align-items-center flex-wrap" style="gap: 1.5rem;">

                    {{-- Date Range Dropdown --}}
                    <div class="d-flex align-items-center" style="gap: 0.75rem;">
                        <label for="date_preset" class="mb-0" style="font-size: 0.95rem; white-space: nowrap;">Date
                            Range:</label>
                        <select name="date_preset" id="date_preset" class="form-select"
                            style="width: 180px; font-size: 0.95rem;">
                            <option value="this-month"
                                {{ request('date_preset', 'this-month') == 'this-month' ? 'selected' : '' }}>This Month
                            </option>
                            <option value="last-month" {{ request('date_preset') == 'last-month' ? 'selected' : '' }}>Last
                                Month</option>
                            <option value="last-3-months"
                                {{ request('date_preset') == 'last-3-months' ? 'selected' : '' }}>Last 3 Months</option>
                            <option value="custom" {{ request('date_preset') == 'custom' ? 'selected' : '' }}>Custom Range
                            </option>
                        </select>
                    </div>

                    {{-- Custom Date Range Pickers (Hidden by default) --}}
                    <div id="customDateInputs" class="align-items-center flex-wrap" style="display: none; gap: 0.75rem;">
                        <label for="start_date" class="mb-0"
                            style="font-size: 0.95rem; white-space: nowrap;">From:</label>
                        <input type="date" name="start_date" id="start_date" class="form-control"
                            style="width: 160px; font-size: 0.95rem;"
                            value="{{ request('start_date') ?? $startDate->format('Y-m-d') }}">
                        <label for="end_date" class="mb-0" style="font-size: 0.95rem; white-space: nowrap;">To:</label>
                        <input type="date" name="end_date" id="end_date" class="form-control"
                            style="width: 160px; font-size: 0.95rem;"
                            value="{{ request('end_date') ?? $endDate->format('Y-m-d') }}">
                    </div>

                    {{-- Search Field --}}
                    <div class="d-flex align-items-center" style="gap: 0.75rem;">
                        <label for="search" class="mb-0"
                            style="font-size: 0.95rem; white-space: nowrap;">Search:</label>
                        <input type="text" name="search" id="search" class="form-control"
                            style="width: 220px; font-size: 0.95rem;" placeholder="Name, UPN, or Email"
                            value="{{ request('search') }}">
                    </div>

                    {{-- Buttons --}}
                    <div class="d-flex" style="gap: 0.75rem;">
                        <button type="submit" class="btn"
                            style="background-color: #513D6C; color: white; padding: 0.6rem 2rem; min-width: 100px; font-size: 1rem;">
                            Apply
                        </button>
                        <a href="{{ route('activity.report') }}" class="btn btn-outline-secondary"
                            style="padding: 0.6rem 2rem; min-width: 100px; font-size: 1rem;">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Active Search Term Display --}}
        @if (request('search'))
            <div class="alert mb-3" style="background-color: #513D6C; color: white; border: none; padding: 12px 20px;">
                <i class="fas fa-search me-2"></i>Showing results for: <strong>"{{ request('search') }}"</strong>
            </div>
        @endif

        {{-- Activity Table --}}
        <div class="card mb-4">
            <div class="card-header" style="background-color: #f8f9fa;">
                <h5 class="mb-0" style="font-size: 1.1rem;">User Activity Summary</h5>
                <small class="text-muted" style="font-size: 0.95rem;">Login counts and activity for the selected
                    period</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover mb-0" style="background-color: white;">
                        <thead>
                            <tr style="background-color: #e9ecef;">
                                <th class="align-middle"
                                    style="padding: 14px 10px; font-size: 0.95rem; font-weight: 600; white-space: nowrap; min-width: 200px;">
                                    UserPrincipalName</th>
                                <th class="align-middle"
                                    style="padding: 14px 10px; font-size: 0.95rem; font-weight: 600; white-space: nowrap; min-width: 150px;">
                                    Display Name</th>
                                <th class="text-center align-middle"
                                    style="padding: 14px 10px; font-size: 0.95rem; font-weight: 600; white-space: nowrap; min-width: 120px; background-color: #624889; color: white;">
                                    Logins</th>
                                <th class="align-middle"
                                    style="padding: 14px 10px; font-size: 0.95rem; font-weight: 600; white-space: nowrap; min-width: 170px;">
                                    First Login</th>
                                <th class="align-middle"
                                    style="padding: 14px 10px; font-size: 0.95rem; font-weight: 600; white-space: nowrap; min-width: 170px;">
                                    Last Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activity as $user)
                                <tr>
                                    <td class="align-middle" style="padding: 12px 10px; font-size: 0.95rem;">
                                        {{ $user->userPrincipalName }}
                                    </td>
                                    <td class="align-middle"
                                        style="padding: 12px 10px; font-size: 0.95rem; font-weight: 500;">
                                        {{ $user->displayName }}
                                    </td>
                                    <td class="text-center align-middle"
                                        style="padding: 12px 10px; font-size: 0.95rem; background-color: #f8f9fa;">
                                        <span class="badge"
                                            style="background-color: #cfc9d8; font-size: 0.9rem; padding: 0.4rem 0.7rem;">
                                            {{ $user->login_count }}
                                        </span>
                                    </td>
                                    <td class="align-middle" style="padding: 12px 10px; font-size: 0.95rem;">
                                        {{ $user->first_login ? \Carbon\Carbon::parse($user->first_login)->format('D d M Y H:i') : 'No Activity' }}
                                    </td>
                                    <td class="align-middle" style="padding: 12px 10px; font-size: 0.95rem;">
                                        {{ $user->last_login ? \Carbon\Carbon::parse($user->last_login)->format('D d M Y H:i') : 'No Activity' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4" style="font-size: 1rem;">
                                        <i class="fas fa-inbox fa-2x mb-2 opacity-25"></i>
                                        <p class="mb-0"><strong>No user activity found</strong></p>
                                        <p class="text-muted mb-0">No sign-ins found for the selected date range.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            @if ($activity->hasPages())
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-center">
                        {{ $activity->appends([
                                'date_preset' => request('date_preset'),
                                'start_date' => request('start_date'),
                                'end_date' => request('end_date'),
                                'search' => request('search'),
                            ])->withQueryString()->links('logintracker.vendor.Pagination.tailwind') }}
                    </div>
                </div>
            @endif
        </div>

    </div>
@endsection

@section('css')
    <style>
        /* Base font size improvements */
        body {
            font-size: 16px;
        }

        .card-body {
            font-size: 1rem;
        }

        /* Hover effect for custom purple button */
        .btn[style*="#513D6C"]:hover {
            background-color: #3f2f54 !important;
            border-color: #3f2f54 !important;
        }

        /* Custom dropdown styling */
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

        /* Input focus styling */
        input[type="date"]:focus,
        input[type="text"]:focus {
            border-color: #513D6C;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(81, 61, 108, 0.25);
        }

        /* Table hover effect */
        .table-hover tbody tr:hover {
            background-color: rgba(81, 61, 108, 0.05);
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
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

            .form-select {
                width: 150px !important;
                font-size: 0.9rem !important;
            }

            .form-control {
                font-size: 0.9rem !important;
            }
        }

        @media (max-width: 576px) {
            input[type="date"] {
                width: 140px !important;
                font-size: 0.9rem !important;
            }

            .btn {
                padding: 0.5rem 1rem !important;
                font-size: 0.9rem !important;
                min-width: 80px !important;
            }

            #search {
                width: 180px !important;
            }
        }
    </style>
@endsection

@section('js')
    <script>
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

                switch (preset) {
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
            const SCROLL_STORAGE_KEY = 'activityReportScrollPosition';
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
                        submitBtn.innerHTML =
                            '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...';
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

        console.log('User Activity Report page loaded');
    </script>
@endsection
