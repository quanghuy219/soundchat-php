<?php


namespace App\Events;


use App\Utils\Constants\PusherEvent;

class VideoStatusChanged extends BaseEvent
{
    public $event;
    public $data;


    /**
     * Create a new event instance.
     * @param room_id
     * @param event
     * @param data: video data
     * @return void
     */
    public function __construct($room_id, $event, $data)
    {
        $this->room_id = $room_id;
        $this->event = $event;
        $this->data = $data;
    }

    public function broadcastAs() {
        return PusherEvent::VIDEO_STATUS_CHANGED;
    }
}