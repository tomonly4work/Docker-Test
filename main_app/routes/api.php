<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group([
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])
    ->middleware('api'); 
    Route::post('/login', [AuthController::class, 'login'])
    ->middleware('api');       
    Route::get('/user-profile', [AuthController::class, 'userProfile'])
    ->middleware('check.access.token', 'api');    
    Route::post('/refresh', [AuthController::class, 'refresh'])
    ->middleware('jwt.auth.check', 'check.refresh.token');       
    Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('api');        
});