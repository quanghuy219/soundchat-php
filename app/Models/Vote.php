<?php

namespace App\Models;
use App\Utils\Constants\VoteStatus;


class Vote extends BaseModel
{
    protected $table = 'votes';

    protected $fillable = ['video_id', 'user_id'];

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
