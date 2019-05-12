<?php


namespace App\Events;


use App\Utils\Constants\PusherEvent;

class ExitParticpant extends BaseEvent
{
    public $name;
    public $user_id;

    /**
     * NewParticipant constructor.
     * @param room_id
     * @param $name
     * @param $user_id
     */
    public function __construct($room_id, $name, $user_id)
    {
        $this->room_id = $room_id;
        $this->name = $name;
        $this->user_id = $user_id;
    }

    public function broadcastAs() {
        return PusherEvent::EXIT_PARTICIPANT;
    }
}