<?php

namespace App\Models;


class RoomParticipant extends BaseModel
{
    protected $table = 'room_participants';

    protected $primaryKey = ['user_id', 'room_id'];
}
