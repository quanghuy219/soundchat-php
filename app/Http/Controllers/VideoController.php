<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Video;
use App\Models\Room;
use App\Models\Vote;
use App\Models\RoomParticipant;
use App\Utils\Constants\VideoStatus;
use App\Utils\Constants\VoteStatus;
use App\Utils\Constants\ParticipantStatus;
use App\Utils\Helper;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Error;
use App\Events\NewVideo;
use App\Events\UpVote;
use App\Events\DownVote;
use App\Http\Services\VideoService;
use App\Events\Proceed;

class VideoController extends Controller
{
    public function addNewVideo(Request $request){
        $validator = Validator::make($request->all(), [
            'url' => 'required|string',
        ]);
        if($validator->fails()){
            return response()->json(["errors" => $validator->errors()], 400);
        }

        $user = $request->get('user');
        $userID = $user->getUserID();
        $roomID = $request->get('room_id');
        $room = Room::where('id', $roomID)->first();
        if (!$room)
            throw new Error(400, 'Invalid room id');
        $participant = RoomParticipant::where([
            ['room_id', $roomID],
            ['user_id', $userID],
        ])->first();
        if ( $participant->status != ParticipantStatus::IN)
            throw new Error(400,'Cannot add video');
            
        $new_video = new Video;
        $data = [
            'url' => $request->get('url'),
            'creator_id' => $userID,
            'room_id' => $roomID,
            'total_vote' => 1
        ];

        $new_video->fill($data);
        $new_video->save();

        event (new NewVideo($new_video));
        
        if (!$room->current_video)
        {
            $new_video->status = VideoStatus::PLAYING;
            $room->current_video = VideoService::setCurrentVideo($roomID);
            $current_video = $room->current_video;
            event (new Proceed($roomID, $current_video->url, $current_video->video_time, $current_video->status));
        }
        return response() -> json([
            'message' => 'new video is added', 
            'data' => $new_video
        ], 200);
    }

    public function upVote(Request $request, $video_id){
        // $validator = Validator::make($request->all(), [

        // ]);

        $video = Video::find($video_id);
        if (!$video || $video->status != VideoStatus::VOTING) {
            throw new Error(400, 'Video is not ready to vote');
        }

        $user = $request->get('user');
        $userID = $user->getUserID();
        $participant = RoomParticipant::where([
            ['room_id', $video->room_id],
            ['user_id', $userID],
        ])->first();
        if (!$participant || $participant->status != ParticipantStatus::IN)
            throw new Error(400,'Forbidden to vote for this video');
        
        $vote = Vote::where([
            ['user_id', $userID],
            ['video_id', $video_id],
        ])->first();

        if (!$vote){ 
            $new_vote = new Vote;
            $data = [
                'user_id' => $userID, 
                'video_id' => $video_id
            ];

            $new_vote->fill($data);
            $new_vote->save();
            
            $video->total_vote += 1;
            $video->save();

            event (new UpVote($user, $video));

            return response() -> json([
                'message' => 'up-voted successfully', 
                'data' => $video
            ], 200);
        }

        if ($vote->status == VoteStatus::DOWNVOTE){
            $vote->status = VoteStatus::UPVOTE;
            $vote->save();

            $video->total_vote += 1;
            $video->save();
            
            event (new UpVote($user, $video));

            return response() -> json([
                'message' => 'up-voted successfully', 
                'data' => $video
            ], 200);

        }

        if ($vote->status == VoteStatus::UPVOTE){
            throw new Error(400, 'Already up-voted');
        }
    }

    public function downVote(Request $request, $video_id){
        // $validator = Validator::make($request->all(), [

        // ]);

        $video = Video::find($video_id);
        if (!$video || $video->status != VideoStatus::VOTING) {
            throw new Error(400, 'Video is not ready to vote');
        }

        $user = $request->get('user');
        $userID = $user->getUserID();
        $participant = RoomParticipant::where([
            ['room_id', $video->room_id],
            ['user_id', $userID],
        ])->first();
        if (!$participant || $participant->status != ParticipantStatus::IN)
            throw new Error(400,'Forbidden to vote for this video');
        
        $vote = Vote::where([
            ['user_id', $userID],
            ['video_id', $video_id],
        ])->first();

        if (!$vote){ 
            throw new Error(400, 'Need to up-vote first');
        }

        if ($vote->status == VoteStatus::UPVOTE){
            $vote->status = VoteStatus::DOWNVOTE;
            $vote->save();

            $video->total_vote -= 1;
            $video->save();

            event (new DownVote($user, $video));
            
            return response() -> json([
                'message' => 'down-voted successfully', 
                'data' => $video
            ], 200);

        }

        if ($vote->status == VoteStatus::DOWNVOTE){
            throw new Error(400, 'Already down-voted');
        }
    }

    public function getNextVideo(Request $request){
        $roomID = $request->get('room_id');
        $user = $request->get('user');
        $userID = $user->getUserID();
        $participant = RoomParticipant::where([
            ['user_id', $userID],
            ['room_id', $roomID],
        ])->first();

        if (!$participant || $participant->status != ParticipantStatus::IN){
            throw new Error(400, 'you are not a member of this room');
        }

        $video = Video::where([
            ['room_id', $roomID],
            ['status', VideoStatus::VOTING],
        ])->max('total_vote');
        return response() -> json([
            'message' => 'Get next song successfully',
            'data' => $video
        ], 200);
    }

}
