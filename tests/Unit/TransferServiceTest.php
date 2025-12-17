<?php

declare(strict_types=1);

namespace PicPay\Tests\Unit;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use PicPay\Domain\Transaction;
use PicPay\Domain\User;
use PicPay\Domain\UserType;
use PicPay\Repository\TransactionRepositoryInterface;
use PicPay\Repository\UserRepositoryInterface;
use PicPay\Service\External\AuthorizationServiceInterface;
use PicPay\Service\External\NotificationServiceInterface;
use PicPay\Service\TransferService;
use Psr\Log\LoggerInterface;

class TransferServiceTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private TransactionRepositoryInterface $transactionRepository;
    private AuthorizationServiceInterface $authorizationService;
    private NotificationServiceInterface $notificationService;
    private Connection $connection;
    private LoggerInterface $logger;
    private TransferService $transferService;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->transactionRepository = $this->createMock(TransactionRepositoryInterface::class);
        $this->authorizationService = $this->createMock(AuthorizationServiceInterface::class);
        $this->notificationService = $this->createMock(NotificationServiceInterface::class);
        $this->connection = $this->createMock(Connection::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->transferService = new TransferService(
            $this->userRepository,
            $this->transactionRepository,
            $this->authorizationService,
            $this->notificationService,
            $this->connection,
            $this->logger
        );
    }

    public function testTransferShouldFailWhenPayerIsMerchant(): void
    {
        $payer = new User(1, 'Merchant', '12345678901', 'merchant@test.com', 'pass', UserType::MERCHANT, 100.0);
        $payee = new User(2, 'User', '98765432100', 'user@test.com', 'pass', UserType::COMMON, 0.0);

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($payer);

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with(2)
            ->willReturn($payee);

        $this->connection->expects($this->once())
            ->method('beginTransaction');

        $this->connection->expects($this->once())
            ->method('rollBack');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Merchants cannot send money');

        $this->transferService->transfer(1, 2, 50.0);
    }

    public function testTransferShouldFailWhenInsufficientBalance(): void
    {
        $payer = new User(1, 'User', '12345678901', 'user@test.com', 'pass', UserType::COMMON, 50.0);
        $payee = new User(2, 'User2', '98765432100', 'user2@test.com', 'pass', UserType::COMMON, 0.0);

        $this->userRepository->expects($this->exactly(2))
            ->method('findById')
            ->willReturnMap([
                [1, $payer],
                [2, $payee],
            ]);

        $this->connection->expects($this->once())
            ->method('beginTransaction');

        $this->connection->expects($this->once())
            ->method('rollBack');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Insufficient balance');

        $this->transferService->transfer(1, 2, 100.0);
    }

    public function testTransferShouldSucceedWhenAllConditionsAreMet(): void
    {
        $payer = new User(1, 'User', '12345678901', 'user@test.com', 'pass', UserType::COMMON, 100.0);
        $payee = new User(2, 'User2', '98765432100', 'user2@test.com', 'pass', UserType::COMMON, 0.0);

        $this->userRepository->expects($this->exactly(2))
            ->method('findById')
            ->willReturnMap([
                [1, $payer],
                [2, $payee],
            ]);

        $this->authorizationService->expects($this->once())
            ->method('authorize')
            ->willReturn(true);

        $this->userRepository->expects($this->exactly(2))
            ->method('updateBalance');

        $this->transactionRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Transaction::class));

        $this->notificationService->expects($this->once())
            ->method('notify')
            ->willReturn(true);

        $this->connection->expects($this->once())
            ->method('beginTransaction');

        $this->connection->expects($this->once())
            ->method('commit');

        $transaction = $this->transferService->transfer(1, 2, 50.0);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertTrue($transaction->isCompleted());
    }
}

