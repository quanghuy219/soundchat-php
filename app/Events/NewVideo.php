<?php

namespace App\Events;

use App\Utils\Constants\PusherEvent;

class NewMessage extends BaseEvent
{
    public $video; 

    /**
     * Create a new event instance.
     * @param video
     * @return void
     */

    public function __construct($video)
    {
        $this->video = $video;
    }

    public function broadcastAs() {
        return PusherEvent::NEW_MEDIA;
    }
}