<table>
    <thead>
    <tr>
        <th>Nomor</th>
        <th>Ticket Number</th>
        <th>Ticket Type</th>
        <th>Nama Pembuat</th>
        <th>Lokasi Pembuat</th>
        <th>Date Raised Ticket</th>
        <th>Date Closed Ticket</th>
        <th>Resolved Time</th>
        <th>Status Ticket</th>
        <th>Assign To</th>
    </tr>
    </thead>
    <tbody>
    <?php $i = 1; ?>
    @foreach($tickets as $ticket)
        <tr>
            <td>{{ $i }}</td>
            <td>{{ $ticket->ticketable->id }}</td>
            <td>{{ $ticket->type->name }}</td>
            <td>{{ $ticket->requester->name }}</td>
            <td>{{ $ticket->requester->company->name ?? "-"}}</td>
            <td>{{ $ticket->raised_at }}</td>
            <td>{{ $ticket->closed_at }}</td>
            <td>{{ $ticket->resolved_time }}</td>
            <td>{{ $ticket->status->name }}</td>
            <td>{{ $ticket->assignable->name }}</td>
        </tr>
        <?php $i++; ?>
    @endforeach
    </tbody>
</table>