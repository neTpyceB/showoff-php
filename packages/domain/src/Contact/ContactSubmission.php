<?php

declare(strict_types=1);

namespace Showoff\Core\Domain\Contact;

final readonly class ContactSubmission
{
    public function __construct(
        public ?ContactSubmissionId $id,
        public ContactName $name,
        public ContactEmail $email,
        public ContactMessage $message,
        public ContactSubmissionStatus $status,
        public \DateTimeImmutable $submittedAt,
    ) {}

    public static function new(
        ContactName $name,
        ContactEmail $email,
        ContactMessage $message,
        \DateTimeImmutable $submittedAt,
    ): self {
        return new self(
            id: null,
            name: $name,
            email: $email,
            message: $message,
            status: ContactSubmissionStatus::Received,
            submittedAt: $submittedAt,
        );
    }

    public function withId(ContactSubmissionId $id): self
    {
        return new self(
            id: $id,
            name: $this->name,
            email: $this->email,
            message: $this->message,
            status: $this->status,
            submittedAt: $this->submittedAt,
        );
    }
}
