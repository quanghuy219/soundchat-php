<?php


namespace App\Utils\Constants;


use MyCLabs\Enum\Enum;

class PusherEvent extends Enum
{
    const NEW_MESSAGE = 'new_message';
    const NEW_PARTICIPANT = 'new_participant';
    const EXIT_PARTICIPANT = 'exit_participant';
    const DELETE_PARTICIPANT = 'delete_participant';
    const UP_VOTE = 'up_vote';
    const DOWN_VOTE = 'down_vote';
    const NEW_MEDIA = 'new_media';
    const MEDIA_STATUS_CHANGED = 'media_status_changed';
    const PROCEED = 'proceed';
}