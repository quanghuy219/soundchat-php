<?php


namespace App\Events;


use App\Utils\Constants\PusherEvent;

class UpVote extends BaseEvent
{
    public $user_name; 
    public $video_id;
    public $total_vote; 

    public function __construct($user, $video)
    {
        $this->user_name = $user->getAttribute('name');
        $this->video_id = $video->getAttribute('id');
        $this->total_vote = $video->getAttribute('total_vote');
    }

    public function broadcastAs(){
        return PusherEvent::UP_VOTE;
    }


}