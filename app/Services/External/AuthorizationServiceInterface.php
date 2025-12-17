<?php

declare(strict_types=1);

namespace App\Services\External;

interface AuthorizationServiceInterface
{
    public function authorize(): bool;
}

