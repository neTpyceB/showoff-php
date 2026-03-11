<?php

declare(strict_types=1);

namespace App\Showcase\Application\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class LowercaseCodeValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof LowercaseCode) {
            throw new UnexpectedTypeException($constraint, LowercaseCode::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!is_string($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', '[invalid-type]')
                ->addViolation();

            return;
        }

        if (preg_match('/^[a-z0-9-]+$/', $value) === 1) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}
