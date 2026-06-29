<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountTransferTest extends TestCase
{
    use RefreshDatabase;

    protected array $apiHeaders = ['Accept' => 'application/json'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_happy_path_transfer_between_existing_accounts_updates_balances_atomically(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        BankAccount::factory()->create(['id' => '100', 'balance' => 40.00]);
        BankAccount::factory()->create(['id' => '300', 'balance' => 10.00]);

        Log::spy();

        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/api/event', [
                'type' => 'transfer',
                'origin' => '100',
                'amount' => 15.00,
                'destination' => '300'
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('origin.id', '100')
            ->assertJsonPath('origin.balance', 25) // 💡 Corrigido para 25 plano
            ->assertJsonPath('destination.id', '300')
            ->assertJsonPath('destination.balance', 25); // 💡 Corrigido para 25 plano

        $this->assertDatabaseHas('bank_accounts', ['id' => '100', 'balance' => 25.00]);
        $this->assertDatabaseHas('bank_accounts', ['id' => '300', 'balance' => 25.00]);

        Log::shouldHaveReceived('info')
            ->once()
            ->withArgs(function ($message) {
                $logData = json_decode($message, true);
                return isset($logData['status']) && 
                       $logData['status'] === 'success' && 
                       str_contains($logData['message'], 'Bank transfer from account 100 in the amount of 15.00 to account 300');
            });
    }

    public function test_transfer_creates_destination_account_if_it_does_not_exist(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        BankAccount::factory()->create(['id' => '100', 'balance' => 40.00]);
        $this->assertDatabaseMissing('bank_accounts', ['id' => '400']);

        Log::spy();

        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/api/event', [
                'type' => 'transfer',
                'origin' => '100',
                'amount' => 10.00,
                'destination' => '400'
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('bank_accounts', ['id' => '400', 'balance' => 10.00]);

        Log::shouldHaveReceived('info')->once();
    }

    public function test_fails_when_origin_account_does_not_exist_and_balances_remain_unchanged(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        BankAccount::factory()->create(['id' => '300', 'balance' => 10.00]);

        Log::spy();

        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/api/event', [
                'type' => 'transfer',
                'origin' => '999',
                'amount' => 15.00,
                'destination' => '300'
            ]);

        $response->assertStatus(404);
        $this->assertDatabaseHas('bank_accounts', ['id' => '300', 'balance' => 10.00]);
    }

    public function test_fails_and_rolls_back_entire_operation_atomically_on_insufficient_funds(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        BankAccount::factory()->create(['id' => '100', 'balance' => 5.00]);
        BankAccount::factory()->create(['id' => '300', 'balance' => 10.00]);

        Log::spy();

        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/api/event', [
                'type' => 'transfer',
                'origin' => '100',
                'amount' => 50.00,
                'destination' => '300'
            ]);

        $response->assertStatus(404);
        
        $this->assertDatabaseHas('bank_accounts', ['id' => '100', 'balance' => 5.00]);
        $this->assertDatabaseHas('bank_accounts', ['id' => '300', 'balance' => 10.00]);
    }

    public function test_fails_when_required_fields_for_transfer_are_missing(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/api/event', [
                'type' => 'transfer',
                'origin' => '100',
                'amount' => 10.00
            ]);

        $response->assertStatus(404);
    }
}