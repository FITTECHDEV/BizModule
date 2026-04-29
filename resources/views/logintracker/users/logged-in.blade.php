<!-- @extends('logintracker.layouts.app')

@section('content')
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">Logged In Users</h1>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            {{-- Filters --}}
            <div class="row g-2 mb-4">
                <div class="col-md-3">
                    <label for="range" class="form-label">Date Range:</label>
                    <select name="range" id="range" class="form-select">
                        <option value="this_month" {{ $filters['range'] == 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ $filters['range'] == 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="last_3_months" {{ $filters['range'] == 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="system" class="form-label">System:</label>
                    <select name="system" id="system" class="form-select">
                        @foreach ($systems ?? [] as $sys)
    <option value="{{ $sys }}" {{ $filters['system'] == $sys ? 'selected' : '' }}>{{ $sys }}</option>
    @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="search" class="form-label">Search:</label>
                    <input type="text" name="search" id="search" class="form-control"
                        placeholder="Name or Email" value="{{ $search }}">
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-primary me-2" onclick="applyFilters()">Apply</button>
                    <a href="{{ route('reporting.users.logged-in') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>

            {{-- Filter Summary --}}
            <div class="alert alert-info mb-4">
                <strong>Showing:</strong> Users who logged into
                <span class="badge bg-primary">{{ $filters['system'] == 'all' ? 'All Systems' : $filters['system'] }}</span>
                during <span class="badge bg-secondary">{{ $filters['rangeLabel'] }}</span>
                @if ($search)
    matching <span class="badge bg-warning text-dark">"{{ $search }}"</span>
    @endif
            </div>

            {{-- Users Table --}}
            @if ($users->isEmpty())
    <div class="alert alert-warning">
                    <h5>No logged-in users found</h5>
                    <p class="mb-0">No users have logged into {{ $filters['system'] == 'all' ? 'any system' : $filters['system'] }} during {{ $filters['rangeLabel'] }}.</p>
                </div>
@else
    <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ $users->total() }} Users Found</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Total Logins</th>
                                        <th>Last Login</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
    <tr>
                                            <td><strong>{{ $user->displayName ?: 'Unknown' }}</strong></td>
                                            <td><small class="text-muted">{{ $user->userPrincipalName }}</small></td>
                                            <td><span class="badge bg-success">{{ $user->login_count }}</span></td>
                                            <td>
                                                @if ($user->signIns && $user->signIns->count() > 0)
    <small>{{ \Carbon\Carbon::parse($user->signIns->first()->date_utc)->format('M d, Y H:i') }}</small>
@else
    <small class="text-muted">No recent logins</small>
    @endif
                                            </td>
                                            <td>
                                                @php
                                                    $userId = $user->id ?? $user->userPrincipalName;
                                                @endphp
                                                @if ($userId)
    <a href="{{ route('reporting.users.show', ['user' => $userId]) }}" class="btn btn-sm btn-primary">
                                                        View Details
                                                    </a>
@else
    <span class="text-muted">No ID available</span>
    @endif
                                            </td>
                                        </tr>
    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Pagination with enhanced styling --}}
                <div class="mt-3" id="pagination-container">
                    {{ $users->withQueryString()->links() }}
                </div>
    @endif
        </div>

        <style>
            /* Pagination styling consistent with dashboard */
            .pagination svg,
            svg.arrow,
            svg.pagination-arrow,
            .pagination-arrow,
            .arrow-left,
            .arrow-right,
            .arrow-container,
            .pagination-large-arrow {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
                visibility: hidden !important;
            }

            .pagination,
            #pagination-container nav,
            #pagination-container .pagination {
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                gap: 0.5rem !important;
                margin: 1rem 0 !important;
                font-size: 0.875rem !important;
                flex-wrap: wrap !important;
                padding: 0 !important;
                list-style: none !important;
            }

            .pagination a,
            .pagination span,
            .pagination button,
            .page-link,
            #pagination-container a,
            #pagination-container span,
            #pagination-container button {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                min-width: 32px !important;
                height: 32px !important;
                padding: 0 0.5rem !important;
                margin: 0 2px !important;
                font-size: 0.875rem !important;
                font-weight: 500 !important;
                line-height: 1 !important;
                color: #4b5563 !important;
                background-color: #ffffff !important;
                border: 1px solid #d1d5db !important;
                border-radius: 6px !important;
                text-decoration: none !important;
                transition: all 0.15s ease !important;
                cursor: pointer !important;
                vertical-align: middle !important;
                overflow: hidden !important;
                white-space: nowrap !important;
            }

            .pagination a:hover:not(.disabled):not(.active),
            .page-link:hover:not(.disabled):not(.active),
            #pagination-container a:hover:not(.disabled):not(.active) {
                background-color: #f3f4f6 !important;
                border-color: #9ca3af !important;
                color: #1f2937 !important;
            }

            .pagination .active,
            .pagination .current,
            .page-item.active .page-link,
            #pagination-container .active,
            #pagination-container .current {
                background-color: #3b82f6 !important;
                border-color: #3b82f6 !important;
                color: #ffffff !important;
                font-weight: 600 !important;
            }

            .pagination .disabled,
            .pagination [disabled],
            .page-item.disabled .page-link,
            #pagination-container .disabled,
            #pagination-container [disabled] {
                opacity: 0.5 !important;
                cursor: not-allowed !important;
                background-color: #f9fafb !important;
                color: #9ca3af !important;
                pointer-events: none !important;
            }

            .pagination [rel="prev"]::before {
                content: "←" !important;
                margin-right: 0.25rem !important;
            }

            .pagination [rel="next"]::after {
                content: "→" !important;
                margin-left: 0.25rem !important;
            }

            #pagination-container {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                gap: 0.5rem !important;
                margin: 1rem 0 !important;
            }

            body #pagination-container svg,
            body .pagination svg {
                display: none !important;
                max-width: 0 !important;
                max-height: 0 !important;
            }
        </style>

        <script>
            function applyFilters() {
                const range = document.getElementById('range').value;
                const system = document.getElementById('system').value;
                let search = document.getElementById('search').value;

                // Sanitize search input
                if (search.length > 255) {
                    search = search.substring(0, 255);
                }

                const params = new URLSearchParams();
                if (range) params.set('range', range);
                if (system) params.set('system', system);
                if (search) params.set('search', search);

                window.location.href = '{{ route('reporting.users.logged-in') }}?' + params.toString();
            }

            // Enhanced event handling
            document.addEventListener('DOMContentLoaded', function() {
                // Enter key support
                document.getElementById('search').addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        applyFilters();
                    }
                });

                // Cleanup pagination arrows on load
                function cleanupPaginationArrows() {
                    document.querySelectorAll('#pagination-container svg, .pagination svg').forEach(svg => {
                        svg.remove();
                    });
                }

                // Run cleanup after a short delay to ensure DOM is ready
                setTimeout(cleanupPaginationArrows, 100);

                // Also run cleanup when pagination changes (for AJAX-loaded content)
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.addedNodes.length) {
                            mutation.addedNodes.forEach(function(node) {
                                if (node.nodeType === 1 && (
                                        node.classList?.contains('pagination') ||
                                        node.querySelector?.('.pagination')
                                    )) {
                                    setTimeout(cleanupPaginationArrows, 10);
                                }
                            });
                        }
                    });
                });

                const paginationContainer = document.getElementById('pagination-container');
                if (paginationContainer) {
                    observer.observe(paginationContainer, {
                        childList: true,
                        subtree: true
                    });
                }
            });
        </script>
@endsection -->
