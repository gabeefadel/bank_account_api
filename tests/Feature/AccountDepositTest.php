<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountDepositTest extends TestCase
{
    use RefreshDatabase;

    protected array $apiHeaders = ['Accept' => 'application/json'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_happy_path_deposit_into_existing_account_updates_balance_and_types(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        BankAccount::factory()->create(['id' => '100', 'balance' => 20.00]);

        Log::spy();

        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/api/event', [
                'type' => 'deposit',
                'destination' => '100',
                'amount' => 10.00
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['destination' => ['id', 'balance']])
            ->assertJsonPath('destination.id', '100')
            ->assertJsonPath('destination.balance', 30);

        $this->assertDatabaseHas('bank_accounts', ['id' => '100', 'balance' => 30.00]);

        Log::shouldHaveReceived('info')
            ->once()
            ->withArgs(function ($message) {
                $logData = json_decode($message, true);
                return isset($logData['status']) && 
                       $logData['status'] === 'success' && 
                       str_contains($logData['message'], 'Deposit of 10.00 processed for account 100');
            });
    }

    public function test_creates_new_account_with_initial_balance_if_destination_does_not_exist(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->assertDatabaseMissing('bank_accounts', ['id' => '200']);

        Log::spy();

        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/api/event', [
                'type' => 'deposit',
                'destination' => '200',
                'amount' => 50.00
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('destination.id', '200')
            ->assertJsonPath('destination.balance', 50); 

        $this->assertDatabaseHas('bank_accounts', ['id' => '200', 'balance' => 50.00]);

        Log::shouldHaveReceived('info')->once();
    }

    public function test_fails_when_required_fields_are_missing(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/api/event', [
                'type' => 'deposit',
                'amount' => 10.00
            ]);

        $response->assertStatus(404);
    }

    public function test_fails_when_field_types_are_incorrect(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/api/event', [
                'type' => 'deposit',
                'destination' => '100',
                'amount' => 'invalid-string-payload'
            ]);

        $response->assertStatus(404);
    }

    public function test_fails_when_amount_is_negative(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/api/event', [
                'type' => 'deposit',
                'destination' => '100',
                'amount' => -10.00
            ]);

        $response->assertStatus(404);
    }
}