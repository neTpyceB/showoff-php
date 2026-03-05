<?php

declare(strict_types=1);

namespace Showoff\Core\Domain\Contact\Repository;

use Showoff\Core\Domain\Contact\ContactSubmissionEvent;
use Showoff\Core\Domain\Contact\ContactSubmissionId;

interface ContactSubmissionEventRepository
{
    public function save(ContactSubmissionEvent $event): ContactSubmissionEvent;

    public function countForSubmission(ContactSubmissionId $submissionId): int;
}
