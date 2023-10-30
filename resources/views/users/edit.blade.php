@extends('adminlte::page')

@section('title', 'Edit User-Dashboard')

@section('content')
    <div class="container mt-2">
        <div class="card shadow">
            <div class="card-header">
                <div class="card-title">
                    <h2>Edit New User</h2>
                </div>
                <div class="card-tools">
                    @if(Auth::user()->can('users-list'))
                    <a class="btn btn-primary btn-md" href="{{ route('users.index') }}"><i class="fas fa-angle-left"></i> Back</a>
                @endif
                </div>
            </div>
            <div class="card-body">

                {!! Form::model($user, ['method' => 'PATCH', 'route' => ['users.update', $user->ID]]) !!}
                <div class="">
                    <div class="row">
                        <div class="form-group col-xs-6 col-sm-6 col-md-6">
                            <div class="row">
                                <div class="col-md-6 col-xs-6 col-sm-6">
                                    <strong>First Name:</strong>
                                    <input type="text" value="{{ old('FirstName', $user->FirstName) }}" name="FirstName"
                                        id="FirstName" placeholder="First Name"
                                        class="form-control @error('FirstName') is-invalid @enderror">
                                    @error('FirstName')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="col-md-6 col-xs-6 col-sm-6">
                                    <strong>Last Name:</strong>
                                    <input type="text" value="{{ old('LastName', $user->LastName) }}" name="LastName"
                                        id="LastName" placeholder="Last Name"
                                        class="form-control @error('LastName') is-invalid @enderror">
                                    @error('LastName')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                        </div>
                        <div class="form-group col-xs-6 col-sm-6 col-md-6">
                            <div class="row">
                                <div class="col-md-6 col-xs-6 col-sm-6">
                                    <strong>Login ID:</strong>
                                    <input type="text" value="{{ old('LoginID', $user->LoginID) }}" name="LoginID"
                                        id="LoginID" placeholder="Last Name"
                                        class="form-control @error('LoginID') is-invalid @enderror">
                                    @error('LoginID')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="col-md-6 col-xs-6 col-sm-6">
                                    <strong>Role</strong>
                                    <select name="Role" id="Role" class="form-control @error('Role') is-invalid @enderror">
                                        <option value="" disabled selected>Role</option>
                                        <option value=2 @if (old('Role', $user->Role) == 2) selected @endif>User</option>
                                        <option value=1 @if (old('Role', $user->Role) == 1) selected @endif)>Admin</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-xs-6 col-sm-6 col-md-6">
                            <strong>Email:</strong>
                            <input type="email" value="{{ old('Email', $user->Email) }}" name="Email" id="Email"
                                placeholder="Email" class="form-control @error('Email') is-invalid @enderror">
                            @error('Email')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group col-xs-6 col-sm-6 col-md-6">
                            <strong for="OfficeID">Select Office</strong>
                            <select name="OfficeID" id="OfficeID"
                                class="form-control @error('OfficeID') is-invalid @enderror">
                                <option value="" disabled selected>Select Office</option>
                                @forelse ($offices as $office)
                                    <option value="{{ $office->ID }}" @if (old('OfficeID', $user->OfficeID) == $office->ID) selected @endif>{{ $office->Name }}</option>
                                @empty
                                    <option value="" disabled selected>No offices</option>
                                @endforelse
                            </select>
                            @error('OfficeID')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-xs-6 col-sm-6 col-md-6">
                            <strong>Password:</strong>
                            <input type="password" name="password" id="password" placeholder="Password"
                                class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group col-xs-6 col-sm-6 col-md-6">
                            <strong>Confirm Password:</strong>
                            <input type="password" name="confirm-password" id="confirm-password"
                                placeholder="confirm-password"
                                class="form-control @error('confirm-password') is-invalid @enderror">
                            @error('confirm-password')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong>Role:</strong>
                            {!! Form::select('roles[]', $roles, $userRole, ['class' => 'form-control', 'multiple']) !!}
                            @error('roles')
                                <span class="text-danger">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

@endsection

@section('css')

@endsection

@section('js')

@endsection
