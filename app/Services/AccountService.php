<?php

namespace App\Services;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AccountService
{

    public function getBalance(string $accountId): float
    {
        $account = BankAccount::findOrFail($accountId);

        return (float) $account->balance;
    }
}