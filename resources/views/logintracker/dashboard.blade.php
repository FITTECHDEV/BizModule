{{-- @extends('logintracker.layouts.app') --}}
@extends('adminlte::page')

@section('content')
    <div class="container-fluid px-2 px-sm-3 mt-4">
        {{-- <h1 class="mb-3 mb-md-4 fs-3 fs-md-2">Login Tracker Dashboard</h1> --}}

        @include('logintracker.partials.alerts')

        {{-- Loading Indicator --}}
        <div id="loading" class="alert alert-info" style="display: none;">
            Loading data, please wait...
        </div>

        {{-- Summary Cards --}}
        <div class="row g-2 g-md-3 mb-3 mb-md-4">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card bg-info text-white shadow h-100 stats-card filter-card" data-filter="all"
                    style="cursor: pointer;">
                    <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                        <h5 class="card-title mb-2 mb-md-3 fw-normal">Total Users</h5>
                        <p class="stats-number mb-0" id="total-users">{{ $total_users ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card bg-success text-white shadow card-hover h-100 stats-card filter-card"
                    data-filter="active_this_month" style="cursor: pointer;">
                    <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                        <h5 class="card-title mb-2 mb-md-3 fw-normal">Active This Month</h5>
                        <p class="stats-number mb-0" id="active-this-month">{{ $active_this_month ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card bg-primary text-white shadow card-hover h-100 stats-card filter-card" data-filter="active_last_month" style="cursor: pointer;">
                    <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                        <h5 class="card-title mb-2 mb-md-3 fw-normal">Active Last Month</h5>
                        <p class="stats-number mb-0" id="active-last-month">{{ $active_last_month ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card bg-danger text-white shadow card-hover h-100 stats-card filter-card" data-filter="inactive"
                    style="cursor: pointer;">
                    <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                        <h5 class="card-title mb-2 mb-md-3 fw-normal">Inactive Users</h5>
                        <p class="stats-number mb-0" id="inactive-users">{{ $inactive_users ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Controls: Show, Search & Action Buttons --}}
        <div
            class="d-flex flex-column flex-lg-row justify-content-between align-items-stretch align-items-lg-center gap-2 gap-sm-3 mb-3 p-2 p-sm-3 bg-light border rounded">
            <div class="d-flex align-items-center">
                <label class="me-2 mb-0 small text-muted text-nowrap">Show</label>
                <select id="entries-select" class="form-select form-select-sm" style="width: auto; min-width: 70px;">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="17" {{ request('per_page', 17) == 17 ? 'selected' : '' }}>17</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>

            <div
                class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 flex-grow-1 flex-lg-grow-0">
                <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
                    <input type="text" id="search-input" class="form-control form-control-sm"
                        placeholder="Search users..." style="max-width: 100%; width: 100%; min-width: 200px;">
                    <div class="spinner-border spinner-border-sm text-primary ms-2 d-none" id="search-spinner"></div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('imports.index') }}" class="btn btn-primary btn-sm text-nowrap">
                        <i class="fas fa-upload me-1"></i>Import Data
                    </a>
                    <a href="{{ route('activity.report') }}" class="btn btn-info btn-sm text-nowrap">
                        <i class="fas fa-chart-line me-1"></i>Activity Report
                    </a>
                </div>
            </div>
        </div>

        {{-- User Table --}}
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0" id="users-table">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-nowrap">Name</th>
                                <th class="text-nowrap">Login ID</th>
                                <th class="text-nowrap d-none d-md-table-cell">Logins Last Month</th>
                                <th class="text-nowrap">Last Login</th>
                                <th class="text-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="user-table-body">
                            @if ($users->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No users found.</td>
                                </tr>
                            @else
                                @foreach ($users as $user)
                                    @php
                                        $userId = $user['id'] ?? $user['login_id'];
                                    @endphp
                                    <tr>
                                        <td class="text-truncate" style="max-width: 150px;">{{ $user['name'] }}</td>
                                        <td class="text-nowrap">{{ $user['login_id'] }}</td>
                                        <td class="d-none d-md-table-cell">{{ $user['logins_last_month'] }}</td>
                                        <td class="text-nowrap small">
                                            @if ($user['last_login'])
                                                <span
                                                    class="d-none d-sm-inline">{{ \Carbon\Carbon::parse($user['last_login'])->format('j M Y H:i') }}</span>
                                                <span
                                                    class="d-inline d-sm-none">{{ \Carbon\Carbon::parse($user['last_login'])->format('j M Y') }}</span>
                                            @else
                                                <span class="text-muted">Never</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($userId)
                                                <a href="{{ route('reporting.users.show', ['user' => $userId]) }}"
                                                    class="btn btn-sm btn-primary">View</a>
                                            @else
                                                <span class="text-muted small">No ID</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Pagination with Info --}}
                <div
                    class="d-flex flex-column flex-sm-row justify-content-between align-items-center px-2 px-sm-3 py-3 gap-2">
                    <div id="bottom-table-info" class="small text-muted text-center text-sm-start">
                        Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }}
                        results
                    </div>
                    <div id="pagination-container"
                        class="w-100 w-sm-auto d-flex justify-content-center justify-content-sm-end">
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous">

    <style>
        /* Stats Cards Styling */
        .stats-card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            min-height: 140px;
        }

        .stats-card .card-body {
            position: relative;
        }

        .stats-card .card-title {
            font-size: 0.9rem;
            opacity: 0.95;
            font-weight: 500;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
            letter-spacing: -0.02em;
        }

        /* Card Hover Effects */
        .card-hover {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
        }

        .filter-card--active {
            outline: 3px solid #fff;
            outline-offset: 3px;
            box-shadow: 0 0 0 5px rgba(0, 0, 0, 0.25) !important;
        }

        /* Table Styling */
        #users-table th {
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.75rem 0.5rem;
        }

        #users-table td {
            vertical-align: middle;
            font-size: 0.9rem;
            padding: 0.75rem 0.5rem;
        }

        #users-table .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            white-space: nowrap;
        }

        /* Hide Laravel's default pagination info text */
        #pagination-container nav>div:first-child {
            display: none !important;
        }

        #pagination-container nav>div.hidden.sm\\:flex-1>div:first-child {
            display: none !important;
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
        }

        #pagination-container nav {
            display: flex !important;
            justify-content: flex-end !important;
            width: auto !important;
        }

        /* Pagination List */
        #pagination-container .pagination {
            display: flex !important;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.25rem;
            margin: 0 !important;
            padding: 0;
            list-style: none;
            justify-content: center !important;
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

        /* Loading State */
        #pagination-container.loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Tablet Responsive (768px to 991px) */
        @media (max-width: 991px) and (min-width: 768px) {
            .stats-card {
                min-height: 130px;
            }

            .stats-card .card-title {
                font-size: 0.875rem;
            }

            .stats-number {
                font-size: 2.25rem;
            }

            #users-table th,
            #users-table td {
                font-size: 0.85rem;
                padding: 0.6rem 0.4rem;
            }

            #users-table .btn-sm {
                font-size: 0.75rem;
                padding: 0.2rem 0.4rem;
            }
        }

        /* Mobile Responsive (below 768px) */
        @media (max-width: 767px) {

            /* Container adjustments */
            .container-fluid {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }

            /* Header */
            h1.fs-3 {
                font-size: 1.5rem !important;
            }

            /* Stats cards - full width stacking */
            .stats-card {
                min-height: 120px;
            }

            .stats-card .card-title {
                font-size: 0.85rem;
            }

            .stats-number {
                font-size: 2rem;
            }

            /* Table adjustments */
            #users-table {
                font-size: 0.8rem;
            }

            #users-table th,
            #users-table td {
                padding: 0.5rem 0.25rem;
                font-size: 0.75rem;
            }

            #users-table th {
                font-size: 0.7rem;
            }

            #users-table .btn-sm {
                font-size: 0.7rem;
                padding: 0.2rem 0.4rem;
            }

            /* Pagination mobile adjustments */
            #pagination-container .page-link {
                min-width: 32px;
                height: 32px;
                font-size: 0.75rem;
                padding: 0 6px;
            }

            #pagination-container .page-item:first-child .page-link,
            #pagination-container .page-item:last-child .page-link {
                padding: 0 8px;
                font-size: 0.7rem;
            }

            /* Make pagination fully centered on mobile */
            #pagination-container {
                justify-content: center !important;
                width: 100% !important;
            }

            #pagination-container .pagination {
                justify-content: center !important;
            }

            /* Table info text smaller */
            #bottom-table-info {
                font-size: 0.75rem;
            }
        }

        /* Extra small devices (below 576px) */
        @media (max-width: 575px) {

            /* Even more compact stats cards */
            .stats-card {
                min-height: 100px;
            }

            .stats-card .card-title {
                font-size: 0.8rem;
                margin-bottom: 0.5rem !important;
            }

            .stats-number {
                font-size: 1.75rem;
            }

            /* Compact table */
            #users-table th,
            #users-table td {
                padding: 0.4rem 0.2rem;
                font-size: 0.7rem;
            }

            /* Hide some pagination numbers on very small screens */
            #pagination-container .page-item:not(.active):not(:first-child):not(:last-child):not(.ellipsis) {
                display: none;
            }

            #pagination-container .page-item.active {
                display: list-item !important;
            }

            /* Show adjacent pages to active */
            #pagination-container .page-item.active+.page-item,
            #pagination-container .page-item.active+.page-item+.page-item {
                display: list-item !important;
            }

            .page-item:has(+ .page-item.active),
            .page-item:has(+ .page-item + .page-item.active) {
                display: list-item !important;
            }
        }

        /* Landscape orientation adjustments */
        @media (max-height: 600px) and (orientation: landscape) {
            .card-body {
                padding: 0.5rem !important;
            }

            h1.mb-3 {
                margin-bottom: 0.5rem !important;
            }

            .mb-3 {
                margin-bottom: 0.5rem !important;
            }
        }
    </style>
@endsection

@section('js')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    <script>
        $(document).ready(function() {
            const userShowBaseUrl = "{{ route('reporting.users.show', ['user' => ':id']) }}".replace(':id', '');
            let currentPage = parseInt("{{ $users->currentPage() }}") || 1;
            let perPage = parseInt("{{ request('per_page', 17) }}") || 17;
            let searchTerm = "{{ request('search', '') }}";
            let isLoading = false;
            let activeFilter = null;

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
                $('#pagination-container svg').remove();

                const $container = $('#pagination-container');
                const $nav = $container.find('nav');

                $nav.find('div > div:first-child').remove();
                $nav.find('p.text-sm.text-gray-700').remove();
                $nav.find('div.hidden').removeClass('hidden');

                const $paginationSpan = $nav.find('span.relative.z-0.inline-flex').first();

                if ($paginationSpan.length === 0) {
                    if ($nav.find('ul.pagination').length > 0) {
                        return;
                    }
                    return;
                }

                const $allItems = $paginationSpan.children('a, span');

                if ($allItems.length === 0) {
                    return;
                }

                const $newPagination = $('<ul class="pagination"></ul>');
                const pageNumbers = [];
                let activePageNum = 1;

                $allItems.each(function(index) {
                    const $this = $(this);
                    const $li = $('<li class="page-item"></li>');
                    const $link = $('<a class="page-link"></a>');

                    let text = $this.text().trim();

                    if ($this.is('a')) {
                        $link.attr('href', $this.attr('href'));
                        const rel = $this.attr('rel');
                        if (rel) $link.attr('rel', rel);
                    } else {
                        $li.addClass('disabled');
                    }

                    if ($this.find('[aria-current="page"]').length > 0 || $this.attr('aria-current') ===
                        'page') {
                        $li.addClass('active');
                        const pageNum = parseInt(text);
                        if (!isNaN(pageNum)) {
                            activePageNum = pageNum;
                        }
                    }

                    if ($this.find('[aria-disabled="true"]').length > 0 || $this.attr('aria-disabled') ===
                        'true') {
                        $li.addClass('disabled');
                    }

                    if (index === 0) {
                        text = 'Previous';
                        if ($li.hasClass('disabled')) {
                            $li.hide();
                        }
                    } else if (index === $allItems.length - 1) {
                        text = 'Next';
                    } else {
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

                const $wrapperDiv = $paginationSpan.closest('div.sm\\:flex-1');
                if ($wrapperDiv.length > 0) {
                    $wrapperDiv.replaceWith($newPagination);
                } else {
                    $paginationSpan.replaceWith($newPagination);
                }

                $nav.css('display', 'flex');

                if (pageNumbers.length > 7) {
                    applySmartPagination(pageNumbers, activePageNum);
                }
            }

            /**
             * Apply smart pagination logic to show relevant pages
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
                        if (lastShownPage > 0 && pageNum > lastShownPage + 1) {
                            const $ellipsis = $(
                                '<li class="page-item ellipsis disabled"><a class="page-link">...</a></li>'
                            );
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
                    url: '{{ route('dashboard') }}',
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        page: page,
                        per_page: perPage,
                        search: searchTerm,
                        filter: activeFilter
                    },
                    success: function(response) {
                        $('#total-users').text(response.total_users || 0);
                        $('#active-this-month').text(response.active_this_month || 0);
                        $('#active-last-month').text(response.active_last_month || 0);
                        $('#inactive-users').text(response.inactive_users || 0);

                        const tbody = $('#user-table-body');
                        tbody.empty();

                        const users = response.users || [];
                        const totalRecords = response.total || 0;
                        const from = users.length ? (page - 1) * perPage + 1 : 0;
                        const to = users.length ? Math.min(from + users.length - 1, totalRecords) : 0;

                        if (users.length > 0) {
                            users.forEach(user => {
                                const userId = user.id || user.login_id;
                                const lastLoginDate = user.last_login ? new Date(user
                                    .last_login) : null;

                                let lastLoginDesktop = '<span class="text-muted">Never</span>';
                                let lastLoginMobile = '<span class="text-muted">Never</span>';

                                if (lastLoginDate) {
                                    lastLoginDesktop = lastLoginDate.toLocaleDateString(
                                        'en-GB', {
                                            day: 'numeric',
                                            month: 'short',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }).replace(',', '');

                                    lastLoginMobile = lastLoginDate.toLocaleDateString(
                                        'en-GB', {
                                            day: 'numeric',
                                            month: 'short',
                                            year: 'numeric'
                                        });
                                }

                                const viewBtn = userId ?
                                    `<a href="${userShowBaseUrl}${encodeURIComponent(userId)}" class="btn btn-sm btn-primary">View</a>` :
                                    '<span class="text-muted small">No ID</span>';

                                tbody.append(`
                                    <tr>
                                        <td class="text-truncate" style="max-width: 150px;">${user.name || 'N/A'}</td>
                                        <td class="text-nowrap">${user.login_id || 'N/A'}</td>
                                        <td class="d-none d-md-table-cell">${user.logins_last_month || 0}</td>
                                        <td class="text-nowrap small">
                                            <span class="d-none d-sm-inline">${lastLoginDesktop}</span>
                                            <span class="d-inline d-sm-none">${lastLoginMobile}</span>
                                        </td>
                                        <td>${viewBtn}</td>
                                    </tr>
                                `);
                            });
                        } else {
                            tbody.append(
                                '<tr><td colspan="5" class="text-center text-muted py-4">No users found.</td></tr>'
                            );
                        }

                        updateTableInfo(from, to, totalRecords);

                        if (response.pagination) {
                            $('#pagination-container').html(response.pagination);
                            setTimeout(cleanupPagination, 100);
                        }

                        currentPage = page;
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading data:', error);
                        const tbody = $('#user-table-body');
                        tbody.html(
                            '<tr><td colspan="5" class="text-center text-danger py-4">Error loading data. Please try again.</td></tr>'
                        );
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
             * Event: Filter card click
             */
            $(document).on('click', '.filter-card', function() {
                const filter = $(this).data('filter');

                if (filter === 'all' || filter === activeFilter) {
                    activeFilter = null;
                } else {
                    activeFilter = filter;
                }

                $('.filter-card').removeClass('filter-card--active');
                if (activeFilter) {
                    $(`.filter-card[data-filter="${activeFilter}"]`).addClass('filter-card--active');
                }

                currentPage = 1;
                loadData(currentPage);
            });

            /**
             * Event: Pagination click
             */
            $(document).on('click', '#pagination-container a.page-link', function(e) {
                e.preventDefault();

                const href = $(this).attr('href');
                const $parent = $(this).parent();

                if (!href || $parent.hasClass('disabled') || $parent.hasClass('active') || $(this)
                    .text() === '...') {
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
