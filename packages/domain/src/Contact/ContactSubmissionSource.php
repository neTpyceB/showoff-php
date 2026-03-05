<?php

declare(strict_types=1);

namespace Showoff\Core\Domain\Contact;

final readonly class ContactSubmissionSource
{
    public function __construct(
        public string $value,
    ) {
        if (preg_match('/^[a-z0-9:_-]{3,64}$/', $value) !== 1) {
            throw new \InvalidArgumentException('Contact submission source must match /^[a-z0-9:_-]{3,64}$/.');
        }
    }
}
