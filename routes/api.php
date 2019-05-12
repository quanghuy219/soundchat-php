<?php

use Illuminate\Http\Request;
use App\Http\Controllers\VideoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Register new user
Route::post('/users', 'UserController@register');
Route::post('/login', 'UserController@authenticate');

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('/user', 'UserController@getAuthenticatedUser');
    Route::post('/messages', 'MessageController@sendNewMessage');
    Route::get('/rooms', 'RoomController@getRoomList');
    Route::get('/rooms/{id}', 'RoomController@getRoomInformation');
    Route::post('/rooms', 'RoomController@createNewRoom');
    Route::post('/rooms/fingerprint', 'RoomController@joinRoomByFingerprint');
    Route::post('/videos', 'VideoController@addNewVideo');
    Route::post('/videos/{video_id}/vote', 'VideoController@upVote');
    Route::put('/videos/{video_id}/vote', 'VideoController@downVote');    
    Route::get('/rooms/{id}/videos', 'RoomController@getVideoInRoom');
    Route::put('/rooms/{id}/videos', 'RoomController@updateVideoStatus');
});




Route::middleware('jwt.verify')->post('/pusher/auth', 'PusherController@auth');

