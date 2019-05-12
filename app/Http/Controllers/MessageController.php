<?php

namespace App\Http\Controllers;

use App\Events\NewMessage;
use App\Exceptions\Error;
use App\Models\Message;
use App\Models\Room;
use App\Models\RoomParticipant;
use App\Utils\Constants\ParticipantStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function sendNewMessage(Request $request) {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|int',
            'content' => 'required|string|min:1',
        ]);

        if($validator->fails()){
            return response()->json(["errors" => $validator->errors()], 400);
        }

        $user = $request->get('user');
        $roomId = $request->get('room_id');

        $room = Room::find($roomId);
        if (!$room) {
            throw new Error(400, 'This is an invalid room');
        }

        $roomMember = RoomParticipant::where('room_id', $roomId)->where('user_id', $user->getUserID())->first();

        if (!$roomMember || $roomMember->status != ParticipantStatus::IN)
            throw new Error(400, 'Invalid room access');

        $content = $request->get('content');

        $message = Message::create([
            'user_id' => $user->getUserID(),
            'room_id' => $roomId,
            'content' => $content
        ]);
        $message->save();

        event(new NewMessage($request->get('room_id'), $user, $content));
        return response()->json([
            'message' => 'Message added'],
            200);
    }
}
