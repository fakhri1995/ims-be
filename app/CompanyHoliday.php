<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyHoliday extends Model
{
    public function company(){
        return $this->belongsTo(Company::class);
    }
}
