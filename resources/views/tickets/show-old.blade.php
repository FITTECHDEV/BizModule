@extends('adminlte::page')

@section('title')

@section('content_header')
    <div class="container">
        <div class="row justify-content-center">
            {{-- <div class="col-md-2 text-center">
                <h4 class="text-uppercase">Ticket Status</h4>
            </div>
            <div class="col-md-10 text-center">
                <ul class="progressbar">
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
                        <li class="@if ($ticket->Status >= 4) active @endif">
                            <i class="fas fa-thumbs-down text-danger"></i> <span class="text-danger">Re-opened</span>
                        </li>
                    @endif
                    @if ($ticket->Status >= 5)
                        <li class="@if ($ticket->Status >= 5) active @endif">
                            <i class="fas fa-smile"></i> Settled
                        </li>
                    @endif
                </ul>
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
                    <h4 class="text-uppercase ">Ticket Status</h4>
                    <ul class="bs4-order-tracking align-content-center">
                        <li class="step @if ($ticket->Status >= 1) active @endif">
                            <div><i class="fas fa-hands"></i></div> Open
                        </li>
                        <li class="step @if ($ticket->Status >= 2) active @endif">
                            <div><i class="fas fa-check"></i></div> Solved
                        </li>
                        <li class="step @if ($ticket->Status >= 3) active @endif">
                            <div><i class="fas fa-envelope"></i></div> Closed
                        </li>

                        <li class="step @if ($ticket->Status >= 4) active @endif">
                            <div><i class="fas fa-thumbs-down  @if ($ticket->Status >= 4) text-danger @endif"></i></div>
                            <span class="@if ($ticket->Status >= 4) text-danger @endif"> Re-Opened</span>
                        </li>
                        <li class="step @if ($ticket->Status == 5) active @endif">
                            <div> <i class="fas fa-smile"></i></div> Settled
                        </li>
                    </ul>
                    {{-- <h5 class="text-center"><b>In transit</b>. The order has been shipped!</h5> --}}
                </div>
            </div>
        </div>
        <div class="row justify-content-center pt-2">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header">
                        <div class="card-title">
                            <span>Title </span>&nbsp; <a href="#" id="TicketTitle" data-toggle="modal"
                                data-target="#modal-default" data-backdrop="static" data-keyboard="false"><i
                                    class="fas fa-pen fa-sm"></i></a> </span></span>
                            <p class="h6 text-bold">{{ ucwords($ticket->Title) }}
                                <br><small>#{{ $ticket->TicketNo }}</small>
                        </div>
                        <div class="card-tools ">
                            @if (Auth::user()->can('ticket-raise'))
                                <a href="{{ URL::signedRoute('tickets.create') }}" class="btn btn-sm btn-outline-info"><i
                                        class="fas fa-plus"></i> Raise a Ticket</a>
                            @endif
                            <form class="float-right ml-1">
                                <input type="hidden" id="ticketId" name="ticketId" value="{{ $ticket->Id }}">
                                <div class="form-group">
                                    <select id="changeTicketStatus" name="changeTicketStatus" class="form-control">
                                        <option value="" disabled selected>Change Status</option>
                                        @if ($ticket->Status <= 2)
                                            <option value="3">Close</option>
                                        @elseif ($ticket->Status == 3 || $ticket->Status == 5)
                                            <option value="4">Re-Open</option>
                                        @elseif ($ticket->Status == 4)
                                            <option value="5">Settled</option>
                                        @else
                                            <option value="" disabled>No Status...</option>
                                        @endif
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 border-bottom border-danger">
                                <span class="h6 text-bold">Description </span> <a href="#" id="TicketTitle"
                                    data-toggle="modal" data-target="#modal-default" data-backdrop="static"
                                    data-keyboard="false"><i class="fas fa-pen fa-sm"></i></a>
                                <p> {{ $ticket->Description }}
                                </p>
                                <hr>
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
                                                                            class="btn btn-xs btn-outline-secondary delete_attachment">
                                                                            <i class="fas fa-x fa-times text-danger"></i>
                                                                        </button>
                                                                    @else
                                                                        -N-A
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
                                <div class="card direct-chat direct-chat-primary shadow">
                                    <div class="card-header">
                                        <h3 class="card-title">Comment</h3>

                                        <div class="card-tools">
                                            {{-- <span title="3 New Messages" class="badge badge-primary">3</span> --}}
                                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <!-- /.card-header -->
                                    @if ($ticket->Status == 3 || $ticket->Status == 5)
                                        <div class="card-body">
                                            <div class="text-primary text-center m-3">
                                                @if ($ticket->Status == 3)
                                                    <i class="fas fa-envelope"></i> TICKET CLOSED
                                                @else
                                                    <i class="fas fa-smile"></i> TICKET SETTLED
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="card-body">
                                            <!-- Conversations are loaded here -->
                                            <div class="direct-chat-messages">
                                                @forelse ($messages as $message)
                                                    @if ($message->Role != 1)
                                                        <!-- Message to the right -->
                                                        <div class="direct-chat-msg right">
                                                            <div class="direct-chat-infos clearfix">
                                                                <span class="direct-chat-name float-right">
                                                                    {{ $message->user->full_name }}
                                                                </span>
                                                                <span class="direct-chat-timestamp float-left">
                                                                    {{ $message->CreatedOn->diffForHumans() }}
                                                                </span>
                                                            </div>
                                                            <!-- /.direct-chat-infos -->
                                                            <img class="direct-chat-img"
                                                                src="{{ asset('site_imgs/avatar5.png') }}"
                                                                alt="message user image">
                                                            <!-- /.direct-chat-img -->
                                                            <div class="direct-chat-text">
                                                                {{ $message->Message }}
                                                            </div>
                                                            <!-- /.direct-chat-text -->
                                                        </div>
                                                        <!-- /.direct-chat-msg -->
                                                    @else
                                                        <!-- Message. Default to the left -->
                                                        <div class="direct-chat-msg">
                                                            <div class="direct-chat-infos clearfix">
                                                                <span class="direct-chat-name float-left">
                                                                    {{ $message->user->full_name }}
                                                                </span>
                                                                <span class="direct-chat-timestamp float-right">
                                                                    {{ $message->CreatedOn->diffForHumans() }}
                                                                </span>
                                                            </div>
                                                            <!-- /.direct-chat-infos -->
                                                            <img class="direct-chat-img"
                                                                src="{{ asset('site_imgs/avatar5.png') }}" alt="">
                                                            <!-- /.direct-chat-img -->
                                                            <div class="direct-chat-text">
                                                                {{ $message->Message }}
                                                            </div>
                                                            <!-- /.direct-chat-text -->
                                                        </div>
                                                        <!-- /.direct-chat-msg -->
                                                    @endif
                                                @empty
                                                    <div class="direct-chat-msg">
                                                        <div class="card bg-secondary text-center">
                                                            No Comments Yet !!
                                                        </div>
                                                    </div>
                                                @endforelse
                                            </div>
                                            <!--/.direct-chat-messages-->
                                        </div>
                                        <!-- /.card-body -->
                                        <div class="card-footer">
                                            <form method="post" id="messageForm">
                                                @csrf
                                                <div class="input-group">
                                                    <input type="hidden" name="TicketID" value="{{ $ticket->Id }}">
                                                    <input type="text" name="message" id="messageText"
                                                        placeholder="Type Message ..."
                                                        class="form-control @error('message') is-invalid @enderror"
                                                        required>
                                                    @error('message')
                                                        <span class="invalid-feedback">
                                                            <strong> {{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                    <span class="input-group-append">
                                                        <button type="submit" class="btn btn-primary" id="saveMessage"><i
                                                                class="fas fa-paper-plane"></i> Send</button>
                                                    </span>
                                                </div>
                                            </form>
                                        </div>
                                        <!-- /.card-footer-->
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modal-default">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Default Modal</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('tickets.update', $ticket->Id) }}" method="post">
                        @method('PUT')
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="TicketID" value="{{ $ticket->Id }}">
                            <div class="form-group">
                                <label for="title">Ticket Title</label>
                                <input type="text" name="Title" id="Title" value="{{ old('Title', $ticket->Title) }}"
                                    class="form-control @error('Title') is-invalid @enderror">
                            </div>
                            <div class="form-group">
                                <label for="Description">Ticket Description</label>
                                <textarea name="Description" id="Description"
                                    class="form-control @error('Description') is-invalid @enderror">{{ old('Description', $ticket->Description) }}</textarea>
                            </div>

                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-outline-success"><i class="fas fa-fresh"></i> Save
                                Changes</button>
                        </div>
                    </form>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
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
        var ticket_id = $('#ticketId').val()
        var container = [];

        // get the last inserted message id when the page loads
        $.ajax({
            url: 'messages/ajax/' + ticket_id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.message != null) {
                    container.push(response.message.MessageID);
                }
            }
        })

        // check if new message has been written every five seconds
        setInterval(function() {
            // console.log(container)
            return getData(ticket_id);
        }, 5000); // ping every five seconds


        //send message
    $('#saveMessage').click(function(e) {
            e.preventDefault();
            if (!$('#messageText').val()) {
                alert('please supply a message')
            } else {
                // $(this).html('<i class="fas fa-paper-plane"></i> Sending..');
                $.ajax({
                    data: $('#messageForm').serialize(),
                    url: "{{ route('messages.send') }}",
                    type: "POST",
                    dataType: 'json',
                    success: function(response) {
                        // console.log(response.message)
                        $('#messageText').val('')
                        $('#messageForm').trigger("reset");
                        container.pop();
                    },
                    error: function(err) {
                        //   alert( data.message)
                        // console.log('Error:', err.message);
                        // $(this).html('<i class="fas fa-paper-plane"></i> Send');
                    }
                });

            }

        });

    function getData(param) {
            $.ajax({
                url: 'messages/ajax/' + param,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    // console.log(container);
                    if (container[0] != response.message.MessageID) {
                        container.pop()
                        container.push(response.message.MessageID)
                        // append the message to the conversation
                        new Toast("You have a text message !!");
                        $('.direct-chat-messages')
                            .append('<div class="direct-chat-msg ' + (response.message.user
                                    .Role == 1 ? 'left' : 'right') + '">' +
                                '<div class="direct-chat-infos clearfix">' +
                                '<span class="direct-chat-name ' + (response.message.user
                                    .Role == 1 ? 'float-left' : 'float-right') + '">' +
                                response
                                .message.user.full_name + '</span>' +
                                '<span class="direct-chat-timestamp ' + (response.message.user.Role == 1 ? 'float-left' : 'float-right') + '">' +
                                '</span></div>' +
                                '<img class="direct-chat-img" src="{{ asset('site_imgs/avatar5.png') }}" alt="">' +
                                '<div class="direct-chat-text">' + response.message.Message +
                                '</div></div>')
                    } else {
                        // $('.direct-chat-messages').append("<p>no new</p>")
                    }
                    //   console.log(container);
                },
            })
        }




        $(".btn-add-more").click(function() {
            var html = $(".clone").html();
            $(".img_div").after(html);
        });

        $("body").on("click", ".btn-remove", function() {
            $(this).parents(".control-group").remove();
        });

        $('#changeTicketStatus').change(function() {
            var statusText = $('#changeTicketStatus option:selected').text();
            Swal.fire({
                title: 'Are you sure?',
                // text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Mark as ' + statusText + '!'
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
                    })
                    Swal.fire(
                        'SUCCESS!',
                        'Ticket Status Changed to ' + statusText,
                        'success'
                    )
                } else {
                    $('#changeTicketStatus').prop('selectedIndex', 0);
                }
            })

        })

    })
</script>
@stop
