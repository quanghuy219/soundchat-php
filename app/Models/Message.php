<?php

namespace App\Models;



class Message extends BaseModel
{
    protected $table = 'messages';

    protected $fillable = [
        'user_id', 'room_id', 'content'
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'active',
    ];

    public function room() {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
