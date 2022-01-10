<!DOCTYPE html>
<html lang="en">

<head>
    <style type="text/css">
        @page {
            margin:0;
        }
        body {
            background-color: rgb(239, 245, 239);
            padding-left: 2rem;
            padding-right: 2rem;
            padding-top: 3rem;
            padding-bottom: 4rem;
            height:1100px; 
            width: 730px;
        }

        h1 {
            color: black;
            font-size: 20px;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            margin-bottom: 0;
        }

        h2 {
            color: gray;
            font-size: smaller;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            margin-bottom: 0;
        }

        p {
            color: black;
            font-size: small;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
        }

        .boxContainer {
            display: flex;
            flex-direction: column;
        }

        .boxRow {
            margin-bottom: 1rem;
        }

        .boxColumn {
            margin-bottom: 2rem;
        }

        .box {
            background-color: white;
            box-shadow: 0.2rem 0.2rem 5px #bbbbbb;
            border-radius: 0.5rem;
            padding-top: 1rem;
            padding-left: 3rem;
            padding-right: 3rem;
        }
    </style>
</head>

<body>
    <div>
        <div class="boxRow">
            <div class="box" style="justify-content: space-between; align-items: center;">
                <img src="{{ public_path('img/logoTicket.png') }}" height="50px" alt="">
                <h1 style="margin-top: 5px; float:right;">Detail Tiket</h1>
            </div>
        </div>
        <div class="boxRow">
            <div class="boxColumn" style="float:right;">
                <div class="box" style="margin-bottom: 1rem;">
                    <div class="diajukanOleh" style=" margin-bottom: 1rem;">
                        <h2>Diajukan Oleh</h2>
                        <p>{{ $ticket->task->creator->name }}</p>
                    </div>
                    <div class="lokasi" style=" margin-bottom: 1rem;">
                        <h2>Lokasi</h2>
                        <p style="text-transform: capitalize;">{{ $ticket->task->creator->company->name ?? '-'}}</p>
                    </div>
                    <div class="waktuKejadian" style=" margin-bottom: 1rem;">
                        <h2>Waktu Pengajuan</h2>
                        <p style=" margin-bottom: 2rem;">{{ $ticket->raised_at }}</p>
                    </div>
                </div>
            </div>
            <div class="boxColumn" style="margin-right: 1rem;">
                <div class="box" style=" width:350px; ">
                    <div style=" margin-bottom: 1rem; float:right; padding-right:2rem;">
                        <h2 style="font-size: 18px;">Tipe Tiket</h2>
                        <h1>{{ $ticket->type->name }}</h1>
                    </div>
                    <div style=" margin-bottom: 3rem;">
                        <h2 style="font-size: 18px;">Nomor Tiket</h2>
                        <h1>{{ '#'. $ticket->type->code .'-'. $ticket->ticketable_id }}</h1>
                    </div>
                    <div>
                        <h2 style="font-size: 18px;">Status</h2>
                        @if($ticket->task->status === 1) <h1 style="color: red; margin-bottom: 3rem;">
                        @else <h1 style="margin-bottom: 3rem;">
                        @endif
                        {{ $ticket->status }}</h1>
                    </div>
                </div>
                
            </div>
            <div class="boxColumn">
                <div class="box" style="">
                    <div class="tipeAset" style=" margin-bottom: 1rem;">
                        <h2>Tipe Aset</h2>
                        <p>{{ $ticket->ticketable->assetType->name }}</p>
                    </div>
                    <div class="lokasi" style=" margin-bottom: 1rem;">
                        <h2>Lokasi</h2>
                        <p>{{ $ticket->task->location->full_location }}</p>
                    </div>
                    <div class="waktuKejadian" style=" margin-bottom: 1rem;">
                        <h2>Waktu Kejadian</h2>
                        <p>{{ $ticket->ticketable->incident_time }}</p>
                    </div>
                    <div class="deskripsi" style=" margin-bottom: 1rem;">
                        <h2>Deskripsi Kerusakan</h2>
                        <p style="margin-bottom: 2rem;">
                            {{ $ticket->ticketable->description }}
                        </p>
                    </div>
                </div>
            </div>
            <!-- <div class="boxColumn">

                <div class="box" style="">
                    <div class="buktiInsiden" style=" margin-bottom: 1rem;">
                        <h2 style="margin-bottom: 1.5rem;">Bukti Insiden</h2>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</body>

</html>