<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Token;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'refresh']]);
    }
    
    /**
     * Get a JWT via given credentials. (使用者登入)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            # 'role' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if (!$access_token = auth()->claims(['token' => 'access'])->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user_id = auth()->user()->id;
        $refresh_token = auth()->claims(['token' => 'refresh'])->setTTL(env('JWT_Real_REFRESH_TTL'))->tokenById($user_id);;
        return $this->createNewToken($access_token, $refresh_token);
    }

    /**
     * Register a User. (使用者註冊)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            # 'role' => 'required|string',
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));
        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    /**
     * Log the user out (Invalidate the token). (使用者登出，移除 JWT token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token. (更新 JWT token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {   
       
        $refresh_token = $request->cookie('refresh_token');
        $userId = JWTAuth::decode(new Token($refresh_token))->get('sub');
        $new_access_token = auth()->claims(['token' => 'access'])->tokenById($userId);
        $new_refresh_token = JWTAuth::setToken($refresh_token)->claims(['token' => 'refresh', 'exp' => Carbon::now()->addMinutes(env('JWT_Real_REFRESH_TTL'))->timestamp])->refresh(true,true);
        JWTAuth::invalidate($refresh_token);
        return $this->createNewToken($new_access_token, $new_refresh_token);
    }

    /**
     * Get the authenticated User. (以 JWT token 取得使用者資訊)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {   
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($access_token, $refresh_token)
    {
        $response = response()->json([
            'access_token' => $access_token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 1,
            'user' => auth()->user()
        ]);
        $response->cookie('refresh_token', $refresh_token);

        return $response;
    }

}