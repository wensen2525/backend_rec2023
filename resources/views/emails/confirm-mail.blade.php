@component('mail::message')
# Hello Extraordinaries!

{{ $body }}
<br>
<br>
{{ $link }}

@component('vendor.mail.text.signature')
@endcomponent
@endcomponent
