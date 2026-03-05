<?php

declare(strict_types=1);

namespace Showoff\Core\Domain\Contact;

final readonly class ContactMessage
{
    public function __construct(
        public string $value,
    ) {
        $trimmed = trim($value);

        if (mb_strlen($trimmed) < 10) {
            throw new \InvalidArgumentException('Contact message must be at least 10 characters.');
        }
    }
}
