<?php

declare(strict_types=1);

namespace App\Module\Contact\Api;

final readonly class ContactSubmissionInput
{
    public function __construct(
        public string $name,
        public string $email,
        public string $message,
        public string $source,
    ) {}
}
