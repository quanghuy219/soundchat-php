<?php

namespace App\Events;

use App\Utils\Constants\PusherEvent;

class NewVideo extends BaseEvent
{
    public $url; 
    public $status; 
    public $creator_id;
    public $total_vote;

    /**
     * Create a new event instance.
     * @param video
     */

    public function __construct($video)
    {
        $this->url = $video->getAttribute('url');
        $this->status = $video->getAttribute('status'); 
        $this->creator_id = $video->getAttribute('creator_id');
        $this->room_id = $video->getAttribute('room_id');
        $this->total_vote = $video->getAttribute('total_vote');
    }

    public function broadcastAs() {
        return PusherEvent::NEW_VIDEO;
    }
}