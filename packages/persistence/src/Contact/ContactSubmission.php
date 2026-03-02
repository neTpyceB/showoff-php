<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Contact;

final readonly class ContactSubmission
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $email,
        public string $message,
        public string $status,
        public \DateTimeImmutable $submittedAt,
    ) {}
}
