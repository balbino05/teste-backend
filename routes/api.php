<?php

use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

Route::post('/transfer', [TransferController::class, 'transfer'])
    ->middleware('throttle:api'); // Rate limiting usando o limiter 'api' configurado no RouteServiceProvider

