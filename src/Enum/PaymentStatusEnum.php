<?php

namespace App\Enum;

enum PaymentStatusEnum: string
{

    case SUCCESS = 'success';
    case FAILED = 'failed';
    case PROCESSING = 'processing';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::SUCCESS =>'Paiement réussi',
            self::FAILED =>'Paiement refusé',
            self::PROCESSING =>'Paiement en attente',
            self::CANCELLED => 'Paiement annulé'
        };
    }

}
