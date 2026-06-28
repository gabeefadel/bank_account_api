<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BankAccountController;

Route::post('/reset', [BankAccountController::class, 'reset']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/balance', [BankAccountController::class, 'balance']);
    Route::post('/event', [BankAccountController::class, 'store']);
});