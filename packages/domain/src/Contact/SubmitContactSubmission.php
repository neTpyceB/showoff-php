<?php

declare(strict_types=1);

namespace Showoff\Core\Domain\Contact;

use Showoff\Core\Domain\Clock\Clock;
use Showoff\Core\Domain\Contact\Exception\ContactSubmissionNotPersisted;
use Showoff\Core\Domain\Contact\Repository\ContactSubmissionEventRepository;
use Showoff\Core\Domain\Contact\Repository\ContactSubmissionRepository;
use Showoff\Core\Domain\Shared\TransactionBoundary;

final readonly class SubmitContactSubmission
{
    public function __construct(
        private TransactionBoundary $transactionBoundary,
        private ContactSubmissionRepository $submissionRepository,
        private ContactSubmissionEventRepository $eventRepository,
        private Clock $clock,
    ) {}

    public function submit(
        ContactName $name,
        ContactEmail $email,
        ContactMessage $message,
        ?ContactSubmissionSource $source = null,
    ): ContactSubmission {
        $source ??= new ContactSubmissionSource('contact_form');

        /** @var ContactSubmission $stored */
        $stored = $this->transactionBoundary->transactional(function () use ($name, $email, $message, $source): ContactSubmission {
            $timestamp = $this->clock->now();
            $stored = $this->submissionRepository->save(ContactSubmission::new(
                name: $name,
                email: $email,
                message: $message,
                submittedAt: $timestamp,
            ));

            if ($stored->id === null) {
                throw new ContactSubmissionNotPersisted('Stored contact submissions must have an id.');
            }

            $this->eventRepository->save(new ContactSubmissionEvent(
                id: null,
                submissionId: $stored->id,
                name: 'stored',
                occurredAt: $timestamp,
                metadata: ['source' => $source->value],
            ));

            return $stored;
        });

        return $stored;
    }
}
