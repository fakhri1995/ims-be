<?php

use App\User;
use App\AttendanceUser;
use Illuminate\Database\Seeder;

class FillAttendanceUserIsLateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private function fillingIsLateAttendancesUser($user_id)
    {
        $user_attendances = AttendanceUser::select('id', 'user_id', 'check_in')->where('user_id', $user_id)->orderBy('check_in', 'asc')->get();
        $check_day = "00";
        $is_late = false;
        if(count($user_attendances)){
            foreach($user_attendances as $user_attendance){
                $attendance_day = date('d', strtotime($user_attendance->check_in));
                if($check_day !== $attendance_day){
                    $check_day = $attendance_day;
                    if(date('H:i:s', strtotime($user_attendance->check_in)) > "08:15:00") $is_late = true;
                    else $is_late = false;
                }
                $user_attendance->is_late = $is_late;
                $user_attendance->save();
            }
        }
    }

    private function fillAttendancesUsers()
    {
        $last_user_id = User::orderBy('id', 'desc')->first();
        $i = 1;
        for($i = 1; $i <= $last_user_id->id; $i++){
            $this->fillingIsLateAttendancesUser($i);
        }
    }

    public function run()
    {
        $this->fillAttendancesUsers();
    }

}