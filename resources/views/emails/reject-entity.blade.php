@component('mail::message')
# Hi

## Entity <a href="{{ $url }}">{{ $entityCode }}</a> Rejected

### Reason : {{ $reason }}

Kindly click the button below to Review and Resubmit your Entity for creation.

@component('mail::button', ['url' => $url])
Review Entity
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
