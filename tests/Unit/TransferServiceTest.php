<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Transaction;
use App\Models\User;
use App\Services\External\AuthorizationServiceInterface;
use App\Services\External\NotificationServiceInterface;
use App\Services\TransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthorizationServiceInterface $authorizationService;
    private NotificationServiceInterface $notificationService;
    private TransferService $transferService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizationService = $this->createMock(AuthorizationServiceInterface::class);
        $this->notificationService = $this->createMock(NotificationServiceInterface::class);

        $this->app->instance(AuthorizationServiceInterface::class, $this->authorizationService);
        $this->app->instance(NotificationServiceInterface::class, $this->notificationService);

        $this->transferService = app(TransferService::class);
    }

    public function testTransferShouldFailWhenPayerIsMerchant(): void
    {
        $payer = User::factory()->create([
            'user_type' => 'merchant',
            'balance' => 100.0,
        ]);

        $payee = User::factory()->create([
            'user_type' => 'common',
            'balance' => 0.0,
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Merchants cannot send money');

        $this->transferService->transfer($payer->id, $payee->id, 50.0);
    }

    public function testTransferShouldFailWhenInsufficientBalance(): void
    {
        $payer = User::factory()->create([
            'user_type' => 'common',
            'balance' => 50.0,
        ]);

        $payee = User::factory()->create([
            'user_type' => 'common',
            'balance' => 0.0,
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Insufficient balance');

        $this->transferService->transfer($payer->id, $payee->id, 100.0);
    }

    public function testTransferShouldSucceedWhenAllConditionsAreMet(): void
    {
        $payer = User::factory()->create([
            'user_type' => 'common',
            'balance' => 100.0,
        ]);

        $payee = User::factory()->create([
            'user_type' => 'common',
            'balance' => 0.0,
        ]);

        $this->authorizationService->expects($this->once())
            ->method('authorize')
            ->willReturn(true);

        $this->notificationService->expects($this->once())
            ->method('notify')
            ->willReturn(true);

        $transaction = $this->transferService->transfer($payer->id, $payee->id, 50.0);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertTrue($transaction->isCompleted());
        $this->assertEquals(50.0, (float) $payer->fresh()->balance);
        $this->assertEquals(50.0, (float) $payee->fresh()->balance);
    }
}
