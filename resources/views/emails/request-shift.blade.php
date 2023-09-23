@component('mail::message')
# Dear, {{ $name }}

Your request has been <b>{{ $status }}</b>. <br>
Check out for more details on our website by clicking the button below. <br>
@component('mail::button', ['url' => 'https://recruitment.mybnec.org/login'])
Go to website
@endcomponent

@component('vendor.mail.text.signature')
@endcomponent
@endcomponent