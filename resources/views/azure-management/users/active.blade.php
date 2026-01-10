@extends('adminlte::page')

@section('title', 'Active Users Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-user-check text-success"></i> Active Users Management</h1>
            <p class="text-muted mb-0">Manage all active system users</p>
        </div>
        <div>
            <a href="{{ route('azure.dashboard.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
@stop

@section('content')

<!-- Users Table Card -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="card-title mb-0">
                    <i class="fas fa-users"></i> Active Users
                    <span class="badge badge-success ml-2">{{ $users->total() }}</span>
                </h3>
            </div>
            <div class="col-md-6 text-right">
                <div class="d-inline-flex align-items-center">
                    <div class="input-group input-group-sm mr-2" style="width: 350px;">
                        <input type="text" 
                            id="searchUsers" 
                            class="form-control form-control-sm" 
                            placeholder="Search... (Press Enter)" 
                            value="{{ request('search') }}"
                            autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch" title="Clear search">
                                <i class="fas fa-times"></i>
                            </button>
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                    <!-- <a href="{{ route('azure.users.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-plus"></i> Add User
                    </a> -->
                </div>
            </div>
        </div>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Login ID</th>
                    <th>Employee ID</th>
                    <th>Group</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="user-avatar mr-2">
                                {{ strtoupper(substr($user->displayName ?? 'NA', 0, 2)) }}
                            </div>
                            <span>{{ $user->displayName ?? 'N/A' }}</span>
                        </div>
                    </td>
                    <td>{{ $user->mail ?? $user->userPrincipalName ?? 'N/A' }}</td>
                    <td>{{ $user->userPrincipalName ?? 'N/A' }}</td>
                    <td>{{ $user->EmployeCode ?? 'N/A' }}</td>
                    <td>{{ $user->department ?? $user->companyName ?? 'N/A' }}</td>
                    <td>{{ $user->officeLocation ?? $user->city ?? 'N/A' }}</td>
                    <td>
                        <span class="badge badge-success">Active</span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route('azure.users.show', $user->Id) }}" 
                                class="btn btn-info btn-sm" 
                                title="View User">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('azure.users.edit', $user->Id) }}" 
                                class="btn btn-warning btn-sm" 
                                title="Edit User">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-danger btn-sm" 
                                    onclick="disableUser('{{ $user->Id }}')"
                                    title="Disable User">
                                <i class="fas fa-user-slash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <p>No active users found</p>
                        @if(request('search'))
                        <button class="btn btn-sm btn-outline-primary" onclick="clearAllFilters()">
                            <i class="fas fa-redo"></i> Clear Search
                        </button>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="card-footer clearfix">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span class="text-muted">
                    Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} active users
                </span>
            </div>
            <div class="col-md-6">
                <ul class="pagination pagination-sm m-0 float-right">
                    {{-- First Page Link --}}
                    @if ($users->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link">First</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $users->appends(request()->except('page'))->url(1) }}">First</a>
                        </li>
                    @endif

                    {{-- Previous Page Link --}}
                    @if ($users->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link">&laquo;</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $users->appends(request()->except('page'))->previousPageUrl() }}">&laquo;</a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                        @if ($page == $users->currentPage())
                            <li class="page-item active">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->appends(request()->except('page'))->url($page) }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($users->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $users->appends(request()->except('page'))->nextPageUrl() }}">&raquo;</a>
                        </li>
                    @else
                        <li class="page-item disabled">
                            <span class="page-link">&raquo;</span>
                        </li>
                    @endif

                    {{-- Last Page Link --}}
                    @if ($users->currentPage() == $users->lastPage())
                        <li class="page-item disabled">
                            <span class="page-link">Last</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $users->appends(request()->except('page'))->url($users->lastPage()) }}">Last</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
    @endif
</div>

@stop

@section('css')
<style>
    .user-avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: linear-gradient(135deg, #28a745, #20c997);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 14px;
    }
    
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    /* Pagination Styles */
    .pagination {
        margin-bottom: 0;
    }
    
    .page-item.active .page-link {
        background-color: #28a745;
        border-color: #28a745;
    }
    
    .page-link {
        color: #28a745;
    }
    
    .page-link:hover {
        color: #1e7e34;
    }
    
    .card-footer {
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
    }
    
    #clearSearch {
        border-left: 0;
    }
    
    #clearSearch:hover {
        background-color: #e9ecef;
    }
    
    #searchUsers:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
</style>
@stop

@section('js')
<script>
    // Search functionality - only trigger on Enter key
    document.getElementById('searchUsers').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
        }
        
        // Clear search on Escape key
        if (e.key === 'Escape') {
            e.target.value = '';
            performSearch();
        }
    });

    // Clear search button
    document.getElementById('clearSearch').addEventListener('click', function() {
        document.getElementById('searchUsers').value = '';
        performSearch();
    });

    function performSearch() {
        const search = document.getElementById('searchUsers').value.trim();
        const url = new URL(window.location.href);
        
        if (search) {
            url.searchParams.set('search', search);
        } else {
            url.searchParams.delete('search');
        }
        
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    function clearAllFilters() {
        document.getElementById('searchUsers').value = '';
        const url = new URL(window.location.href);
        url.searchParams.delete('search');
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    function disableUser(userId) {
        if (!confirm('Disable User\n\nAre you sure you want to disable this user?')) {
            return;
        }

        const url = `/azure/users/${userId}/disable`;
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'User has been disabled successfully');
                window.location.reload();
            } else {
                alert(data.message || 'Failed to disable user');
            }
        })
        .catch(error => {
            console.error('Error disabling user:', error);
            alert('Failed to disable user. ' + error.message);
        });
    }

    console.log('Active users page scripts loaded successfully');
</script>
@stop