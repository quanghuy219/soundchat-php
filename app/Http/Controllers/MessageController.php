<?php

namespace App\Http\Controllers;

use App\Events\NewMessage;
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
        event(new NewMessage($request->get('room_id'), $user, $request->get('content')));
        return response()->json([
            'message' => 'Message added'],
            200);
    }
}
