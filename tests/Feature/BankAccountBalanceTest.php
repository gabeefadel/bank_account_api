<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankAccountBalanceTest extends TestCase
{
    use RefreshDatabase;

    protected array $apiHeaders = ['Accept' => 'application/json'];

    public function test_can_get_balance_for_existing_account(): void
    {
        BankAccount::factory()->create(['id' => 100, 'balance' => 20.00]);

        $response = $this->withHeaders($this->apiHeaders)
            ->getJson('/balance?account_id=100');

        $response->assertStatus(200);
        $this->assertEquals("20", $response->getContent());
    }

    public function test_returns_404_if_account_does_not_exist(): void
    {
        $response = $this->withHeaders($this->apiHeaders)
            ->getJson('/balance?account_id=9999');

        $response->assertStatus(404);
    }
}