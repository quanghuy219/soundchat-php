<?php


namespace App\Events;


use App\Utils\Constants\PusherEvent;

class NewParticipant extends BaseEvent
{
    public $name;
    public $emamil;
    public $user_id;

    /**
     * NewParticipant constructor.
     * @param room_id
     * @param $name
     * @param $user_id
     */
    public function __construct($room_id, $name, $email, $user_id)
    {
        $this->room_id = $room_id;
        $this->name = $name;
        $this->email = $email;
        $this->user_id = $user_id;
    }

    public function broadcastAs() {
        return PusherEvent::NEW_PARTICIPANT;
    }
}