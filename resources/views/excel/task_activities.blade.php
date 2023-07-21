<!-- Dont use php's echo, it doesn't fully support for maatwebsite and will get some errors for some cases -->

<table>
    <thead>
    <tr>
        <th>Nomor</th>
        <th>Nama</th>
        <th>Tanggal</th>
        <th>Jam</th>
        <th>Aktivitas</th>
    </tr>
    </thead>
    <tbody>
    <?php $i = 1; ?>
    @foreach($activities as $activity)
        <tr>
            <td>{{ $i }}</td>
            <td>{{ $activity->user->name ?? '-' }}</td>
            <td>{{ date('Y-m-d', strtotime($activity->updated_at)) }}</td>
            <td>{{ date('H:i:s', strtotime($activity->updated_at)) }}</td>
            <td>{{ $activity->activity ?? '-' }}</td>
        </tr>
        <?php $i++; ?>
    @endforeach
    </tbody>
</table>