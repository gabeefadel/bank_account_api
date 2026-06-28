<?php

namespace Database\Seeders;

use App\Models\BankingOperation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankingOperationsSeeder extends Seeder
{

    public function run(): void
    {
        $operations = [
            ['id' => 1, 'action' => 'deposit'],
            ['id' => 2, 'action' => 'withdraw'],
            ['id' => 3, 'action' => 'transfer'],
        ];

        foreach ($operations as $operation) {
            BankingOperation::updateOrCreate(
                ['id' => $operation['id']],
                ['action' => $operation['action']] // O Eloquent gerencia o timestamps automaticamente
            );
        }
    }
}