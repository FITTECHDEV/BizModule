@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')

@stop

@section('content')
    <div class="container mt-1">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <a href="{{ url('/') }}" class="btn btn-primary float-right btn-sm"><i class="fas fa-arrow-left"></i>  Back</a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 border-right border-dark">
                                <h5 class="text-center">Ticket Details</h5>
                                <ul class="list-group">
                                    <li class="list-group-item">Ticket No: {{ $ticket->TicketNo }}</li>
                                    <li class="list-group-item">Title: {{ $ticket->Title }}</li> 
                                    <li class="list-group-item">Created By: {{ $ticket->CreatedBy }}</li> 
                                    <li class="list-group-item">Created: {{ $ticket->CreatedOn->diffForHumans() }}</li> 
                                    <li class="list-group-item">
                                        <table class="table table-sm">
                                            <thead>
                                                <th>Solution</th>
                                                <th>Category</th>
                                                <th>Sub-Category</th>
                                                <th>Priority</th>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>{{ $ticket->SolutionName }}</td>
                                                    <td>{{ $ticket->ModuleName }}</td>
                                                    <td>{{ $ticket->SubModuleName }}</td>
                                                    <td>{{ $ticket->Priority }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </li>
                                    <li class="list-group-item">Description: {{ $ticket->Description }}</li>
                                    {{-- <li>Office : {{ $ticket->user()->full_name }}</li> --}}
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5 class="text-center">Assign</h5>
                                <form action="{{ route('tickets.assign_ticket_user') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="TicketID" value="{{ $ticket->Id }}">
                                    <div class="form-group">
                                        <label for="AssignedTo">Select User <span class="text-danger">*</span> </label>
                                        <select name="AssignedTo" id="AssignedTo" class="form-control @error('AssignedTo') is-invalid @enderror">
                                            <option value="" selected disabled>Select User</option>
                                            @forelse($admin_users as $admin_user)
                                                <option value="{{ $admin_user->ID }}">{{ $admin_user->full_name }}</option>
                                            @empty
                                                <option value="" disabled>No Admins</option>
                                            @endforelse
                                            }
                                        </select>
                                        @error('AssignedTo')
                                            <span class="invalid-feedback">
                                                <strong> {{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="Comment">Comment <span class="text-danger">*</span></label>
                                        <textarea name="Comment" id="Comment" cols="" rows=""
                                            class="form-control @error('Comment') is-invalid @enderror">{{ old('Comment') }}</textarea>
                                            @error('Comment')
                                            <span class="invalid-feedback">
                                                <strong> {{ $message }}</strong>
                                            </span>
                                        @enderror
                                        </div>
                                    <div class="form-group text-center">
                                        <button type="submit" class="btn btn-outline-info">Assign</button>
                                    </div>
                                </form>
                                {{-- assign history --}}
                                <strong>Assign History</strong>
                                <div class="overflow-auto" style="max-height: 100px">
                                    @forelse ($assign_histories as $history)
                                        <div class="small"><b>{{ $history->assignedBy->full_name }}</b> Assigned To <b>{{ $history->assignedTo->full_name }}</b> : <b>{{ $history->AssignedOn->diffForHumans()}}</b> 
                                        Message : <b>{{ $history->Comment ?? 'No Comment'}}</b></div>
                                    @empty
                                        <div class="text-info"> No Assign History</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')

@stop

@section('js')
<script>

</script>
@stop
