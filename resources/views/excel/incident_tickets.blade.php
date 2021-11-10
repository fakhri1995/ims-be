<table>
    <thead>
    <tr>
        <th>Nomor</th>
        @if($core_attributes[0])
            <th>Nama Pembuat</th>
            <th>Lokasi Pembuat</th>
            <th>Date Raised Ticket</th>
            <th>Date Closed Ticket</th>
            <th>Ticket Number</th>
            <th>Ticket Type</th>
            <th>Status Ticket</th>
            <th>Assign To</th>
        @else
            @if($core_attributes[1])<th>Nama Pembuat</th>@endif
            @if($core_attributes[2])<th>Lokasi Pembuat</th>@endif
            @if($core_attributes[3])<th>Date Raised Ticket</th>@endif
            @if($core_attributes[4])<th>Date Closed Ticket</th>@endif
            @if($core_attributes[5])<th>Ticket Number</th>@endif
            @if($core_attributes[6])<th>Ticket Type</th>@endif
            @if($core_attributes[7])<th>Status Ticket</th>@endif
            @if($core_attributes[8])<th>Assign To</th>@endif
        @endif

        @if($secondary_attributes[0])
            <th>Jenis Produk</th>
            <th>Id Produk</th>
            <th>Nama PIC</th>
            <th>Kontak PIC</th>
            <th>Problem</th>
            <th>Lokasi Problem</th>
            <th>Waktu Kejadian</th>
        @else
            @if($secondary_attributes[1])<th>Jenis Produk</th>@endif
            @if($secondary_attributes[2])<th>Id Produk</th>@endif
            @if($secondary_attributes[3])<th>Nama PIC</th>@endif
            @if($secondary_attributes[4])<th>Kontak PIC</th>@endif
            @if($secondary_attributes[5])<th>Problem</th>@endif
            @if($secondary_attributes[6])<th>Lokasi Problem</th>@endif
            @if($secondary_attributes[7])<th>Waktu Kejadian</th>@endif
        @endif
    </tr>
    </thead>
    <tbody>
    <?php $i = 1; ?>
    @foreach($tickets as $ticket)
        <tr>
            <td>{{ $i }}</td>
            @if($core_attributes[0])
                <td>{{ $ticket->requester->name }}</td>
                <td>{{ $ticket->requester->company->name ?? "-"}}</td>
                <td>{{ $ticket->getRawOriginal('raised_at') }}</td>
                <td>{{ $ticket->closed_at }}</td>
                <td>{{ $ticket->ticketable->id }}</td>
                <td>{{ $ticket->type->name }}</td>
                <td>{{ $ticket->status->name }}</td>
                <td>{{ $ticket->assignable->name }}</td>
            @else
                @if($core_attributes[1])<td>{{ $ticket->requester->name }}</td>@endif
                @if($core_attributes[2])<td>{{ $ticket->requester->company->name ?? "-"}}</td>@endif
                @if($core_attributes[3])<td>{{ $ticket->getRawOriginal('raised_at') }}</td>@endif
                @if($core_attributes[4])<td>{{ $ticket->closed_at }}</td>@endif
                @if($core_attributes[5])<td>{{ $ticket->ticketable->id }}</td>@endif
                @if($core_attributes[6])<td>{{ $ticket->type->name }}</td>@endif
                @if($core_attributes[7])<td>{{ $ticket->status->name }}</td>@endif
                @if($core_attributes[8])<td>{{ $ticket->assignable->name }}</td>@endif
            @endif
            
            @if($secondary_attributes[0])
                <td>{{ $ticket->ticketable->productType->name }}</td>
                <td>{{ $ticket->ticketable->product_id }}</td>
                <td>{{ $ticket->ticketable->pic_name }}</td>
                <td>{{ $ticket->ticketable->pic_contact }}</td>
                <td>{{ $ticket->ticketable->problem }}</td>
                <td>{{ $ticket->ticketable->location->topParent ? $ticket->ticketable->location->topParent->name.' - '.$ticket->ticketable->location->name : $ticket->ticketable->location->name }}</td>
                <td>{{ $ticket->ticketable->incident_time }}</td>
            @else
                @if($secondary_attributes[1])<td>{{ $ticket->ticketable->productType->name }}</td>@endif
                @if($secondary_attributes[2])<td>{{ $ticket->ticketable->product_id }}</td>@endif
                @if($secondary_attributes[3])<td>{{ $ticket->ticketable->pic_name }}</td>@endif
                @if($secondary_attributes[4])<td>{{ $ticket->ticketable->pic_contact }}</td>@endif
                @if($secondary_attributes[5])<td>{{ $ticket->ticketable->problem }}</td>@endif
                @if($secondary_attributes[6])<td>{{ $ticket->ticketable->location->topParent ? $ticket->ticketable->location->topParent->name.' - '.$ticket->ticketable->location->name : $ticket->ticketable->location->name }}</td>@endif
                @if($secondary_attributes[7])<td>{{ $ticket->ticketable->incident_time }}</td>@endif
            @endif
        </tr>
        <?php $i++; ?>
    @endforeach
    </tbody>
</table>