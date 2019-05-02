<?php


namespace App\Utils\Constants;


use MyCLabs\Enum\Enum;

class ParticipantStatus extends Enum
{
    const IN = 'in';
    const OUT = 'out';
    const DELETED = 'deleted';
}