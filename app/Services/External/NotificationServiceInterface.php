<?php

declare(strict_types=1);

namespace App\Services\External;

interface NotificationServiceInterface
{
    public function notify(int $userId, string $message): bool;
}

