<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use PicPay\Domain\User;
use PicPay\Domain\UserType;
use PicPay\Infrastructure\Database\ConnectionFactory;
use PicPay\Repository\UserRepository;

// Carrega variáveis de ambiente
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Cria conexão com banco de dados
$dbConfig = [
    'host' => $_ENV['DB_HOST'] ?? 'mysql',
    'port' => (int) ($_ENV['DB_PORT'] ?? 3306),
    'name' => $_ENV['DB_NAME'] ?? 'picpay_db',
    'user' => $_ENV['DB_USER'] ?? 'picpay_user',
    'password' => $_ENV['DB_PASS'] ?? 'picpay_password',
];

$connection = ConnectionFactory::create($dbConfig);
$userRepository = new UserRepository($connection);

echo "Seeding database...\n";

// Usuários comuns
$commonUser1 = new User(
    id: null,
    name: 'João Silva',
    cpf: '12345678901',
    email: 'joao@example.com',
    password: password_hash('senha123', PASSWORD_DEFAULT),
    userType: UserType::COMMON,
    balance: 1000.00
);

$commonUser2 = new User(
    id: null,
    name: 'Maria Santos',
    cpf: '98765432100',
    email: 'maria@example.com',
    password: password_hash('senha123', PASSWORD_DEFAULT),
    userType: UserType::COMMON,
    balance: 500.00
);

// Lojistas
$merchant1 = new User(
    id: null,
    name: 'Loja ABC',
    cpf: '11223344556',
    email: 'loja@example.com',
    password: password_hash('senha123', PASSWORD_DEFAULT),
    userType: UserType::MERCHANT,
    balance: 0.00
);

$merchant2 = new User(
    id: null,
    name: 'Comércio XYZ',
    cpf: '99887766554',
    email: 'comercio@example.com',
    password: password_hash('senha123', PASSWORD_DEFAULT),
    userType: UserType::MERCHANT,
    balance: 0.00
);

$userId1 = $userRepository->save($commonUser1);
$commonUser1 = $userRepository->findById($userId1);
echo "Created user: João Silva (ID: {$userId1})\n";

$userId2 = $userRepository->save($commonUser2);
$commonUser2 = $userRepository->findById($userId2);
echo "Created user: Maria Santos (ID: {$userId2})\n";

$merchantId1 = $userRepository->save($merchant1);
$merchant1 = $userRepository->findById($merchantId1);
echo "Created merchant: Loja ABC (ID: {$merchantId1})\n";

$merchantId2 = $userRepository->save($merchant2);
$merchant2 = $userRepository->findById($merchantId2);
echo "Created merchant: Comércio XYZ (ID: {$merchantId2})\n";

echo "Database seeded successfully!\n";
echo "\nTest users:\n";
echo "- Common User 1: ID {$userId1}, Balance: R$ 1000.00\n";
echo "- Common User 2: ID {$userId2}, Balance: R$ 500.00\n";
echo "- Merchant 1: ID {$merchantId1}, Balance: R$ 0.00\n";
echo "- Merchant 2: ID {$merchantId2}, Balance: R$ 0.00\n";

