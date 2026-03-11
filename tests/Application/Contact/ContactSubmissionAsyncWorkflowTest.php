<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Application\Contact;

use App\Application\Contact\ContactSubmissionAsyncWorkflow;
use App\Application\Contact\ContactSubmissionStatsService;
use App\Infrastructure\Cache\ArrayCacheStore;
use App\Messaging\Publisher\NullMessagePublisher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Domain\Contact\ContactEmail;
use Showoff\Core\Domain\Contact\ContactMessage;
use Showoff\Core\Domain\Contact\ContactName;
use Showoff\Core\Domain\Contact\ContactSubmission;
use Showoff\Core\Domain\Contact\ContactSubmissionId;
use Showoff\Core\Domain\Contact\ContactSubmissionStatus;
use Showoff\Core\Domain\Contact\Repository\ContactSubmissionRepository;

#[CoversClass(ContactSubmissionAsyncWorkflow::class)]
final class ContactSubmissionAsyncWorkflowTest extends TestCase
{
    public function testItInvalidatesCacheAndPublishesMessage(): void
    {
        $cache = new ArrayCacheStore();
        $publisher = new NullMessagePublisher();
        $stats = new ContactSubmissionStatsService(new AsyncWorkflowSubmissionRepository(), $cache);
        $workflow = new ContactSubmissionAsyncWorkflow($stats, $publisher);

        $stats->get();
        $workflow->afterStored($this->submission(), 'rest_api');

        self::assertNull($cache->get('api:contact_submission:stats:v1'));
        self::assertCount(1, $publisher->published());
    }

    private function submission(): ContactSubmission
    {
        return new ContactSubmission(
            id: new ContactSubmissionId(99),
            name: new ContactName('Queue User'),
            email: new ContactEmail('queue@example.com'),
            message: new ContactMessage('This payload should trigger async workflow publication.'),
            status: ContactSubmissionStatus::Received,
            submittedAt: new \DateTimeImmutable('2026-03-10T10:00:00+00:00'),
        );
    }
}

final class AsyncWorkflowSubmissionRepository implements ContactSubmissionRepository
{
    public function save(ContactSubmission $submission): ContactSubmission
    {
        return $submission;
    }

    public function countAll(): int
    {
        return 1;
    }

    public function latest(): ?ContactSubmission
    {
        return null;
    }
}
