<?php

namespace App\Validator;

use App\Entity\Event;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class IsValidDateValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IsValidDate) {
            throw new UnexpectedTypeException($constraint, IsValidDate::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof Event) {
            throw new UnexpectedValueException($value, 'PackageFormData');
        }

        $today = new \DateTime('now');
        $startDate = $value->getStartDate();
        $endDate = $value->getEndDate();

        if ($startDate < $today) {
            $this->context->buildViolation($constraint->messageStartDate)
                ->atPath('startDate')
                ->addViolation();
        }

        if ($endDate <= $startDate) {
            $this->context->buildViolation($constraint->messageEndDate)
                ->atPath('endDate')
                ->addViolation();
        }
    }
}