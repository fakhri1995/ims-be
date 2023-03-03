<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Slip Gaji</title>
  <style>
    @font-face {
      font-family: 'Inter';
      src: url("https://fonts.gstatic.com/s/inter/v12/UcCO3FwrK3iLTeHuS_fvQtMwCp50KnMw2boKoduKmMEVuLyfMZhrib2Bg-4.ttf");
    }

    html,
    body {
      flex-direction: column;
      background-color: white;
      font-family: 'Inter', sans-serif;
      color: #4D4D4D;
      width: 100%;
      height: 100%;
      margin: 0px;
    }

    h2 {
      font-size: 32px;
      font-weight: 700;
      line-height: 1.5px;
      color: #4D4D4D;
    }

    h6 {
      font-size: 14px;
      font-weight: 500;
      letter-spacing: 1.5px;
      line-height: 1.5px;
      margin: 16px 0px 10px;
    }

    .rowSection {
      margin: 0px 32px 20px;
      padding-bottom: 20px;
      border-bottom: 1px solid #CCCCCC;
    }

    .rowBetween {
      width: 100%;
      height: 20px;
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
      font-size: 12px;
      font-weight: 700;
      line-height: 1.5px;
      color: #4D4D4D;
    }

    .textCaptionSmallBold {
      font-size: 10px;
      font-weight: 700;
      line-height: 1.5px;
    }

    .textCaptionSmallRegular {
      font-size: 10px;
      font-weight: 400;
      line-height: 1.5px;
    }

    .footerPage {
      width: 100%;
      height: 40px;
      line-height: 20px;
      padding: 20px 0;
      align-items: center;
    }
  </style>
</head>

<body>
  <!-- Header -->
  <div class="rowSection" style="margin-top: 24px; height: 100px;">
    <div class="leftContent">
      <img style="width:99px; height:39.43px;" src='https://mig.id/image/LogoMig2.png' alt="MIG Logo">
      <h6 style="color:#000000;">
        MITRAMAS INFOSYS GLOBAL
      </h6>
      <div class="textCaptionSmallRegular" style="display: flex; flex-direction: row;">
        <p style="color: #808080;margin-right: 8px;">Alamat</p>
        <p style="color: #4D4D4D;">
          Tebet raya no. 42 South Jakarta, DKI Jakarta,12820
        </p>
      </div>
      <div class="textCaptionSmallRegular" style="display: flex; flex-direction: row;">
        <p style="color: #808080;margin-right: 8px;">No. Telp</p>
        <p style="color: #4D4D4D;">
          +62-21-831-4522
        </p>
      </div>
    </div>
    <div class="rightContent">
      <h2 style="text-align: end;">SLIP GAJI</h2>
      <h6 style="color: #35763B; font-weight: 700; text-align: end;">
        OKTOBER 2022
      </h6>
    </div>
  </div>

  <!-- Body -->
  <!-- EMPLOYEE INFO SECTION -->
  <div class="rowSection" style="border-bottom-width: 0px; padding-bottom: 0px; height: 62px;">
    <!-- Left column  -->
    <div style="width: 330px;" class="leftContent">
      <div class="textCaptionBold" style="color: #808080; width: 120px; margin-right: 12px; float: left; ">
        <p style="padding-bottom: 10px;">Name</p>
        <p style="padding-bottom: 10px;">NIP</p>
        <p style="padding-bottom: 10px;">Total Hari Kerja</p>
      </div>
      <div class="textCaptionBold" style="float: left;">
        <p style="padding-bottom: 10px;">Bunga Mawar Indah</p>
        <p style="padding-bottom: 10px;">71231922</p>
        <p style="padding-bottom: 10px;">10 hari</p>
      </div>
    </div>

    <!-- Right column -->
    <div style="width: 330px;" class="rightContent">
      <div class="textCaptionBold" style="color: #808080; width: 120px; margin-right: 12px; float: left; ">
        <p style="padding-bottom: 10px;">Jabatan</p>
        <p style="padding-bottom: 10px;">Status</p>
        <p style="padding-bottom: 10px;">Tanggal Mulai Kerja</p>
      </div>
      <div class="textCaptionBold" style="float: left;">
        <p style="padding-bottom: 10px;">Admin Operasional</p>
        <p style="padding-bottom: 10px;">Tetap</p>
        <p style="padding-bottom: 10px;">
          3 Januari 2022
        </p>
      </div>
    </div>
  </div>

  <!-- Payslip detail table -->
  <div style="position: relative; margin: 0px 32px">
    <img src='https://mig.id/image/allWatermark.png' alt="MIG watermark" style="width: 100%;">
    <div style="position: absolute; top: 10px; width: 100%;">
      <!-- Benefit list -->
      <div class="rowSection" style="border-bottom-width: 0px; margin: 0px;">
        <!-- Table Penerimaan -->
        <table style="width: 330px; table-layout: fixed;" class="leftContent">
          <tr class="textCaptionBold" style="color: #35763B;	
            text-align: left;  
            height: 10px;">
            <th style="width: 190px; padding: 16px 0px;						          
            border: 1px solid #CCCCCC; border-width: 1px 0px;">
              DESKRIPSI
            </th>
            <th style="width: 140px; padding: 16px 0px;						          
            border: 1px solid #CCCCCC; border-width: 1px 0px;">
              PENERIMAAN (IDR)
            </th>
          </tr>
          <!-- TODO: loop benefit penerimaan -->
          <tr style="margin-top: 16px; height: 20px;">
            <td class="textCaptionSmallRegular">Gaji Pokok</td>
            <td class="textCaptionSmallBold" style="text-align: right;">
              5.000.000
            </td>
          </tr>
          <tr style="margin-top: 16px; height: 20px;">
            <td class="textCaptionSmallRegular">Tunjangan Uang Makan</td>
            <td class="textCaptionSmallBold" style="text-align: right;">
              550.000
            </td>
          </tr>
        </table>

        <!-- Table Pengurangan -->
        <table style="width: 330px; table-layout: fixed;" class="rightContent">
          <tr class="textCaptionBold" style="color: #35763B;
						text-align: left;
            height: 10px;">
            <th style="width: 190px; padding: 16px 0px;						          
            border: 1px solid #CCCCCC; border-width: 1px 0px;">
              DESKRIPSI
            </th>
            <th style="width: 140px; padding: 16px 0px;						          
            border: 1px solid #CCCCCC; border-width: 1px 0px;">
              PENGURANGAN (IDR)
            </th>
          </tr>
          <!-- TODO: loop benefit pengurangan -->
          <tr style="margin-top: 16px; height: 20px;">
            <td class="textCaptionSmallRegular">PPh 21</td>
            <td class="textCaptionSmallBold" style="text-align: right;">
              125.000
            </td>
          </tr>
          <tr style="margin-top: 16px; height: 20px;">
            <td class="textCaptionSmallRegular">BPJS KS (5% Perusahaan)</td>
            <td class="textCaptionSmallBold" style="text-align: right;">
              299.250
            </td>
          </tr>
          <tr style="margin-top: 16px; height: 20px;">
            <td class="textCaptionSmallRegular">BPJS TK-JKK (0,24% Perusahaan) </td>
            <td class="textCaptionSmallBold" style="text-align: right;">
              12.600
            </td>
          </tr>
          <tr style="margin-top: 16px; height: 20px;">
            <td class="textCaptionSmallRegular">BPKS TK-JKM (0,3% Perusahaan) </td>
            <td class="textCaptionSmallBold" style="text-align: right;">
              15.750
            </td>
          </tr>
        </table>
      </div>

      <!-- Summary row-->
      <div style="padding-bottom: 20px;">
        <!-- Total Penerimaan -->
        <div style="width: 330px; margin-right: 66px;" class="leftContent">
          <div class="rowBetween textCaptionSmallBold" style="border: 1px solid #CCCCCC;
									border-width: 1px 0px; 
									padding: 12px 0px;">
            <p class="leftContent" style="width: 190px;">Total Penerimaan</p>
            <p class="rightContent" style="width: 140px; text-align: right;">
              5.997.600
            </p>
          </div>
          <div class="rowBetween textCaptionBold" style="margin-top:16px;">
            <p class="leftContent" style="width: 190px;">Jumlah Diterima</p>
            <p class="rightContent" style="width: 140px; color: #35763B; text-align: right;">
              5.250.000
            </p>
          </div>
        </div>
      </div>

      <!-- Total Pengurangan -->
      <div style="width: 330px;" class="rightContent">
        <div class="rowBetween textCaptionSmallBold" style="border: 1px solid #CCCCCC;
									border-width: 1px 0px;
									padding: 12px 0px;">
          <p class="leftContent">Total Pengurangan</p>
          <p class="rightContent" style="text-align: right;">
            747.600
          </p>
        </div>
        <div class="rowBetween" style="margin-top:16px;">
          <p class="textCaptionSmallBold leftContent">Terbilang</p>
          <p class="textCaptionSmallRegular rightContent"
            style="color: #35763B; text-transform: capitalize; text-align: right;">
            Lima Juta Dua Ratus Lima Puluh Ribu Rupiah
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="textCaptionBold" style="position: absolute; bottom: 0px; width: 100%;">
    <div style="margin: 0px 32px; border-top: 1px solid #CCCCCC; padding-top: 20px;">
      <p style="font-weight: 400; padding-bottom: 10px;">
        Telah dibayarkan pada tanggal
        <span class="textCaptionBold" style="color: black;">
          28 Oktober 2022
        </span>
      </p>
      <div class="footerPage">
        <p style="color: black;" class="leftContent">
          1 <span style="color: #808080;">/ 1</span>
        </p>
        <div style="align-items: center; width: 170px;" class="rightContent">
          <p style="font-size: 8px; font-weight: 700; letter-spacing: 1px;" class="leftContent">
            APPROVED BY:
          </p>
          <img style="width: 80px; height: 31.86px;" class="rightContent" src="https://mig.id/image/LogoMig2.png"
            alt="MIG Logo">
        </div>
      </div>
    </div>
  </footer>

</body>

</html>