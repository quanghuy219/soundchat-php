<?php

namespace App\Models;



class Message extends BaseModel
{
    protected $table = 'messages';

    protected $fillable = [
        'user_id', 'room_id', 'content'
    ];

    protected $hidden = [
        'status', 'created', 'updated'
    ];

    public function user() {
        return $this -> morphTo();
    }

    public function room() {
        return $this -> morphTo();
    }
    
}
