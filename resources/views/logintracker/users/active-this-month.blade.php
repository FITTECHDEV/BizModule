@extends('adminlte::page')

@section('title', 'Active This Month')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="mb-0 page-title">Active This Month</h1>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>
@stop

@section('content')
<div class="container-fluid">

    {{-- Page Header Banner --}}
    <div class="alert mb-4" style="background-color: #483d98; color: white; border: none; padding: 15px 20px;">
        <h5 class="mb-1"><strong>Users Active This Month</strong></h5>
        <small>Showing users who have logged in during {{ now()->format('F Y') }}</small>
    </div>

    {{-- Search Bar --}}
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <form method="GET" action="{{ route('reporting.users.active-this-month') }}" class="row g-3 align-items-center">
                <div class="col-md-8">
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Search by name, email, or login ID..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn" style="background-color: #483d98; color: white;">
                        <i class="fas fa-search me-2"></i>Search
                    </button>
                    @if(request('search'))
                        <a href="{{ route('reporting.users.active-this-month') }}" class="btn btn-outline-secondary">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if($users->isEmpty())
                <div class="alert mb-0" style="background-color: #483d98; color: white; border: none;">
                    <i class="fas fa-info-circle me-2"></i>
                    No active users found for this month.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0" id="users-table">
                        <thead style="background-color: #483d98; color: white;">
                            <tr>
                                <th>Name</th>
                                <th>Login ID</th>
                                <th>Email</th>
                                <th>Login Count</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
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
                                            {{ \Carbon\Carbon::parse($user->signIns->first()->date_utc)->format('M j, Y H:i') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('reporting.users.show', $user->userPrincipalName ?? $user->id) }}"
                                           class="btn btn-sm"
                                           style="background-color: #483d98; color: white;">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-between align-items-center px-3 py-3 flex-wrap gap-3">
                    <div class="small text-muted">
                        Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
                    </div>
                    <div id="pagination-container" class="ms-auto">
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>
@stop

@section('css')
<style>
    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1f1f1f;
    }

    .table thead th {
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        transition: all 0.2s;
    }

    .card {
        border: none;
        border-radius: 8px;
    }

    /* Pagination Alignment Fix */
    #pagination-container nav > div:first-child {
        display: none !important;
    }

    #pagination-container nav {
        width: auto !important;
        margin-left: auto !important;
        display: flex !important;
        justify-content: flex-end !important;
    }

    #pagination-container svg {
        display: none !important;
    }

    #pagination-container .pagination {
        display: flex !important;
        align-items: center;
        gap: 0.25rem;
        margin: 0;
        list-style: none;
    }

    #pagination-container .page-item {
        list-style: none;
    }

    #pagination-container .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 10px;
        font-size: 0.875rem;
        color: #495057;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.2s ease-in-out;
    }

    #pagination-container .page-link:hover:not(.disabled) {
        background-color: #f8f9fa;
        color: #0d6efd;
    }

    #pagination-container .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
        font-weight: 600;
    }

    #pagination-container .page-item.disabled .page-link {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Mobile */
    @media (max-width: 768px) {
        #pagination-container {
            justify-content: center !important;
            width: 100%;
        }
        .d-flex.justify-content-between.align-items-center.px-3.py-3 {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
    }
</style>
@stop

@section('js')
<script>
    console.log('Active This Month page loaded');
</script>
@stop
