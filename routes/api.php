<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\EventReminderController;

Route::post('/register',[AuthController::class,'register_user']);

Route::post('/login',[AuthController::class,'login_user']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/events', [EventReminderController::class,'create_events']);

    Route::post('/reminders', [EventReminderController::class,'create_reminder']);
    Route::get('/events', [EventReminderController::class, 'getUser_events']);
    Route::get('/reminders/{event_id}', [EventReminderController::class, 'getUser_Reminders']);
    Route::put('/events/{id}', [EventReminderController::class, 'update_event']);
});


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
