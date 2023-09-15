<!-- Dont use php's echo, it doesn't fully support for maatwebsite and will get some errors for some cases -->

<table>
    <thead>
    <tr>
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
    @foreach($tasks as $task)
      <tr>
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
    @endforeach
    </tbody>
</table>