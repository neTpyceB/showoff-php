<?php

declare(strict_types=1);

namespace App\Application\Contact;

use Showoff\Core\Domain\Contact\ContactEmail;
use Showoff\Core\Domain\Contact\ContactMessage;
use Showoff\Core\Domain\Contact\ContactName;
use Showoff\Core\Domain\Contact\ContactSubmission;
use Showoff\Core\Domain\Contact\ContactSubmissionSource;
use Showoff\Core\Domain\Contact\SubmitContactSubmission;

final readonly class ApiContactSubmissionService
{
    public function __construct(
        private SubmitContactSubmission $submitContactSubmission,
    ) {}

    public function submit(string $name, string $email, string $message, string $source): ContactSubmission
    {
        return $this->submitContactSubmission->submit(
            name: new ContactName($name),
            email: new ContactEmail($email),
            message: new ContactMessage($message),
            source: new ContactSubmissionSource($source),
        );
    }
}
