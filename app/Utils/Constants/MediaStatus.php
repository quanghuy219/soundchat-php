<?php


namespace App\Utils\Constants;


use MyCLabs\Enum\Enum;

class MediaStatus extends Enum
{
    const ACTIVE = 'active';
    const DELETED = 'deleted';
    const VOTING = 'voting';
    const PLAYING = 'playing';
    const FINISHED = 'finished';
    const PAUSING = 'pausing';
    const READY = 'ready';
    const SEEKING = 'seeking';
}