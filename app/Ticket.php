<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    public $timestamps = false;
    // protected $fillable = ['type'];
    // protected $attributes = ['type'];
    // protected $appends = ['type'];

    public function type()
    {
        return $this->belongsTo(TicketType::class, 'type_id');
    }

    // public function getTypeAttribute($value)
    // {
    //     return $value * 5;
    // }

    // public function getFullTypeAttribute()
    // {
    //     return $this->type * 5;
    // }

    public function status()
    {
        return $this->belongsTo(TicketStatus::class, 'status_id');
    }

    public function asignTo()
    {
        if($this->asign_to) return "Group";
        else return "Engineer";
    }

    public function fullDetail()
    {
        return $this->belongsTo(Incident::class, 'subject_id')->with(['productType','location'])->withDefault([
            'id' => 0,
            'product_type' => (object)[
                "id" => 1,
                "name" => "UPS"
            ],
            'product_id'=> '-',
            'pic_name'=> 'Incident Tidak Ditemukan',
            'pic_contact'=> 'Incident Tidak Ditemukan',
            'location'=> (object)[
                'company_id'=> 0,
                'company_name'=> 'Perusahaan Tidak Ditemukan'
            ],
            'problem'=> '-',
            'incident_time'=> null,
            'files'=> [],
            'description'=> null,
            'deleted_at'=> null
        ]);
    }

    public function detail()
    {
        return $this->belongsTo(Incident::class, 'subject_id')->select('id','location')->with('location');
        if($this->getTypeAttribute == 1){
        // if($this->type == 1){
        }
        return (object)[
            'id' => 0,
            'location' => (object)[
                'id' => 0,
                'location_name' => 'Location Tidak Ditemukan'
            ]
        ];
    }

    public function asign()
    {
        return $this->belongsTo(User::class, 'asign_id')->withDefault([
            'id' => 0,
            'fullname' => 'User Tidak Ditemukan'
        ])->select('user_id AS id', 'fullname');
        if($this->asign_to)
        {
            return $this->belongsTo(Group::class, 'asign_id')->withDefault([
                'id' => 0,
                'name' => 'User Tidak Ditemukan'
            ])->select('user_id As id', 'fullname as name');
        }
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id', 'user_id')->withDefault([
            'user_id' => 0,
            'fullname' => 'User Tidak Ditemukan'
        ])->select('user_id', 'fullname','role','company_id')->where('role', 2);
    }

    // public function scopeDetail($query)
    // {
    //     return $query
    //           ->when($this->status === 1,function($q){
    //               return $q->with('incident');
    //          })
    //          ->when($this->type === 'school',function($q){
    //               return $q->with('schoolProfile');
    //          })
    //          ->when($this->type === 'academy',function($q){
    //               return $q->with('academyProfile');
    //          },function($q){
    //              return $q->with('abc');
    //          });
    // }
}
