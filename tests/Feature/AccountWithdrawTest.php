<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountWithdrawTest extends TestCase
{
    use RefreshDatabase;

    protected array $apiHeaders = ['Accept' => 'application/json'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_cannot_withdraw_if_user_is_not_authenticated(): void
    {
        BankAccount::factory()->create(['id' => '100', 'balance' => 20.00]);

        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/api/event', [
                'type' => 'withdraw',
                'origin' => '100',
                'amount' => 10.00
            ]);

        $response->assertStatus(401);
    }

    public function test_happy_path_withdraw_debits_correct_account_and_logs_audit(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        BankAccount::factory()->create(['id' => '100', 'balance' => 30.00]);
        BankAccount::factory()->create(['id' => '555', 'balance' => 10.00]);

        Log::spy();

        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/api/event', [
                'type' => 'withdraw',
                'origin' => '100',
                'amount' => 10.00
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('origin.id', '100')
            ->assertJsonPath('origin.balance', 20); // 💡 Corrigido para 20 plano

        $this->assertDatabaseHas('bank_accounts', ['id' => '100', 'balance' => 20.00]);
        $this->assertDatabaseHas('bank_accounts', ['id' => '555', 'balance' => 10.00]);

        Log::shouldHaveReceived('info')
            ->once()
            ->withArgs(function ($message) {
                $logData = json_decode($message, true);
                return isset($logData['status']) && 
                       $logData['status'] === 'success' && 
                       str_contains($logData['message'], 'Withdrawal of 10.00 processed from account 100');
            });
    }

    public function test_fails_and_logs_warning_when_origin_account_does_not_exist(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Log::spy();

        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/api/event', [
                'type' => 'withdraw',
                'origin' => '999',
                'amount' => 10.00
            ]);

        $response->assertStatus(404)
            ->assertSee('0');

        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(function ($message) {
                $logData = json_decode($message, true);
                return isset($logData['status']) && 
                       $logData['status'] === 'failed' && 
                       // 💡 Alterado para pegar a mensagem real da exception de banco do Laravel
                       (str_contains($logData['reason'], 'No query results') || str_contains($logData['reason'], 'not found'));
            });
    }

    public function test_fails_and_logs_warning_when_amount_is_insufficient(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        BankAccount::factory()->create(['id' => '100', 'balance' => 5.00]);

        Log::spy();

        $response = $this->withHeaders($this->apiHeaders)
            ->postJson('/api/event', [
                'type' => 'withdraw',
                'origin' => '100',
                'amount' => 50.00
            ]);

        $response->assertStatus(404);
        $this->assertDatabaseHas('bank_accounts', ['id' => '100', 'balance' => 5.00]);

        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(function ($message) {
                $logData = json_decode($message, true);
                return isset($logData['status']) && 
                       $logData['status'] === 'failed' && 
                       str_contains($logData['reason'], 'Insufficient funds.');
            });
    }
}