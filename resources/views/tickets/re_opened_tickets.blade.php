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
                               Re-Opened Tickets
                            </div>
                            <div class="card-tools">
                                <a href="{{ URL::signedRoute('tickets.create') }}" class="btn btn-sm btn-outline-info"><i
                                        class="fas fa-plus"></i> Raise a Ticket</a>
                                        <a href="{{ url()->previous() }}" class="btn btn-info">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-sm" id="tickets-table">
                                <thead>
                                    <th>TicketNo</th>
                                    <th>CreatedBy</th>
                                    <th>Office</th>
                                    <th>Title</th>
                                    <th>CreatedOn</th>
                                    <th>Solution</th>
                                    <th>Category</th>
                                    <th>Sub-Category</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Action</th>
                                </thead>
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
            $('#tickets-table').DataTable({
                "order": [[ 4, "desc" ]],
                processing: true,
                serverSide: true,
                ajax: '{!! route('datatables.re_opened_tickets') !!}',
                columns: [{
                        data: 'TicketNo',
                        name: 'TicketNo'
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
                        name: 'SubModuleName',
                    },
                    {
                        data: 'Status',
                        name: 'Status',
                        orderable: false,
                        searchable: false
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
                    }
                ],
                initComplete: function() {
                    // this.api().columns().every(function() {
                    //     var column = this;
                    //     var input = document.createElement('input');
                    //     $(input).appendTo($(column.footer()).empty())
                    //         .on('change', function() {
                    //             var val = $.fn.dataTable.util.escapeRegex($(this).val());
                    //             column.search(val ? val : '', true, false).draw();
                    //         });
                    // });
                }
            });
        });
    </script>
@stop
