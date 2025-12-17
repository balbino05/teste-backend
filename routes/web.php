<?php

use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Endpoint de transferência conforme especificação do desafio
Route::post('/transfer', [TransferController::class, 'transfer']);

