<?php

declare(strict_types=1);

namespace App\Application\Contact;

use App\Http\Form\ContactRequest;
use Showoff\Core\Domain\Contact\ContactEmail;
use Showoff\Core\Domain\Contact\ContactMessage;
use Showoff\Core\Domain\Contact\ContactName;
use Showoff\Core\Domain\Contact\ContactSubmissionSource;
use Showoff\Core\Domain\Contact\SubmitContactSubmission;
use Showoff\Core\Http\Contact\SubmissionSourceStrategy;
use Symfony\Component\HttpFoundation\Request;

final readonly class ContactSubmissionApplicationService
{
    public function __construct(
        private SubmitContactSubmission $submitContactSubmission,
        private SubmissionSourceStrategy $submissionSourceStrategy,
        private ContactSubmissionAsyncWorkflow $asyncWorkflow,
    ) {}

    public function submit(ContactRequest $requestData, Request $request): void
    {
        $source = $this->submissionSourceStrategy->resolve($request);
        $submission = $this->submitContactSubmission->submit(
            name: new ContactName($requestData->name),
            email: new ContactEmail($requestData->email),
            message: new ContactMessage($requestData->message),
            source: new ContactSubmissionSource($source),
        );

        $this->asyncWorkflow->afterStored($submission, $source);
    }
}
