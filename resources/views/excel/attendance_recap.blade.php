<table>
    <thead>
    <tr>
        <th>Number</th>
        <th>Employee Name</th>
        <th>Role</th>
        <th>Company</th>
        <th>Total Daily Work</th>
        <th>WFO</th>
        <th>WFH</th>
        <th>Late</th>
        <th>Alpha</th>
				<th>Leave</th>
				<th>Overtime</th>
    </tr>
    </thead>
    <tbody>
    <?php $i = 1; ?>
    @foreach($data_array as $data)
        <tr>
            <td>{{ $i }}</td>
            <td>{{ $data->name }}</td>
            <td>{{ $data->position }}</td>
            <td>{{ $data->company }}</td>
            <td>{{ $data->total_work_day }}</td>
            <td>{{ $data->wfo_count }}</td>
            <td>{{ $data->wfh_count }}</td>
            <td>{{ $data->late_count }}</td>
            <td>{{ $data->alpha_count }}</td>
            <td>{{ $data->leave_count }}</td>
            <td>{{ $data->overtime_count }}</td>
        </tr>
        <?php $i++; ?>
    @endforeach
    </tbody>
</table>