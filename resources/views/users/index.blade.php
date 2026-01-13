@extends('adminlte::page')

@section('title', 'Users-Dashboard')

@section('content')
<div class="container-fluid mt-2">
    
    <!-- Statistics Cards Row -->
    <div class="row mb-4">
        <!-- Total Users Card -->
        <div class="col-lg-4 col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="totalUsers">
                        <i class="fas fa-spinner fa-spin"></i>
                    </h3>
                    <p>Total Users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <!-- Active Users Card -->
        <div class="col-lg-4 col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="activeUsers">
                        <i class="fas fa-spinner fa-spin"></i>
                    </h3>
                    <p>Active Users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>

        <!-- Inactive Users Card -->
        <div class="col-lg-4 col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="inactiveUsers">
                        <i class="fas fa-spinner fa-spin"></i>
                    </h3>
                    <p>Inactive Users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-times"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card shadow">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">
                    <h2 class="mb-0">Users Management</h2>
                </div>
                {{-- <div class="card-tools">
                    @if (Auth::user()->can('users-list'))
                        <a class="btn btn-md btn-success" href="{{ route('users.create') }}">
                            <i class="fas fa-plus"></i> Create New User
                        </a>
                    @endif
                </div> --}}
            </div>
        </div>
        <div class="card-body">
            <table class="table table-sm table-bordered table-hover display text-sm" style="width:100%" id="users-table">
                <thead class="thead-light">
                    <tr>
                        <th>LoginID</th>
                        <th>FullName</th>
                        <th>Email</th>
                        <th>Office</th>
                        <th>Roles</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('css')
<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/jquery-editable/css/jquery-editable.css" rel="stylesheet"/>

<style>
    /* Card styling */
    .small-box {
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,.08), 0 4px 8px rgba(0,0,0,.06);
        display: block;
        margin-bottom: 0;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .small-box:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,.12), 0 8px 16px rgba(0,0,0,.1);
        transform: translateY(-2px);
    }

    .small-box > .inner {
        padding: 20px;
    }

    .small-box h3 {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0 0 10px 0;
        padding: 0;
        white-space: nowrap;
        color: white;
    }

    .small-box p {
        font-size: 1.1rem;
        margin: 0;
        color: rgba(255,255,255,0.9);
        font-weight: 500;
    }

    .small-box .icon {
        color: rgba(255,255,255,.2);
        font-size: 80px;
        position: absolute;
        right: 15px;
        top: 15px;
        transition: all .3s ease;
    }

    .small-box:hover .icon {
        transform: scale(1.1) rotate(5deg);
        color: rgba(255,255,255,.3);
    }

    /* DataTable enhancements */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }

    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 0.375rem 0.75rem;
    }

    /* Table styling */
    #users-table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    #users-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Card header improvements */
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom: none;
    }

    .card-header h2 {
        color: white;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .card-header .btn-success {
        background: white;
        color: #667eea;
        border: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .card-header .btn-success:hover {
        background: #f8f9fa;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Spacing improvements */
    .mb-4 {
        margin-bottom: 2rem !important;
    }

    .card {
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,.08), 0 4px 8px rgba(0,0,0,.06);
    }
</style>
@endsection

@section('js')
<script>
    $(function() {
        // Fetch user statistics
        fetchUserStats();

        // Initialize DataTable
        $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            searching: true,
            stateSave: true,
            ajax: '{!! route('datatables.users') !!}',
            columns: [
                { data: 'LoginID', name: 'LoginID' },
                { data: 'FullName', name: 'FullName' },
                { data: 'Email', name: 'Email' },
                { data: 'Office', name: 'Office' },
                { data: 'Roles', name: 'Roles' },
                { data: 'Action', name: 'Action', orderable: false, searchable: false }
            ],
            language: {
                search: "Search users:",
                lengthMenu: "Show _MENU_ users per page",
                info: "Showing _START_ to _END_ of _TOTAL_ users",
                infoEmpty: "No users found",
                infoFiltered: "(filtered from _MAX_ total users)",
                zeroRecords: "No matching users found",
                emptyTable: "No users available"
            },
            pageLength: 25,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
        });
    });

    // Function to fetch user statistics
    function fetchUserStats() {
        console.log('🔄 Fetching user stats...');
        
        $.ajax({
            url: '{{ route("bizpro.users.stats") }}',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log('✅ Stats received:', data);
                
                // Update card values with animation
                animateValue('totalUsers', 0, data.total || 0, 1000);
                animateValue('activeUsers', 0, data.active || 0, 1000);
                animateValue('inactiveUsers', 0, data.inactive || 0, 1000);
            },
            error: function(xhr, status, error) {
                console.error('❌ Failed to fetch user stats:', error);
                console.error('Response:', xhr.responseText);
                
                // Show error state
                $('#totalUsers').html('<i class="fas fa-exclamation-triangle"></i>');
                $('#activeUsers').html('<i class="fas fa-exclamation-triangle"></i>');
                $('#inactiveUsers').html('<i class="fas fa-exclamation-triangle"></i>');
            }
        });
    }

    // Animate number counting
    function animateValue(elementId, start, end, duration) {
        const element = document.getElementById(elementId);
        const range = end - start;
        const increment = range / (duration / 16); // 60 FPS
        let current = start;

        const timer = setInterval(function() {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                current = end;
                clearInterval(timer);
            }
            element.textContent = Math.round(current);
        }, 16);
    }
</script>
@endsection