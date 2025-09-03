@extends('adminlte::page')

@section('title', $title.'-Dashboard')

{{-- @section('content_top_nav_left', $title) --}}

@section('content_header')

@stop

@section('content')
<div class="">
    <div class="">
        <div class="row pt-1 pb-0 m-0 justify-content-center">
            <div class="col-12 col-sm-6 col-md-3">
                <a href="#" id="open">
                    <div class="info-box mb-1">
                        <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-envelope-open"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-center">Open </span>
                            <span class="info-box-number text-center">{{ $ticket->ticketCount('Status', 1, $criterias) }}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                </a>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-12 col-sm-6 col-md-2">
                <a href="#" id="solved">
                    <div class="info-box mb-1">
                        <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-center">Solved </span>
                            <span class="info-box-number text-center">{{ $ticket->ticketCount('Status', 2, $criterias) }}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                </a>

            </div>
            <!-- /.col -->
            <!-- fix for small devices only -->
            <div class="clearfix hidden-md-up"></div>
            <!-- /.col -->
            <div class="col-12 col-sm-6 col-md-2">
                <a href="/archive" target="_blank">
                    <div class="info-box mb-1">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-thumbs-up"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-center">Closed</span>
                            <span class="info-box-number text-center">{{ $ticket->ticketCount('Status', 3, $criterias) }}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <a href="#" id="onHold">
                    <div class="info-box mb-1">
                        <span class="info-box-icon bg-purple elevation-1"><i class="fa fa-ban"
                                aria-hidden="true"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-center">On Hold</span>
                            <span class="info-box-number text-center">{{ $ticket->ticketCount('Status', '0', $criterias) }}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                </a>
                <!-- /.info-box -->
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <a href="/archive" target="_blank">
                    <div class="info-box mb-1">
                        <span class="info-box-icon bg-info elevation-1"><i class="fa fa-calculator"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-center">All Tickets</span>
                            <span class="info-box-number text-center">{{ $ticket->ticketCount('Status', '*', $criterias) }}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                </a>

                <!-- /.info-box -->
            </div>
        </div>
        <div class="row justify-content-center mb-0">
            <div class="col-md-3 text-center"><h5>{{ $title ?? '' }}</h5></div>
            <div class="col-md-2 mb-0"><a href="#" id="ShowStopper" class="card text-center mb-1 pb-0"><div><i class="badge badge-danger">{{ $ticket->ticketCount('Priority', 'ShowStopper', $criterias) }}</i> ShowStopper</div></a> </div>
            <div class="col-md-2 mb-0"><a href="#" id="High" class="card text-center mb-1 pb-0"><div><i class="badge badge-warning">{{ $ticket->ticketCount('Priority', 'High', $criterias) }}</i> High</div></a> </div>
            <div class="col-md-2 mb-0"><a href="#" id="Medium" class="card text-center mb-1 pb-0"><div><i class="badge badge-primary">{{ $ticket->ticketCount('Priority', 'Medium', $criterias) }}</i> Medium</div></a> </div>
            <div class="col-md-3 mb-0"><a href="#" id="Low" class="card text-center mb-1 pb-0"><div><i class="badge badge-info">{{ $ticket->ticketCount('Priority', 'Low', $criterias) }}</i> Low</div></a> </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-12 col-sm-12 col-md-12 ">
            <div class="card">
                <div class="card-body">
                    <div class="tab-content">
                        <div class="active tab-pane" id="open">
                            <div class="">
                                <table class="table table-sm table-bordered display text-sm" style="width:100%"
                                    id="my-tickets-table">
                                    <thead>
                                        <th>TicketNo</th>
                                        <th>Title</th>
                                        <th>Solution</th>
                                        <th>Category</th>
                                        <th>Sub-Category</th>
                                        <th>CreatedBy</th>
                                        <th>Office</th>
                                        <th>AssignedTo</th>
                                        <th>CreatedOn</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Action</th>
                                    </thead>
                                    <tbody>
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

@stop

@section('js')
<script>
    $(function() {
        let page_filter = `{{ $pageFilter }}`;
        let url = '';

        switch (page_filter) {
            case 'myTickets':
                    url = `{!! route('datatables.my_tickets') !!}`;
                break;
            case 'myOfficeTickets':
                    url = `{!! route('datatables.my_office_tickets') !!}`;
                break;
            case 'assignedTickets':
                    url = `{!! route('datatables.assigned') !!}`;
                break;
            default:
                url = '';
                break;
        }


        let myTable = $('#my-tickets-table').DataTable({
                "order": [
                    [8, "desc"]
                ],
                orderCellsTop: true,
                fixedHeader: true,
                processing: true,
                serverSide: true,
                ajax: url,
                columns: [{
                        data: 'TicketNo',
                        name: 'TicketNo'
                    },
                    {
                        data: 'Title',
                        name: 'Title'
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
                        data: 'CreatedBy',
                        name: 'CreatedBy'
                    },
                    {
                        data: 'OfficeName',
                        name: 'OfficeName'
                    },
                    {
                        data: 'AssignedTo',
                        name: 'AssignedTo'
                    },
                    {
                        data: 'CreatedOn',
                        name: 'CreatedOn'
                    },
                    {
                        data: 'Status',
                        name: 'Status'
                    },
                    {
                        data: 'Priority',
                        name: 'Priority'
                    },

                    {
                        data: 'Action',
                        name: 'Action'
                    }
                ],
            });

            window.user_raise_ticket = `{{ Auth::user()->can('ticket-raise') }}`;

        if (window.user_raise_ticket)
        {
            $('#my-tickets-table_filter').css('margin-left', '2px').append('<a href="/tickets/create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i>New Ticket</a>');
        }


        $('#refreshTable').click(function() {
            table.ajax.reload();
        });

        searchTable(1);

        $('#open').on('click', function() {
            searchTable(1);
        });

        $('#solved').on('click', function() {
            searchTable(2);
        });

        $('#closed').on('click', function() {
            searchTable(3);
        });

        $('#onHold').on('click', function() {
            searchTable(0);
        });

        $('#allTickets').on('click', function() {
            searchTable(' ');
        });

        function searchTable(param) {
            let column = 9;
            if(typeof(param) != 'number'){
                column = 10;
            }
            myTicketsTable.column(column).search(param, false, false, false).draw();
        }

        $('#ShowStopper').on('click', function() {
            searchTable('ShowStopper');
        });
        $('#High').on('click', function() {
            searchTable('High');
        });
        $('#Medium').on('click', function() {
            searchTable('Medium');
        });
        $('#Low').on('click', function() {
            searchTable('Low');
        });
    });
</script>
@stop
