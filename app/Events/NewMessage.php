<?php

namespace App\Events;

use App\Utils\Constants\PusherEvent;

class NewMessage extends BaseEvent
{
    public $user_id;
    public $username;
    public $content;


    /**
     * Create a new event instance.
     * @param room_id
     * @param user
     * @param message
     * @return void
     */
    public function __construct($room_id, $user, $message)
    {
        $this->room_id = $room_id;
        $this->user_id = $user->getAttribute('id');
        $this->username = $user->getAttribute('name');
        $this->content = $message;
    }

    public function broadcastAs() {
        return PusherEvent::NEW_MESSAGE;
    }
}
