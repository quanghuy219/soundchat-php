<?php

namespace App\Http\Controllers;

use App\Events\ExitParticpant;
use App\Events\NewParticipant;
use App\Events\Proceed;
use App\Events\VideoStatusChanged;
use App\Exceptions\Error;
use App\Http\Services\VideoService;
use App\Models\Message;
use App\Models\Room;
use App\Models\RoomParticipant;
use App\Models\User;
use App\Models\Video;
use App\Models\Vote;
use App\Utils\Constants\ParticipantStatus;
use App\Utils\Constants\RoomStatus;
use App\Utils\Constants\VideoStatus;
use App\Utils\Constants\VoteStatus;
use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    /** Return list of room that user has joined
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoomList(Request $request) {
        $user = $request->get('user');
        $rooms = Room::whereHas('participants', function($participant) use ($user) {
            $participant->where('user_id', '=', $user->getUserID());
        })->get();
        return response()->json([
            'message' => 'List of user\'s rooms',
            'data' => $rooms
        ], 200);
    }

    public function createNewRoom(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);
        if($validator->fails()){
            return response()->json(["error_data" => $validator->errors()], 400);
        }

        $user = $request->get('user');
        $userID = $user->getUserID();
        $roomFingerprint = Helper::createRoomFingerprint();

        $room = new Room;
        $data = [
            'name' => $request->get('name'),
            'creator_id' => $userID,
            'fingerprint' => $roomFingerprint
        ];
        $room->fill($data);
        $room->save();

        $roomParticipant = new RoomParticipant();
        $roomParticipant->setAttribute('user_id', $userID);
        $roomParticipant->setAttribute('room_id', $room->getKey());
        $roomParticipant->save();

        return response()->json([
            'message' => 'New room is created',
            'data' => $room
        ], 200);
    }

    public function getRoomInformation(Request $request, $id) {
        $room = Room::find($id);
        if (!$room) {
            throw new Error(400, 'This is an invalid room');
        }
        $user = $request->get('user');

        $participant = RoomParticipant::where('user_id', $user->getUserID())
            ->where('room_id', $id)
            ->first();
        if(!$participant || $participant->status == ParticipantStatus::DELETED) {
            throw new Error(400, 'You are not allowed to access this room\'s information');
        }

        $roomParticipants = RoomParticipant::with('user')->where('room_id', $id)->get();
        $messages = Message::where('room_id', $id)->get();
        $videos = Video::where('room_id', '=', $id)->where('status', '=', VideoStatus::VOTING)->get();
        foreach ($videos as $video) {
            $vote = Vote::where('video_id', $video->id)->where('user_id', $user->getUserID())
                ->where('status', VoteStatus::UPVOTE)->first();
            if ($vote)
                $video->setAttribute('is_voted', true);
            else
                $video->setAttribute('is_voted', false);
        }

        return response()->json([
            'message' => 'Retrieve room information',
            'data' => [
                'fingerprint' => $room->fingerprint,
                'name' => $room->name,
                'participants' => $roomParticipants,
                'messages' => $messages,
                'videos' => $videos
            ]
        ], 200);
    }

    public function joinRoomByFingerprint(Request $request) {
        $validator = Validator::make($request->all(), [
            'fingerprint' => 'required|string',
        ]);
        if($validator->fails()){
            return response()->json(["error_data" => $validator->errors()], 400);
        }

        $user = $request->get('user');
        $fingerprint = $request->get('fingerprint');
        $room = Room::where('fingerprint', $fingerprint)->first();
        if (!$room)
            throw new Error(400, 'Invalid room fingerprint');

        $participant = RoomParticipant::where('user_id', $user->getUserID())->where('room_id', $room->id)->first();
        if (!$participant) {
            $participant = new RoomParticipant([
                'user_id' => $user->getUserID(),
                'room_id' => $room->id,
                'status' => ParticipantStatus::IN
            ]);
        } else {
            $participant->status = ParticipantStatus::IN;
        }
        $participant->save();
        return response()->json([
            'message' => 'Joining room successfully',
            'data' => $room
        ], 200);
    }

    public function addMemberByEmail(Request $request, $roomId) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if($validator->fails()){
            return response()->json(["error_data" => $validator->errors()], 400);
        }

        $room = Room::find($roomId);
        if (!$room) {
            throw new Error(400, 'This is an invalid room');
        }
        $user = $request->get('user');
        $requester = RoomParticipant::where('room_id', $roomId)->where('user_id', $user->getUserID())->first();

        if (!$requester || $requester->status == ParticipantStatus::DELETED)
            throw new Error(400, 'You\'re not allowed to add new member to this room');

        $addedUser = User::where('email', $request->get('email'))->first();
        if (!$addedUser)
            throw new Error(400, 'This user does not exist');

        $participant = RoomParticipant::where('room_id', $roomId)->where('user_id', $addedUser->getUserID())->first();

        if (!$participant) {
            $participant =  RoomParticipant::create([
                'user_id' => $addedUser->getUserID(),
                'room_id' => $roomId,
                'status' => ParticipantStatus::OUT
            ]);
            $participant->save();

            event(new NewParticipant($roomId, $addedUser->name, $addedUser->email, $addedUser->getUserID()));

            return response()->json([
                'message' => 'New participant to the room is created',
                'data' => $participant
            ], 200);
        } else if ($participant->status == ParticipantStatus::DELETED) {
            $participant->status = ParticipantStatus::OUT;
            $participant->save();
            event(new NewParticipant($roomId, $addedUser->name, $addedUser->email, $addedUser->getUserID()));
            return response()->json([
                'message' => 'New participant to the room is created',
                'data' => $participant
            ], 200);
        }

        throw new Error(400, 'This user is already a member of this room');
    }

    public function userExitFromRoom(Request $request, $roomId) {
        $room = Room::find($roomId);
        if (!$room) {
            throw new Error(400, 'This is an invalid room');
        }

        $user = $request->get('user');

        $participant = RoomParticipant::where('room_id', $roomId)->where('user_id', $user->getUserID())->first();
        if (!$participant || $participant->status == ParticipantStatus::DELETED)
            throw new Error(400, 'You are not member of this room');

        if ($participant->status == ParticipantStatus::IN) {
            $participant->status = ParticipantStatus::OUT;
            $participant->save();
            event(new ExitParticpant($roomId, $user->name, $user->getUserID()));
            return response()->json([
                'message' => 'Participant exited successfully'
            ], 200);
        }

        throw new Error(401, 'participant failed to exit');
    }


    public function getVideoInRoom(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:current,next',
        ]);
        if($validator->fails()){
            return response()->json(["error_data" => $validator->errors()], 400);
        }
        $user = $request->get('user');

        // Only room participant can get video data
        $participant = RoomParticipant::where('user_id', $user->getUserID())
            ->where('room_id', $id)
            ->first();
        if(!$participant) {
            throw new Error(400, 'You are not a member of this room');
        }
        $type = $request->get('type');

        if ($type == 'current') {
            $currentVideo = VideoService::getCurrentVideo($id);
            if (!$currentVideo)
                return response()->json([
                    'message' => 'There is no available video'
                ], 200);

            return response()->json([
                'message' => 'Get current video successfully',
                'data' => $currentVideo
            ], 200);
        } else {
            $nextVideo = VideoService::getNextVideo($id);
            if (!$nextVideo)
                return response()->json([
                    'message' => 'There is no available song'
                ], 200);

            return response()->json([
                'message' => 'Get next video successfully',
                'data' => $nextVideo
            ], 200);
        }
    }

    public function updateVideoStatus(Request $request, $roomId) {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:ready,seeking,pausing,playing,finished',
            'video_time' => 'required|numeric'
        ]);
        if($validator->fails()){
            return response()->json(["error_data" => $validator->errors()], 400);
        }

        $status = $request->get('status');
        $user = $request->get('user');
        switch ($status) {
            case VideoStatus::READY:
                $this->handleVideoReady($roomId, $request);
                $resData = [
                    'message' => 'Waiting for other members to be ready'
                ];
                break;
            case VideoStatus::PLAYING:
                $this->handleVideoPlaying($roomId, $request);
                $resData = [
                    'message' => 'Play video'
                ];
                break;
            case VideoStatus::PAUSING:
                $this->handleVideoPausing($roomId, $request);
                $resData = [
                    'message' => 'Pause video'
                ];
                break;
            case VideoStatus::SEEKING:
                $this->handleVideoSeeking($roomId, $request);
                $resData = [
                    'message' => 'Seek video'
                ];
                break;
            case VideoStatus::FINISHED:
                $this->handleVideoFinished($roomId, $request);
                $resData = [
                    'message' => 'Wait for other members to finish their video'
                ];
                break;
            default:
                throw new Error(400, 'Invalid video status');
                break;
        }
        return response()->json($resData, 200);
    }

    private function handleVideoReady($roomId, $request) {
        $status = VideoStatus::READY;
        $room = Room::find($roomId);
        if (!$room) {
            throw new Error(400, 'This is an invalid room');
        }
        $user = $request->get('user');
        $roomMember = RoomParticipant::where('room_id', $roomId)->where('user_id', $user->getUserID())->first();

        if (!$roomMember) {
            throw new Error(400, 'You are not member of this room');
        }

        $roomMember->video_status = $status;
        $roomMember->save();

        if (VideoService::checkAllUserHaveSameVideoStatus($roomId, VideoStatus::READY)) {
            $currentVideo = VideoService::getCurrentVideo($roomId);
            // Publish event to all user in the room
            event(new VideoStatusChanged($roomId, VideoStatus::PLAYING, $currentVideo->getAttributes()));
            VideoService::setOnlineUsersVideoStatus($roomId, VideoStatus::PLAYING);
            $room->status = VideoStatus::PLAYING;
            $room->save();
        }
    }

    private function handleVideoPlaying($roomId, $request) {
        $room = Room::find($roomId);
        if (!$room) {
            throw new Error(400, 'This is an invalid room');
        }
        $user = $request->get('user');
        $roomMember = RoomParticipant::where('room_id', $roomId)->where('user_id', $user->getUserID())->first();

        if (!$roomMember) {
            throw new Error(400, 'You are not member of this room');
        }

        $room->video_time = $request->get('video_time');
        $room->status = VideoStatus::PLAYING;
        $room->save();
        VideoService::setOnlineUsersVideoStatus($roomId, VideoStatus::PLAYING);
        $currentVideo = VideoService::getCurrentVideo($roomId);
        event(new VideoStatusChanged($roomId, VideoStatus::PLAYING, $currentVideo->getAttributes()));
    }

    private function handleVideoPausing($roomId, $request) {
        $room = Room::find($roomId);
        if (!$room) {
            throw new Error(400, 'This is an invalid room');
        }
        $user = $request->get('user');
        $roomMember = RoomParticipant::where('room_id', $roomId)->where('user_id', $user->getUserID())->first();

        if (!$roomMember) {
            throw new Error(400, 'You are not member of this room');
        }

        $room->video_time = $request->get('video_time');
        $room->status = VideoStatus::PAUSING;
        $room->save();
        VideoService::setOnlineUsersVideoStatus($roomId, VideoStatus::PAUSING);
        $currentVideo = VideoService::getCurrentVideo($roomId);
        event(new VideoStatusChanged($roomId, VideoStatus::PAUSING, $currentVideo->getAttributes()));
    }

    private function handleVideoSeeking($roomId, $request) {
        $room = Room::find($roomId);
        if (!$room) {
            throw new Error(400, 'This is an invalid room');
        }
        $user = $request->get('user');
        $roomMember = RoomParticipant::where('room_id', $roomId)->where('user_id', $user->getUserID())->first();

        if (!$roomMember) {
            throw new Error(400, 'You are not member of this room');
        }

        $room->video_time = $request->get('video_time');
        $room->status = VideoStatus::PAUSING;
        $room->save();
        VideoService::setOnlineUsersVideoStatus($roomId, VideoStatus::PAUSING);
        $currentVideo = VideoService::getCurrentVideo($roomId);
        event(new VideoStatusChanged($roomId, VideoStatus::SEEKING, $currentVideo->getAttributes()));
    }

    private function handleVideoFinished($roomId, $request) {
        $status = VideoStatus::FINISHED;
        $room = Room::find($roomId);
        if (!$room) {
            throw new Error(400, 'This is an invalid room');
        }
        $user = $request->get('user');
        $roomMember = RoomParticipant::where('room_id', $roomId)->where('user_id', $user->getUserID())->first();

        if (!$roomMember) {
            throw new Error(400, 'You are not member of this room');
        }
        $room->video_time = $request->get('video_time');
        $room->save();
        $roomMember->video_status = $status;
        $roomMember->save();

        if (VideoService::checkAllUserHaveSameVideoStatus($roomId, VideoStatus::FINISHED)) {
            $currentVideo = Video::where('id', $room->current_video)->first();
            $currentVideo->status = VideoStatus::FINISHED;
            $currentVideo->save();

            VideoService::setOnlineUsersVideoStatus($roomId, VideoStatus::PAUSING);
            $nextVideo = VideoService::setCurrentVideo($roomId);

            if (!$nextVideo) {
                $url = null;
            } else {
                $url = $nextVideo->getAttribute('url');
            }
            // Publish event to all user in the room
            event(new Proceed($roomId, $url, 0, VideoStatus::PAUSING));
            VideoService::setOnlineUsersVideoStatus($roomId, VideoStatus::PLAYING);
            $room->status = VideoStatus::PLAYING;
            $room->save();
        }
    }
}
