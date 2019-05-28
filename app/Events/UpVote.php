<?php


namespace App\Events;


use App\Utils\Constants\PusherEvent;

class UpVote extends BaseEvent
{
    public $user_name; 
    public $id;
    public $total_vote; 

    public function __construct($room_id, $user, $video)
    {
        $this->room_id = $room_id;
        $this->user_name = $user->getAttribute('name');
        $this->id = $video->getAttribute('id');
        $this->total_vote = $video->getAttribute('total_vote');
    }

    public function broadcastAs(){
        return PusherEvent::UP_VOTE;
    }


}