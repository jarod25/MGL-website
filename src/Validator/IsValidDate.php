<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class IsValidDate extends Constraint
{
    public string $messageStartDate = 'La date de début doit être ultérieure à la date du jour.';
    public string $messageEndDate = 'La date de fin doit être ultérieure à la date de début.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}