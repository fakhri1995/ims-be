<?php

namespace App\Exports;

use App\AttendanceUser;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;

class AttendanceRecapExport implements FromView, WithTitle
{

    public function __construct($data_array)
    {
        $this->data_array = $data_array;
    }

    public function view(): View
    {
			return view('excel.attendance_recap', [
            'data_array' => $this->data_array
        ]);
    }

    public function title(): string
    {
        return 'Attendance Recap';
    }
}