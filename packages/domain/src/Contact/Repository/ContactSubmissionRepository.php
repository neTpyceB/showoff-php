<?php

declare(strict_types=1);

namespace Showoff\Core\Domain\Contact\Repository;

use Showoff\Core\Domain\Contact\ContactSubmission;

interface ContactSubmissionRepository
{
    public function save(ContactSubmission $submission): ContactSubmission;

    public function countAll(): int;

    public function latest(): ?ContactSubmission;
}
