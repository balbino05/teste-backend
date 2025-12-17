<?php

declare(strict_types=1);

namespace PicPay\Domain;

enum UserType: string
{
    case COMMON = 'common';
    case MERCHANT = 'merchant';
}

