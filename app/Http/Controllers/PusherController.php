<?php

namespace App\Http\Controllers;

use Pusher\Pusher;
use Illuminate\Http\Request;

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
}
