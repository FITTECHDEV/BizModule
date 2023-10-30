@extends('adminlte::page')

@section('title', 'Dashboard-Re-Opened-Tickets')

@section('content_header')

@stop

@section('content')
    <div class="p-2">
        <div class="container">
            <!-- table row -->
            <div class="row justify-content-center">
                <div class="col-12 col-sm-12 col-md-12 ">
                    <div class="card card-widget p-2 table-responsive">
                        <div class="card-header">
                            <div class="user-block">
                               {{ ucwords($title )}} Tickets
                            </div>
                            <div class="card-tools">
                                <a href="{{ URL::signedRoute('tickets.create') }}" class="btn btn-sm btn-outline-info"><i
                                        class="fas fa-plus"></i> Raise a Ticket</a>
                                        <a href="{{ url('/') }}" class="btn btn-info">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-sm" id="tickets-table">
                                <thead>
                                    <th>TicketNo</th>
                                    <th>CreatedBy</th>
                                    <th>Title</th>
                                    <th>CreatedOn</th>
                                    <th>Solution</th>
                                    <th>Category</th>
                                    <th>Sub-Category</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Action</th>
                                </thead>
                                <tbody>
                                    @forelse ($tickets as $ticket)
                                       <tr>
                                           <td>{{ $ticket->TicketNo }}</td>
                                           <td>{{ $ticket->user->full_name }}</td>
                                           <td>{{ $ticket->Title }}</td>
                                           <td>{{ $ticket->CreatedOn }}</td>
                                           <td>{{ $ticket->SolutionName }}</td>
                                           <td>{{ $ticket->ModuleName }}</td>
                                           <td>{{ $ticket->SubModuleName }}</td>
                                           <td>{{ $ticket->ticketStatus($ticket->Status) }}</td>
                                           <td>{{ $ticket->Priority }}</td>
                                           <td><a href="{{ URL::signedRoute('tickets.show', [$ticket->Id]) }}" class="btn btn-xs btn-warning shadow"><i class="fas fa-envelope-open"></i> View</a></td>
                                       </tr>
                                    @empty

                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.table row -->
        </div>
    </div>

@stop

@section('css')

@stop

@section('js')
<script>
    $(function() {
        $('#tickets-table').DataTable()
    })
</script>
@stop
