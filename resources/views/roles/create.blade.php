@extends('adminlte::page')

@section('title', '')

@section('content_header')
@if (count($errors) > 0)
<div class="alert alert-danger">
    <strong>Whoops!</strong> There were some problems with your input.<br><br>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
@stop

@section('content')
<div class="contaier mt-2">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Create New Role</h2>
                    </div>
                    <div class="card-tools">
                        <a class="btn btn-primary btn-md" href="{{ route('roles.index') }}"> Back</a>
                    </div>
                </div>
                <div class="card-body">
                    {!! Form::open(['route' => 'roles.store', 'method' => 'POST']) !!}
                    <div class="row justify-content-center">
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <h2><strong>Role Name:</strong></h2>
                                {!! Form::text('name', null, ['placeholder' => 'Role Name', 'class' => 'form-control']) !!}
                            </div>
                            <h2><strong>Role Permission:</strong></h2>
                            <div id="accordion">
                                @php
                                $index = 0;
                                @endphp
                                @foreach ($permission->groupBy('module_name') as $key => $module)
                                <div class="card card-default">
                                    <div class="card-header">
                                        <h4 class="card-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#accordion{{ $index }}"
                                                class="collapsed" aria-expanded="false">
                                                {{ $key }} Module
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="accordion{{ $index }}" class="panel-collapse in collapse" style="">
                                        <div class="card-body">
                                            <table>
                                                @forelse ($module->chunk(4) as $chunk)
                                                <tr>
                                                    @foreach ($chunk as $value)
                                                        <td>
                                                            <label>
                                                                {{ Form::checkbox('permission[]', $value->id, false, ['class' => 'name']) }} {{
                                                                ucwords(str_replace('-', ' ',$value->name)) }}
                                                            </label>
                                                        </td>
                                                    @endforeach
                                                   </tr>
                                                @empty

                                                @endforelse
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @php
                                $index++;
                                @endphp
                                @endforeach
                            </div>
                        </div>
                        {{-- <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Permission:</strong>
                                <br />

                                @foreach ($permission->groupBy('module_name') as $key => $module)
                                {{ $key }} <br>
                                @foreach ($module as $value)
                                <label>
                                    {{ Form::checkbox('permission[]', $value->id, false, ['class' => 'name']) }} {{
                                    $value->name }}
                                </label> <br>
                                @endforeach
                                <br />
                                @endforeach
                            </div>
                        </div> --}}

                        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                    {!! Form::close() !!}

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
