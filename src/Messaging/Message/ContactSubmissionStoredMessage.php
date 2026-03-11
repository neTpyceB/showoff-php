<?php

declare(strict_types=1);

namespace App\Messaging\Message;

final readonly class ContactSubmissionStoredMessage
{
    public function __construct(
        public int $submissionId,
        public string $email,
        public string $source,
        public string $occurredAt,
    ) {}

    public function toJson(): string
    {
        return json_encode([
            'submissionId' => $this->submissionId,
            'email' => $this->email,
            'source' => $this->source,
            'occurredAt' => $this->occurredAt,
        ], JSON_THROW_ON_ERROR);
    }

    public static function fromJson(string $json): self
    {
        $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid message payload.');
        }

        $submissionId = $decoded['submissionId'] ?? null;
        $email = $decoded['email'] ?? null;
        $source = $decoded['source'] ?? null;
        $occurredAt = $decoded['occurredAt'] ?? null;
        if (!is_int($submissionId) || !is_string($email) || !is_string($source) || !is_string($occurredAt)) {
            throw new \RuntimeException('Invalid message fields.');
        }

        return new self($submissionId, $email, $source, $occurredAt);
    }
}
