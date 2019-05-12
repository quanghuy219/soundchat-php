<?php

namespace App\Models;


use App\Utils\Constants\ParticipantStatus;

class RoomParticipant extends BaseModel
{
    protected $table = 'room_participants';
    protected $fillable = [
        'user_id', 'room_id', 'status'
    ];
    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => ParticipantStatus::IN,
    ];
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
