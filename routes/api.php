<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BankAccountController;

Route::get('/reset',BankAccountController::class,'index');