<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Contact;

final readonly class NewContactSubmission
{
    public function __construct(
        public string $name,
        public string $email,
        public string $message,
    ) {}
}
