<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
});

Route::post('/transfer', [TransferController::class, 'transfer'])
    ->middleware('throttle:api'); // Rate limiting usando o limiter 'api' configurado no RouteServiceProvider

