<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountWithdrawTest extends TestCase
{
    use RefreshDatabase;

    protected array $apiHeaders = ['Accept' => 'application/json'];

    public function test_happy_path_withdraw_debits_correct_account(): void
    {
        BankAccount::factory()->create(['id' => '100', 'balance' => 30.00]);

        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/event', [
                'type' => 'withdraw',
                'origin' => '100',
                'amount' => 10.00
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('bank_accounts', ['id' => '100', 'balance' => 20.00]);
    }

    public function test_fails_when_origin_account_does_not_exist(): void
    {
        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/event', [
                'type' => 'withdraw',
                'origin' => '999',
                'amount' => 10.00
            ]);

        $response->assertStatus(404);
    }
}