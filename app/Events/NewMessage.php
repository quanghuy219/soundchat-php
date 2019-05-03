<?php

namespace App\Events;

use App\Utils\Constants\PusherEvent;
use http\Client\Curl\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewMessage extends BaseEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

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
