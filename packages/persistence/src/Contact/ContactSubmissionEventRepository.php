<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Contact;

interface ContactSubmissionEventRepository
{
    public function add(ContactSubmissionEvent $event): ContactSubmissionEvent;

    public function countForSubmission(int $submissionId): int;
}
