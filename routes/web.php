<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::post('/reset', function () {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    DB::table('log_accounts')->truncate();
    DB::table('banking_operations')->truncate();
    DB::table('bank_accounts')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    return response('OK', 200);
});