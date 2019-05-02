<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;

class UserController extends Controller
{
    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json(["errors" => $validator->errors()], 400);
        }

        $user = User::where('email', $request->get('email'))->first();

        if ( !$user ||  !Hash::check($request->get('password'), $user->getAttribute('password_hash'))){
            return response()->json(['message' => 'Incorrect email or password'], 400);
        }

        $token = JWTAuth::fromUser($user);


        return response()->json([
            'message' => 'Login successfully',
            'access_token' => $token,
            'data' => $user,
        ], 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json(["errors" => $validator->errors()], 400);
        }


        User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password_hash' => Hash::make($request->get('password'))
        ]);

        return response()->json([
            'message' => 'Your account was created successfully'
            ], 200);
    }

    public function getAuthenticatedUser()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['message' => 'Token has expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['message' => 'Invalid token'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['message' => 'Missing access token'], $e->getStatusCode());

        }

        return response()->json(compact('user'));
    }
}
