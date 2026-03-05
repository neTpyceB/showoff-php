<?php

declare(strict_types=1);

namespace Showoff\Core\Domain\Contact;

final readonly class ContactSubmissionEvent
{
    /**
     * @param array<string, scalar|null> $metadata
     */
    public function __construct(
        public ?int $id,
        public ContactSubmissionId $submissionId,
        public string $name,
        public \DateTimeImmutable $occurredAt,
        public array $metadata,
    ) {}
}
