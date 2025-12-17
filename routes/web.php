<?php

use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Endpoint de transferência conforme especificação do desafio
Route::post('/transfer', [TransferController::class, 'transfer'])
    ->middleware('throttle:60,1'); // Rate limiting: 60 requisições por minuto

