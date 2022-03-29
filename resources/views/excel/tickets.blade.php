<table>
    <thead>
    <tr>
        <th>Nomor</th>
        @if($core_attributes[0] || $core_attributes[1])<th>Kode Ticket</th>@endif
        @if($core_attributes[0] || $core_attributes[2])<th>Nama Pembuat</th>@endif
        @if($core_attributes[0] || $core_attributes[3])<th>Lokasi Pembuat</th>@endif
        @if($core_attributes[0] || $core_attributes[4])<th>Tanggal Diajukan</th>@endif
        @if($core_attributes[0] || $core_attributes[5])<th>Tanggal Ditutup</th>@endif
        @if($core_attributes[0] || $core_attributes[6])<th>Durasi Pengerjaan</th>@endif
        @if($core_attributes[0] || $core_attributes[7])<th>Status Ticket</th>@endif
        @if($core_attributes[8] !== 0)<th>{{ $core_attributes[8] }}</th>@endif
    </tr>
    </thead>
    <tbody>
    <?php $i = 1; ?>
    @foreach($tickets as $ticket)
        <tr>
            <td>{{ $i }}</td>
            @if($core_attributes[0] || $core_attributes[1])<td>{{ $ticket->name }}</td>@endif
            @if($core_attributes[0] || $core_attributes[2])<td>{{ $ticket->creator->name ?? "-"}}</td>@endif
            @if($core_attributes[0] || $core_attributes[3])<td>{{ $ticket->creator->company->full_name }}</td>@endif
            @if($core_attributes[0] || $core_attributes[4])<td>{{ $ticket->raised_at }}</td>@endif
            @if($core_attributes[0] || $core_attributes[5])<td>{{ $ticket->closed_at ?? "-"}}</td>@endif
            @if($core_attributes[0] || $core_attributes[6])<td>{{ $ticket->resolved_times }}</td>@endif
            @if($core_attributes[0] || $core_attributes[7])<td>{{ $ticket->status }}</td>@endif
            @if($core_attributes[8])<td>{{ $ticket->assignment_operator }}</td>@endif
        </tr>
        <?php $i++; ?>
    @endforeach
    </tbody>
</table>