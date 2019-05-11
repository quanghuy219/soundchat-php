<?php


namespace App\Http\Services;


use App\Models\Room;
use App\Models\RoomParticipant;
use App\Models\Video;
use App\Utils\Constants\ParticipantStatus;
use App\Utils\Constants\RoomStatus;
use App\Utils\Constants\VideoStatus;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;

class VideoService
{
    public static function getCurrentVideo($roomId) {
        $room = Room::where('id', $roomId)->first();
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

    public static function getNextVideo($roomId) {
        $nextVideo = Video::where('room_id', $roomId)
            ->where('status', VideoStatus::VOTING)->orderBy('total_vote', 'desc')->first();
        return $nextVideo;
    }

    public static  function setCurrentVideo($roomId, $currentVideoId = null, $videoTime = 0, $status = VideoStatus::PAUSING) {
        if (!$currentVideoId) {
            $nextVideo = self::getNextVideo($roomId);
            if ($nextVideo)
                $currentVideoId = $nextVideo->id;
        }
        $room = Room::where('id', $roomId)->first();
        $room->current_video = $currentVideoId;
        $room->video_time = $videoTime;
        $room->status = $status;
        $room->save();

        $currentVideo = self::getCurrentVideo($roomId);
        return $currentVideo;
    }

    public static function checkAllUserHaveSameVideoStatus($roomId, $videoStatus) {
        $notReadyUsers = RoomParticipant::where('room_id', $roomId)->where('status', ParticipantStatus::IN)
            ->where('video_status', '!=', $videoStatus)->count();

        if (!$notReadyUsers)
            return true;

        return false;
    }

    /**
     * @param $roomId room's id
     * @param $status video status
     */
    public static function setOnlineUsersVideoStatus($roomId, $videoStatus) {
        $query = RoomParticipant::where('room_id', $roomId)->where('status', ParticipantStatus::IN);

        if (!$query->count())
            return;

        $query->update(['video_status' => $videoStatus]);
    }
}
