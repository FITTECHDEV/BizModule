@component('mail::message')
# Hi {{ $reminder->FirstName ?? ''}} {{ $reminder->LastName ?? '' }},
Be notified that these tickets require your attention.

<p>| Open    | {{ $reminder->Open ?? '' }}    | </p>
<p>| Re-open | {{ $reminder->Re_Open ?? '' }} | </p>
<p>| Pending | {{ $reminder->Pending ?? '' }} | </p>

<p>Kindly Click the action button below to Resolve.</p>
<hr>

@component('mail::button', ['url' =>  'https://eticket.freightintime.com/ticktes/assigned'])
    Action
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
