<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\External\NotificationServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        private int $userId,
        private string $message
    ) {
    }

    public function handle(NotificationServiceInterface $notificationService): void
    {
        try {
            $notificationService->notify($this->userId, $this->message);
        } catch (\Exception $e) {
            Log::error('Notification job failed', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Notification job failed permanently', [
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);
    }
}

