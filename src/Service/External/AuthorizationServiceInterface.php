<?php

declare(strict_types=1);

namespace PicPay\Service\External;

interface AuthorizationServiceInterface
{
    public function authorize(): bool;
}

