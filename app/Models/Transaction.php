<?php

declare(strict_types=1);

namespace App\Models;

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
        $this->status = 'completed';
        $this->authorization_code = $authorizationCode;
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->status = 'failed';
        $this->error_message = $errorMessage;
    }

    public function markAsReversed(): void
    {
        $this->status = 'reversed';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isReversed(): bool
    {
        return $this->status === 'reversed';
    }
}

