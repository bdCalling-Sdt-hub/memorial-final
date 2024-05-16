<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserMiddleware
{

//    public function handle(Request $request, Closure $next): Response
//    {
//        if (auth()->check() && auth()->user()->userType == 'USER'){
//            return $next($request);
//        }
//        return response()->json([
//            'message' => 'Unauthorized user'
//        ],401);
//    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = auth()->userOrFail();
            if ($user->userType == 'USER') {
                return $next($request);
            }
            return response()->json([
                'message' => 'Unauthorized user'
            ], 401);
        } catch (AuthenticationException $exception) {
            return response()->json([
                'message' => 'Unauthorized: ' . $exception->getMessage()
            ], 401);
        }
    }
}
