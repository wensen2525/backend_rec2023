@component('mail::message')
# Dear, {{ $name }}

Looks like you had forgotten your password, so we will give it to you again.

Your Password: {{ $ticketNumber }}

@component('vendor.mail.text.signature')
@endcomponent
@endcomponent
