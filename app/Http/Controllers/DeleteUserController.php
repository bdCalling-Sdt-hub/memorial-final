<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DeleteUserController extends Controller
{
    //
    public function deleteUser(Request $request)
    {

        if (auth()->user()->google_id == null && auth()->user()->apple_id == null){
            $validator = Validator::make($request->all(), [
                'password' => 'required|string',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $auth_email = auth()->user();
            if ($auth_email){
                $user = User::where('email', $auth_email->email)->first();
                // Check if user exists and password is correct
                if ($user && Hash::check($request->password, $user->password)) {
                    $user->forcedelete();
                    return response()->json(['message' => 'User deleted successfully'], 200);
                } else {
                    // Incorrect credentials
                    return response()->json(['message' => 'Invalid email or password'], 401);
                }
            }
        }else{
            // for google user only
            $user = User::where('email',auth()->user()->email)->first();
            $user->forcedelete();
            return response()->json([
                'message' => 'Google or Apple user deleted successfully',
            ]);
        }
    }
}
