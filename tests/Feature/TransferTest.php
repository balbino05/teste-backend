<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use RefreshDatabase;

    public function testTransferBetweenCommonUsers(): void
    {
        $payer = User::factory()->create([
            'user_type' => 'common',
            'balance' => 1000.00,
        ]);

        $payee = User::factory()->create([
            'user_type' => 'common',
            'balance' => 0.00,
        ]);

        Http::fake([
            'util.devi.tools/api/v2/authorize' => Http::response(['message' => 'Autorizado'], 200),
            'util.devi.tools/api/v1/notify' => Http::response([], 200),
        ]);

        $response = $this->postJson('/transfer', [
            'value' => 100.0,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'transaction_id',
                'status',
                'message',
            ]);

        $this->assertEquals(900.00, (float) $payer->fresh()->balance);
        $this->assertEquals(100.00, (float) $payee->fresh()->balance);
    }

    public function testTransferFromCommonToMerchant(): void
    {
        $payer = User::factory()->create([
            'user_type' => 'common',
            'balance' => 500.00,
        ]);

        $payee = User::factory()->create([
            'user_type' => 'merchant',
            'balance' => 0.00,
        ]);

        Http::fake([
            'util.devi.tools/api/v2/authorize' => Http::response(['message' => 'Autorizado'], 200),
            'util.devi.tools/api/v1/notify' => Http::response([], 200),
        ]);

        $response = $this->postJson('/transfer', [
            'value' => 50.0,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $response->assertStatus(201);
        $this->assertEquals(450.00, (float) $payer->fresh()->balance);
        $this->assertEquals(50.00, (float) $payee->fresh()->balance);
    }

    public function testTransferFailsWhenMerchantTriesToSend(): void
    {
        $payer = User::factory()->create([
            'user_type' => 'merchant',
            'balance' => 1000.00,
        ]);

        $payee = User::factory()->create([
            'user_type' => 'common',
            'balance' => 0.00,
        ]);

        $response = $this->postJson('/transfer', [
            'value' => 100.0,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Merchants cannot send money',
            ]);
    }

    public function testTransferFailsWhenInsufficientBalance(): void
    {
        $payer = User::factory()->create([
            'user_type' => 'common',
            'balance' => 50.00,
        ]);

        $payee = User::factory()->create([
            'user_type' => 'common',
            'balance' => 0.00,
        ]);

        $response = $this->postJson('/transfer', [
            'value' => 100.0,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Insufficient balance',
            ]);
    }

    public function testTransferFailsWhenNotAuthorized(): void
    {
        $payer = User::factory()->create([
            'user_type' => 'common',
            'balance' => 1000.00,
        ]);

        $payee = User::factory()->create([
            'user_type' => 'common',
            'balance' => 0.00,
        ]);

        Http::fake([
            'util.devi.tools/api/v2/authorize' => Http::response(['message' => 'Não autorizado'], 200),
        ]);

        $response = $this->postJson('/transfer', [
            'value' => 100.0,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Transaction not authorized',
            ]);
    }

    public function testTransferValidatesRequiredFields(): void
    {
        $response = $this->postJson('/transfer', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['value', 'payer', 'payee']);
    }
}

