<table>
    <thead>
    <tr>
        <th>Nomor</th>
        @if($core_attributes[0])
            <th>Kode Ticket</th>
            <th>Nama Pembuat</th>
            <th>Lokasi Pembuat</th>
            <th>Tanggal Diajukan</th>
            <th>Tanggal Ditutup</th>
            <th>Durasi Pengerjaan</th>
            <th>Status Ticket</th>
        @else
            @if($core_attributes[1])<th>Kode Ticket</th>@endif
            @if($core_attributes[2])<th>Nama Pembuat</th>@endif
            @if($core_attributes[3])<th>Lokasi Pembuat</th>@endif
            @if($core_attributes[4])<th>Tanggal Diajukan</th>@endif
            @if($core_attributes[5])<th>Tanggal Ditutup</th>@endif
            @if($core_attributes[6])<th>Durasi Pengerjaan</th>@endif
            @if($core_attributes[7])<th>Status Ticket</th>@endif
        @endif
        @if($core_attributes[8] !== 0)<th>{{ $core_attributes[8] }}</th>@endif

        @if($secondary_attributes[0])
            <th>Jenis Aset</th>
            <th>Terminal Id</th>
            <th>Nama PIC</th>
            <th>Kontak PIC</th>
            <th>Waktu Kejadian</th>
            <th>Lokasi Kejadian</th>
            <th>Deskripsi Kerusakan</th>
        @else
            @if($secondary_attributes[1])<th>Jenis Aset</th>@endif
            @if($secondary_attributes[2])<th>Terminal Id</th>@endif
            @if($secondary_attributes[3])<th>Nama PIC</th>@endif
            @if($secondary_attributes[4])<th>Kontak PIC</th>@endif
            @if($secondary_attributes[5])<th>Waktu Kejadian</th>@endif
            @if($secondary_attributes[6])<th>Lokasi Kejadian</th>@endif
            @if($secondary_attributes[7])<th>Deskripsi Kerusakan</th>@endif
        @endif
    </tr>
    </thead>
    <tbody>
    <?php $i = 1; ?>
    @foreach($tickets as $ticket)
        <tr>
            <td>{{ $i }}</td>
            @if($core_attributes[0])
                <td>{{ $ticket->name }}</td>
                <td>{{ $ticket->creator->name ?? "-"}}</td>
                <td>{{ $ticket->creator->company->full_name ?? "-" }}</td>
                <td>{{ $ticket->raised_at }}</td>
                <td>{{ $ticket->closed_at }}</td>
                <td>{{ $ticket->resolved_times }}</td>
                <td>{{ $ticket->status }}</td>
            @else
                @if($core_attributes[1])<td>{{ $ticket->name }}</td>@endif
                @if($core_attributes[2])<td>{{ $ticket->creator->name ?? "-"}}</td>@endif
                @if($core_attributes[3])<td>{{ $ticket->creator->company->full_name }}</td>@endif
                @if($core_attributes[4])<td>{{ $ticket->raised_at }}</td>@endif
                @if($core_attributes[5])<td>{{ $ticket->closed_at ?? "-"}}</td>@endif
                @if($core_attributes[6])<td>{{ $ticket->resolved_times }}</td>@endif
                @if($core_attributes[7])<td>{{ $ticket->status }}</td>@endif
            @endif
            @if($core_attributes[8])<td>{{ $ticket->assignment_operator }}</td>@endif
            
            @if($secondary_attributes[0])
                <td>{{ $ticket->ticketable->assetType->name ?? '-' }}</td>
                <td>{{ $ticket->ticketable->product_id }}</td>
                <td>{{ $ticket->ticketable->pic_name }}</td>
                <td>{{ $ticket->ticketable->pic_contact }}</td>
                <td>{{ $ticket->ticketable->incident_time }}</td>
                <td>{{ $ticket->ticketable->full_location }}</td>
                <td>{{ $ticket->ticketable->description }}</td>
            @else
                @if($secondary_attributes[1])<td>{{ $ticket->ticketable->assetType->name ?? '-' }}</td>@endif
                @if($secondary_attributes[2])<td>{{ $ticket->ticketable->product_id }}</td>@endif
                @if($secondary_attributes[3])<td>{{ $ticket->ticketable->pic_name }}</td>@endif
                @if($secondary_attributes[4])<td>{{ $ticket->ticketable->pic_contact }}</td>@endif
                @if($secondary_attributes[5])<td>{{ $ticket->ticketable->incident_time }}</td>@endif
                @if($secondary_attributes[6])<td>{{ $ticket->ticketable->full_location }}</td>@endif
                @if($secondary_attributes[7])<td>{{ $ticket->ticketable->description }}</td>@endif
            @endif
        </tr>
        <?php $i++; ?>
    @endforeach
    </tbody>
</table>