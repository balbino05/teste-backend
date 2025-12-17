<?php

use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

Route::post('/transfer', [TransferController::class, 'transfer'])
    ->middleware('throttle:60,1'); // Rate limiting: 60 requisições por minuto

