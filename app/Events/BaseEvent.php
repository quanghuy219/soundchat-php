<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class BaseEvent implements ShouldBroadcast
{
    protected $room_id;

    protected function constructChannelName($room_id) {
        $namespace = env('PUSHER_CHANNEL_NAMESPACE', '');
        return "room-{$room_id}-{$namespace}";
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel($this->constructChannelName($this->room_id));
    }
}
