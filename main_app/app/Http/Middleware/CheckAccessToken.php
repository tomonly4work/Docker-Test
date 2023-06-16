<?php

namespace App\Http\Middleware;

use Closure;

class CheckAccessToken
{
    public function handle($request, Closure $next)
    {
        $tokenType = auth()->payload()->get('token');

        if ($tokenType !== 'access') {
            return response()->json(['message' => 'Error, not access token.'], 401);
        }

        return $next($request);
    }
}
