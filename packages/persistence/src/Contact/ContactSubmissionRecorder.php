<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Contact;

use Showoff\Core\Persistence\Clock\Clock;
use Showoff\Core\Persistence\Connection\TransactionManager;

final readonly class ContactSubmissionRecorder
{
    public function __construct(
        private TransactionManager $transactionManager,
        private ContactSubmissionRepository $submissionRepository,
        private ContactSubmissionEventRepository $eventRepository,
        private Clock $clock,
    ) {}

    public function record(NewContactSubmission $submission): ContactSubmission
    {
        /** @var ContactSubmission $stored */
        $stored = $this->transactionManager->transactional(function () use ($submission): ContactSubmission {
            $timestamp = $this->clock->now();
            $stored = $this->submissionRepository->add(new ContactSubmission(
                id: null,
                name: $submission->name,
                email: $submission->email,
                message: $submission->message,
                status: 'received',
                submittedAt: $timestamp,
            ));

            if ($stored->id === null) {
                throw new \RuntimeException('Stored contact submissions must have an identifier.');
            }

            $this->eventRepository->add(new ContactSubmissionEvent(
                id: null,
                submissionId: $stored->id,
                eventName: 'stored',
                occurredAt: $timestamp,
                metadata: ['source' => 'contact_form'],
            ));

            return $stored;
        });

        return $stored;
    }
}
