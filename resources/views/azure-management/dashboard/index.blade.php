@extends('azure-management.layouts.app')

@section('title', 'User Management Dashboard')


@push('styles')
<style>
    /* Dashboard Header Styles */
    .dashboard-header {
        background: linear-gradient(135deg, var(--primary-color), #2980b9);
        color: white;
        padding: 20px 30px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 0;
        z-index: 100;
        margin: -28px -28px 20px;
    }

    .header-content {
        max-width: 1400px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .header-logo {
        font-size: 2em;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        backdrop-filter: blur(10px);
    }

    .header-title-group h1 {
        margin: 0;
        font-size: 1.8em;
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    .header-subtitle {
        margin: 4px 0 0 0;
        font-size: 0.9em;
        opacity: 0.9;
        font-weight: 400;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .header-user {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 25px;
        backdrop-filter: blur(10px);
    }

    .header-user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }

    .header-user-info {
        display: flex;
        flex-direction: column;
        line-height: 1.3;
    }

    .header-user-name {
        font-weight: 600;
        font-size: 14px;
    }

    .header-user-role {
        font-size: 12px;
        opacity: 0.8;
    }

    .main-content {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: var(--light-bg);
        backdrop-filter: blur(10px);
        border-radius: var(--radius);
        padding: 20px;
        box-shadow: var(--shadow-sm);
        text-align: center;
        transition: var(--transition);
        border: 1px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color), var(--success));
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .stat-card:hover::before {
        transform: scaleX(1);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .stat-icon {
        font-size: 2.5em;
        margin-bottom: 12px;
        opacity: 0.9;
    }

    .stat-number {
        font-size: 2em;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 4px;
        line-height: 1;
    }

    .stat-label {
        color: var(--muted);
        font-weight: 500;
        font-size: 0.9em;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .users-table-container {
        background: var(--light-bg);
        backdrop-filter: blur(10px);
        border-radius: var(--radius);
        padding: 20px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .table-header {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid var(--border-color);
    }

    .table-title {
        font-size: 1.3em;
        font-weight: 600;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .table-controls {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
    }

    .table-controls-left {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        flex: 1;
    }

    .search-bar {
        position: relative;
        min-width: 200px;
        flex: 1;
        max-width: 300px;
    }

    .search-bar i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--muted);
        z-index: 10;
        pointer-events: none;
    }

    .search-bar input {
        width: 100%;
        padding: 12px 15px 12px 45px;
        border: 2px solid var(--border-color);
        border-radius: 25px;
        font-size: 14px;
        background: white;
        transition: var(--transition);
        outline: none;
    }

    .search-bar input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .filter-select {
        padding: 10px 15px;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        background: white;
        min-width: 150px;
        transition: var(--transition);
        outline: none;
    }

    .filter-select:focus {
        border-color: var(--primary-color);
    }

    .table-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }

    .users-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
        background: white;
    }

    .users-table th,
    .users-table td {
        padding: 16px 12px;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
        font-size: 14px;
        vertical-align: middle;
    }

    .users-table th {
        background: var(--hover-bg);
        font-weight: 600;
        color: var(--text);
        position: sticky;
        top: 0;
        z-index: 10;
        white-space: nowrap;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }

    .users-table tbody tr {
        transition: var(--transition);
    }

    .users-table tbody tr:hover {
        background: var(--hover-bg);
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), #2980b9);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        margin-right: 12px;
        font-size: 14px;
        flex-shrink: 0;
    }

    .user-info {
        display: flex;
        align-items: center;
        min-width: 200px;
    }

    .user-details {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .user-name {
        font-weight: 600;
        color: var(--text);
        font-size: 14px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75em;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
        min-width: 70px;
        text-align: center;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .status-inactive {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .actions-cell {
        display: flex;
        gap: 6px;
        flex-wrap: nowrap;
        min-width: 180px;
    }

    .btn.btn-sm {
        padding: 8px 12px;
        font-size: 12px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        touch-action: manipulation;
    }

    .btn.btn-sm:hover {
        transform: translateY(-1px);
    }

    .btn.btn-sm:active {
        transform: translateY(0);
    }

    .btn.btn-sm:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    .btn-success {
        background: var(--success);
        color: white;
    }

    .btn-success:hover:not(:disabled) {
        background: #229954;
    }

    .btn-danger {
        background: var(--danger);
        color: white;
    }

    .btn-danger:hover:not(:disabled) {
        background: #c0392b;
    }

    .btn-warning {
        background: var(--warning);
        color: white;
    }

    .btn-warning:hover:not(:disabled) {
        background: #e67e22;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 1000;
        overflow-y: auto;
        padding: 20px;
        backdrop-filter: blur(4px);
    }

    .modal-content {
        background: white;
        margin: 5% auto;
        padding: 24px;
        border-radius: var(--radius);
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid var(--border-color);
    }

    .modal-header h3 {
        font-size: 1.4em;
        font-weight: 600;
        color: var(--text);
        margin: 0;
    }

    .close {
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        color: var(--muted);
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: var(--transition);
    }

    .close:hover {
        background: var(--hover-bg);
        color: var(--danger);
    }

    .user-details-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin: 20px 0;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        padding: 12px;
        background: var(--hover-bg);
        border-radius: 6px;
        border-left: 4px solid var(--primary-color);
    }

    .detail-label {
        font-weight: 600;
        color: var(--muted);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .detail-value {
        font-size: 14px;
        color: var(--text);
        font-weight: 500;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--muted);
    }

    .empty-state i {
        font-size: 3em;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .btn.btn-primary {
        background: var(--primary-color);
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: var(--transition);
        border: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .btn.btn-primary:hover {
        background: #2980b9;
        transform: translateY(-2px);
    }

    /* Pagination Styles */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 24px;
        padding: 20px;
        flex-wrap: wrap;
    }

    .pagination-info {
        color: var(--muted);
        font-size: 14px;
        margin: 0 16px;
        white-space: nowrap;
    }

    .pagination-btn {
        padding: 8px 16px;
        border: 2px solid var(--border-color);
        background: white;
        color: var(--text);
        border-radius: 6px;
        cursor: pointer;
        transition: var(--transition);
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
        min-height: 38px;
    }

    .pagination-btn:hover:not(:disabled) {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
        transform: translateY(-1px);
    }

    .pagination-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .pagination-btn.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .pagination-numbers {
        display: flex;
        gap: 4px;
    }

    .page-number {
        min-width: 38px;
        height: 38px;
        padding: 8px;
        border: 2px solid var(--border-color);
        background: white;
        color: var(--text);
        border-radius: 6px;
        cursor: pointer;
        transition: var(--transition);
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .page-number:hover {
        background: var(--hover-bg);
        border-color: var(--primary-color);
    }

    .page-number.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .page-ellipsis {
        padding: 8px 12px;
        color: var(--muted);
        display: flex;
        align-items: center;
    }

    @media (max-width: 1024px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .users-table th,
        .users-table td {
            padding: 12px 8px;
            font-size: 13px;
        }
        
        .user-details-grid {
            grid-template-columns: 1fr;
        }

        .actions-cell {
            min-width: 170px;
        }
    }

    @media (max-width: 768px) {
        .dashboard-header {
            padding: 16px 20px;
        }

        .header-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .header-logo {
            width: 40px;
            height: 40px;
            font-size: 1.5em;
        }

        .header-title-group h1 {
            font-size: 1.4em;
        }

        .header-user {
            align-self: stretch;
        }

        .main-content {
            padding: 16px;
        }

        .stats-grid {
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .stat-card {
            padding: 16px;
        }

        .stat-icon {
            font-size: 2em;
        }

        .stat-number {
            font-size: 1.6em;
        }

        .table-header {
            gap: 12px;
        }

        .table-controls {
            flex-direction: column;
            align-items: stretch;
        }

        .table-controls-left {
            justify-content: stretch;
        }

        .search-bar {
            max-width: none;
        }

        .filter-select {
            min-width: 100%;
        }

        .btn.btn-primary {
            align-self: flex-end;
            width: fit-content;
        }

        .users-table th,
        .users-table td {
            padding: 10px 6px;
            font-size: 12px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            font-size: 12px;
            margin-right: 8px;
        }

        .actions-cell {
            min-width: 160px;
        }

        .btn.btn-sm {
            min-width: 32px;
            height: 32px;
            padding: 6px 8px;
        }

        .modal-content {
            margin: 10px auto;
            padding: 16px;
        }

        .user-details-grid {
            gap: 12px;
            grid-template-columns: 1fr;
        }

        .pagination {
            gap: 6px;
            padding: 16px;
        }

        .pagination-info {
            width: 100%;
            text-align: center;
            margin: 8px 0;
        }

        .pagination-btn,
        .page-number {
            min-width: 36px;
            height: 36px;
            padding: 6px 10px;
            font-size: 13px;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .stat-card {
            padding: 12px;
        }

        .stat-icon {
            font-size: 1.8em;
        }

        .stat-number {
            font-size: 1.4em;
        }

        .stat-label {
            font-size: 0.8em;
        }

        .users-table {
            min-width: 650px;
        }

        .users-table th,
        .users-table td {
            padding: 8px 4px;
            font-size: 11px;
        }

        .user-avatar {
            width: 28px;
            height: 28px;
            font-size: 10px;
            margin-right: 6px;
        }

        .user-info {
            min-width: 140px;
        }

        .actions-cell {
            min-width: 150px;
            gap: 4px;
        }

        .btn.btn-sm {
            min-width: 28px;
            height: 28px;
            padding: 4px 6px;
            font-size: 11px;
        }

        .modal-content {
            margin: 5px auto;
            padding: 12px;
            width: 95%;
        }
    }
</style>
@endpush

@section('content')
<!-- Dashboard Header -->
<header class="dashboard-header">
    <div class="header-content">
        <div class="header-left">
            <div class="header-logo">
                <i class="fas fa-users-cog"></i>
            </div>
            <div class="header-title-group">
                <h1>User Management</h1>
                <p class="header-subtitle">Manage and monitor all system users</p>
            </div>
        </div>
        <div class="header-right">
            <div class="header-user">
                <div class="header-user-avatar">AD</div>
                <div class="header-user-info">
                    <div class="header-user-name">Admin User</div>
                    <div class="header-user-role">Administrator</div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Main Content -->
<div class="main-content">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="color: var(--primary-color);">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-number" id="activeUsers">{{ $stats['active'] ?? 0 }}</div>
            <div class="stat-label">Active Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="color: var(--danger);">
                <i class="fas fa-user-times"></i>
            </div>
            <div class="stat-number" id="inactiveUsers">{{ $stats['inactive'] ?? 0 }}</div>
            <div class="stat-label">Inactive</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="color: var(--warning);">
                <i class="fas fa-user-clock"></i>
            </div>
            <div class="stat-number" id="pendingUsers">{{ $stats['pending'] ?? 0 }}</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="color: var(--success);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-number" id="totalUsers">{{ $stats['total'] ?? 0 }}</div>
            <div class="stat-label">Total Users</div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="users-table-container">
        <div class="table-header">
            
            <div class="table-controls">
    <div class="table-controls-left">
        <select id="statusFilter" class="filter-select">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="pending">Pending</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
    <div class="table-controls-right" style="display:flex;gap:12px;align-items:center;">
        <div style="position:relative;width:200px;">
            <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#999;font-size:14px;"></i>
            <input type="text" 
                    id="searchUsers" 
                    placeholder="Search users..." 
                    autocomplete="off" 
                    style="width:100%;padding:10px 12px 10px 36px;border:2px solid #e1e8ed;border-radius:8px;font-size:14px;outline:none;">
        </div>
        <a href="{{ route('azure.users.create') }}" 
           class="btn btn-primary" 
           style="height:38px;display:inline-flex;align-items:center;padding:0 16px;white-space:nowrap;">
            <i class="fas fa-user-plus" style="font-size:14px;"></i>
            <span style="margin-left:6px;">Add User</span>
        </a>
    </div>
</div>
        </div>
        <div class="table-wrapper">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Login ID</th>
                        <th>Employee ID</th>
                        <th>Group</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
    @forelse($users as $user)
        <tr>
            <td>
                <div class="user-info">
                    <div class="user-avatar">{{ strtoupper(substr($user->displayName ?? 'NA', 0, 2)) }}</div>
                    <div class="user-details">
                        <div class="user-name">{{ $user->displayName ?? 'N/A' }}</div>
                    </div>
                </div>
            </td>
            <td>{{ $user->mail ?? $user->userPrincipalName ?? 'N/A' }}</td>
            <td>{{ $user->userPrincipalName ?? 'N/A' }}</td>
            <td>{{ $user->EmployeCode ?? 'N/A' }}</td>
            <td>{{ $user->department ?? $user->companyName ?? 'N/A' }}</td>
            <td>
                @php
                    $status = $user->Status ?? 'Unknown';
                    $badgeClass = match(strtolower($status)) {
                        'active' => 'status-active',
                        'pending' => 'status-pending',
                        'inactive' => 'status-inactive',
                        default => 'status-inactive'
                    };
                @endphp
                <span class="status-badge {{ $badgeClass }}">
                    {{ $status }}
                </span>
            </td>
            <td class="actions-cell">
                <button class="btn btn-sm btn-primary" onclick="viewUser('{{ $user->Id }}')" 
                        title="View User">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-warning" onclick="editUser('{{ $user->Id }}')" 
                        title="Edit User">
                    <i class="fas fa-edit"></i>
                </button>
                @if($user->Status === 'Active')
                    <button class="btn btn-sm btn-danger" onclick="disableUser('{{ $user->Id }}')" 
                            title="Disable User">
                        <i class="fas fa-user-slash"></i>
                    </button>
                @else
                    <button class="btn btn-sm btn-success" onclick="enableUser('{{ $user->Id }}')" 
                            title="Enable User">
                        <i class="fas fa-user-check"></i>
                    </button>
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="empty-state">
                <i class="fas fa-users"></i>
                <div>No users found</div>
            </td>
        </tr>
    @endforelse
</tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <button class="pagination-btn" id="firstPageBtn" onclick="goToPage(1)" aria-label="First page">
                <i class="fas fa-angle-double-left"></i>
                <span class="btn-text">First</span>
            </button>
            <button class="pagination-btn" id="prevPageBtn" onclick="goToPage(currentPage - 1)" aria-label="Previous page">
                <i class="fas fa-angle-left"></i>
                <span class="btn-text">Previous</span>
            </button>
            
            <div class="pagination-numbers" id="paginationNumbers">
                <!-- Page numbers will be inserted here -->
            </div>

            <button class="pagination-btn" id="nextPageBtn" onclick="goToPage(currentPage + 1)" aria-label="Next page">
                <span class="btn-text">Next</span>
                <i class="fas fa-angle-right"></i>
            </button>
            <button class="pagination-btn" id="lastPageBtn" onclick="goToPage(totalPages)" aria-label="Last page">
                <span class="btn-text">Last</span>
                <i class="fas fa-angle-double-right"></i>
            </button>

            <div class="pagination-info" id="paginationInfo">
                Showing 0 - 0 of 0 users
            </div>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div id="viewUserModal" class="modal" role="dialog" aria-labelledby="viewUserModalTitle" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="viewUserModalTitle">User Details</h3>
            <button class="close" onclick="closeModal('viewUserModal')" aria-label="Close modal">&times;</button>
        </div>
        <div id="userDetailsContent">
            <!-- User details will be populated here -->
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal" role="dialog" aria-labelledby="editUserModalTitle" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="editUserModalTitle">Edit User</h3>
            <button class="close" onclick="closeModal('editUserModal')" aria-label="Close modal">&times;</button>
        </div>
        <div id="editUserContent">
            <!-- Edit form will be populated here -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentPage = 1;
    let totalPages = 1;
    let perPage = 10;
    let totalUsers = 0;
    let filters = {
        search: '',
        status: ''
    };

    document.addEventListener('DOMContentLoaded', function() {
        // loadUsers();
        setupEventListeners();
        setupAccessibility();
    });

    function setupEventListeners() {
        const searchInput = document.getElementById('searchUsers');
        searchInput.addEventListener('input', debounce(function(e) {
            filters.search = e.target.value.trim();
            currentPage = 1;
            loadUsers();
        }, 300));

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                e.target.value = '';
                filters.search = '';
                currentPage = 1;
                loadUsers();
            }
        });

        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', function(e) {
                filters.status = e.target.value;
                currentPage = 1;
                loadUsers();
            });
        }

        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                closeModal(e.target.id);
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const openModals = document.querySelectorAll('.modal[style*="block"]');
                openModals.forEach(modal => {
                    closeModal(modal.id);
                });
            }
        });
    }

    function setupAccessibility() {
        const liveRegion = document.createElement('div');
        liveRegion.setAttribute('aria-live', 'polite');
        liveRegion.setAttribute('aria-atomic', 'true');
        liveRegion.style.position = 'absolute';
        liveRegion.style.left = '-10000px';
        liveRegion.style.width = '1px';
        liveRegion.style.height = '1px';
        liveRegion.style.overflow = 'hidden';
        liveRegion.id = 'liveRegion';
        document.body.appendChild(liveRegion);
    }

    function announceToScreenReader(message) {
        const liveRegion = document.getElementById('liveRegion');
        if (liveRegion) {
            liveRegion.textContent = message;
        }
    }

    function loadUsers() {
        showLoading();
        
        const params = new URLSearchParams({
            page: currentPage,
            per_page: perPage,
            search: filters.search,
            status: filters.status
        });

        fetch(`/api/users?${params}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            totalPages = data.last_page;
            totalUsers = data.total;

            updateUsersTable(data.data);
            updatePagination({
                from: ((data.current_page - 1) * data.per_page) + 1,
                to: Math.min(data.current_page * data.per_page, data.total),
                total: data.total,
                current_page: data.current_page,
                last_page: data.last_page
            });
            
            updateStats(data.stats || {});
            
            hideLoading();
            announceToScreenReader('Users table loaded successfully');
        })
        .catch(error => {
            handleError(error);
        });
    }

    function updateStats(stats) {
        if (stats.active !== undefined) {
            document.getElementById('activeUsers').textContent = stats.active;
        }
        if (stats.inactive !== undefined) {
            document.getElementById('inactiveUsers').textContent = stats.inactive;
        }
        if (stats.pending !== undefined) {
            document.getElementById('pendingUsers').textContent = stats.pending;
        }
        if (stats.total !== undefined) {
            document.getElementById('totalUsers').textContent = stats.total;
        }
    }

    function updatePagination(response) {
        const from = response.from || 0;
        const to = response.to || 0;
        const total = response.total || 0;
        document.getElementById('paginationInfo').textContent = 
            `Showing ${from} - ${to} of ${total} users`;

        const firstBtn = document.getElementById('firstPageBtn');
        const prevBtn = document.getElementById('prevPageBtn');
        const nextBtn = document.getElementById('nextPageBtn');
        const lastBtn = document.getElementById('lastPageBtn');

        firstBtn.disabled = currentPage === 1;
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;
        lastBtn.disabled = currentPage === totalPages;

        renderPageNumbers();
    }

    function renderPageNumbers() {
        const paginationNumbers = document.getElementById('paginationNumbers');
        paginationNumbers.innerHTML = '';

        const maxVisiblePages = window.innerWidth < 768 ? 3 : 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

        if (endPage - startPage < maxVisiblePages - 1) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        if (startPage > 1) {
            addPageNumber(1);
            if (startPage > 2) {
                addEllipsis();
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            addPageNumber(i);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                addEllipsis();
            }
            addPageNumber(totalPages);
        }
    }

    function addPageNumber(pageNum) {
        const paginationNumbers = document.getElementById('paginationNumbers');
        const button = document.createElement('button');
        button.className = 'page-number' + (pageNum === currentPage ? ' active' : '');
        button.textContent = pageNum;
        button.onclick = () => goToPage(pageNum);
        button.setAttribute('aria-label', `Go to page ${pageNum}`);
        button.setAttribute('aria-current', pageNum === currentPage ? 'page' : 'false');
        paginationNumbers.appendChild(button);
    }

    function addEllipsis() {
        const paginationNumbers = document.getElementById('paginationNumbers');
        const span = document.createElement('span');
        span.className = 'page-ellipsis';
        span.textContent = '...';
        span.setAttribute('aria-hidden', 'true');
        paginationNumbers.appendChild(span);
    }

    function goToPage(page) {
        if (page < 1 || page > totalPages || page === currentPage) {
            return;
        }
        currentPage = page;
        loadUsers();
        window.scrollTo({ top: 0, behavior: 'smooth' });
        announceToScreenReader(`Navigated to page ${page}`);
    }

    function updateUsersTable(users) {
        const tbody = document.getElementById('usersTableBody');
       
        if (!users || users.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="empty-state">
                        <i class="fas fa-users"></i>
                        <div>No users found</div>
                    </td>
                </tr>
            `;
            return;
        }
        tbody.innerHTML = users.map(user => {
            const statusButton = user.status === 'active' 
                ? `<button class="btn btn-sm btn-danger" onclick="toggleUserStatus('${user.id}', 'disable')"
                        title="Disable User" aria-label="Disable user ${escapeHtml(user.name)}">
                        <i class="fas fa-user-slash"></i>
                    </button>`
                : `<button class="btn btn-sm btn-success" onclick="toggleUserStatus('${user.id}', 'enable')"
                        title="Enable User" aria-label="Enable user ${escapeHtml(user.name)}">
                        <i class="fas fa-user-check"></i>
                    </button>`;

            return `
                <tr>
                    <td>
                        <div class="user-info">
                            <div class="user-avatar" title="${user.name}">${user.name.substring(0, 2).toUpperCase()}</div>
                            <div class="user-details">
                                <div class="user-name">${escapeHtml(user.name)}</div>
                            </div>
                        </div>
                    </td>
                    <td title="${user.email || 'N/A'}">${escapeHtml(user.email || 'N/A')}</td>
                    <td>${escapeHtml(user.login_id || 'N/A')}</td>
                    <td>${escapeHtml(user.employee_id)}</td>
                    <td>${escapeHtml(user.group || 'N/A')}</td>
                    <td>
                        <span class="status-badge status-${user.status}" title="Status: ${user.status}">
                            ${user.status.charAt(0).toUpperCase() + user.status.slice(1)}
                        </span>
                    </td>
                    <td class="actions-cell">
                        <button class="btn btn-sm btn-primary" onclick="viewUser('${user.id}')"
                                title="View User" aria-label="View user ${escapeHtml(user.name)}">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="editUser('${user.id}')"
                                title="Edit User" aria-label="Edit user ${escapeHtml(user.name)}">
                            <i class="fas fa-edit"></i>
                        </button>
                        ${statusButton}
                    </td>
                </tr>
            `;
        }).join('');
    }

    function toggleUserStatus(userId, action) {
        const actionText = action === 'enable' ? 'enable' : 'disable';
        const actionTitle = action === 'enable' ? 'Enable User' : 'Disable User';
        const actionMessage = action === 'enable'
            ? 'Are you sure you want to enable this user? This will restore access to all systems.'
            : 'Are you sure you want to disable this user? This will revoke access to all systems.';
        
        confirmAction(actionTitle, actionMessage, function() {
            showLoading();
            
            axios.post(`/api/users/${userId}/${actionText}`, {}, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.data.success) {
                    const statusMessage = action === 'enable' 
                        ? 'User has been enabled successfully' 
                        : 'User has been disabled successfully';
                    showAlert('success', action === 'enable' ? 'User Enabled' : 'User Disabled', response.data.message || statusMessage);
                    
                    setTimeout(() => {
                        loadUsers();
                    }, 1000);
                } else {
                    showAlert('error', 'Error', response.data.message || `Failed to ${actionText} user`);
                }
                hideLoading();
            })
            .catch(error => {
                console.error(`Error ${actionText}ing user:`, error);
                hideLoading();
                const errorMessage = error.response?.data?.message || `Failed to ${actionText} user: ` + error.message;
                showAlert('error', 'Error', errorMessage);
            });
        });
    }

    function viewUser(userId) {
        showLoading();
        
        setTimeout(() => {
            try {
                const mockUser = {
                    id: userId,
                    name: 'John Doe',
                    email: 'john.doe@example.com',
                    login_id: 'jdoe',
                    employee_id: 'EMP001',
                    group: 'Administrators',
                    status: 'active',
                    created_at: new Date().toISOString(),
                    last_login: new Date().toISOString(),
                    department: 'IT',
                    role: 'Developer'
                };

                displayUserDetails(mockUser);
                hideLoading();
            } catch (error) {
                handleError(error);
            }
        }, 300);
    }

    function displayUserDetails(user) {
        const content = `
            <div class="user-details-grid">
                <div class="detail-item">
                    <div class="detail-label">Name</div>
                    <div class="detail-value">${escapeHtml(user.name)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Email</div>
                    <div class="detail-value">${escapeHtml(user.email)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Login ID</div>
                    <div class="detail-value">${escapeHtml(user.login_id || 'N/A')}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Employee ID</div>
                    <div class="detail-value">${escapeHtml(user.employee_id)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Group</div>
                    <div class="detail-value">${escapeHtml(user.group || 'N/A')}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Department</div>
                    <div class="detail-value">${escapeHtml(user.department || 'N/A')}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Role</div>
                    <div class="detail-value">${escapeHtml(user.role || 'N/A')}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Last Login</div>
                    <div class="detail-value">${user.last_login ? new Date(user.last_login).toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    }) : 'Never'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">
                        <span class="status-badge status-${user.status}">
                            ${user.status.charAt(0).toUpperCase() + user.status.slice(1)}
                        </span>
                    </div>
                </div>
            </div>
        `;
        
        document.getElementById('userDetailsContent').innerHTML = content;
        openModal('viewUserModal');
        announceToScreenReader(`User details loaded for ${user.name}`);
    }

    function editUser(userId) {
        window.location.href = `azure/users/${userId}/edit`;
    }

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            modal.setAttribute('aria-hidden', 'false');
            
            const firstFocusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (firstFocusable) {
                firstFocusable.focus();
            }
            
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            
            const searchInput = document.getElementById('searchUsers');
            if (searchInput) {
                searchInput.focus();
            }
        }
    }

    function handleError(error) {
        console.error('Error:', error);
        hideLoading();
        showAlert('error', 'Error', error.message || 'An unexpected error occurred');
        announceToScreenReader('An error occurred. Please try again.');
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text || '').replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    window.UserDashboard = {
        loadUsers,
        viewUser,
        editUser,
        goToPage,
        toggleUserStatus
    };
</script>
@endpush