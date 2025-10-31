{{-- @extends('logintracker.layouts.app') --}}
@extends('adminlte::page')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Login Tracker Dashboard</h1>

        <div class="mb-3">
            <a href="{{ route('imports.index') }}" class="btn btn-primary">Import Data</a>
            <a href="{{ route('activity.report') }}" class="btn btn-info">Activity Report</a>
        </div>

        @include('logintracker.partials.alerts')

        {{-- Loading Indicator --}}
        <div id="loading" class="alert alert-info" style="display: none;">
            Loading data, please wait...
        </div>

        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-info text-white shadow">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <p class="display-5" id="total-users">{{ $total_users ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <a href="{{ route('reporting.users.logged-in') }}?range=this_month" class="text-decoration-none">
                    <div class="card bg-success text-white shadow card-hover">
                        <div class="card-body">
                            <h5 class="card-title">Active This Month</h5>
                            <p class="display-5" id="active-this-month">{{ $active_this_month ?? 0 }}</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ route('reporting.users.logged-in') }}?range=last_month" class="text-decoration-none">
                    <div class="card bg-primary text-white shadow card-hover">
                        <div class="card-body">
                            <h5 class="card-title">Active Last Month</h5>
                            <p class="display-5" id="active-last-month">{{ $active_last_month ?? 0 }}</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ route('reporting.users.not-logged-in') }}" class="text-decoration-none">
                    <div class="card bg-danger text-white shadow card-hover">
                        <div class="card-body">
                            <h5 class="card-title">Inactive Users</h5>
                            <p class="display-5" id="inactive-users">{{ $inactive_users ?? 0 }}</p>
                        </div>
                    </div>
                </a>
            </div>
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
                <span class="ms-2 small text-muted">entries</span>
            </div>

            <div class="d-flex align-items-center">
                <input type="text" id="search-input" class="form-control form-control-sm" placeholder="Search users..."
                       style="width: 200px;" value="{{ request('search', '') }}">
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
                                <th>No. of Logins Last Month</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="user-table-body">
                            @if ($users->isEmpty())
                                <tr><td colspan="5" class="text-center text-muted py-4">No users found.</td></tr>
                            @else
                                @foreach ($users as $user)
                                    @php
                                        $userId = $user['id'] ?? $user['login_id'];
                                    @endphp
                                    <tr>
                                        <td>{{ $user['name'] }}</td>
                                        <td>{{ $user['login_id'] }}</td>
                                        <td>{{ $user['logins_last_month'] }}</td>
                                        <td>
                                            @if ($user['last_login'])
                                                {{ \Carbon\Carbon::parse($user['last_login'])->format('j M Y H:i') }}
                                            @else
                                                <span class="text-muted">Never</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($userId)
                                                <a href="{{ route('reporting.users.show', ['user' => $userId]) }}"
                                                   class="btn btn-sm btn-primary">View</a>
                                            @else
                                                <span class="text-muted">No ID</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Pagination & Info --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-3">
                    <div id="table-info" class="small text-muted">
                        Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} entries
                    </div>
                    <div id="pagination-container">
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
        .card-hover {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2) !important;
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

        /* --- Pagination Styling (Improved) --- */
        .pagination svg { display: none !important; }

        #pagination-container {
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        #pagination-container .pagination {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            margin: 0;
        }

        #pagination-container .page-item { list-style: none; }

        #pagination-container .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 38px;
            padding: 0 12px;
            font-size: 0.9rem;
            font-weight: 500;
            color: #495057;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            transition: all 0.2s ease-in-out;
        }

        #pagination-container .page-link:hover:not(.disabled),
        #pagination-container .page-link:focus-visible {
            background-color: #f8f9fa;
            border-color: #adb5bd;
            color: #0d6efd;
        }

        #pagination-container .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(13,110,253,0.25);
        }

        #pagination-container .page-item.disabled .page-link {
            color: #adb5bd;
            cursor: not-allowed;
            background-color: #f8f9fa;
        }

        #pagination-container .page-item:first-child .page-link,
        #pagination-container .page-item:last-child .page-link {
            font-weight: 600;
            padding: 0 16px;
            min-width: auto;
        }

        #pagination-container .page-item:first-child .page-link {
            border-top-left-radius: 20px;
            border-bottom-left-radius: 20px;
        }
        #pagination-container .page-item:last-child .page-link {
            border-top-right-radius: 20px;
            border-bottom-right-radius: 20px;
        }

        @media (max-width: 768px) {
            #pagination-container .page-link {
                min-width: 32px;
                height: 32px;
                font-size: 0.8rem;
                padding: 0 8px;
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
            const userShowBaseUrl = '{{ route('reporting.users.show', ['user' => ':id']) }}'.replace(':id', '');
            let currentPage = {{ $users->currentPage() }};
            let perPage = {{ request('per_page', 17) }};
            let searchTerm = '{{ request('search', '') }}';
            let isLoading = false;

            function updateTableInfo(from, to, total) {
                $('#table-info').text(`Showing ${from} to ${to} of ${total} entries`);
            }

            function cleanupPagination() {
                $('#pagination-container svg').remove();
                $('#pagination-container .page-link').each(function() {
                    const text = $(this).text().trim();
                    if (text === '' || text === '‹' || text === '›') {
                        const isPrev = $(this).parent().is('.page-item:first-child');
                        const isNext = $(this).parent().is('.page-item:last-child');
                        $(this).html(isPrev ? 'Previous' : isNext ? 'Next' : '');
                    }
                });
            }

            function loadData(page = 1) {
                if (isLoading) return;

                isLoading = true;
                $('#loading').show();
                $('#search-spinner').removeClass('d-none');

                $.ajax({
                    url: '{{ route('dashboard') }}',
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: { page, per_page: perPage, search: searchTerm },
                    success: function(response) {
                        const tbody = $('#user-table-body');
                        tbody.empty();

                        const users = response.users || [];
                        const totalRecords = response.total || 0;
                        const from = users.length ? (page - 1) * perPage + 1 : 0;
                        const to = users.length ? Math.min(from + users.length - 1, totalRecords) : 0;

                        if (users.length > 0) {
                            users.forEach(user => {
                                const userId = user.id || user.login_id;
                                const lastLogin = user.last_login 
                                    ? new Date(user.last_login).toLocaleDateString('en-GB', {
                                        day: 'numeric', month: 'short', year: 'numeric',
                                        hour: '2-digit', minute: '2-digit'
                                      }).replace(',', '')
                                    : '<span class="text-muted">Never</span>';

                                const viewBtn = userId
                                    ? `<a href="${userShowBaseUrl}${encodeURIComponent(userId)}" class="btn btn-sm btn-primary">View</a>`
                                    : '<span class="text-muted">No ID</span>';

                                tbody.append(`
                                    <tr>
                                        <td>${user.name || 'N/A'}</td>
                                        <td>${user.login_id || 'N/A'}</td>
                                        <td>${user.logins_last_month || 0}</td>
                                        <td>${lastLogin}</td>
                                        <td>${viewBtn}</td>
                                    </tr>
                                `);
                            });
                        } else {
                            tbody.append('<tr><td colspan="5" class="text-center text-muted py-4">No users found.</td></tr>');
                        }

                        updateTableInfo(from, to, totalRecords);

                        if (response.pagination) {
                            $('#pagination-container').html(response.pagination);
                            setTimeout(cleanupPagination, 50);
                        }

                        currentPage = page;
                    },
                    complete: function() {
                        $('#loading').hide();
                        $('#search-spinner').addClass('d-none');
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
                if (!href || $(this).parent().hasClass('disabled') || $(this).parent().hasClass('active')) return;

                const url = new URL(href);
                const newPage = parseInt(url.searchParams.get('page')) || 1;
                if (newPage !== currentPage) loadData(newPage);
            });

            $('#entries-select').val(perPage);
            $('#search-input').val(searchTerm);
            cleanupPagination();
        });
    </script>
@endsection
