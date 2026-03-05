<?php

declare(strict_types=1);

namespace Showoff\Core\Domain\Contact;

final readonly class ContactName
{
    public function __construct(
        public string $value,
    ) {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new \InvalidArgumentException('Contact name must not be empty.');
        }

        if (mb_strlen($trimmed) > 120) {
            throw new \InvalidArgumentException('Contact name must be at most 120 characters.');
        }
    }
}
