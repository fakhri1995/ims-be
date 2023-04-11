<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Slip Gaji</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@1,700&display=swap" rel="stylesheet">
  <style>
    @font-face {
      font-family: 'Inter', sans-serif;
      src: url("https://fonts.gstatic.com/s/inter/v12/UcCO3FwrK3iLTeHuS_fvQtMwCp50KnMw2boKoduKmMEVuLyfMZhrib2Bg-4.ttf");
    }

    @page {
      margin: 20px 0.5cm;
    }

    html,
    body {
      /* font-family: 'Roboto', sans-serif; */
      font-family: 'Inter', sans-serif;
      height: 100%;
      color: #4D4D4D;
    }

    header {
      position: fixed;
      left: 0px;
      top: 0px;
      right: 0px;
      height: 280px;
    }


    footer {
      position: fixed;
      left: 0px;
      bottom: 0px;
      right: 0px;
      height: 200px;
    }

    footer .pagenum:after {
      content: counter(page);
    }

    main {
      padding-top: 200px;
    }

    h2 {
      font-size: 56px;
      font-weight: 700;
      line-height: 6px;
      color: #4D4D4D;
    }

    h6 {
      font-size: 30px;
      font-weight: 700;
      letter-spacing: 1.5px;
      line-height: 6px;
      margin: 16px 0px 10px;
    }

    .watermark {
      background-image: url('https://mig.id/image/allWatermark.png');
      background-size: cover;

    }

    .rowSection {
      margin: 0px 0px 20px;
      padding-bottom: 20px;
      border-bottom: 1px solid #CCCCCC;
    }

    .rightContent {
      height: 100%;
      float: right;
    }

    .leftContent {
      height: 100%;
      float: left;
    }

    .textCaptionBold {
      font-size: 22px;
      font-weight: 700;
      line-height: 6px;
      color: #4D4D4D;
    }

    .textCaptionSmallRegular {
      font-size: 20px;
      font-weight: 400;
      line-height: 6px;
    }

    .text-green {
      color: #188e4d;
    }

    .text-bold {
      font-weight: bold;
    }

    .letter-spacing {
      letter-spacing: 0.4em;
    }

    .text-right {
      text-align: right;
    }

    .text-left {
      text-align: left;
    }

    .border {
      border: 1px solid #CCCCCC;
      border-width: 1px 0px 0px;
      padding: 4px 0px;
    }

    .text-header {
      font-weight: bold;
      color: #188e4d;
      text-align: left;
    }

    .border-header {
      border: 1px solid #CCCCCC;
      border-width: 1px 0px;
      padding: 35px 0px 20px;
    }
  </style>

<body>
  <header>
    <table style="width: 100%; vertical-align: top;">
      <tr>
        <td width="60%">
          <img src="https://mig.id/image/LogoMig2.png" alt="MIG Logo"
            style="width:198px; height:78px; margin-bottom: 20px;">
          <h6 style="font-weight: 700; ">MITRAMAS INFOSYS GLOBAL</h6>
          <p class="textCaptionSmallRegular">Tebet Raya No.42 South Jakarta, DKI Jakarta, 12820</p>
          <p class="textCaptionSmallRegular">+62-21-831-4522</p>
        </td>
        <td width="40%" style="text-align: right; vertical-align: top;">
          <h2>SLIP GAJI</h2>
          <h6 class="text-green text-bold letter-spacing">OKTOBER 2022</h6>
        </td>
      </tr>
    </table>
    <hr>
  </header>

  <main>
    <!-- EMPLOYEE INFO SECTION -->
    <table style="width: 100%; margin: 40px 0px 20px;" cellpadding="10">
      <tr class="textCaptionBold">
        <td width="20%" style="color: #808080; ">
          Name
        </td>
        <td width="20%">
          {{ $payslip->employee->name }}
        </td>
        <td width="10%">
        </td>
        <td width="20%" style="color: #808080; ">
          Jabatan
        </td>
        <td width="20%">
          {{ $payslip->employee->contract->role->name }}
        </td>
      </tr>
      <tr class="textCaptionBold">
        <td width="20%" style="color: #808080;">
          NIP
        </td>
        <td width="20%">
        {{ $payslip->employee->nip }}
        </td>
        <td width="10%">
        </td>
        <td width="20%" style="color: #808080;">
          Status
        </td>
        <td width="20%">
        {{ $payslip->employee->contract->contract_status->name }}
        </td>
      </tr>
      <tr class="textCaptionBold">
        <td width="20%" style="color: #808080;">
          Total Hari Kerja
        </td>
        <td width="20%">
        {{ $payslip->total_hari_kerja }} hari
        </td>
        <td width="10%">
        </td>
        <td width="20%" style="color: #808080;">
          Tanggal Mulai Kerja
        </td>
        <td width="20%">
        {{ $payslip->employee->contract->contract_start_at }}
        </td>
      </tr>
    </table>

    <!-- Table -->
    <div class="watermark">
      <table id="invoice-data" style="width: 100%; top: 10px; " cellpadding="5">
        <tr class="textCaptionBold">
          <th width="22%" class="text-header border-header">DESKRIPSI</th>
          <th width="18%" class="text-header border-header">PENERIMAAN (IDR)</th>
          <th width="10%"></th>
          <th width="22%" class="text-header border-header">DESKRIPSI</th>
          <th width="18%" class="text-header border-header">PENGURANGAN (IDR)</th>
        </tr>
        <tr style="font-size: 22px;">
          <td>Gaji Pokok</td>
          <td class="text-bold text-right">5.000.000</td>
          <td></td>
          <td>PPh 21</td>
          <td class="text-bold text-right">125.000</td>
        </tr>
        <tr style="font-size: 22px;">
          <td>Tunjangan Makan</td>
          <td class="text-bold text-right">550.000</td>
          <td></td>
          <td>BPJS KS (5% Perusahaan)</td>
          <td class="text-bold text-right">299.250</td>
        </tr>
        <tr style="font-size: 22px;">
          <td></td>
          <td></td>
          <td></td>
          <td>BPJS TK-JKK (0,24% Perusahaan)</td>
          <td class="text-bold text-right">12.600</td>
        </tr>
        <tr style="font-size: 22px;">
          <td></td>
          <td></td>
          <td></td>
          <td>BPJS TK-JKM (0,3% Perusahaan)</td>
          <td class="text-bold text-right">15.750</td>
        </tr>
        <tr style="font-size: 22px;">
          <td class="text-bold border">Total Penerimaan</td>
          <td class="text-bold text-right border">5.550.000</td>
          <td></td>
          <td class="text-bold border">Total Pengurangan</td>
          <td class="text-bold text-right border">452.600</td>
        </tr>
        <tr>
          <td class="text-bold border">Jumlah Diterima</td>
          <td class="text-bold text-right text-green border">5.250.000</td>
          <td></td>
          <td class="text-bold border">Terbilang</td>
          <td class="text-right text-green border" style="font-size: 20px;">Lima Juta Dua Ratus Lima Puluh Ribu Rupiah
          </td>
        </tr>
      </table>
    </div>
  </main>
  <footer>
    <hr>
    <table style="width: 100%; vertical-align: top;">
      <tr>
        <td colspan="2">
          Telah dibayarkan pada tanggal <span class="text-bold">28 Oktober 2022</span>
          <br><br>
        </td>
      </tr>
      <tr>
        <td class="text-left" style="width: 70%;">
          <span class="pagenum">Halaman </span>
        </td>
        <td class="text-left" style="width: 20%; font-size: 22px; font-weight: 700; letter-spacing: 1px;">
          APPROVED BY:
        </td>
        <td class="text-left" style="width: 10%;">
          <img style="width: 160px; height: 63.72px; vertical-align: middle;" src="https://mig.id/image/LogoMig2.png"
            alt="MIG Logo">
        </td>
      </tr>
    </table>
  </footer>
</body>

</html>