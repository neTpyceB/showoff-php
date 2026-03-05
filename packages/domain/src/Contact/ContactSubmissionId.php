<?php

declare(strict_types=1);

namespace Showoff\Core\Domain\Contact;

final readonly class ContactSubmissionId
{
    public function __construct(
        public int $value,
    ) {
        if ($value < 1) {
            throw new \InvalidArgumentException('Contact submission id must be a positive integer.');
        }
    }
}
