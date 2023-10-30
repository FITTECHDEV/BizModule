@component('mail::message')
# Hi @if(!$admin){{ $ticket->user->full_name }}@else Admin @endif, 
This is to inform you that @if(!$admin) your @endif ticket <a href="{{ $url }}">#{{ $ticket->TicketNo }}</a> has been successfully created @if($admin)by <b>{{ $ticket->user->full_name }}, {{ $ticket->user->Email }}</b>@endif. <br>
Kindly @if(!$admin) be patient as we @endif look into the issue @if(!$admin)you have @endif raised.
<hr>
# RE: {!! ucwords($ticket->Title) !!} <br>

{!! ucfirst($ticket->Description) !!}

@component('mail::button', ['url' => $url])
    View Ticket in FIT
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
