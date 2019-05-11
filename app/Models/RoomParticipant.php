<?php

namespace App\Models;

use App\Utils\Constants\ParticipantStatus;
use App\Utils\Constants\MediaStatus;

class RoomParticipant extends BaseModel
{
    protected $table = 'room_participants';

    protected $primaryKey = ['user_id', 'room_id'];

    protected $attributes = [
        'media_status' => MediaStatus::VOTING,
        'status' => ParticipantStatus::IN
    ];

    protected $fillable = ['user_id', 'room_id'];
    protected $hidden = ['created', 'updated', 'media_status', 'status'];

    public function user(){
        $this->morphTo();
    }

    public function room(){
        $this->morphTo();
    }

}
