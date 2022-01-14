@component('mail::message')

Silahkan ubah password anda pada <a href="{{ url('http://migsys.test/test?token='.$data['token']) }}">link ini</a> {{ $data['token'] }} <br>

Salam,<br>
{{ config('app.name') }}
@endcomponent
