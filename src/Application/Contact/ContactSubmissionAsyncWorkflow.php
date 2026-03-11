<?php

declare(strict_types=1);

namespace App\Application\Contact;

use App\Messaging\Message\ContactSubmissionStoredMessage;
use App\Messaging\Publisher\MessagePublisher;
use Showoff\Core\Domain\Contact\ContactSubmission;

final readonly class ContactSubmissionAsyncWorkflow
{
    public function __construct(
        private ContactSubmissionStatsService $stats,
        private MessagePublisher $publisher,
    ) {}

    public function afterStored(ContactSubmission $submission, string $source): void
    {
        $this->stats->invalidate();

        if ($submission->id === null) {
            return;
        }

        $this->publisher->publish(new ContactSubmissionStoredMessage(
            submissionId: $submission->id->value,
            email: $submission->email->value,
            source: $source,
            occurredAt: $submission->submittedAt->format(\DateTimeInterface::ATOM),
        )->toJson());
    }
}
