@extends('adminlte::page')

@section('title', 'Active This Month')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="page-title mb-0">Active This Month</h1>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
    </a>
</div>
@stop

@section('content')
<div class="container mt-4">
    {{-- Alerts --}}
    @include('logintracker.partials.alerts')

    {{-- Loading Indicator --}}
    <div id="loading" class="alert alert-info" style="display: none;">
        Loading data, please wait...
    </div>

    {{-- Controls: Show & Search --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 p-3 bg-light border rounded">
        <div class="d-flex align-items-center mb-2 mb-sm-0">
            <label class="me-2 mb-0 small text-muted">Show</label>
            <select id="entries-select" class="form-select form-select-sm" style="width: auto;">
                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                <option value="17" {{ request('per_page', 17) == 17 ? 'selected' : '' }}>17</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
            </select>
        </div>

        <div class="d-flex align-items-center">
            <input type="text" id="search-input" class="form-control" placeholder="Search users..."
                    style="width: 300px;" value="{{ request('search') }}">
            <div class="spinner-border spinner-border-sm text-primary ms-2 d-none" id="search-spinner"></div>
        </div>
    </div>

    {{-- User Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0" id="users-table">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Login ID</th>
                            <th>Email</th>
                            <th>Login Count</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="user-table-body">
                        @if ($users->isEmpty())
                            <tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>
                        @else
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->displayName ?? 'N/A' }}</td>
                                    <td>{{ $user->userPrincipalName ?? 'N/A' }}</td>
                                    <td>{{ $user->mail ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ $user->login_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($user->signIns && $user->signIns->isNotEmpty())
                                            {{ \Carbon\Carbon::parse($user->signIns->first()->date_utc)->format('j M Y H:i') }}
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('reporting.users.show', $user->userPrincipalName ?? $user->id) }}"
                                            class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Pagination with Info --}}
            <div class="d-flex justify-content-between align-items-center px-3 py-3 flex-wrap gap-3">
                <div id="bottom-table-info" class="small text-muted">
                    Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results
                </div>
                <div id="pagination-container" class="ms-auto">
                    {{ $users->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Font Awesome -->
    <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
            integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
            crossorigin="anonymous">

    <style>
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f1f1f;
        }

        #users-table th {
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        #users-table td {
            vertical-align: middle;
            font-size: 0.9rem;
        }

        #users-table .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }

        /* Hide Laravel's default pagination info text */
        #pagination-container nav > div:first-child {
            display: none !important;
        }
        
        #pagination-container nav > div.hidden.sm\\:flex-1 > div:first-child {
            display: none !important;
        }

        /* Force pagination nav wrapper to align right */
        #pagination-container nav {
            width: auto !important;
            margin-left: auto !important;
        }

        /* Hide SVG icons from pagination */
        #pagination-container svg {
            display: none !important;
        }

        /* Pagination Container */
        #pagination-container {
            display: flex !important;
            justify-content: flex-end !important;
            align-items: center !important;
            flex: 0 0 auto !important;
            margin-left: auto !important;
        }

        #pagination-container nav {
            display: flex !important;
            justify-content: flex-end !important;
            width: auto !important;
        }

        #pagination-container nav > * {
            margin-left: auto !important;
        }

        /* Pagination List */
        #pagination-container .pagination {
            display: flex !important;
            align-items: center;
            gap: 0.25rem;
            margin: 0 0 0 auto !important;
            padding: 0;
            list-style: none;
            justify-content: flex-end !important;
        }

        /* Page Items */
        #pagination-container .page-item {
            list-style: none;
            margin: 0;
        }

        /* Page Links */
        #pagination-container .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #495057;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
        }

        /* Hover State */
        #pagination-container .page-link:hover:not(.disabled) {
            background-color: #f8f9fa;
            border-color: #adb5bd;
            color: #0d6efd;
        }

        /* Focus State */
        #pagination-container .page-link:focus-visible {
            outline: 2px solid #0d6efd;
            outline-offset: 2px;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* Active Page */
        #pagination-container .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(13, 110, 253, 0.3);
            cursor: default;
        }

        /* Disabled State */
        #pagination-container .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
            cursor: not-allowed;
            opacity: 0.65;
        }

        /* Previous/Next Buttons */
        #pagination-container .page-item:first-child .page-link,
        #pagination-container .page-item:last-child .page-link {
            font-weight: 600;
            padding: 0 14px;
            min-width: auto;
        }

        /* Ellipsis Styling */
        #pagination-container .page-item.ellipsis .page-link {
            cursor: default;
            pointer-events: none;
            background-color: transparent;
            border-color: transparent;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .page-title {
                font-size: 1.6rem;
            }

            #pagination-container .page-link {
                min-width: 32px;
                height: 32px;
                font-size: 0.8rem;
                padding: 0 8px;
            }

            #pagination-container .page-item:first-child .page-link,
            #pagination-container .page-item:last-child .page-link {
                padding: 0 10px;
            }

            /* Stack info and pagination on mobile */
            .d-flex.justify-content-between.align-items-center.px-3.py-3 {
                flex-direction: column !important;
                align-items: flex-start !important;
            }

            #bottom-table-info {
                margin-bottom: 1rem;
            }

            #pagination-container {
                justify-content: center !important;
                width: 100%;
            }
        }

        /* Loading State */
        #pagination-container.loading {
            opacity: 0.6;
            pointer-events: none;
        }
    </style>
@endsection

@section('js')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        $(document).ready(function() {
            const userShowBaseUrl = '{{ route('reporting.users.show', ['user' => ':id']) }}'.replace(':id', '');
            let currentPage = {{ $users->currentPage() }};
            let perPage = {{ request('per_page', 17) }};
            let searchTerm = '{{ request('search', '') }}';
            let isLoading = false;

            /**
             * Update the table information text at the bottom
             */
            function updateTableInfo(from, to, total) {
                const infoText = `Showing ${from} to ${to} of ${total} results`;
                $('#bottom-table-info').text(infoText);
            }

            /**
             * Convert Laravel Tailwind pagination to Bootstrap structure and apply improvements
             */
            function cleanupPagination() {
                // Remove any SVG icons
                $('#pagination-container svg').remove();
                
                const $container = $('#pagination-container');
                const $nav = $container.find('nav');
                
                // Remove the "Showing X to Y of Z results" text
                $nav.find('div > div:first-child').remove();
                $nav.find('p.text-sm.text-gray-700').remove();
                
                // Remove the "hidden" class that's hiding pagination on mobile
                $nav.find('div.hidden').removeClass('hidden');
                
                // Find the Tailwind pagination span container - look in the second div
                const $paginationSpan = $nav.find('span.relative.z-0.inline-flex').first();
                
                if ($paginationSpan.length === 0) {
                    // Check if we already converted it
                    if ($nav.find('ul.pagination').length > 0) {
                        return;
                    }
                    return;
                }
                
                // Get all pagination items (anchors and spans)
                const $allItems = $paginationSpan.children('a, span');
                
                if ($allItems.length === 0) {
                    return;
                }
                
                // Create Bootstrap pagination structure
                const $newPagination = $('<ul class="pagination"></ul>');
                const pageNumbers = [];
                let activePageNum = 1;
                
                $allItems.each(function(index) {
                    const $this = $(this);
                    const $li = $('<li class="page-item"></li>');
                    const $link = $('<a class="page-link"></a>');
                    
                    // Get text content
                    let text = $this.text().trim();
                    
                    // Determine if it's a link or disabled element
                    if ($this.is('a')) {
                        $link.attr('href', $this.attr('href'));
                        const rel = $this.attr('rel');
                        if (rel) $link.attr('rel', rel);
                    } else {
                        // It's a span (disabled or current)
                        $li.addClass('disabled');
                    }
                    
                    // Check for active/current page
                    if ($this.find('[aria-current="page"]').length > 0 || $this.attr('aria-current') === 'page') {
                        $li.addClass('active');
                        const pageNum = parseInt(text);
                        if (!isNaN(pageNum)) {
                            activePageNum = pageNum;
                        }
                    }
                    
                    // Check for disabled state
                    if ($this.find('[aria-disabled="true"]').length > 0 || $this.attr('aria-disabled') === 'true') {
                        $li.addClass('disabled');
                    }
                    
                    // Handle Previous button (first item)
                    if (index === 0) {
                        text = 'Previous';
                        if ($li.hasClass('disabled')) {
                            $li.hide(); // Hide disabled Previous button
                        }
                    } 
                    // Handle Next button (last item)
                    else if (index === $allItems.length - 1) {
                        text = 'Next';
                    }
                    // Handle page numbers and ellipsis
                    else {
                        const pageNum = parseInt(text);
                        if (!isNaN(pageNum)) {
                            pageNumbers.push({
                                num: pageNum,
                                $li: $li,
                                $link: $link
                            });
                        } else if (text === '...') {
                            $li.addClass('ellipsis');
                        }
                    }
                    
                    $link.text(text);
                    $li.append($link);
                    $newPagination.append($li);
                });
                
                // Replace old structure with new Bootstrap pagination
                const $wrapperDiv = $paginationSpan.closest('div.sm\\:flex-1');
                if ($wrapperDiv.length > 0) {
                    $wrapperDiv.replaceWith($newPagination);
                } else {
                    $paginationSpan.replaceWith($newPagination);
                }
                
                // Ensure the nav is visible
                $nav.css('display', 'flex');
                
                // Apply smart pagination display logic
                if (pageNumbers.length > 7) {
                    applySmartPagination(pageNumbers, activePageNum);
                }
            }

            /**
             * Apply smart pagination logic to show relevant pages
             * Show: First 2, Last 2, Current, and Adjacent pages
             */
            function applySmartPagination(pageNumbers, activePage) {
                const totalPages = pageNumbers[pageNumbers.length - 1].num;
                let lastShownPage = 0;
                
                pageNumbers.forEach((page, index) => {
                    const pageNum = page.num;
                    const isActive = pageNum === activePage;
                    const isFirst = pageNum <= 2;
                    const isLast = pageNum > totalPages - 2;
                    const isAdjacent = Math.abs(pageNum - activePage) <= 1;
                    
                    const shouldShow = isFirst || isLast || isActive || isAdjacent;
                    
                    if (shouldShow) {
                        // Show ellipsis if there's a gap
                        if (lastShownPage > 0 && pageNum > lastShownPage + 1) {
                            const $ellipsis = $('<li class="page-item ellipsis disabled"><a class="page-link">...</a></li>');
                            page.$li.before($ellipsis);
                        }
                        
                        page.$li.show();
                        lastShownPage = pageNum;
                    } else {
                        page.$li.hide();
                    }
                });
            }

            /**
            * Load data via AJAX
             */
            function loadData(page = 1) {
                if (isLoading) return;

            isLoading = true;
            $('#loading').show();
            $('#search-spinner').removeClass('d-none');
            $('#pagination-container').addClass('loading');

            $.ajax({
                url: '{{ route("reporting.users.active-this-month") }}',
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: { 
            page: page, 
            per_page: perPage, 
            search: searchTerm 
        },
        success: function(response) {
            console.log('AJAX Response:', response); // Debug log
            
            const tbody = $('#user-table-body');
            tbody.empty();

            const users = response.users || [];
            const totalRecords = response.total || 0;
            const from = response.from || 0;  // Use response.from
            const to = response.to || 0;      // Use response.to

            if (users.length > 0) {
                users.forEach(user => {
                    const userId = user.userPrincipalName || user.id;
                    const displayName = user.displayName || 'N/A';
                    const userPrincipalName = user.userPrincipalName || 'N/A';
                    const mail = user.mail || 'N/A';
                    const loginCount = user.login_count || 0;
                    
                    let lastLogin = '<span class="text-muted">Never</span>';
                    if (user.signIns && user.signIns.length > 0) {
                        const loginDate = new Date(user.signIns[0].date_utc);
                        lastLogin = loginDate.toLocaleDateString('en-GB', {
                            day: 'numeric', 
                            month: 'short', 
                            year: 'numeric',
                            hour: '2-digit', 
                            minute: '2-digit'
                        }).replace(',', '');
                    }

                    const viewBtn = userId
                        ? `<a href="${userShowBaseUrl}${encodeURIComponent(userId)}" class="btn btn-sm btn-primary"><i class="fas fa-eye me-1"></i>View</a>`
                        : '<span class="text-muted">No ID</span>';

                    tbody.append(`
                        <tr>
                            <td>${displayName}</td>
                            <td>${userPrincipalName}</td>
                            <td>${mail}</td>
                            <td><span class="badge bg-success">${loginCount}</span></td>
                            <td>${lastLogin}</td>
                            <td>${viewBtn}</td>
                        </tr>
                    `);
                });
            } else {
                tbody.append('<tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>');
            }

            updateTableInfo(from, to, totalRecords);

            // Update pagination
            if (response.pagination) {
                $('#pagination-container').html(response.pagination);
                setTimeout(cleanupPagination, 100);
            } else {
                console.warn('No pagination HTML in response');
            }

            currentPage = page;
        },
        error: function(xhr, status, error) {
            console.error('Error loading data:', {
                status: xhr.status,
                statusText: xhr.statusText,
                error: error,
                response: xhr.responseText
            });
            
            const tbody = $('#user-table-body');
            tbody.html('<tr><td colspan="6" class="text-center text-danger py-4">Error loading data. Please try again.</td></tr>');
        },
        complete: function() {
            $('#loading').hide();
            $('#search-spinner').addClass('d-none');
            $('#pagination-container').removeClass('loading');
            isLoading = false;
        }
    });
}

            /**
             * Event: Entries per page change
             */
            $('#entries-select').on('change', function() {
                perPage = parseInt($(this).val());
                currentPage = 1;
                loadData(currentPage);
            });

            /**
             * Event: Search input with debounce
             */
            let searchTimeout;
            $('#search-input').on('input', function() {
                clearTimeout(searchTimeout);
                searchTerm = $(this).val().trim();
                searchTimeout = setTimeout(() => {
                    currentPage = 1;
                    loadData(currentPage);
                }, 300);
            });

            /**
             * Event: Pagination click
             */
            $(document).on('click', '#pagination-container a.page-link', function(e) {
                e.preventDefault();
                
                const href = $(this).attr('href');
                const $parent = $(this).parent();
                
                // Ignore if disabled, active, or ellipsis
                if (!href || $parent.hasClass('disabled') || $parent.hasClass('active') || $(this).text() === '...') {
                    return;
                }

                try {
                    const url = new URL(href);
                    const newPage = parseInt(url.searchParams.get('page')) || 1;
                    
                    if (newPage !== currentPage) {
                        loadData(newPage);
                    }
                } catch (error) {
                    console.error('Error parsing pagination URL:', error);
                }
            });

            // Initialize form values
            $('#entries-select').val(perPage);
            $('#search-input').val(searchTerm);
            
            // Run cleanup on initial page load
            setTimeout(cleanupPagination, 150);
        });
    </script>
@endsection