<!-- Dont use php's echo, it doesn't fully support for maatwebsite and will get some errors for some cases -->

<table>
    <thead>
    <tr>
        <th>Proyek</th>
        <th>ID Tugas</th>
        <th>Nama Tugas</th>
        <th>Status Tugas</th>
        <th>Staf Tugas</th>
        <th>Tag Tugas</th>
        <th>Tanggal Dimulai</th>
        <th>Tanggal Selesai</th>
        <th>Deskripsi Tugas</th>
    </tr>
    </thead>
    <tbody>
      @php($i = 0)
    @foreach($tasks as $task)
      <tr>
          @if($i != $task->project_id)
            <td>{{ $task->project->name ?? '-'}}</td>
          @else
            <td></td>
          @endif
          <td>{{ $task->id ?? '-'}}</td>
          <td>{{ $task->name ?? '-' }}</td>
          <td>{{ $task->status->name ?? '-' }}</td>
          <td>
            @foreach($task->task_staffs as $task_staff)
            {{ $task_staff->name ?? '-'}};
            @endforeach
          </td>
          <td>
            @foreach($task->categories as $category)
            {{ $category->name ?? '-'}};
            @endforeach
          </td>
          <td>{{ date('Y-m-d', strtotime($task->start_date)) ?? '-'}}</td>
          <td>{{ date('Y-m-d', strtotime($task->end_date)) ?? '-'}}</td>
          <td>{{ $task->description ?? '-'}}</td>
      </tr>
      @php($i = $task->project_id)
    @endforeach
    </tbody>
</table>