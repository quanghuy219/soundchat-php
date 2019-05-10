<?php


namespace App\Utils\Constants;


use MyCLabs\Enum\Enum;

class UserStatus extends Enum
{
    const ACTIVE = 'active';
    const DELETED = 'deleted';
}