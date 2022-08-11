<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Auth;

class AuthController extends Controller
{
    public function login(Request $request){
        //validate input
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string'
        ]);
        if($validator->fails()){
            return response()->json([
                $validator->errors()
            ], 401);
        }

        if(!Auth::attempt(['username' => $request->input('username'), 'password' => $request->input('password')])){
            return response()->json(["error" => __('إسم المستخدم أو كلمة المرور خاطئة')]);
        }

        //get tokens
        $user = $request->user();
        $tokenResult = $user->createToken(uniqid());
        return response()->json([
            "access_token" => $tokenResult->accessToken,
            "token_type" => "Bearer",
            "expires_at" => Carbon::parse(
                $tokenResult->token->expires_at
            )->getTimestamp()
        ]);
    }

    public function logout(Request $request){
        $request->user()->token()->revoke();
        return response()->json([
            'success' => 'Successfully logged out'
        ]);
    }
}
