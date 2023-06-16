<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class Authenticate extends Middleware
{
    /**
     * Exclude these routes from authentication check.
     *
     * @var array
     */
    protected $except = [
        'api/auth/refresh'
    ];

    /**
     * Ensure the user is authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next,...$guards)
    {
        try {
            foreach ($this->except as $excluded_route) {
                if ($request->path() === $excluded_route) {
                    return $next($request);
                }
            }
            JWTAuth::parseToken()->authenticate();
            return $next($request);
        }catch (Exception $e)
        {
            if ($e instanceof TokenBlacklistedException)
            {
                return response()->json([
                    'error' => 'token_blacklisted',
                ], 401);
            } 
            elseif ($e instanceof TokenExpiredException)
            {
                return response()->json([
                    'error' => 'token_expired',
                ], 401);
            }
            elseif ($e instanceof TokenInvalidException)
            {
                return response()->json([
                    'error' => 'token_invalid',
                ], 401);
            }
            elseif ($e instanceof JWTException)
            {
                return response()->json([
                    'error' => 'token_absent',
                ], 401);
            }
        }
    }
}
