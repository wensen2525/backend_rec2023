@component('mail::message')
# Dear, {{ $name }}

Thank you for your registration. Your account has been created. <br>
Here is your credentials, please keep in mind that this credentials is <b>confidential</b>.

NIM: {{ $nim }} <br>
Password: {{ $password }} <br><br>

You can login to the website by clicking the button below.
@component('mail::button', ['url' => 'https://recruitment.mybnec.org/login'])
Go to website
@endcomponent

@component('vendor.mail.text.signature')
@endcomponent
@endcomponent
