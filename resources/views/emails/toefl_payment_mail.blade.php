@component('mail::message')
# Hello Extraordinaries!

{{  $body }}
<br>
<br>
 {!! $receipt !!}
<br>
<br>
{!! $guidebook !!}
<br>
<br>
{!! $confirmationForm !!}
<br>
<br>
{!! $lineGroup !!}


@component('vendor.mail.text.signature')
@endcomponent
@endcomponent
