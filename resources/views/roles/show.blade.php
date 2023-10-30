@extends('adminlte::page')

@section('title', '')

@section('content')
    <div class="container mt-2">
        <div class="card shadow">
            <div class="card-header">
                <div class="card-title">
                    <h2> Show <em>{{ $role->name }}</em> Role</h2>
                </div>
                <div class="card-tools">
                    <a class="btn btn-primary btn-md" href="{{ route('roles.index') }}"> Back</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong>Name:</strong>
                            {{ $role->name }}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong>Permissions:</strong>
                            @if (!empty($rolePermissions))
                            <ul class="list">
                               @foreach ($rolePermissions as $v)
                                    <li class="label label-success">{{ $v->name }}</li>
                                @endforeach 
                            </ul>                                
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('css')

@endsection

@section('js')

@endsection
