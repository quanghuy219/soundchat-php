<?php

namespace App\Models;

use App\Utils\Constants\RoomStatus;


class Room extends BaseModel
{
    protected $table = 'rooms';

    protected $primaryKey = ['id'];
    protected $fillable = [
        'name', 'creator_id', 'current_video', 'video_time', 'fingerprint'
    ];

    protected $hidden = [
        'created', 'updated', 'status'
    ];

    protected $attributes = [
        'status' => RoomStatus::ACTIVE,
    ];



    public function messages()
    {
        return $this->hasMany('App\Models\Message');
    }

    public function participants()
    {
        return $this->hasMany('App\Models\RoomParticipant');
    }
}
