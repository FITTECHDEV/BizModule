@component('mail::message')
# Hi {{ $ticket->user->FirstName ?? 'User'}},
This is to inform you that your  ticket <a href="{{ $url }}">#{{ $ticket->TicketNo }}</a> has been successfully <b>Solved</b>.
Kindly confirm and close the ticket.
<hr>
<p>
    #Title: {!! ucwords($ticket->Title) !!} <br>
</p>
<p>
    {!! ucfirst($ticket->Description) !!}
</p>

@component('mail::button', ['url' => $url, 'color' => 'success'])
    View Ticket in FIT E-Ticket
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
