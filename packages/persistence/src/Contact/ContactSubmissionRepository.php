<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Contact;

interface ContactSubmissionRepository
{
    public function add(ContactSubmission $submission): ContactSubmission;

    public function countAll(): int;

    public function latest(): ?ContactSubmission;
}
