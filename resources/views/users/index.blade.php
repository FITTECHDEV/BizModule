@extends('adminlte::page')

@section('title', 'Users-Dashboard')

@section('content')
<div class="container mt-2">
    <div class="card shadow">
        <div class="card-header">
            <div class="card-title">
                <h2>Users Management</h2>
            </div>
            <div class="card-tools">
                @if (Auth::user()->can('users-list'))
                    <a class="btn btn-md btn-outline-success" href="{{ route('users.create') }}"><i
                            class="fas fa-plus"></i> Create New User</a>
                @endif
            </div>
        </div>
        <div class="card-body">
            <table class="table table-sm table-bordered display text-sm" style="width:100%" id="users-table">
                <thead>
                    <th>LoginID</th>
                    <th>FullName</th>
                    <th>Email</th>
                    <th>Office</th>
                    <th>Roles</th>
                    <th>Action</th>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>



{{-- {!! $data->render() !!} --}}

@endsection

@section('css')
{{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" /> --}}
{{-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script> --}}
<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/jquery-editable/css/jquery-editable.css" rel="stylesheet"/>

@endsection

@section('js')
{{-- <script>$.fn.poshytip={defaults:null}</script> --}}
{{-- <script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/jquery-editable/js/jquery-editable-poshytip.min.js"></script> --}}

<script>
    $(function() {
        $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            "searching": true,
            stateSave: true,
            ajax: '{!! route('datatables.users') !!}',
            columns: [{
                        data: 'LoginID',
                        name: 'LoginID'
                    },
                    {
                        data: 'FullName',
                        name: 'FullName'
                    },
                    {
                        data: 'Email',
                        name: 'Email'
                    },
                    {
                        data: 'Office',
                        name: 'Office'
                    },
                    {
                        data: 'Roles',
                        name: 'Roles'
                    },
                    {
                        data: 'Action',
                        name: 'Action',
                        orderable: false,
                        searchable: false
                    }
            ]
        });

        // $.fn.editable.defaults.mode = 'inline';

        // $.ajaxSetup({
        //     headers: {
        //         'X-CSRF-TOKEN': '{{csrf_token()}}'
        //     }
        // });

        // $('.updateAjax').editable({
        //     url: "{{ route('users.update_ajax') }}",
        //     type: 'text',
        //     pk: 1,
        //     name: 'name',
        //     title: 'Enter name'
        // });
    });
</script>
@endsection
