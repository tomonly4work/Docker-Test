<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Token;

class CheckRefreshToken
{
    public function handle($request, Closure $next)
    {

        $refresh_token = $request->cookie('refresh_token');
        $tokenType = JWTAuth::decode(new Token($refresh_token))->get('token');

        if ($tokenType !== 'refresh') {
            return response()->json(['message' => 'Error, not refresh token.'], 401);
        }

        return $next($request);
    }
}
