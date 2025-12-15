@extends('adminlte::page')

@section('title', 'Active This Month')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h1 class="page-title mb-0">Active This Month</h1>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back to dashboard
    </a>
</div>
@stop

@section('content')
<div class="container-fluid px-2 px-md-3 mt-3">
    {{-- Alerts --}}
    @include('logintracker.partials.alerts')

    {{-- Loading Indicator --}}
    <div id="loading" class="alert alert-info" style="display: none;">
        Loading data, please wait...
    </div>

    {{-- Controls: Show & Search --}}
    <div class="controls-wrapper mb-3">
        <div class="d-flex align-items-center mb-2 mb-md-0">
            <label class="me-2 mb-0 small text-muted">Show</label>
            <select id="entries-select" class="form-select form-select-sm" style="width: auto; min-width: 70px;">
                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                <option value="17" {{ request('per_page', 17) == 17 ? 'selected' : '' }}>17</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
            </select>
        </div>

        <div class="d-flex align-items-center flex-grow-1 flex-md-grow-0">
            <input type="text" id="search-input" class="form-control form-control-sm" placeholder="Search users..."
                value="{{ request('search') }}">
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
                            <th class="th-name">Name</th>
                            <th class="th-login-id d-none d-lg-table-cell">Login ID</th>
                            <th class="th-email d-none d-md-table-cell">Email</th>
                            <th class="th-count text-center">Logins</th>
                            <th class="th-last-login d-none d-sm-table-cell">Last Login</th>
                            <th class="th-actions text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="user-table-body">
                        @if ($users->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                        </tr>
                        @else
                        @foreach ($users as $user)
                        <tr>
                            <td class="td-name">
                                <div class="fw-semibold">{{ $user->displayName ?? 'N/A' }}</div>
                                <div class="d-md-none small text-muted">{{ $user->mail ?? 'N/A' }}</div>
                            </td>
                            <td class="d-none d-lg-table-cell">{{ $user->userPrincipalName ?? 'N/A' }}</td>
                            <td class="d-none d-md-table-cell">{{ $user->mail ?? 'N/A' }}</td>
                            <td class="text-center">
                                <span class="badge bg-success">
                                    {{ $user->login_count ?? 0 }}
                                </span>
                            </td>
                            <td class="d-none d-sm-table-cell">
                                @if($user->signIns && $user->signIns->isNotEmpty())
                                <span class="d-none d-lg-inline">{{ \Carbon\Carbon::parse($user->signIns->first()->date_utc)->format('j M Y H:i') }}</span>
                                <span class="d-lg-none">{{ \Carbon\Carbon::parse($user->signIns->first()->date_utc)->format('j M Y') }}</span>
                                @else
                                <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('reporting.users.show', $user->userPrincipalName ?? $user->id) }}"
                                    class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye d-sm-none"></i>
                                    <span class="d-none d-sm-inline"><i class="fas fa-eye me-1"></i>View</span>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Pagination with Info --}}
            <div class="pagination-footer">
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
    /* Base Typography */
    .page-title {
        font-size: clamp(1.25rem, 4vw, 2rem);
        font-weight: 700;
        color: #1f1f1f;
    }

    /* Controls Section */
    .controls-wrapper {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }

    #search-input {
        flex: 1;
        min-width: 150px;
        max-width: 100%;
    }

    /* Table Styling */
    #users-table {
        font-size: clamp(0.75rem, 2vw, 0.9rem);
    }

    #users-table th {
        font-weight: 600;
        font-size: clamp(0.7rem, 2vw, 0.875rem);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
        padding: 0.75rem 0.5rem;
    }

    #users-table td {
        vertical-align: middle;
        padding: 0.75rem 0.5rem;
    }

    .td-name {
        min-width: 120px;
    }

    #users-table .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: clamp(0.7rem, 2vw, 0.8rem);
        white-space: nowrap;
    }

    /* Pagination Footer */
    .pagination-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    #bottom-table-info {
        flex: 0 0 auto;
    }

    /* Hide Laravel's default pagination info */
    #pagination-container nav>div:first-child,
    #pagination-container nav>div.hidden.sm\\:flex-1>div:first-child {
        display: none !important;
    }

    #pagination-container svg {
        display: none !important;
    }

    /* Pagination Container */
    #pagination-container {
        display: flex !important;
        justify-content: flex-end !important;
        align-items: center !important;
        flex: 0 0 auto !important;
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
        gap: 0.25rem;
        margin: 0 !important;
        padding: 0;
        list-style: none;
        justify-content: flex-end !important;
        flex-wrap: wrap;
    }

    #pagination-container .page-item {
        list-style: none;
        margin: 0;
    }

    /* Page Links */
    #pagination-container .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: clamp(28px, 8vw, 36px);
        height: clamp(28px, 8vw, 36px);
        padding: 0 8px;
        font-size: clamp(0.7rem, 2vw, 0.875rem);
        font-weight: 500;
        color: #495057;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }

    #pagination-container .page-link:hover:not(.disabled) {
        background-color: #f8f9fa;
        border-color: #adb5bd;
        color: #0d6efd;
    }

    #pagination-container .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
        font-weight: 600;
        box-shadow: 0 2px 6px rgba(13, 110, 253, 0.3);
        cursor: default;
    }

    #pagination-container .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #fff;
        border-color: #dee2e6;
        cursor: not-allowed;
        opacity: 0.65;
    }

    #pagination-container .page-item.ellipsis .page-link {
        cursor: default;
        pointer-events: none;
        background-color: transparent;
        border-color: transparent;
    }

    #pagination-container.loading {
        opacity: 0.6;
        pointer-events: none;
    }

    /* Mobile Optimizations */
    @media (max-width: 576px) {
        .controls-wrapper {
            padding: 0.5rem;
        }

        #search-input {
            font-size: 0.875rem;
        }

        #users-table th,
        #users-table td {
            padding: 0.5rem 0.25rem;
        }

        .pagination-footer {
            flex-direction: column;
            align-items: flex-start;
            padding: 0.5rem;
        }

        #pagination-container {
            width: 100%;
            justify-content: center !important;
        }

        #pagination-container .pagination {
            justify-content: center !important;
        }

        #bottom-table-info {
            width: 100%;
            text-align: center;
            margin-bottom: 0.5rem;
        }
    }

    @media (min-width: 577px) and (max-width: 768px) {
        #search-input {
            max-width: 250px;
        }
    }

    @media (min-width: 769px) {
        #search-input {
            max-width: 300px;
        }
    }

    /* Tablet Landscape and Up */
    @media (min-width: 992px) {
        .controls-wrapper {
            padding: 1rem;
        }
    }

    /* Print Styles */
    @media print {

        .btn,
        #search-input,
        #entries-select,
        #pagination-container {
            display: none !important;
        }
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
        const userShowBaseUrl = "{{ route('reporting.users.show', ['user' => ':id']) }}".replace(':id', '');
        let currentPage = parseInt("{{ $users->currentPage() }}") || 1;
        let perPage = parseInt("{{ request('per_page', 17) }}") || 17;
        let searchTerm = "{{ request('search', '') }}";
        let isLoading = false;

        function updateTableInfo(from, to, total) {
            const infoText = `Showing ${from} to ${to} of ${total} results`;
            $('#bottom-table-info').text(infoText);
        }

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

                if ($this.find('[aria-current="page"]').length > 0 || $this.attr('aria-current') === 'page') {
                    $li.addClass('active');
                    const pageNum = parseInt(text);
                    if (!isNaN(pageNum)) {
                        activePageNum = pageNum;
                    }
                }

                if ($this.find('[aria-disabled="true"]').length > 0 || $this.attr('aria-disabled') === 'true') {
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

        function loadData(page = 1) {
            if (isLoading) return;

            isLoading = true;
            $('#loading').show();
            $('#search-spinner').removeClass('d-none');
            $('#pagination-container').addClass('loading');

            $.ajax({
                url: "{{ route('reporting.users.active-this-month') }}",
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
                    const tbody = $('#user-table-body');
                    tbody.empty();

                    const users = response.users || [];
                    const totalRecords = response.total || 0;
                    const from = response.from || 0;
                    const to = response.to || 0;

                    if (users.length > 0) {
                        users.forEach(user => {
                            const userId = user.userPrincipalName || user.id;
                            const displayName = user.displayName || 'N/A';
                            const userPrincipalName = user.userPrincipalName || 'N/A';
                            const mail = user.mail || 'N/A';
                            const loginCount = user.login_count || 0;

                            let lastLogin = '<span class="text-muted">Never</span>';
                            let lastLoginShort = '<span class="text-muted">Never</span>';
                            if (user.signIns && user.signIns.length > 0) {
                                const loginDate = new Date(user.signIns[0].date_utc);
                                lastLogin = loginDate.toLocaleDateString('en-GB', {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }).replace(',', '');
                                lastLoginShort = loginDate.toLocaleDateString('en-GB', {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric'
                                });
                            }

                            const viewBtn = userId ?
                                `<a href="${userShowBaseUrl}${encodeURIComponent(userId)}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye d-sm-none"></i>
                                        <span class="d-none d-sm-inline"><i class="fas fa-eye me-1"></i>View</span>
                                        </a>` :
                                '<span class="text-muted">No ID</span>';

                            tbody.append(`
                                    <tr>
                                        <td class="td-name">
                                            <div class="fw-semibold">${displayName}</div>
                                            <div class="d-md-none small text-muted">${mail}</div>
                                        </td>
                                        <td class="d-none d-lg-table-cell">${userPrincipalName}</td>
                                        <td class="d-none d-md-table-cell">${mail}</td>
                                        <td class="text-center"><span class="badge bg-success">${loginCount}</span></td>
                                        <td class="d-none d-sm-table-cell">
                                            <span class="d-none d-lg-inline">${lastLogin}</span>
                                            <span class="d-lg-none">${lastLoginShort}</span>
                                        </td>
                                        <td class="text-center">${viewBtn}</td>
                                    </tr>
                                `);
                        });
                    } else {
                        tbody.append('<tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>');
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

        $('#entries-select').on('change', function() {
            perPage = parseInt($(this).val());
            currentPage = 1;
            loadData(currentPage);
        });

        let searchTimeout;
        $('#search-input').on('input', function() {
            clearTimeout(searchTimeout);
            searchTerm = $(this).val().trim();
            searchTimeout = setTimeout(() => {
                currentPage = 1;
                loadData(currentPage);
            }, 300);
        });

        $(document).on('click', '#pagination-container a.page-link', function(e) {
            e.preventDefault();

            const href = $(this).attr('href');
            const $parent = $(this).parent();

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

        $('#entries-select').val(perPage);
        $('#search-input').val(searchTerm);

        setTimeout(cleanupPagination, 150);
    });
</script>
@endsection