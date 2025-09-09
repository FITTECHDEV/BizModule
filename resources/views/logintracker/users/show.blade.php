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
    <div class="d-flex justify-content-between align-items-start mb-4">
        <h4 class="mb-0">Sign In History</h4>
        
        <div class="d-flex flex-column gap-3">
            {{-- Search Field --}}
            <div class="input-group" style="width: 300px;">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="applicationSearch" placeholder="Search applications...">
            </div>
            
            {{-- Filter Form --}}
            <div class="card" style="width: 600px;">
                <div class="card-header py-2">
                    <h6 class="mb-0">Filter Data</h6>
                </div>
                <div class="card-body py-3">
                    <form method="GET" 
                          action="{{ route('reporting.users.show', $user->userPrincipalName ?? $user->id) }}" 
                          id="filterForm">
                        <div class="row align-items-end g-2">
                            <div class="col-md-4">
                                <label for="range" class="form-label small">Date Range</label>
                                <select name="range" id="range" class="form-select form-select-sm">
                                    <option value="this_month" {{ ($range ?? 'this_month') == 'this_month' ? 'selected' : '' }}>This Month</option>
                                    <option value="last_month" {{ ($range ?? '') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                                    <option value="last_3_months" {{ ($range ?? '') == 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
                                    <option value="custom" {{ ($range ?? '') == 'custom' ? 'selected' : '' }}>Custom Date</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="customDateDiv" style="{{ ($range ?? '') == 'custom' ? '' : 'display: none;' }}">
                                <label for="date" class="form-label small">Select Date</label>
                                <input type="date" name="date" id="date" class="form-control form-control-sm" value="{{ $selectedDate ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label for="system" class="form-label small">System</label>
                                <select name="system" id="system" class="form-select form-select-sm">
                                    <option value="">All Systems</option>
                                    @foreach($systems as $systemOption)
                                        <option value="{{ $systemOption }}" {{ ($system ?? '') == $systemOption ? 'selected' : '' }}>
                                            {{ $systemOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex flex-column gap-1">
                                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                                <a href="{{ route('reporting.users.show', $user->userPrincipalName ?? $user->id) }}" 
                                   class="btn btn-outline-secondary btn-sm">
                                   Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if ($signIns->isEmpty())
        <div class="alert alert-info">No login history available for the selected date range.</div>
    @else
        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>IP Address</th>
                    <th>Application</th>
                    <th>System</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="signInTableBody">
                @foreach ($signIns as $signIn)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($signIn->date_utc)->format('D, M j, Y g:i A') }}</td>
                        <td>{{ $signIn->ip_address ?? 'N/A' }}</td>
                        <td>
                            <span class="fw-bold">{{ $signIn->application ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $signIn->system ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $signIn->status === 'Success' ? 'bg-success' : 'bg-danger' }}">
                                {{ $signIn->status }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        {{-- Pagination --}}
        <div class="d-flex justify-content-center mt-4" id="pagination-container">
            {{ $signIns->withQueryString()->links('logintracker.vendor.Pagination.tailwind') }}
        </div>
    @endif

    {{-- Back to Dashboard --}}
    <div class="mt-4">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle range selection change
    const rangeSelect = document.getElementById('range');
    const customDateDiv = document.getElementById('customDateDiv');
    const dateInput = document.getElementById('date');
    
    rangeSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customDateDiv.style.display = 'block';
            dateInput.setAttribute('required', 'required');
        } else {
            customDateDiv.style.display = 'none';
            dateInput.removeAttribute('required');
            dateInput.value = '';
        }
    });
    
    // Auto-submit form when range changes (except for custom)
    rangeSelect.addEventListener('change', function() {
        if (this.value !== 'custom') {
            document.getElementById('filterForm').submit();
        }
    });
    
    // Auto-submit when system changes
    document.getElementById('system').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
    
    // Search functionality for applications
    document.getElementById('applicationSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('#signInTableBody tr');
        
        tableRows.forEach(row => {
            const applicationCell = row.cells[2].textContent.toLowerCase();
            if (searchTerm === '' || applicationCell.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Restore scroll position after page load
    const savedScrollPos = sessionStorage.getItem('scrollPosition');
    if (savedScrollPos) {
        window.scrollTo(0, parseInt(savedScrollPos));
        sessionStorage.removeItem('scrollPosition');
    }
    
    // Store scroll position before form submission
    document.getElementById('filterForm').addEventListener('submit', function() {
        sessionStorage.setItem('scrollPosition', window.pageYOffset);
    });
});
</script>

@endsection