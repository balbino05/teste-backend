<?php

declare(strict_types=1);

namespace PicPay\Infrastructure\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class ConnectionFactory
{
    public static function create(array $config): Connection
    {
        return DriverManager::getConnection([
            'dbname' => $config['name'],
            'user' => $config['user'],
            'password' => $config['password'],
            'host' => $config['host'],
            'port' => $config['port'] ?? 3306,
            'driver' => 'pdo_mysql',
            'charset' => 'utf8mb4',
        ]);
    }
}

