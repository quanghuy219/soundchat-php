<?php


namespace App\Events;


use App\Utils\Constants\PusherEvent;

class Proceed extends BaseEvent
{
    public $url;
    public $video_time;
    public $status;
    public $id;

    /**
     * Proceed constructor.
     * @param $room_id
     * @param $url
     * @param $video_time
     * @param $status
     * @param $video_id
     */
    public function __construct($room_id, $url, $video_time, $status, $video_id)
    {
        $this->room_id = $room_id;
        $this->url = $url;
        $this->video_time = $video_time;
        $this->status = $status;
        $this->id = $video_id;
    }


    public function broadcastAs() {
        return PusherEvent::PROCEED;
    }
}