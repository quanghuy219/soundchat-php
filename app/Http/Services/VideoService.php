<?php


namespace App\Http\Services;


use App\Models\Room;
use App\Models\Video;
use App\Utils\Constants\RoomStatus;
use App\Utils\Constants\VideoStatus;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;

class VideoService
{
    public static function getCurrentVideo($room_id) {
        $room = Room::where('id', $room_id)->first();
        if (!$room->current_video) {
            return null;
        }

        if ($room->status == RoomStatus::PAUSING) {
            $currentVideoTime = $room->video_time;

        } else {
            $timeDiff = Carbon::now(new CarbonTimeZone(0))->diffInSeconds($room->updated);
            $currentVideoTime = $room->video_time + $timeDiff;
        }
        $currentVideo = Video::where('id', $room->current_video)->first();
        $currentVideo->status = $room->status;
        $currentVideo->video_time = $currentVideoTime;
        return $currentVideo;
    }

    public static function getNextVideo($room_id) {
        $nextVideo = Video::where('room_id', $room_id)
            ->where('status', VideoStatus::VOTING)->orderBy('total_vote', 'desc')->first();
        return $nextVideo;
    }

}