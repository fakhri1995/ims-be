@component('mail::message')

Silahkan gunakan token ini untuk merubah password anda {{ $data['token'] }} <br>

Salam,<br>
{{ config('app.name') }}
@endcomponent
