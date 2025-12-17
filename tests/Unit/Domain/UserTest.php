<?php

declare(strict_types=1);

namespace PicPay\Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use PicPay\Domain\User;
use PicPay\Domain\UserType;

class UserTest extends TestCase
{
    public function testUserCanDebitWhenHasSufficientBalance(): void
    {
        $user = new User(1, 'Test User', '12345678901', 'test@test.com', 'pass', UserType::COMMON, 100.0);

        $user->debit(50.0);

        $this->assertEquals(50.0, $user->getBalance());
    }

    public function testUserCannotDebitWhenInsufficientBalance(): void
    {
        $user = new User(1, 'Test User', '12345678901', 'test@test.com', 'pass', UserType::COMMON, 50.0);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Insufficient balance');

        $user->debit(100.0);
    }

    public function testUserCanCredit(): void
    {
        $user = new User(1, 'Test User', '12345678901', 'test@test.com', 'pass', UserType::COMMON, 50.0);

        $user->credit(25.0);

        $this->assertEquals(75.0, $user->getBalance());
    }

    public function testUserCannotCreditNegativeAmount(): void
    {
        $user = new User(1, 'Test User', '12345678901', 'test@test.com', 'pass', UserType::COMMON, 50.0);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Amount must be greater than zero');

        $user->credit(-10.0);
    }

    public function testIsMerchantReturnsTrueForMerchant(): void
    {
        $user = new User(1, 'Merchant', '12345678901', 'merchant@test.com', 'pass', UserType::MERCHANT, 0.0);

        $this->assertTrue($user->isMerchant());
        $this->assertFalse($user->isCommon());
    }

    public function testIsCommonReturnsTrueForCommonUser(): void
    {
        $user = new User(1, 'User', '12345678901', 'user@test.com', 'pass', UserType::COMMON, 0.0);

        $this->assertTrue($user->isCommon());
        $this->assertFalse($user->isMerchant());
    }
}

