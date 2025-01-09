<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <!--[if gte mso 9]>
<xml>
  <o:OfficeDocumentSettings>
    <o:AllowPNG/>
    <o:PixelsPerInch>96</o:PixelsPerInch>
  </o:OfficeDocumentSettings>
</xml>
<![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <!--[if !mso]><!-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"><!--<![endif]-->
    <title></title>
    <style>
        /* Warna tombol biru dasar */
        .blue-button {
            background-color: #007BFF;
            /* Warna biru */
            border: none;
            /* Hapus border default */
            color: white !important;
            /* Warna teks putih */
            padding: 10px 20px;
            /* Padding tombol */
            text-align: center;
            /* Posisikan teks di tengah */
            text-decoration: none;
            /* Hapus garis bawah pada teks */
            display: inline-block;
            /* Tampilkan sebagai elemen inline-block */
            font-size: 16px;
            /* Ukuran font */
            margin: 4px 2px;
            /* Margin */
            cursor: pointer;
            /* Ubah cursor menjadi pointer */
            border-radius: 4px;
            /* Sudut tombol melengkung */
            transition: background-color 0.3s ease;
            /* Transisi untuk perubahan warna background */
        }
        /* Efek hover untuk tombol */
        .blue-button:hover {
            background-color: #0056b3;
            /* Warna biru lebih gelap saat hover */
        }
        /* Efek aktif untuk tombol */
        .blue-button:active {
            background-color: #004494;
            /* Warna biru lebih gelap saat tombol ditekan */
        }
    </style>
</head>
<body>
    <div style="width: 100%">
        <div style="padding-bottom: 2rem">
            Your OTP is {{ $data->token }}. It is valid for 10 minutes. DO NOT share it with anyone.
        </div>
    </div>
</body>
</html>