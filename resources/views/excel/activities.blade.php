<!-- Dont use php's echo, it doesn't fully support for maatwebsite and will get some errors for some cases -->

<table>
    <thead>
    <tr>
        <th>Nomor</th>
        <th>Nama</th>
        <th>Tanggal</th>
        <th>Jam</th>
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
            <td>{{ date('Y-m-d', strtotime($activity->updated_at)) }}</td>
            <td>{{ date('H:i:s', strtotime($activity->updated_at)) }}</td>
            <?php
            foreach($attendance_form->details as $detail){
                $search = array_search($detail['key'], array_column($activity->details, 'key'));

                if($search !== false){
                    $value = $activity->details[$search]['value'];
                    if($detail['type'] !== 3){ ?>
                        <td> {{ $value }} </td>
                    <?php } 
                    else {
                        if(count($value)){
                            $checklist_value = "";
                            $index_checklist = 1;
                            foreach($value as $item){
                                if($index_checklist !== 1) $checklist_value .= ", ";
                                $checklist_value .= $detail['list'][$item];
                                $index_checklist++;
                            }
                            ?> <td> {{ $checklist_value }} </td> <?php
                        } else {
                            ?> <td> - </td> <?php 
                        } 
                    } 
                } else {
                    ?> <td> - </td> <?php 
                } 
            }
            ?>
        </tr>
        <?php $i++; ?>
    @endforeach
    </tbody>
</table>