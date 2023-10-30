@component('mail::message')
# Hi {{ $ticket->UserId }}, 
This is to inform you that your ticket <a href="{{ $url }}">#{{ $ticket->TicketNo }}</a> has been successfully logged.
        Kindly be patient as we look into it.
<hr>
# RE: {{ ucwords($ticket->Title) }} <br>

{!! ucfirst($ticket->Description) !!}

@component('mail::button', ['url' => $url])
View Ticket in FIT
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
