<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->userType == 'USER' && auth()->user()->user_status == 1 ){
            return $next($request);
        }
        return response()->json([
            'message' => 'Subscription is not complete yet'
        ],402);
    }
}
