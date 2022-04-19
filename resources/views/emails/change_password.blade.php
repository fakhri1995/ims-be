<body>
<h2>Selamat datang di Migsys</h2> 
<img src="{{ $message->embed($image_url) }}" height="100px" alt="" />
<h3>Konfirmasi Email Anda</h3> 
Hi {{$data['username']}} <br>
Anda menerima pemberitahuan email ini karena Anda baru terdaftar di Migsys. Silahkan melakukan aktivasi akun! <br>
Abaikan email ini jika anda merasa tidak terdaftar di Migsys. <br> <br>  

<a href="{{ $data['url'].'/changePassword?token='.$data['token'] }}" target="_blank" style="background-color: #35763B; /* Green */
  border: none;
  border-radius: 10px;
  color: white;
  padding: 10px 32px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;">Konfirmasi Akun</a> <br> <br>


Jika anda memiliki pertanyaan, silahkan menghubungi kami di help@mitrasolusi.group<br>
Salam,<br>
Mitramas Infosys Global
</body>