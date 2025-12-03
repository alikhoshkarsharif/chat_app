<?php

use App\Http\Controllers\Api\ChatController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/user', function () {
    return auth()->user();
})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/contacts', [ChatController::class, 'contacts']);
    Route::get('/messages/{receiver}', [ChatController::class, 'messages']);
    Route::post('/messages', [ChatController::class, 'send']);
});
