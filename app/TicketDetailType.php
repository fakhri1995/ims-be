<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketDetailType extends Model
{
    use SoftDeletes;
    public $timestamps = false;

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }

    public function taskType()
    {
        return $this->belongsTo(TaskType::class)->select('id', 'name')->withTrashed();
    }
}