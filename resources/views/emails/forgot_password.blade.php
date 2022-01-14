@component('mail::message')

Silahkan ubah password anda pada <a href="{{ url('https://migsys.herokuapp.com/forgetPassword?token='.$data['token']) }}">link ini</a>  <br>

Salam,<br>
{{ config('app.name') }}
@endcomponent
