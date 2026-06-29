<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTransferTest extends TestCase
{
    use RefreshDatabase;

    protected array $apiHeaders = ['Accept' => 'application/json'];

    public function test_happy_path_transfer_between_existing_accounts_updates_balances_atomically(): void
    {
        BankAccount::factory()->create(['id' => '100', 'balance' => 40.00]);
        BankAccount::factory()->create(['id' => '300', 'balance' => 10.00]);

        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/event', [
                'type' => 'transfer',
                'origin' => '100',
                'amount' => 15.00,
                'destination' => '300'
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('bank_accounts', ['id' => '100', 'balance' => 25.00]);
        $this->assertDatabaseHas('bank_accounts', ['id' => '300', 'balance' => 25.00]);
    }

    public function test_transfer_creates_destination_account_if_it_does_not_exist(): void
    {
        BankAccount::factory()->create(['id' => '100', 'balance' => 40.00]);

        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/event', [
                'type' => 'transfer',
                'origin' => '100',
                'amount' => 10.00,
                'destination' => '400'
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('bank_accounts', ['id' => '400', 'balance' => 10.00]);
    }

    public function test_fails_when_origin_account_does_not_exist(): void
    {
        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/event', [
                'type' => 'transfer',
                'origin' => '999',
                'amount' => 15.00,
                'destination' => '300'
            ]);

        $response->assertStatus(404);
    }

    public function test_fails_on_insufficient_funds(): void
    {
        BankAccount::factory()->create(['id' => '100', 'balance' => 5.00]);

        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/event', [
                'type' => 'transfer',
                'origin' => '100',
                'amount' => 50.00,
                'destination' => '300'
            ]);

        $response->assertStatus(404);
    }
}