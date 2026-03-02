<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Contact;

final readonly class ContactSubmissionEvent
{
    /**
     * @param array<string, scalar|null> $metadata
     */
    public function __construct(
        public ?int $id,
        public int $submissionId,
        public string $eventName,
        public \DateTimeImmutable $occurredAt,
        public array $metadata,
    ) {}
}
