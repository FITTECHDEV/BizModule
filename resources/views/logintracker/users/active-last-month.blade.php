@extends('adminlte::page')

@section('title', 'Active Last Month')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="page-title mb-0">Active Last Month</h1>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
    </a>
</div>
@stop

@section('content')
<div class="container mt-4">
    {{-- Alerts --}}
    @include('logintracker.partials.alerts')

    {{-- Filter Controls --}}
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
            <input type="text" id="search-input" class="form-control" placeholder="Search users..." style="width: 300px;">
            <div class="spinner-border spinner-border-sm text-primary ms-2 d-none" id="search-spinner"></div>
        </div>
    </div>

    {{-- Users Table --}}
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
                        @forelse($users as $user)
                            @php $userId = $user['id'] ?? $user['login_id']; @endphp
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
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Info & Controls --}}
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

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

    /* Pagination Cleanup & Styling */
    #pagination-container nav > div:first-child,
    #pagination-container nav > div.hidden.sm\\:flex-1 > div:first-child,
    #pagination-container svg {
        display: none !important;
    }

    #pagination-container {
        display: flex !important;
        justify-content: flex-end !important;
        align-items: center !important;
        margin-left: auto !important;
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
        font-weight: 500;
        color: #495057;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        transition: all 0.2s ease-in-out;
    }

    #pagination-container .page-link:hover {
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
    }

    #pagination-container .page-item.disabled .page-link {
        color: #6c757d;
        opacity: 0.6;
    }

    @media (max-width: 768px) {
        .page-title {
            font-size: 1.6rem;
        }

        #pagination-container .page-link {
            min-width: 32px;
            height: 32px;
            font-size: 0.8rem;
        }

        .d-flex.justify-content-between.align-items-center.px-3.py-3 {
            flex-direction: column !important;
            align-items: flex-start !important;
        }

        #pagination-container {
            justify-content: center !important;
            width: 100%;
        }
    }
</style>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
    // Automatically remove unwanted pagination artifacts from Laravel’s Tailwind output
    $('#pagination-container svg, #pagination-container p, #pagination-container div:first-child').remove();
});
</script>
@endsection
