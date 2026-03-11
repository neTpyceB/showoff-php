<?php

declare(strict_types=1);

namespace App\Showcase\Application\Processor;

final readonly class ShowcaseProcessorResult
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string $stage,
        public array $payload,
    ) {}

    /**
     * @return array{stage: string, payload: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'stage' => $this->stage,
            'payload' => $this->payload,
        ];
    }
}
