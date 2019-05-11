<?php


namespace App\Utils\Constants;


use MyCLabs\Enum\Enum;

class RoomStatus extends Enum
{
    const ACTIVE = 'active';
    const DELETED = 'deleted';
    const PAUSING = 'pausing';
    const PLAYING = 'playing';
}