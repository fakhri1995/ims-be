<table>
    <thead>
    <tr>
        <th>Nomor</th>
        <th>Nama</th>
        <th>Waktu</th>
        @foreach($attendance_form->details as $detail)
        <th>{{ $detail['name'] }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    <?php $i = 1; ?>
    @foreach($activities as $activity)
        <tr>
            <td>{{ $i }}</td>
            <td>{{ $activity->user->name ?? '-' }}</td>
            <td>{{ $activity->updated_at }}</td>
            <?php
            foreach($attendance_form->details as $detail){
                $search = array_search($detail['key'], array_column($activity->details, 'key'));

                if($search !== false){
                    $value = $activity->details[$search]['value'];
                    if($detail['type'] !== 3) echo "<td> ".htmlentities($value)." </td>";
                    else {
                        if(count($value)){
                            $checklist_value = "";
                            $index_checklist = 1;
                            foreach($value as $item){
                                if($index_checklist !== 1) $checklist_value .= ", ";
                                $checklist_value .= $detail['list'][$item];
                                $index_checklist++;
                            }
                            echo "<td> $checklist_value </td>";
                        } else echo "<td> - </td>";
                    } 
                } else echo "<td> - </td>";
            }
            ?>
        </tr>
        <?php $i++; ?>
    @endforeach
    </tbody>
</table>