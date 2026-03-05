<?php

declare(strict_types=1);

namespace Showoff\Core\Domain\Contact;

final readonly class ContactEmail
{
    public function __construct(
        public string $value,
    ) {
        $normalized = trim(strtolower($value));

        if (filter_var($normalized, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('Contact email must be a valid email address.');
        }

        if (mb_strlen($normalized) > 180) {
            throw new \InvalidArgumentException('Contact email must be at most 180 characters.');
        }
    }
}
