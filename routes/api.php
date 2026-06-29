<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BankAccountController;

Route::get('/balance', [BankAccountController::class, 'balance']);
Route::post('/event', [BankAccountController::class, 'store']);
