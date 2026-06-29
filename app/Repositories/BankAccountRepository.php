<?php

namespace App\Repositories;

use App\Models\BankAccount;

class BankAccountRepository
{

    public function findForUpdate(string $id): ?BankAccount
    {
        return BankAccount::where('id', $id)->lockForUpdate()->first();
    }

    public function create(string $id, float $initialBalance): BankAccount
    {
        return BankAccount::create([
            'id' => $id,
            'balance' => $initialBalance,
        ]);
    }

    public function updateBalance(BankAccount $account, float $newBalance): bool
    {
        return $account->update(['balance' => $newBalance]);
    }
}