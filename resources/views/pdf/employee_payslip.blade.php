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

    table {
      border-collapse:collapse; table-layout:fixed;
    }

    table td {
      word-wrap:break-word;
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
      line-height: 24px;
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

    #invoice-data tr, #invoice-data td * {
      vertical-align: top;
    }
  </style>

<body>
  <header>
    <table style="width: 100%; vertical-align: top;">
      <tr>
        <td width="60%">
          <img src="https://mig.id/image/LogoMig2.png" alt="MIG Logo"
            style="width:198px; height:78px; margin-bottom: 20px;">
          <h6 style="font-weight: 700; ">PT. MITRAMAS INFOSYS GLOBAL</h6>
          <p class="textCaptionSmallRegular">Tebet Raya No.42 South Jakarta, DKI Jakarta, 12820</p>
          <p class="textCaptionSmallRegular">+62-21-831-4522</p>
        </td>
        <td width="40%" style="text-align: right; vertical-align: top;">
          <h2>SLIP GAJI</h2>
          <h6 class="text-green text-bold letter-spacing">{{ $salaries['periode'] }}</h6>
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
          Nama
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
          @php
          $months = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
          $str_contract = $payslip->employee->contract->contract_start_at;
          $contract_day = date('d', strtotime($str_contract));
          $contract_month = $months[date('n', strtotime($str_contract)) - 1];
          $contract_year = date('Y', strtotime($str_contract));
          $date = sprintf("%s-%s-%s",$contract_day, $contract_month, $contract_year);
          @endphp
        {{ $date }}
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
        <!-- <tr style="font-size: 22px;">
          <td>Gaji Pokok</td>
          <td class="text-bold text-right">{{ $payslip->gaji_pokok }}</td>
          <td></td>
          <td>PPh 21</td>
          <td class="text-bold text-right">{{ $payslip->pph21 }}</td>
        </tr> -->
        @for ($i = 0; $i < $salaries['len']; $i++)
        <tr style="font-size: 22px;">
          <td>{{ $salaries['penerimaan'][$i]['name'] }}</td>
          <td class="text-bold text-right">{{ $salaries['penerimaan'][$i]['value'] }}</td>
          <td></td>
          <td>{{ $salaries['pengurangan'][$i]['name'] }}</td>
          <td class="text-bold text-right">{{ $salaries['pengurangan'][$i]['value'] }}</td>
        </tr>
        @endfor
        <tr style="font-size: 22px;">
          <td class="text-bold border">Total Penerimaan</td>
          <td class="text-bold text-right border">{{ $salaries['total_gross_penerimaan'] }}</td>
          <td></td>
          <td class="text-bold border">Total Pengurangan</td>
          <td class="text-bold text-right border">{{ $salaries['total_gross_pengurangan'] }}</td>
        </tr>
        <tr>
          <td class="text-bold border">Jumlah Diterima</td>
          <td class="text-bold text-right text-green border">{{ $salaries['total_penerimaan'] }}</td>
          <td></td>
        </tr>
        <tr>
          <td class="text-bold border">Terbilang</td>
          <td class="text-right text-green border" style="font-size: 20px;">{{ $salaries['terbilang'] }}</td>
        </tr>
      </table>
    </div>
  </main>
  <footer>
    <hr>
    <table style="width: 100%; vertical-align: top;">
      <tr>
        <td colspan="2">
          Telah dibayarkan pada tanggal <span class="text-bold">{{ $salaries["dibayarkan"] }}</span>
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