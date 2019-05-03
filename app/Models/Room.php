<?php

namespace App\Models;


class Room extends BaseModel
{
    protected $table = 'rooms';

    public function messages()
    {
        return $this->hasMany('App\Models\Message');
    }

    public function participants()
    {
        return $this->hasMany('App\Models\RoomParticipant');
    }
}
