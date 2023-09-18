<!-- Dont use php's echo, it doesn't fully support for maatwebsite and will get some errors for some cases -->

<table>
    <thead>
    <tr>
        <th width="10" style="background-color: #ffff00;">ID Tugas</th>
        <th width="20" style="background-color: #ffff00;">Nama Tugas</th>
        <th width="20" style="background-color: #ffff00;">Status Tugas</th>
        <th width="20" style="background-color: #ffff00;">Staf Tugas</th>
        <th width="20" style="background-color: #ffff00;">Tag Tugas</th>
        <th width="20" style="background-color: #ffff00;">Tanggal Dimulai</th>
        <th width="20" style="background-color: #ffff00;">Tanggal Selesai</th>
        <th width="20" style="background-color: #ffff00;">Deskripsi Tugas</th>
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