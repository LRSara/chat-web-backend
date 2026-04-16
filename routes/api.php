<?php

use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Support\Facades\Route;

Route::post('/rooms', [RoomController::class, 'store']);
Route::get('/rooms', [RoomController::class, 'index']);
Route::post('/rooms/{id}/join', [RoomController::class, 'join']);
Route::post('/rooms/{id}/leave/{nick}', [RoomController::class, 'leave']);
Route::get('/rooms/{id}/online', [RoomController::class, 'online']);

Route::get('/rooms/{roomId}/messages', [MessageController::class, 'index']);
Route::post('/rooms/{roomId}/messages', [MessageController::class, 'store']);

Route::post('/upload/image', [UploadController::class, 'image']);
Route::post('/upload/audio', [UploadController::class, 'audio']);
