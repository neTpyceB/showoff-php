<?php

declare(strict_types=1);

namespace App\Module\Contact\Api;

final readonly class ContactSubmissionStatsView
{
    public function __construct(
        public int $count,
        public ?ContactSubmissionView $latest,
    ) {}

    /**
     * @return array{count: int, latest: array{id: int, name: string, email: string, message: string, status: string, submittedAt: string}|null}
     */
    public function toArray(): array
    {
        return [
            'count' => $this->count,
            'latest' => $this->latest?->toArray(),
        ];
    }
}
