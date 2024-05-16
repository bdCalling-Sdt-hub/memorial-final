<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SocialLoginController extends Controller
{

    public function guard()
    {
        return Auth::guard('api');
    }

    public function socialLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullName' => 'required|min:2',
            'email' => 'email|required|max:100',
            'userType' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Check if a user with this email exists without Google or Facebook ID
        $manual_user = User::where('email', $request->email)
            ->whereNull('google_id')
            ->whereNull('apple_id')
            ->first();

        if ($manual_user) {
            return response()->json([
                'message' => 'User already exists. Sign in manually.',
            ], 422);
        } else {
            // Check if a user with this email exists with Google or Facebook ID
            $user = User::where('email', $request->email)
                ->where(function ($query) {
                    $query
                        ->whereNotNull('google_id')
                        ->orWhereNotNull('apple_id');
                })
                ->first();

            if ($user) {
                if ($token = $this->guard()->login($user)) {
                    return $this->responseWithToken($token);
                }
                return response()->json([
                    'message' => 'User unauthorized'
                ], 401);
            } else {
//                $avatar = 'dummyImg/default.jpg';
                // Create a new user
                $user = new User();
                $user->fullName = $request->fullName;
                $user->email = $request->email;
                $user->userType = $request->userType;
                $user->google_id = $request->google_id ?? null;
                $user->apple_id = $request->apple_id ?? null;
                $user->verify_email = 1;
                $user->otp=0;
                $user->image = $request->image ?? null;
                $user->save();

                // Generate token for the new user
                if ($token = $this->guard()->login($user)) {
                    return $this->responseWithToken($token);
                }
                return response()->json([
                    'message' => 'User unauthorized'
                ], 401);
            }
        }
    }


    protected function responseWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'user_id' => $this->guard()->user()->id,
            'user_type' =>$this->guard()->user()->userType,
            'google_id' =>$this->guard()->user()->google_id,
            'user_status' =>$this->guard()->user()->user_status,
            'apple_id' =>$this->guard()->user()->apple_id,
            'expires_in' => $this->guard()->factory()->getTTL() * 6000000000000000
        ], 200);
    }
}
