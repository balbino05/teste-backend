<?php

declare(strict_types=1);

namespace PicPay\Service\External;

interface NotificationServiceInterface
{
    public function notify(int $userId, string $message): bool;
}

