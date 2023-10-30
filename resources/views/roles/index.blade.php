@extends('adminlte::page')

@section('title', 'Role Management')

@section('content')
    <div class="container mt-2">
        <div class="card shadow">
            <div class="card-header">
                <div class="card-title">
                    <h2>Role Management</h2>
                </div>
                <div class="card-tools">
                    @can('role-create')
                        <a class="btn btn-outline-success btn-md" href="{{ route('roles.create') }}">
                            <i class="fas fa-plus"></i> Create New Role
                        </a>
                    @endcan
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-sm" id="roles-table">
                    <thead>
                        <th>No</th>
                        <th>Name</th>
                        <th>Action</th>
                    </thead>
                    <tbody>
                        @foreach ($roles as $key => $role)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td>{{ $role->name }}</td>
                                <td>
                                    <a class="btn btn-info btn-xs" href="{{ route('roles.show', $role->id) }}">Show</a>
                                    @can('role-edit')
                                        <a class="btn btn-primary btn-xs" href="{{ route('roles.edit', $role->id) }}">Edit</a>
                                    @endcan
                                    @can('role-delete')
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['roles.destroy', $role->id], 'style' => 'display:inline']) !!}
                                        {!! Form::submit('Delete', ['class' => 'btn btn-danger btn-xs', 'onclick' => 'return confirm("Are you sure ?")']) !!}
                                        {!! Form::close() !!}
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    {!! $roles->render() !!}
@endsection

@section('css')

@endsection

@section('js')
    <script>
        $(function() {
            $('#roles-table').DataTable({

            })
        })
    </script>
@endsection
