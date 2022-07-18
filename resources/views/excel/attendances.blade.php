<table>
    <thead>
    <tr>
        <th>Nomor</th>
        <th>Nama</th>
        <th>Waktu Check In</th>
        <th>Waktu Check Out</th>
        <th>Lokasi Check In</th>
        <th>Lokasi Check Out</th>
        <th>WFO / WFH</th>
    </tr>
    </thead>
    <tbody>
    <?php $i = 1; ?>
    @foreach($attendances as $attendance)
        <tr>
            <td>{{ $i }}</td>
            <td>{{ $attendance->user->name }}</td>
            <td>{{ $attendance->check_in }}</td>
            <td>{{ $attendance->check_out ?? "-"}}</td>
            <td>{{ $attendance->geo_loc_check_in->display_name ?? "-" }}</td>
            <td>{{ $attendance->geo_loc_check_out->display_name ?? "-" }}</td>
            <td>{{ $attendance->is_who == 1 ? "Work From Office" : "Work From Home" }}</td>
        </tr>
        <?php $i++; ?>
    @endforeach
    </tbody>
</table>