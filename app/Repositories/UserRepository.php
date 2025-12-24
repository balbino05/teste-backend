<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserRepository
{
    private const CACHE_TTL = 3600;

    public function findById(int $id): ?User
    {
        return Cache::remember("user.{$id}", self::CACHE_TTL, function () use ($id) {
            return User::find($id);
        });
    }

    public function findByCpf(string $cpf): ?User
    {
        return Cache::remember("user.cpf.{$cpf}", self::CACHE_TTL, function () use ($cpf) {
            return User::where('cpf', $cpf)->first();
        });
    }

    public function findByEmail(string $email): ?User
    {
        return Cache::remember("user.email.{$email}", self::CACHE_TTL, function () use ($email) {
            return User::where('email', $email)->first();
        });
    }

    public function invalidateCache(User $user): void
    {
        Cache::forget("user.{$user->id}");
        Cache::forget("user.cpf.{$user->cpf}");
        Cache::forget("user.email.{$user->email}");
    }

    public function invalidateCacheById(int $userId): void
    {
        $user = User::find($userId);
        if ($user) {
            $this->invalidateCache($user);
        }
    }

    public function findByIdWithLock(int $id): ?User
    {
        Cache::forget("user.{$id}");
        return User::where('id', $id)->lockForUpdate()->first();
    }

    public function create(array $data): User
    {
        $user = User::create($data);
        $this->invalidateCache($user);
        return $user;
    }
}

