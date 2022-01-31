<body>
<h2>Reset Password Migsys</h2> 
<img src="{{ $message->embed($image_url) }}" height="100px" alt="" />
<h3>Reset Password Akun Migsys Anda</h3> 
Hi {{$data['username']}} <br>
Anda menerima pemberitahuan email ini karena Anda telah lupa password Akun Migsys. Silahkan melakukan reset password! <br>
Abaikan email ini jika anda merasa tidak menggunakan fitur reset password. <br> <br>  

<a href="{{ 'https://migsys.herokuapp.com/resetPassword?token='.$data['token'] }}" target="_blank" style="background-color: #35763B; /* Green */
  border: none;
  border-radius: 10px;
  color: white;
  padding: 10px 32px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;">Reset Password</a> <br> <br>


Jika anda memiliki pertanyaan, silahkan menghubungi kami di help@mitrasolusi.group<br>
Salam,<br>
Mitramas Infosys Global
</body>