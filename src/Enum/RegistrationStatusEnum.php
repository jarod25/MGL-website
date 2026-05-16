<?php

namespace App\Enum;

enum RegistrationStatusEnum: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public static function values(): array
    {
        return [
            self::PENDING_PAYMENT->value,
            self::PAID->value,
            self::CANCELLED->value,
            self::REFUNDED->value,
        ];
    }
}