<?php

declare(strict_types=1);

namespace App\Showcase\Application\Validation;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
final class LowercaseCode extends Constraint
{
    public string $message = 'Value "{{ value }}" must contain only lowercase letters, digits, and hyphens.';
}
