<!-- Dont use php's echo, it doesn't fully support for maatwebsite and will get some errors for some cases -->

<table>
    <thead>
    <tr>
        <th width="10" style="background-color: #ffff00;">ID Proyek</th>
        <th width="20" style="background-color: #ffff00;">Nama Proyek</th>
        <th width="20" style="background-color: #ffff00;">Status Proyek</th>
        <th width="20" style="background-color: #ffff00;">Tag Proyek</th>
        <th width="20" style="background-color: #ffff00;">Staff Proyek</th>
        <th width="20" style="background-color: #ffff00;">Tanggal Dimulai</th>
        <th width="20" style="background-color: #ffff00;">Tanggal Estimasi Selesai</th>
        <th width="15" style="background-color: #ffff00;">Jumlah Tugas</th>
    </tr>
    </thead>
    <tbody>
    @foreach($projects as $project)
      <tr>
          <td>{{ $project->id ?? '-'}}</td>
          <td>{{ $project->name ?? '-' }}</td>
          <td>{{ $project->status->name ?? '-' }}</td>
          <td>
            @foreach($project->categories as $category)
            {{ $category->name ?? '-'}};
            @endforeach
          </td>
          <td>
            @foreach($project->project_staffs as $project_staff)
            {{ $project_staff->name ?? '-'}};
            @endforeach
          </td>
          <td>{{ date('Y-m-d', strtotime($project->start_date)) ?? '-'}}</td>
          <td>{{ date('Y-m-d', strtotime($project->end_date)) ?? '-'}}</td>
          <td>{{ $project->tasks_count ?? '0'}}</td>
      </tr>
    @endforeach
    </tbody>
</table>