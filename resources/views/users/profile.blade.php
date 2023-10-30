@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')

@stop

@section('content')
    <div class="container-fluid p-2">
        <div class="row">
            <div class="col-md-3">

                <!-- Profile Image -->
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            {{-- profile-user-img img-fluid --}}
                            <img class="" src="https://freightintime.com/wp-content/uploads/2019/04/freight-in-time-logo-Edit.gif"
                                alt="User profile picture">
                        </div>

                        <h3 class="profile-username text-center">{{ ucwords($user->full_name) }}</h3>

                        <p class="text-muted text-center">
                            Role(s) <br>
                            @if (!empty($user->getRoleNames()))
                                @foreach ($user->getRoleNames() as $v)
                                    <label class="badge badge-success">{{ $v }}</label>
                                @endforeach
                            @endif
                        </p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Tickets Open</b> <a
                                    class="float-right">{{ $user->tickets->where('Status', 1)->count() }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Tickets Solved</b> <a
                                    class="float-right">{{ $user->tickets->where('Status', 2)->count() }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Ticket Closed</b> <a
                                    class="float-right">{{ $user->tickets->where('Status', 3)->count() }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Ticket Re-opened</b> <a
                                    class="float-right">{{ $user->tickets->where('Status', 4)->count() }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Ticket Settled</b> <a
                                    class="float-right">{{ $user->tickets->where('Status', 5)->count() }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Tickets Raised</b> <a class="float-right">{{ $user->tickets->count() }}</a>
                            </li>
                        </ul>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->


            </div>
            <!-- /.col -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link active" href="#profile" data-toggle="tab">Profile</a></li>
                            <li class="nav-item"><a class="nav-link " href="#settings" data-toggle="tab">Settings</a>

                            {{-- <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Activity</a></li> --}}
                            </li>
                        </ul>
                    </div><!-- /.card-header -->
                    <div class="card-body">
                        <div class="tab-content">
                            {{-- <div class="active tab-pane" id="activity"></div> --}}
                            <div class="active tab-pane" id="profile">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="inputName" class="col-form-label">First Name</label> <br>
                                            <div class="updateAjax" data-name="FirstName" data-type="text"
                                                data-pk="{{ $user->ID }}" data-title="Enter First Name">
                                                {{ $user->FirstName }}</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="inputName" class="col-form-label">Last Name</label> <br>
                                            <div class="updateAjax " data-name="LastName" data-type="text"
                                                data-pk="{{ $user->ID }}" data-title="Enter Last Name">
                                                {{ $user->LastName }}</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="inputName" class="col-form-label">Login ID</label> <br>
                                            <div class="updateAjax " data-name="LoginID" data-type="text"
                                                data-pk="{{ $user->ID }}" data-title="Enter Login ID">
                                                {{ $user->LoginID }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <label for="inputEmail" class="col-form-label">Email : </label>
                                    <div class="updateAjax" data-name="Email" data-type="text"
                                        data-pk="{{ $user->ID }}" data-title="Enter Login ID"> {{ $user->Email }}
                                    </div>
                                </div>
                                <form action="{{ route('user.update.password', [$user]) }}" method="POST" class="form-horizontal">
                                    @csrf
                                    <div class="row  justify-content-center">
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
                                    </div>
                                    <div class="row  justify-content-center">
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
                                    <div class=" row justify-content-center">
                                        <div class="form-group col-xs-6 col-sm-6 col-md-6">
                                            <button type="submit" class="btn btn-outline-danger" >Change Password</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane" id="settings">
                                <label for="">Email Settings</label>
                                <table class="ml-2">
                                    <tr>
                                        <th>
                                            Receive All Emails
                                        </th>
                                        <td>
                                           &nbsp; <input type="checkbox" name="all_emails" id="all_emails">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            Ticket Emails
                                        </th>
                                        <td>
                                           &nbsp; <input type="checkbox" name="all_emails" id="all_emails">
                                        </td>
                                    </tr>
                                </table>

                            </div>

                            <!-- /.tab-pane -->
                        </div>
                        <!-- /.tab-content -->
                    </div><!-- /.card-body -->
                </div>
                <!-- /.nav-tabs-custom -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
@stop

@section('css')
    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/jquery-editable/css/jquery-editable.css"
        rel="stylesheet" />

@stop

@section('js')
<script>
    $.fn.poshytip = {
        defaults: null
    }
</script>
<script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/jquery-editable/js/jquery-editable-poshytip.min.js"></script>
<script>
    $(function() {
        $.fn.editable.defaults.mode = 'inline';

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        $('.updateAjax').editable({
            url: "{{ route('users.update_ajax') }}",
            type: 'text',
            pk: 1,
            name: 'name',
            title: 'Enter name'
        });


    });
</script>
@stop
