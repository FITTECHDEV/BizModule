@extends('adminlte::page')

@section('title', 'Show User-Dashboard')

@section('content')
    <div class="container mt-2">
        <div class="card shadow">
            <div class="card-header">
                <div class="card-title">
                    <h2> Showing <em>{{ ucwords($user->full_name) }}</em></h2>
                </div>
                <div class="card-tools">
                    @if(Auth::user()->can('users-list'))
                        <a class="btn btn-primary btn-md" href="{{ route('users.index') }}"><i class="fas fa-angle-left"></i> Back</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong>First Name:</strong>
                            {{ $user->FirstName }}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong>Last Name:</strong>
                            {{ $user->LastName }}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong>Email:</strong>
                            {{ $user->Email }}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong>Roles:</strong>
                            @if (!empty($user->getRoleNames()))
                                @foreach ($user->getRoleNames() as $v)
                                    <label class="badge badge-success">{{ $v }}</label>
                                @endforeach
                            @endif
                        </div>
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
