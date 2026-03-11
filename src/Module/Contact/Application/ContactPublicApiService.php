<?php

declare(strict_types=1);

namespace App\Module\Contact\Application;

use App\Application\Contact\ApiContactSubmissionService;
use App\Application\Contact\ContactSubmissionStatsService;
use App\Module\Contact\Api\ContactPublicApi;
use App\Module\Contact\Api\ContactSubmissionInput;
use App\Module\Contact\Api\ContactSubmissionStatsView;
use App\Module\Contact\Api\ContactSubmissionView;

final readonly class ContactPublicApiService implements ContactPublicApi
{
    public function __construct(
        private ContactSubmissionStatsService $stats,
        private ApiContactSubmissionService $submissionService,
    ) {}

    public function stats(): ContactSubmissionStatsView
    {
        $stats = $this->stats->get();
        $latest = $stats['latest'] ?? null;

        return new ContactSubmissionStatsView(
            count: $stats['count'],
            latest: is_array($latest) ? ContactSubmissionView::fromArray($this->normalizeArray($latest)) : null,
        );
    }

    public function submit(ContactSubmissionInput $input): ContactSubmissionView
    {
        $submission = $this->submissionService->submit(
            name: $input->name,
            email: $input->email,
            message: $input->message,
            source: $input->source,
        );

        if ($submission->id === null) {
            throw new \RuntimeException('Contact submission was stored without an identifier.');
        }

        return new ContactSubmissionView(
            id: $submission->id->value,
            name: $submission->name->value,
            email: $submission->email->value,
            message: $submission->message->value,
            status: $submission->status->value,
            submittedAt: $submission->submittedAt->format(\DateTimeInterface::ATOM),
        );
    }

    /**
     * @param array<mixed, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function normalizeArray(array $payload): array
    {
        $normalized = [];
        foreach ($payload as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
