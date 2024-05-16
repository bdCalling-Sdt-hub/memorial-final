<?php

namespace App\Http\Controllers;

use App\Events\SendNotificationEvent;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Mail\OtpMail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\NotificationController;

class AuthController extends Controller
{
    public function register(Request $request)

    {
        $user = User::where('email', $request->email)
            ->where('verify_email', 0)
            ->first();

        if ($user) {

            $random = Str::random(6);
            Mail::to($request->email)->send(new OtpMail($random));
            $user->update(['otp' => $random]);
            $user->update(['verify_email' => 0]);

            return response(['message' => 'Please check your email for validate your email.'], 200);
        } else {
            Validator::extend('contains_dot', function ($attribute, $value, $parameters, $validator) {
                return strpos($value, '.') !== false;
            });

            $validator = Validator::make($request->all(), [
                'fullName' => 'required|string|min:2|max:100',
                'email' => 'required|string|email|max:60|unique:users|contains_dot',
                'password' => 'required|string|min:6|confirmed',
                'userType' => ['required', Rule::in(['USER', 'ADMIN', 'SUPER ADMIN'])],
            ], [
                'email.contains_dot' => 'without (.) Your email is invalid',
            ]);
            if ($validator->fails()) {
                return response()->json(["message" => $validator->errors()], 400);
            }


            $userData = [
                'fullName' => $request->fullName,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'userType' => $request->userType,
                'otp' =>  Str::random(6),
                'verify_email' => 0
            ];

            $user = User::create($userData);

            Mail::to($request->email)->send(new OtpMail($user->otp));
            return response()->json([
                'message' => 'Please check your email to valid your email',
            ]);
        }
    }

    public function emailVerified(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }
        if ($request->otp){
            $user = User::where('otp',$request->otp)->first();
            if($user!=null){
                $token = $this->guard()->login($user);
            }

        }

        $user = User::where('otp', $request->otp)->first();

        if (!$user) {
            return response(['message' => 'Invalid'], 422);
        }
        $user->update(['verify_email' => 1]);
        $user->update(['otp' => 0]);
        $result = app('App\Http\Controllers\NotificationController')->sendNotification('Welcome to the memorial moment app',$user->created_at,$user);
        $admin_result = app('App\Http\Controllers\NotificationController')->sendAdminNotification('New Customer Registered',$user->created_at,$user->fullName,$user);
        event(new SendNotificationEvent('New Customer Registered',$user->created_at,$user));
        return response([
            'message' => 'Email verified successfully',
            'notification' => $result,
            'token' => $this->respondWithToken($token),
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $userData = User::where("email", $request->email)->first();
        //return gettype($userData->otp);
        if ($userData && Hash::check($request->password, $userData->password)) {
            if ($userData->verify_email == 0) {
                return response()->json(['message' => 'Your email is not verified'], 401);
            }
        }

        $credentials = $request->only('email', 'password');

        if ($token = $this->guard()->attempt($credentials)) {

            return $this->respondWithToken($token);
        }

        return response()->json(['message' => 'Your credential is wrong'], 402);
    }


    protected function respondWithToken($token)
    {

        $user = Auth::guard('api')->user()->makeHidden(['mobile','address','image','otp','created_at','updated_at']);
        return response()->json([
            'access_token' => $token,
            'user' => $user,
            'token_type' => 'bearer',
            'user'=>$user,
            'expires_in' => auth('api')
                ->factory()
                ->getTTL()*600000000000, //hour*seconds
        ]);
    }

    public function guard()
    {
        return Auth::guard('api');
    }

    public function loggedUserData()
    {
        if ($this->guard()->user()) {
            $user = $this->guard()->user();


            return response()->json([
                "user"=>$user
            ]);
        } else {
            return response()->json(['message' => 'You are unauthorized']);
        }
    }

    public function forgetPassword(Request $request)
    {
        $email = $request->email;
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['error' => 'Email not found'], 401);
        }else if($user->google_id != null || $user->apple_id != null){
            return response()->json([
                'message' => 'Your are social user, You do not need to forget password',
            ],400);
        }
        else {
            $random = Str::random(6);
            Mail::to($request->email)->send(new OtpMail($random));
            $user->update(['otp' => $random]);
            $user->update(['verify_email' => 0]);
            return response()->json(['message' => 'Please check your email for get the OTP']);
        }
    }

    public function emailVerifiedForResetPass(Request $request)
    {
        $user = User::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$user) {

            return response()->json(['error' => 'Your verified code does not matched '], 401);
        } else {
            $user->update(['verify_email' => 1]);
            $user->update(['otp' => 0]);
            return response()->json(['message' => 'Now your email is verified'], 200);
        }
    }

    public function resetPassword(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                "message" => "Your email is not exists"
            ], 401);
        }
        if ($user->verify_email == 0) {
            return response()->json([
                "message" => "Your email is not verified"
            ], 401);
        }
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        } else {
            $user->update(['password' => Hash::make($request->password)]);
            return response()->json(['message' => 'Password reset successfully'], 200);
        }
    }

    public function updatePassword(Request $request)
    {
        $user = $this->guard()->user();

        if ($user) {

            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|different:current_password',
                'confirm_password' => 'required|string|same:new_password',
            ]);

            if ($validator->fails()) {
                return response(['errors' => $validator->errors()], 409);
            }
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Your current password is wrong'], 409);
            }
            $user->update(['password' => Hash::make($request->new_password)]);

            return response(['message' => 'Password updated successfully'], 200);
        } else {
            return response()->json(['message' => 'You are not authorized!'], 401);
        }
    }



    public function editProfile(Request $request,$id)
    {
        $user = $this->guard()->user();

        if ($user) {
            $validator = Validator::make($request->all(), [
                'fullName' => 'required|string|min:2|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $user->fullName = $request->fullName;
            $user->mobile = $request->mobile ? $request->mobile : $user->mobile;
            $user->address = $request->address ? $request->address : $user->address;

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $destination = 'storage/image/' . $user->image;

                if (File::exists($destination)) {
                    File::delete($destination);
                }

                $timeStamp = time(); // Current timestamp
                $fileName = $timeStamp . '.' . $file->getClientOriginalExtension();
                $file->storeAs('image', $fileName, 'public');

                $filePath = '/storage/image/' . $fileName;
                $fileUrl = $filePath;
                $user->image = $fileUrl;
            }

            $user->update();
            return response()->json([
                "message" => "Profile updated successfully",
                'data' => $user,
            ]);
        } else {
            return response()->json([
                "message" => "You are not authorized!"
            ], 401);
        }
    }

    //jusef
    public function resendOtp(Request $request)
    {
        $user = User::where('email', $request->email)
//            ->where('verify_email', 0)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found or email already verified'], 404);
        }

        // Check if OTP resend is allowed (based on time expiration)
        $currentTime = now();
        $lastResentAt = $user->last_otp_sent_at; // Assuming you have a column in your users table to track the last OTP sent time

        // Define your expiration time (e.g., 5 minutes)
        $expirationTime = 5; // in minutes

        if ($lastResentAt && $lastResentAt->addMinutes($expirationTime)->isFuture()) {
            // Resend not allowed yet
            return response()->json(['message' => 'You can only resend OTP once every ' . $expirationTime . ' minutes'], 400);
        }

        // Generate new OTP
        $newOtp = Str::random(6);
        Mail::to($user->email)->send(new OtpMail($newOtp));

        // Update user data
        $user->update(['otp' => $newOtp]);
        $user->update(['last_otp_sent_at' => $currentTime]);

        return response()->json(['message' => 'OTP resent successfully']);
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
}
