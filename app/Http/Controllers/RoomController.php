<?php

namespace App\Http\Controllers;

use App\Exceptions\Error;
use App\Models\Message;
use App\Models\Room;
use App\Models\RoomParticipant;
use App\Models\Video;
use App\Models\Vote;
use App\Utils\Constants\ParticipantStatus;
use App\Utils\Constants\RoomStatus;
use App\Utils\Constants\VideoStatus;
use App\Utils\Constants\VoteStatus;
use App\Utils\Helper;
use function foo\func;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
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
            return response()->json(["errors" => $validator->errors()], 400);
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
            return response()->json(["errors" => $validator->errors()], 400);
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
}
