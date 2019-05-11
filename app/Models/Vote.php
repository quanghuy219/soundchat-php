<?php

namespace App\Models;
use App\Utils\Constants\VoteStatus;


class Vote extends BaseModel
{
    protected $table = 'votes';
    
    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    protected $primaryKey = ['id'];

    protected $fillable = ['video_id', 'user_id'];

    protected $hidden = ['created', 'updated', 'status'];

    protected $attributes = [
        'status' => VoteStatus::UPVOTE,
    ];

    public function video(){
        return $this->morphTo();
    }

    public function user(){
        return $this->morphTo();
    }
}
