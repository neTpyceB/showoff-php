<?php

declare(strict_types=1);

namespace App\Module\Analytics\Api;

final readonly class ContactSubmissionProcessingView
{
    public function __construct(
        public int $processed,
        public ?string $lastEmail,
        public ?string $lastOccurredAt,
    ) {}

    /**
     * @return array{processed: int, lastEmail: string|null, lastOccurredAt: string|null}
     */
    public function toArray(): array
    {
        return [
            'processed' => $this->processed,
            'lastEmail' => $this->lastEmail,
            'lastOccurredAt' => $this->lastOccurredAt,
        ];
    }
}
