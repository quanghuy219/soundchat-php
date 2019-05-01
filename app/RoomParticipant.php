<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoomParticipant extends BaseModel
{
    protected $table = 'room_participant';

    protected $primaryKey = ['user_id', 'room_id'];
}
