<table>
    <thead>
    <tr>
        <th>Nomor</th>
        @if($core_attributes[0] || $core_attributes[1])<th>Nama Pembuat</th>@endif
        @if($core_attributes[0] || $core_attributes[2])<th>Lokasi Pembuat</th>@endif
        @if($core_attributes[0] || $core_attributes[3])<th>Date Raised Ticket</th>@endif
        @if($core_attributes[0] || $core_attributes[4])<th>Date Closed Ticket</th>@endif
        @if($core_attributes[0] || $core_attributes[5])<th>Ticket Number</th>@endif
        @if($core_attributes[0] || $core_attributes[6])<th>Ticket Type</th>@endif
        @if($core_attributes[0] || $core_attributes[7])<th>Status Ticket</th>@endif
        @if($core_attributes[0] || $core_attributes[8])<th>Assign To</th>@endif
    </tr>
    </thead>
    <tbody>
    <?php $i = 1; ?>
    @foreach($tickets as $ticket)
        <tr>
            <td>{{ $i }}</td>
            @if($core_attributes[0] || $core_attributes[1])<td>{{ $ticket->requester->name }}</td>@endif
            @if($core_attributes[0] || $core_attributes[2])<td>{{ $ticket->requester->company->name ?? "-"}}</td>@endif
            @if($core_attributes[0] || $core_attributes[3])<td>{{ $ticket->getRawOriginal('raised_at') }}</td>@endif
            @if($core_attributes[0] || $core_attributes[4])<td>{{ $ticket->closed_at }}</td>@endif
            @if($core_attributes[0] || $core_attributes[5])<td>{{ $ticket->ticketable->id }}</td>@endif
            @if($core_attributes[0] || $core_attributes[6])<td>{{ $ticket->type->name }}</td>@endif
            @if($core_attributes[0] || $core_attributes[7])<td>{{ $ticket->status->name }}</td>@endif
            @if($core_attributes[0] || $core_attributes[8])<td>{{ $ticket->assignable->name }}</td>@endif
        </tr>
        <?php $i++; ?>
    @endforeach
    </tbody>
</table>