<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Services\TransferValidator;
use PHPUnit\Framework\TestCase;

class TransferValidatorTest extends TestCase
{
    private TransferValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new TransferValidator();
    }

    public function testShouldThrowExceptionWhenPayerIsMerchant(): void
    {
        $payer = $this->createUserMock('merchant', 100.0);
        $payee = $this->createUserMock('common', 0.0);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Merchants cannot send money');

        $this->validator->validate($payer, $payee, 50.0);
    }

    public function testShouldThrowExceptionWhenInsufficientBalance(): void
    {
        $payer = $this->createUserMock('common', 50.0);
        $payee = $this->createUserMock('common', 0.0);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Insufficient balance');

        $this->validator->validate($payer, $payee, 100.0);
    }

    public function testShouldThrowExceptionWhenValueIsZero(): void
    {
        $payer = $this->createUserMock('common', 100.0);
        $payee = $this->createUserMock('common', 0.0);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Transfer value must be greater than zero');

        $this->validator->validate($payer, $payee, 0.0);
    }

    public function testShouldThrowExceptionWhenValueIsNegative(): void
    {
        $payer = $this->createUserMock('common', 100.0);
        $payee = $this->createUserMock('common', 0.0);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Transfer value must be greater than zero');

        $this->validator->validate($payer, $payee, -10.0);
    }

    public function testShouldPassWhenAllConditionsAreMet(): void
    {
        $payer = $this->createUserMock('common', 100.0);
        $payee = $this->createUserMock('common', 0.0);

        $this->validator->validate($payer, $payee, 50.0);

        $this->assertTrue(true);
    }

    private function createUserMock(string $userType, float $balance): User
    {
        $user = $this->createMock(User::class);
        $user->method('isMerchant')->willReturn($userType === 'merchant');
        $user->method('hasSufficientBalance')->willReturnCallback(function ($amount) use ($balance) {
            return $balance >= $amount;
        });

        return $user;
    }
}

