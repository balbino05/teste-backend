<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionStatus as Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payer_id',
        'payee_id',
        'value',
        'status',
        'authorization_code',
        'error_message',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'status' => Status::class,
    ];

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payee_id');
    }

    public function markAsCompleted(string $authorizationCode): void
    {
        $this->status = Status::COMPLETED;
        $this->authorization_code = $authorizationCode;
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->status = Status::FAILED;
        $this->error_message = $errorMessage;
    }

    public function markAsReversed(): void
    {
        $this->status = Status::REVERSED;
    }

    public function isCompleted(): bool
    {
        return $this->status === Status::COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === Status::FAILED;
    }

    public function isReversed(): bool
    {
        return $this->status === Status::REVERSED;
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', Status::COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', Status::FAILED);
    }

    public function scopePending($query)
    {
        return $query->where('status', Status::PENDING);
    }
}

