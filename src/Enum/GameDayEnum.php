<?php

namespace App\Enum;

enum GameDayEnum: string
{
    case SATURDAY = 'saturday';
    case SUNDAY = 'sunday';

    public static function values(): array
    {
        return [
            self::SATURDAY->value,
            self::SUNDAY->value,
        ];
    }
}
