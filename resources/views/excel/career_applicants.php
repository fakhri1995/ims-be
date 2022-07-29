<table>
    <thead>
    <tr>
        <th>Nomor</th>
        <?php 
            if($column[0]) echo "<th>Name</th>";
            if($column[1]) echo "<th>Email</th>";
            if($column[2]) echo "<th>Phone</th>";
            if($column[3]) echo "<th>Created at</th>";
        ?>
        <th>Role Name</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>
    <?php $i = 1; ?>
    <?php foreach($applicants as $applicant):
        $role = $applicant->role;
        $status = $applicant->status;
        echo "<tr>";
        echo "<th>".$i++."</th>";
        if($column[0]) echo "<th>$applicant->name</th>";
        if($column[1]) echo "<th>$applicant->email</th>";
        if($column[2]) echo "<th>$applicant->phone</th>";
        if($column[3]) echo "<th>$applicant->created_at</th>";
        echo "<th>$role->name</th>";
        echo "<th>$status->name</th>";
        echo "</tr>";
    endforeach; ?>
    </tbody>
</table>