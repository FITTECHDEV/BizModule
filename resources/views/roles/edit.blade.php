@extends('adminlte::page')

@section('title', '')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <h2>Edit <em>{{ $role->name }}</em> Role</h2>
                </div>
                <div class="card-tools">
                    <a class="btn btn-primary btn-md" href="{{ route('roles.index') }}"> Back</a>

                </div>
            </div>
            <div class="card-body">
                {!! Form::model($role, ['method' => 'PATCH', 'route' => ['roles.update', $role->id]]) !!}
                <div class="row justify-content-center">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                        <div class="form-group">
                           <h2><strong>Role Name:</strong></h2>
                            {!! Form::text('name', null, ['placeholder' => 'Name', 'class' => 'form-control']) !!}
                        </div>
                    {{-- </div>
                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6"> --}}
                        {{-- <div class="form-group"> --}}
                        <h2><strong>Role Permission:</strong></h2>
                            {{-- @foreach ($permission as $value)
                                <label>{{ Form::checkbox('permission[]', $value->id, in_array($value->id, $rolePermissions) ? true : false, ['class' => 'name']) }}
                                    {{ $value->name }}</label>
                                <br />
                            @endforeach --}}
                        {{-- </div> --}}
                        <div class="accordion" id="accordion">
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
                                                            {{ Form::checkbox('permission[]', $value->id, in_array($value->id, $rolePermissions) ? true : false, ['class' => 'name']) }} {{
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
