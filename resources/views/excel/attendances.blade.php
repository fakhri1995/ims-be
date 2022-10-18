<table>
    <thead>
    <tr>
        <th>Nomor</th>
        <th>Nama</th>
        <th>Tanggal Check In</th>
        <th>Jam Check In</th>
        <th>Tanggal Check Out</th>
        <th>Jam Check Out</th>
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
            <td>{{ date('Y-m-d', strtotime($attendance->check_in)) }}</td>
            <td>{{ date('H:i:s', strtotime($attendance->check_in)) }}</td>
            <td>{{ $attendance->check_out ? date('d-M-Y', strtotime($attendance->check_out)) : "-"}}</td>
            <td>{{ $attendance->check_out ? date('H:i:s', strtotime($attendance->check_out)) : "-"}}</td>
            <td>{{ $attendance->geo_loc_check_in->display_name ?? "-" }}</td>
            <td>{{ $attendance->geo_loc_check_out->display_name ?? "-" }}</td>
            <td>{{ $attendance->is_wfo ? "Work From Office" : "Work From Home" }}</td>
        </tr>
        <?php $i++; ?>
    @endforeach
    </tbody>
</table>