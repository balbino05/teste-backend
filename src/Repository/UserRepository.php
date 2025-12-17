<?php

declare(strict_types=1);

namespace PicPay\Repository;

use Doctrine\DBAL\Connection;
use PicPay\Domain\User;
use PicPay\Domain\UserType;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public function findById(int $id): ?User
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM users WHERE id = ?',
            [$id]
        );

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findByCpf(string $cpf): ?User
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM users WHERE cpf = ?',
            [$cpf]
        );

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findByEmail(string $email): ?User
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM users WHERE email = ?',
            [$email]
        );

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function save(User $user): int
    {
        $data = [
            'name' => $user->getName(),
            'cpf' => $user->getCpf(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'user_type' => $user->getUserType()->value,
            'balance' => $user->getBalance(),
        ];

        if ($user->getId() === null) {
            $this->connection->insert('users', $data);
            return (int) $this->connection->lastInsertId();
        } else {
            $this->connection->update('users', $data, ['id' => $user->getId()]);
            return $user->getId();
        }
    }

    public function updateBalance(int $userId, float $balance): void
    {
        $this->connection->update(
            'users',
            ['balance' => $balance],
            ['id' => $userId]
        );
    }

    private function hydrate(array $row): User
    {
        return new User(
            id: (int) $row['id'],
            name: $row['name'],
            cpf: $row['cpf'],
            email: $row['email'],
            password: $row['password'],
            userType: UserType::from($row['user_type']),
            balance: (float) $row['balance']
        );
    }
}

