<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketTaskType extends Model
{
    use SoftDeletes;
    public $timestamps = false;

    public function taskType()
    {
        return $this->belongsTo(TaskType::class);
    }

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }
}
