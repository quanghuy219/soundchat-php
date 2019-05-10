<?php

namespace App\Models;


use App\Utils\Constants\RoomStatus;

class Room extends BaseModel
{
    protected $table = 'rooms';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'creator_id', 'fingerprint'
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => RoomStatus::ACTIVE,
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function participants()
    {
        return $this->hasMany(RoomParticipant::class);
    }
}
