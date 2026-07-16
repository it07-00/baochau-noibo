<?php

use App\Http\Controllers\Api\WorkScheduleApiController;
use Illuminate\Support\Facades\Route;

Route::get('/work-schedules', [WorkScheduleApiController::class, 'index']);
Route::get('/users', [WorkScheduleApiController::class, 'users']);
Route::post('/notify', [WorkScheduleApiController::class, 'notify']);
