@extends('layouts.app')

@section('content')
<div class="container mt-5">

    {{-- Page Heading --}}
    <h2 class="mb-4">Employee Details</h2>

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

    {{-- Current Filter Display --}}
    @if(isset($start) && isset($end))
        <div class="alert alert-info mb-4">
            <strong>Showing data for:</strong> 
            {{ $start->format('M j, Y') }} 
            @if($start->format('Y-m-d') != $end->format('Y-m-d'))
                to {{ $end->format('M j, Y') }}
            @endif
            @if($system)
                | System: <strong>{{ $system }}</strong>
            @endif
        </div>
    @endif

    {{-- Recent Applications Used --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Recent Applications Used</h5>
        </div>
        <div class="card-body">
            @if ($recentApplications->isEmpty())
                <div class="alert alert-info">No recent application usage found for the selected date range.</div>
            @else
                <div class="row">
                @foreach ($recentApplications as $app)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title fs-5">{{ $app->application ?? 'Unknown App' }}</h5>
                                <p class="card-text fs-6">
                                    @if($app->system && $app->system != $app->application)
                                        <span class="text-muted">System: {{ $app->system }}</span><br>
                                    @endif
                                    <span class="badge bg-primary fs-6">{{ $app->usage_count }} logins</span><br>
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

    {{-- Sign In History Header with Search and Filter --}}
<div class="row align-items-center mb-3">
    {{-- Left side: Heading --}}
    <div class="col-md-3">
        <h4 class="mb-0 fw-semibold">Sign In History</h4>
    </div>

    {{-- Right side: Search + Filters --}}
    <div class="col-md-9">
        <form method="GET" 
              action="{{ route('reporting.users.show', $user->userPrincipalName ?? $user->id) }}" 
              id="filterForm"
              class="d-flex flex-wrap align-items-center gap-2 justify-content-end bg-light p-2 rounded">

            {{-- Server-side Search --}}
            <div class="input-group input-group-sm w-auto">
                <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="applicationSearch" 
                       name="search"
                       placeholder="Search applications..." 
                       value="{{ request('search') }}">
            </div>

            {{-- IMPROVED: Date Range Dropdown --}}
            <select name="range" id="range" class="form-select form-select-sm w-auto">
                @php
                    $selectedRange = request('range', 'this_month');
                @endphp
                <option value="this_month" {{ $selectedRange == 'this_month' ? 'selected' : '' }}>This Month</option>
                <option value="last_month" {{ $selectedRange == 'last_month' ? 'selected' : '' }}>Last Month</option>
                <option value="last_3_months" {{ $selectedRange == 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
                <option value="custom" {{ $selectedRange == 'custom' ? 'selected' : '' }}>Custom Date</option>
            </select>

            {{-- IMPROVED: Calendar Date Picker (Hidden by default) --}}
            <input type="date" 
                   name="date" 
                   id="date" 
                   class="form-control form-control-sm w-auto"
                   style="{{ $selectedRange == 'custom' ? '' : 'display: none;' }}"
                   value="{{ request('date') ?? $selectedDate ?? '' }}">

            {{-- System Filter with All Systems option --}}
            <select name="system" id="system" class="form-select form-select-sm w-auto">
                <option value="" {{ empty($system) ? 'selected' : '' }}>All Systems</option>
                @foreach($systems as $systemOption)
                    <option value="{{ $systemOption }}" {{ ($system ?? '') == $systemOption ? 'selected' : '' }}>
                        {{ $systemOption }}
                    </option>
                @endforeach
            </select>

            {{-- Buttons --}}
            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
            <a href="{{ route('reporting.users.show', $user->userPrincipalName ?? $user->id) }}" 
               class="btn btn-outline-secondary btn-sm">Reset</a>
        </form>
    </div>
</div>

    {{-- Enhanced No Data Message --}}
    @if ($signIns->isEmpty())
        <div class="alert alert-info">
            <h6>No login history available</h6>
            @if($system)
                <p class="mb-0">No sign-ins found for system "<strong>{{ $system }}</strong>" in the selected date range.</p>
            @elseif(request('search'))
                <p class="mb-0">No sign-ins found matching search term "<strong>{{ request('search') }}</strong>".</p>
            @else
                <p class="mb-0">No sign-ins found for the selected date range.</p>
            @endif
        </div>
    @else
        {{-- Enhanced Table with Better Responsive Design --}}
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>IP Address</th>
                        <th>Application</th>
                        <th>System</th>
                        <th>Status</th>
                        <th class="d-none d-md-table-cell">Location</th>
                    </tr>
                </thead>
                <tbody id="signInTableBody">
                    @foreach ($signIns as $signIn)
                        <tr class="signin-row" 
                            data-application="{{ strtolower($signIn->application ?? '') }}"
                            data-system="{{ strtolower($signIn->system ?? '') }}">
                            <td>
                                <span class="fw-medium">{{ \Carbon\Carbon::parse($signIn->date_utc)->format('D, M j') }}</span><br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($signIn->date_utc)->format('g:i A') }}</small>
                            </td>
                            <td>
                                <code class="text-muted">{{ $signIn->ip_address ?? 'N/A' }}</code>
                            </td>
                            <td>
                                <span class="fw-bold text-primary">{{ $signIn->application ?? 'N/A' }}</span>
                                @if($signIn->application && $signIn->system && $signIn->application != $signIn->system)
                                    <br><small class="text-muted">via {{ $signIn->system }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $signIn->system ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $signIn->status === 'Success' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $signIn->status ?? 'Unknown' }}
                                </span>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <small class="text-muted">
                                    {{ $signIn->city ?? '' }}{{ $signIn->city && $signIn->country ? ', ' : '' }}{{ $signIn->country ?? 'N/A' }}
                                </small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- Enhanced Pagination with Info --}}
        <div class="d-flex justify-content-between align-items-center mt-4" id="pagination-container">
            <div class="text-muted">
                Showing {{ $signIns->firstItem() ?? 0 }} to {{ $signIns->lastItem() ?? 0 }} 
                of {{ $signIns->total() }} results
                @if($system)
                    for system "<strong>{{ $system }}</strong>"
                @endif
            </div>
            <div>
                {{ $signIns->withQueryString()->links('logintracker.vendor.Pagination.tailwind') }}
            </div>
        </div>
    @endif

    {{-- Back to Dashboard --}}
    <div class="mt-4">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

</div>

{{-- IMPROVED: Enhanced JavaScript with Date Range Functionality --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rangeSelect = document.getElementById('range');
    const dateInput = document.getElementById('date');
    const filterForm = document.getElementById('filterForm');
    const systemSelect = document.getElementById('system');
    const searchInput = document.getElementById('applicationSearch');
    
    // IMPROVED: Handle range selection change with auto-submit
    if (rangeSelect) {
        rangeSelect.addEventListener('change', function() {
            const selectedRange = this.value;
            
            if (selectedRange === 'custom') {
                // Show date picker for custom selection
                dateInput.style.display = 'block';
                dateInput.setAttribute('required', 'required');
                // Don't auto-submit, wait for date selection
            } else {
                // Hide date picker and clear its value
                dateInput.style.display = 'none';
                dateInput.removeAttribute('required');
                dateInput.value = '';
                // Auto-submit for predefined ranges
                filterForm.submit();
            }
        });
    }
    
    // IMPROVED: Handle date input change for custom date
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            if (rangeSelect.value === 'custom' && this.value) {
                // Auto-submit when custom date is selected
                filterForm.submit();
            }
        });
    }
    
    // Auto-submit when system changes
    if (systemSelect) {
        systemSelect.addEventListener('change', function() {
            // Clear search when changing system
            if (searchInput) {
                searchInput.value = '';
            }
            filterForm.submit();
        });
    }
    
    // Enhanced search functionality with debouncing
    if (searchInput) {
        let searchTimeout;
        
        // Real-time client-side filtering
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.toLowerCase().trim();
            
            // Client-side filtering for current page
            const tableRows = document.querySelectorAll('#signInTableBody .signin-row');
            let visibleCount = 0;
            
            tableRows.forEach(row => {
                const applicationText = row.getAttribute('data-application') || '';
                const systemText = row.getAttribute('data-system') || '';
                const isVisible = searchTerm === '' || 
                                applicationText.includes(searchTerm) || 
                                systemText.includes(searchTerm);
                
                if (isVisible) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show/hide "no results" message for client-side filtering
            updateClientSideNoResults(visibleCount, searchTerm);
            
            // Debounced server-side search for comprehensive results
            if (searchTerm.length >= 2 || searchTerm === '') {
                searchTimeout = setTimeout(() => {
                    filterForm.submit();
                }, 1000); // 1 second delay
            }
        });
        
        // Immediate search on Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(searchTimeout);
                filterForm.submit();
            }
        });
    }
    
    // Function to show/hide client-side no results message
    function updateClientSideNoResults(visibleCount, searchTerm) {
        let noResultsRow = document.getElementById('client-no-results');
        
        if (visibleCount === 0 && searchTerm.length > 0) {
            if (!noResultsRow) {
                const tbody = document.getElementById('signInTableBody');
                if (tbody) {
                    noResultsRow = document.createElement('tr');
                    noResultsRow.id = 'client-no-results';
                    noResultsRow.innerHTML = `
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-search mb-2"></i><br>
                                No results found for "${searchTerm}" on this page.<br>
                                <small>Results from other pages will appear after a brief delay.</small>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(noResultsRow);
                }
            }
        } else if (noResultsRow) {
            noResultsRow.remove();
        }
    }
    
    // Enhanced scroll position restoration
    const SCROLL_STORAGE_KEY = 'userShowScrollPosition';
    const savedScrollPos = sessionStorage.getItem(SCROLL_STORAGE_KEY);
    
    if (savedScrollPos) {
        // Restore scroll position after a short delay to ensure content is loaded
        setTimeout(() => {
            window.scrollTo({
                top: parseInt(savedScrollPos),
                behavior: 'smooth'
            });
        }, 100);
        sessionStorage.removeItem(SCROLL_STORAGE_KEY);
    }
    
    // Store scroll position before form submission
    filterForm.addEventListener('submit', function() {
        sessionStorage.setItem(SCROLL_STORAGE_KEY, window.pageYOffset.toString());
    });
    
    // Loading states for better UX
    filterForm.addEventListener('submit', function(e) {
        const submitBtn = filterForm.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Applying...';
            submitBtn.disabled = true;
        }
    });
    
    // Reset loading state if form submission fails
    window.addEventListener('pageshow', function() {
        const submitBtn = filterForm.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = 'Apply';
            submitBtn.disabled = false;
        }
    });
});
</script>

@endsection