<?php

declare(strict_types=1);

namespace App\Module\Contact\Api;

final readonly class ContactSubmissionView
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $message,
        public string $status,
        public string $submittedAt,
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            id: is_int($payload['id'] ?? null) ? $payload['id'] : 0,
            name: is_string($payload['name'] ?? null) ? $payload['name'] : '',
            email: is_string($payload['email'] ?? null) ? $payload['email'] : '',
            message: is_string($payload['message'] ?? null) ? $payload['message'] : '',
            status: is_string($payload['status'] ?? null) ? $payload['status'] : '',
            submittedAt: is_string($payload['submittedAt'] ?? null) ? $payload['submittedAt'] : '',
        );
    }

    /**
     * @return array{id: int, name: string, email: string, message: string, status: string, submittedAt: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'message' => $this->message,
            'status' => $this->status,
            'submittedAt' => $this->submittedAt,
        ];
    }
}
