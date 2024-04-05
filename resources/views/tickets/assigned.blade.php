@extends('adminlte::page')

@section('title', 'Assigned-Dashboard')

@section('content_header')

@stop

@section('content')
<div class="p-2">
    <div class="">
        <div class="row justify-content-center">
            <div class="col-md-12 card collapsed-card shadow-lg text-center h3">
                <div class="card-header">
                    <h3 class="card-title text-uppercase"> Assigned To Me</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                                class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- badge row -->
        <div class="row">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-envelope-open"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-center">Open </span>
                        <span class="info-box-number text-center">
                            {{ $tickets->where('Status', '=', 1)->count() }}
                        </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-12 col-sm-6 col-md-3">

                <div class="info-box mb-3">
                    <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-center">Solved </span>
                        <span class="info-box-number text-center">{{ $tickets->where('Status', '=', 2)->count() }}</span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                {{-- <!-- /.info-box --><a href="{{ route('tickets.solved_tickets') }}" class=""></a> --}}
            </div>
            <!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix hidden-md-up"></div>

            <div class="col-12 col-sm-6 col-md-3">

                <div class="info-box">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-envelope"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-center">Closed </span>
                        <span class="info-box-number text-center">
                            {{ $tickets->where('Status', '=', 3)->count() }}
                        </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
                {{-- <a href="{{ route('tickets.closed_tickets') }}"></a> --}}
            </div>
            <!-- /.col -->
            {{-- <div class="col-12 col-sm-6 col-md-3">

                <div class="info-box mb-3">
                    <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-thumbs-down"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-center">Re-Opened </span>
                        <span class="info-box-number text-center">
                            {{ $tickets->where('Status', '=', 4)->count() }}
                        </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
                 <a href="{{ route('tickets.re_opened_tickets') }}"></a>

            </div> --}}
            <!-- /.col -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-th"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-center">On Hold</span>
                        <span class="info-box-number text-center">
                            {{ $tickets->where('Status', '=', 0)->count() }}
                        </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            {{-- <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-grey elevation-1"><i class="fa fa-anchor"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-center">Settled</span>
                        <span class="info-box-number text-center">
                            {{ $tickets->where('Status', '=', 5)->count() }}
                        </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div> --}}
        </div>
        <!-- /.badge row -->
    </div><!-- table row -->
    <div class="row justify-content-center">
        <div class="col-12 col-sm-12 col-md-12 ">
            <div class="card">
                <div class="card-header ">
                    <ul class="nav nav-tabs float-left">
                        <li class="nav-item"><a class="nav-link text-warning active" href="#open" data-toggle="tab">Open </a></li>
                        <li class="nav-item"><a class="nav-link text-blue" href="#solved" data-toggle="tab">Solved</a></li>
                        <li class="nav-item"><a class="nav-link text-success" href="#closed" data-toggle="tab">Closed</a></li>
                        {{-- <li class="nav-item"><a class="nav-link text-danger" href="#re-open" data-toggle="tab">Re-opened</a></li> --}}
                        {{-- <li class="nav-item"><a class="nav-link " href="#settled" data-toggle="tab">Settled </a></li> --}}
                        <li class="nav-item"><a class="nav-link text-cyan" href="#pending" data-toggle="tab">On Hold </a></li>
                    </ul>
                    <div class="float-right">
                        @if (Auth::user()->can('ticket-raise'))
                            <a href="{{ URL::signedRoute('tickets.create') }}" class="btn btn-sm btn-outline-info"><i
                                    class="fas fa-plus"></i> Raise a Ticket</a>
                        @endif
                        <button class="btn btn-sm btn-outline-primary" id="refreshTable"><i class="fas fa-sync"></i>
                            Refresh</button>
                        {{-- <a href="{{route('push')}}" class="btn btn-outline-primary btn-block">Make a Push Notification!</a> --}}

                    </div>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="active tab-pane" id="open">
                            <div class="">
                                <table class="table table-sm table-bordered display text-sm" style="width:100%"
                                    id="open-tickets-table">
                                    <thead>
                                        <th>TicketNo</th>
                                        <th>CreatedBy</th>
                                        <th>AssignedTo</th>
                                        <th>Office</th>
                                        <th>Title</th>
                                        <th>CreatedOn</th>
                                        <th>Solution</th>
                                        <th>Category</th>
                                        <th>Sub-Category</th>
                                        <th>Priority</th>
                                        <th>Action</th>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="solved">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered text-sm " style="width:100%"
                                    id="solved-tickets-table">
                                    <thead>
                                        <th>TicketNo</th>
                                        <th>CreatedBy</th>
                                        <th>AssignedTo</th>
                                        <th>Office</th>
                                        <th>Title</th>
                                        <th>CreatedOn</th>
                                        <th>Solution</th>
                                        <th>Category</th>
                                        <th>Sub-Category</th>

                                        <th>Priority</th>
                                        <th>Action</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($tickets->where('Status', '=', 2) as $ticket)
                                            <tr>
                                                <td><a
                                                        href="{{ URL::signedRoute('tickets.show', [$ticket->Id]) }}">{{ $ticket->TicketNo }}</a>
                                                </td>
                                                <td>{{ $ticket->CreatedBy }}</td>
                                                <td>{{ $ticket->assignedTo->full_name ?? 'N/A' }}</td>
                                                <td>{{ $ticket->user->Office->Name }}</td>
                                                <td>{{ $ticket->Title }}</td>
                                                <td>{{ $ticket->CreatedOn->format('Y-m-d H:i:s')  }}</td>
                                                <td>{{ $ticket->SolutionName }}</td>
                                                <td>{{ $ticket->ModuleName }}</td>
                                                <td>{{ $ticket->SubModuleName }}</td>

                                                <td>{!! $ticket->TicketPriorityPaint($ticket->Priority) !!}</td>
                                                <td><a href="{{ URL::signedRoute('tickets.show', [$ticket->Id]) }}"
                                                        class="btn btn-xs btn-warning shadow">
                                                        View
                                                    </a></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="closed">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered display text-sm" style="width:100%"
                                    id="closed-tickets-table">
                                    <thead>
                                        <th>TicketNo</th>
                                        <th>CreatedBy</th>
                                        <th>AssignedTo</th>
                                        <th>Office</th>
                                        <th>Title</th>
                                        <th>CreatedOn</th>
                                        <th>Solution</th>
                                        <th>Category</th>
                                        <th>Sub-Category</th>

                                        <th>Priority</th>
                                        <th>Action</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($tickets->where('Status', '=', 3) as $ticket)
                                            <tr>
                                                <td><a
                                                        href="{{ URL::signedRoute('tickets.show', [$ticket->Id]) }}">{{ $ticket->TicketNo }}</a>
                                                </td>
                                                <td>{{ $ticket->CreatedBy }}</td>
                                                <td>{{ $ticket->assignedTo->full_name ?? 'N/A' }}</td>
                                                <td>{{ $ticket->user->Office->Name }}</td>
                                                <td>{{ $ticket->Title }}</td>
                                                <td>{{ $ticket->CreatedOn->format('Y-m-d H:i:s')  }}</td>
                                                <td>{{ $ticket->SolutionName }}</td>
                                                <td>{{ $ticket->ModuleName }}</td>
                                                <td>{{ $ticket->SubModuleName }}</td>

                                                <td>{!! $ticket->TicketPriorityPaint($ticket->Priority) !!}</td>
                                                <td><a href="{{ URL::signedRoute('tickets.show', [$ticket->Id]) }}"
                                                        class="btn btn-xs btn-warning shadow">
                                                        View
                                                    </a></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="re-open">
                            <div class="table-responsive">
                                <table class="table table-xs table-bordered display text-sm" style="width:100%"
                                    id="re-opened-tickets-table">
                                    <thead>
                                        <th>TicketNo</th>
                                        <th>CreatedBy</th>
                                        <th>AssignedTo</th>
                                        <th>Office</th>
                                        <th>Title</th>
                                        <th>CreatedOn</th>
                                        <th>Solution</th>
                                        <th>Category</th>
                                        <th>Sub-Category</th>

                                        <th>Priority</th>
                                        <th>Action</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($tickets->where('Status', '=', 4) as $ticket)
                                            <tr>
                                                <td><a
                                                        href="{{ URL::signedRoute('tickets.show', [$ticket->Id]) }}">{{ $ticket->TicketNo }}</a>
                                                </td>
                                                <td>{{ $ticket->CreatedBy }}</td>
                                                <td>{{ $ticket->assignedTo->full_name ?? 'N/A' }}</td>
                                                <td>{{ $ticket->user->Office->Name }}</td>
                                                <td>{{ $ticket->Title }}</td>
                                                <td>{{ $ticket->CreatedOn->format('Y-m-d H:i:s')  }}</td>
                                                <td>{{ $ticket->SolutionName }}</td>
                                                <td>{{ $ticket->ModuleName }}</td>
                                                <td>{{ $ticket->SubModuleName }}</td>
                                                <td>{!! $ticket->TicketPriorityPaint($ticket->Priority) !!}</td>
                                                <td><a href="{{ URL::signedRoute('tickets.show', [$ticket->Id]) }}"
                                                        class="btn btn-xs btn-warning shadow text-xs">
                                                        View
                                                    </a></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="settled">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered display text-sm" style="width:100%"
                                    id="settled-tickets-table">
                                    <thead>
                                        <th>TicketNo</th>
                                        <th>CreatedBy</th>
                                        <th>AssignedTo</th>
                                        <th>Office</th>
                                        <th>Title</th>
                                        <th>CreatedOn</th>
                                        <th>Solution</th>
                                        <th>Category</th>
                                        <th>Sub-Category</th>

                                        <th>Priority</th>
                                        <th>Action</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($tickets->where('Status', '=', 5) as $ticket)
                                            <tr>
                                                <td><a
                                                        href="{{ URL::signedRoute('tickets.show', [$ticket->Id]) }}">{{ $ticket->TicketNo }}</a>
                                                </td>
                                                <td>{{ $ticket->CreatedBy }}</td>
                                                <td>{{ $ticket->assignedTo->full_name ?? 'N/A' }}</td>
                                                <td>{{ $ticket->user->Office->Name }}</td>
                                                <td>{{ $ticket->Title }}</td>
                                                <td>{{ $ticket->CreatedOn->format('Y-m-d H:i:s')  }}</td>
                                                <td>{{ $ticket->SolutionName }}</td>
                                                <td>{{ $ticket->ModuleName }}</td>
                                                <td>{{ $ticket->SubModuleName }}</td>

                                                <td>{!! $ticket->TicketPriorityPaint($ticket->Priority) !!}</td>
                                                <td><a href="{{ URL::signedRoute('tickets.show', [$ticket->Id]) }}"
                                                        class="btn btn-xs btn-warning shadow">
                                                        View
                                                    </a></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="pending">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered display text-sm"
                                    style="width:100%" id="pending-tickets-table">
                                    <thead>
                                        <th>TicketNo</th>
                                        <th>CreatedBy</th>
                                        <th>AssignedTo</th>
                                        <th>Office</th>
                                        <th>Title</th>
                                        <th>CreatedOn</th>
                                        <th>Solution</th>
                                        <th>Category</th>
                                        <th>Sub-Category</th>
                                        <th>Priority</th>
                                        <th>Action</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($tickets->where('Status', '=', 0) as $ticket)
                                            <tr>
                                                <td><a
                                                        href="{{ URL::signedRoute('tickets.pick', [$ticket->Id]) }}">{{ $ticket->TicketNo }}</a>
                                                </td>
                                                <td>{{ $ticket->CreatedBy }}</td>
                                                <td>{{ $ticket->assignedTo->full_name ?? 'Not Assigned' }}</td>
                                                <td>{{ $ticket->Office->Name ?? $ticket->user->Office->Name }}</td>
                                                <td>{{ $ticket->Title }}</td>
                                                <td>{{ $ticket->CreatedOn->format('Y-m-d H:i:s')  }}</td>
                                                <td>{{ $ticket->SolutionName }}</td>
                                                <td>{{ $ticket->ModuleName }}</td>
                                                <td>{{ $ticket->SubModuleName }}</td>
                                                <td>{!! $ticket->TicketPriorityPaint($ticket->Priority) !!}</td>
                                                <td><a href="{{ URL::signedRoute('tickets.pick', [$ticket->Id]) }}"
                                                        class="btn btn-xs btn-warning shadow">
                                                        View
                                                    </a></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
    <!-- /.table row -->
</div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">
@stop

@section('js')
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>
    <script>
        $(function() {
            var table2 = $('#solved-tickets-table').DataTable({
                "order": [
                [5, "desc"]
            ],
                orderCellsTop: true,
            fixedHeader: true,
        });
        var table3 = $('#closed-tickets-table').DataTable({
            "order": [
                [5, "desc"]
            ],
            orderCellsTop: true,
            fixedHeader: true,
        });
        var table4 = $('#re-opened-tickets-table').DataTable({
            "order": [
                [5, "desc"]
            ],
            orderCellsTop: true,
            fixedHeader: true,
        });
        var table5 = $('#settled-tickets-table').DataTable({
            "order": [
                [5, "desc"]
            ],
            orderCellsTop: true,
            fixedHeader: true,
        });
            var table5 = $('#pending-tickets-table').DataTable({
                "order": [
                [5, "desc"]
            ],
                orderCellsTop: true,
                        fixedHeader: true,
                    });
            var table = $('#open-tickets-table').DataTable({
                    "order": [
                    [5, "desc"]
                ],
                processing: true,
                serverSide: true,
                buttons: [
                    'copy', 'excel', 'pdf'
                ],
                ajax: "{!! route('datatables.assigned') !!}",
                columns: [{
                    data: 'TicketNo',
                    name: 'TicketNo'
                },
                {
                    data: 'CreatedBy',
                    name: 'CreatedBy'
                },
                {
                    data: 'AssignedTo',
                    name: 'AssignedTo',
                    orderable: true,
                    searchable: true,
                },
                {
                    data: 'OfficeName',
                    name: 'OfficeName'
                },
                {
                    data: 'Title',
                    name: 'Title'
                },
                {
                    data: 'CreatedOn',
                    name: 'CreatedOn'
                },
                {
                    data: 'SolutionName',
                    name: 'SolutionName'
                },
                {
                    data: 'ModuleName',
                    name: 'ModuleName'
                },
                {
                    data: 'SubModuleName',
                    name: 'SubModuleName'
                },
                {
                    data: 'Priority',
                    name: 'Priority'
                },

                {
                    data: 'Action',
                    name: 'Action',
                    orderable: false,
                    searchable: false
                }]
            });

            /* table.buttons().container().appendTo($('.col-sm-6:eq(0)', table.table().container()));
        */});
    </script>
@stop
