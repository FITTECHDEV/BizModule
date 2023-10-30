@extends('adminlte::page')

@section('title', 'Tickets')

@section('content_header')
    <div class="container">
        <div class="row justify-content-center">
            {{-- <div class="col-md- text-center">
            </div> --}}
            <div class="col-md-12 text-center">
                {{-- <ul class="progressbar">
                    <li class="@if ($ticket->Status >= 1) active @endif ">
                        <i class="fas fa-hands"></i> Open
                    </li>
                    <li class="@if ($ticket->Status >= 2) active @endif ">
                        <i class="fas fa-check"></i> Solved
                    </li>
                    <li class="@if ($ticket->Status >= 3) active @endif">
                        <i class="fas fa-envelope"></i> Closed
                    </li>
                    @if ($ticket->Status >= 4)
                        <li class="@if ($ticket->Status == 4) active @endif">
                            <i class="fas fa-thumbs-down text-danger"></i> <span class="text-danger">Re-opened</span>
                        </li>
                    @endif
                    @if ($ticket->Status == 5)
                        <li class="@if ($ticket->Status == 5) active @endif">
                            <i class="fas fa-smile"></i> Settled
                        </li>
                    @endif
                </ul> --}}
                <div class="card card-timeline px-1 border-none ">
                    <div class="row justify-content-center">
                        <div class="col-md-1">
                            <span class="text-uppercase float-left p-2">
                                Ticket Status
                            </span>
                            <small>#{{ $ticket->TicketNo }}:</small>
                            Created: <b>{{ $ticket->CreatedOn->diffForHumans() }}</b></span>

                        </div>
                        <div class="col-md-10">
                            <ul class="bs4-order-tracking p-2">
                                <li class="step @if ($ticket->Status >= 1) active @endif">
                                    <div><i class="fas fa-hands"></i></div> Open
                                </li>
                                <li class="step @if ($ticket->Status >= 2) active @endif">
                                    <div><i class="fas fa-check"></i></div> Solved
                                </li>
                                <li class="step @if ($ticket->Status >= 3) active @endif">
                                    <div><i class="fas fa-envelope"></i></div> Closed
                                </li>
                                @if ($ticket->Status >= 4)
                                    <li class="step @if ($ticket->Status >= 4) active @endif">
                                        <div><i class="fas fa-thumbs-down  @if ($ticket->Status >=
                                                4) text-danger @endif"></i></div>
                                        <span class="@if ($ticket->Status >= 4) text-danger @endif"> Re-Opened</span>
                                    </li>
                                    <li class="step @if ($ticket->Status == 5) active @endif">
                                        <div> <i class="fas fa-smile"></i></div> Settled
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <div class="col-md-1 p-2">
                            <a href="{{ route('tickets.my_tickets') }}" class="btn btn-primary float-right"><i class="fas fa-home"></i>  Home</a>
                        </div>
                    </div>
                    {{-- <h5 class="text-center"><b>In transit</b>. The order has been shipped!</h5> --}}
                </div>
            </div>
        </div>
        <div class="row justify-content-center pt-2">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header">
                        <div class="card-title">
                            <span class="h6 text-bold">{{ ucwords($ticket->Title) }}</span>
                            <hr>
                        </div>
                        <div class="card-tools">
                            <form class="">
                                <input type="hidden" id="ticketId" name="ticketId" value="{{ $ticket->Id }}">
                                <div class="form-group">
                                    <select id="changeTicketStatus" name="changeTicketStatus" class="form-control">
                                        <option value="" disabled selected>Change Status</option>
                                        @if ($ticket->Status == 1)
                                            <option value="2">Solved</option>
                                            <option value="3">Closed</option>
                                        @elseif ($ticket->Status == 2)
                                            <option value="1">Opened</option>
                                            <option value="3">Closed</option>
                                        @elseif ($ticket->Status == 3)
                                            <option value="0">Re-Opened</option>
                                        {{-- @elseif ($ticket->Status == 4)
                                            <option value="5">Settled</option>
                                        @elseif ($ticket->Status > 4)
                                            <option value="4">Re-Opened</option> --}}
                                        @endif

                                    </select>
                                </div>
                            </form>
                            @can('assign-ticket')
                              @if ($ticket->Status != 3 && $ticket->Status != 5)
                                <a href="{{ URL::signedRoute('tickets.assign_ticket', [$ticket->Id]) }}"
                                    class="btn btn-outline-info btn-xs btn-tool">
                                    @if ($ticket->assignedTo)
                                    Re-Assign @else Assign
                                    @endif
                                </a>
                            @endif
                            <small class="small float-right"> Assigned To:
                                <br><b>{{ $ticket->assignedTo->full_name ?? 'Not Assigned' }}</b></small>

                            @endcan
                          </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 border-bottom border-danger">
                                <h3>Description</h3>
                                <p> {{ $ticket->Description }}</p>
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
                                {{-- <hr> --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="float-left">Attachment(s)</h5>
                                        <span class="float-right">
                                            @if ($ticket->Status != 3 && $ticket->Status != 5)
                                                <form name="attachment_upload_form" id="attachment_upload_form"
                                                    enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="hidden" name="TicketID" value="{{ $ticket->Id }}">
                                                    <label for="upload">
                                                        <span class="fas fa-file-upload" aria-hidden="true"></span>
                                                        Upload
                                                        <input type="file" id="upload" name="attachments[]"
                                                            style="display:none">
                                                    </label>
                                                </form>
                                            @endif
                                        </span>
                                        @if (count($ticket->attachments->toArray()) > 0)
                                            <table class="table table-sm clearfix">
                                                <thead>
                                                    <th>CreatedBy</th>
                                                    <th>File Name</th>
                                                    <th>Opetation</th>
                                                </thead>
                                                <tbody>
                                                    @foreach ($ticket->attachments->toArray() as $attachment)
                                                        <tr>
                                                            <td>{{ $user->where('ID', $attachment['CreatedBy'])->first()->full_name }}
                                                            </td>
                                                            <td>
                                                                <a
                                                                    href="{{ URL::signedRoute('attachment.download', $attachment['ID']) }}"><i
                                                                        class="fas fa-paperclip"></i>
                                                                    {{ $attachment['FileName'] }} &nbsp;
                                                                    {{-- <i class="fas fa-download"></i> --}}
                                                                </a>
                                                            </td>
                                                            <td>
                                                                @if ($ticket->Status != 3 && $ticket->Status != 5)

                                                                    @if (Auth::user()->ID == $attachment['CreatedBy'])
                                                                        <input type="hidden" name="del_attachment"
                                                                            id="del_attachment"
                                                                            value="{{ $attachment['ID'] }}">
                                                                        <button
                                                                            class="btn btn-xs btn-outline-danger delete_attachment">
                                                                            <i class="fas fa-x fa-times "></i> Remove
                                                                        </button>
                                                                    @else
                                                                      <button
                                                                            class="btn btn-xs btn-outline-danger delete_attachment disabled">
                                                                            <i class="fas fa-x fa-times "></i> Remove
                                                                        </button>
                                                                    @endif
                                                                @else
                                                                    Locked
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <div class="text-info clearfix">
                                                <i class="fas fa-paperclip"></i> No attachment(s)
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 border-bottom border-success">
                                <div id="app">
                                    <div class="card card-default">
                                        <div class="card-header">Chats</div>

                                        <div class="card-body" style="  max-height: 300px;
                                        overflow-y: auto;">
                                            <chat-messages :messages="messages" :data="{{ $ticket }}"></chat-messages>
                                        </div>
                                        <div class="card-footer">
                                            <chat-form v-on:messagesent="addMessage" :user="{{ Auth::user() }}" :data="{{ $ticket }}">
                                            </chat-form>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@stop

@section('content')

@stop

@section('css')
    <style>
        .bs4-order-tracking {
            /* margin-bottom: 30px; */
            overflow: hidden;
            color: #878788;
            padding-left: 0px;
            /* margin-top: 30px */
        }

        .bs4-order-tracking li {
            list-style-type: none;
            font-size: 13px;
            width: 20%;
            float: left;
            position: relative;
            font-weight: 400;
            color: #878788;
            text-align: center
        }

        .bs4-order-tracking li:first-child:before {
            margin-left: 15px !important;
            padding-left: 11px !important;
            text-align: left !important
        }

        .bs4-order-tracking li:last-child:before {
            margin-right: 5px !important;
            padding-right: 11px !important;
            text-align: right !important
        }

        .bs4-order-tracking li>div {
            color: #fff;
            width: 29px;
            text-align: center;
            line-height: 29px;
            display: block;
            font-size: 12px;
            background: #878788;
            border-radius: 50%;
            margin: auto
        }

        .bs4-order-tracking li:after {
            content: '';
            width: 150%;
            height: 2px;
            background: #878788;
            position: absolute;
            left: 0%;
            right: 0%;
            top: 15px;
            z-index: -1
        }

        .bs4-order-tracking li:first-child:after {
            left: 50%
        }

        .bs4-order-tracking li:last-child:after {
            left: 0% !important;
            width: 0% !important
        }

        .bs4-order-tracking li.active {
            font-weight: bold;
            color: #2b8b3b
        }

        .bs4-order-tracking li.active>div {
            background: #2b8b3b
        }

        .bs4-order-tracking li.active:after {
            background: #2b8b3b
        }

        .card-timeline {
            background-color: #fff;
            z-index: 0
        }

    </style>
@stop

@section('js')
    <script>
        $(function() {


            $(".btn-add-more").click(function() {
                var html = $(".clone").html();
                $(".img_div").after(html);
            });

            $("body").on("click", ".btn-remove", function() {
                $(this).parents(".control-group").remove();
            });

            //on click delete attachment
            $('.delete_attachment').on('click', function() {
                var attachmentID = $('#del_attachment').val();
                // console.log(attachmentID);
                Swal.fire({
                    title: 'Are you sure?',
                    // text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Mark as Delete !!',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('attachment.destroy') }}",
                            type: 'DELETE',
                            dataType: 'json',
                            data: {
                                "_token": "{{ csrf_token() }}",
                                ID: attachmentID,
                            },
                            success: function(success) {
                                // console.log(success);

                                // Swal.fire(
                                //     'Deleted!',
                                //     'Attachment Deleted !!',
                                //     'success'
                                // )

                                // location.reload();
                            },
                            error: function(error) {
                                // console.log(error)
                            }
                        })

                    } else {
                        // location.reload();
                    }
                })
            })
            // change file input
            $('#upload').on('change', function() {
                var form = document.forms.namedItem("attachment_upload_form");
                var formdata = new FormData(form);
                $.ajax({
                    async: true,
                    type: "POST",
                    dataType: "json", // or html if you want...
                    contentType: false, // high importance!
                    url: '{{ route('attachment.upload') }}', // you need change it.
                    data: formdata, // high importance!
                    processData: false, // high importance!
                    success: function(data) {
                        // console.log(data);
                        location.reload();
                        //do thing with data....

                    },
                    error: function(error) {
                        // console.log(error);
                    }

                });
            });

            $('#changeTicketStatus').change(function() {
                var statusText = $('#changeTicketStatus option:selected').text();
                $.ajax({
                            url: "{{ route('tickets.update_status') }}",
                            method: 'GET',
                            dataType: 'json',
                            data: {
                                ID: $('#ticketId').val(),
                                Status: $('#changeTicketStatus option:selected').val(),
                                Message: statusText,
                            },
                            success: function(success) {
                                // console.log(success);
                                location.reload();
                            },
                            error: function(error) {
                                // console.log(error)
                            }
                        });
                        Swal.fire(
                            'SUCCESS!',
                            'Ticket Status Changed to ' + statusText,
                            'success'
                        );
                        location.reload();
               /* Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Mark as ' + statusText + '!',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('tickets.update_status') }}",
                            method: 'GET',
                            dataType: 'json',
                            data: {
                                ID: $('#ticketId').val(),
                                Status: $('#changeTicketStatus option:selected').val(),
                                Message: statusText,
                            },
                            success: function(success) {
                                // console.log(success);
                                location.reload();
                            },
                            error: function(error) {
                                // console.log(error)
                            }
                        });
                        Swal.fire(
                            'SUCCESS!',
                            'Ticket Status Changed to ' + statusText,
                            'success'
                        )
                    } else {
                        // location.reload();
                    }
                });*/

            })

        })
    </script>
@stop
