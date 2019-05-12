<?php

namespace App\Http\Controllers;

use App\Events\ExitParticpant;
use App\Events\NewParticipant;
use App\Models\Room;
use App\Models\RoomParticipant;
use App\Utils\Constants\ParticipantStatus;
use App\Utils\Constants\VideoStatus;
use PhpParser\Error;
use Pusher\Pusher;
use Illuminate\Http\Request;
use Pusher\PusherException;

class PusherController extends Controller
{
    public function auth(Request $request) {
        $user = $request->get('user');
        $pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            config('broadcasting.connections.pusher.options')
        );
        if (!$pusher)
            return response()->json(['message' => 'Bad Request'], 400);

        return $pusher->presence_auth($request->channel_name, $request->socket_id, $user->getJWTIdentifier());
    }

    public function handlePusherWebhook(Request $request) {
        $pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            config('broadcasting.connections.pusher.options')
        );

        try {
            $header = [
                'X-Pusher-Key' => $request->header('X-Pusher-Key'),
                'X-Pusher-Signature' => $request->header('X-Pusher-Signature')
            ];
            $webhooks = $pusher->webhook($header, $request->getContent());
            $events = $webhooks->get_events();
            foreach ($events as $event) {
                if ($event->name == 'channel_vacated')
                    $this->handleChanelVacated($event);
                else if ($event->name == 'member_removed')
                    $this->handleMemberRemoved($event);
                else if ($event->name == 'member_added')
                    $this->handleMemberAdded($event);
            }
            return "ok";
        } catch (PusherException $ex) {
            logger('Pusher webhooks error');
        }
    }

    private function handleChanelVacated($data) {
        $roomId = $this->parseChannelName($data->channel);

        $room = Room::find($roomId);
        if ($room->status == VideoStatus::PLAYING)
            $room->status = VideoStatus::PAUSING;
            $room->save();
    }

    private function handleMemberRemoved($data) {
        $roomId = $this->parseChannelName($data->channel);
        $userId = $data->user_id;

        $participant = RoomParticipant::where('user_id', $userId)->where('room_id', $roomId)->first();
        if ($participant and $participant != ParticipantStatus::DELETED) {
            $user = $participant->user;
            event(new ExitParticpant($roomId, $user->name, $userId));
            $participant->status = ParticipantStatus::OUT;
            $participant->save();
        }
    }

    private function handleMemberAdded($data) {
        $roomId = $this->parseChannelName($data->channel);
        $userId = $data->user_id;

        $participant = RoomParticipant::where('user_id', $userId)->where('room_id', $roomId)->first();
        if ($participant and $participant != ParticipantStatus::DELETED) {
            $user = $participant->user;
            event(new NewParticipant($roomId, $user->name, $user->email, $userId));
            $participant->status = ParticipantStatus::IN;
            $participant->save();
        }
    }

    private function parseChannelName($channelName) {
        $names = explode('-', $channelName);
        if (count($names) != 4)
            throw new Error('Invalid channel name');

        return $names[2];
    }
}
