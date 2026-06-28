<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BankAccountBalanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Cabeçalhos padrão para forçar o Laravel a responder estritamente como API JSON.
     */
    protected array $apiHeaders = [
        'Accept' => 'application/json',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_get_balance_for_existing_account(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        BankAccount::factory()->create([
            'id' => 100,
            'balance' => 20.00
        ]);

        // Força o comportamento de API JSON
        $response = $this->withHeaders($this->apiHeaders)
            ->getJson('/api/balance?account_id=100');

        $response->assertStatus(200);
        $this->assertEquals("20", $response->getContent()); 
    }

    public function test_returns_404_and_zero_balance_if_account_does_not_exist(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->withHeaders($this->apiHeaders)
            ->getJson('/api/balance?account_id=9999');

        $response->assertStatus(404);
        $this->assertEquals("0", $response->getContent());
    }

    public function test_cannot_get_balance_if_user_is_not_authenticated(): void
    {
        BankAccount::factory()->create(['id' => 100, 'balance' => 20.00]);

        // Sem Sanctum::actingAs, o Accept JSON vai forçar o retorno correto de 401 Unauthorized
        $response = $this->withHeaders($this->apiHeaders)
            ->getJson('/api/balance?account_id=100');
        
        $response->assertStatus(401);
    }

    public function test_handles_malicious_sql_injection_payload_securely(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        BankAccount::factory()->create(['id' => 100, 'balance' => 20.00]);

        $maliciousId = "100 OR 1=1; --";

        $response = $this->withHeaders($this->apiHeaders)
            ->getJson('/api/balance?account_id=' . urlencode($maliciousId));

        $response->assertStatus(404);
        $this->assertEquals("0", $response->getContent());
    }
}