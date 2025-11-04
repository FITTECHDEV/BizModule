@extends('adminlte::page')

@section('title', 'Inactive Users')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="page-title mb-0">Inactive Users</h1>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>
@stop

@section('content')
<div class="container-fluid">

    {{-- Page Header Banner --}}
    <div class="alert mb-4" style="background-color: #483d98; color: white; border: none; padding: 15px 20px;">
        <h5 class="mb-1"><strong>Inactive Users</strong></h5>
        <small>Users who have not logged in recently or have never logged in.</small>
    </div>

    {{-- Search Bar --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reporting.users.inactive') }}" class="row g-3">
                <div class="col-md-8">
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Search by name, email, or login ID..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-4 d-flex align-items-center gap-2">
                    <button type="submit" class="btn" style="background-color: #483d98; color: white;">
                        <i class="fas fa-search me-2"></i>Search
                    </button>
                    @if(request('search'))
                        <a href="{{ route('reporting.users.inactive') }}" class="btn btn-outline-secondary">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="card shadow-sm">
        <div class="card-body">
            @if($users->isEmpty())
                <div class="alert" style="background-color: #483d98; color: white; border: none;">
                    <i class="fas fa-info-circle me-2"></i>
                    No inactive users found.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead style="background-color: #483d98; color: white;">
                            <tr>
                                <th>Name</th>
                                <th>Login ID</th>
                                <th>Email</th>
                                <th>Status</th>
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
                                        <span class="badge bg-danger">Inactive</span>
                                    </td>
                                    <td>
                                        @if($user->signIns && $user->signIns->isNotEmpty())
                                            {{ \Carbon\Carbon::parse($user->signIns->first()->date_utc)->format('M j, Y H:i') }}
                                        @else
                                            <span class="text-muted">Never</span>
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
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
                    </div>
                    <div>
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
        color: #2e2e2e;
        letter-spacing: 0.3px;
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

    .alert h5 {
        font-size: 1.1rem;
    }
</style>
@stop

@section('js')
<script>
    console.log('Inactive Users page loaded');
</script>
@stop
