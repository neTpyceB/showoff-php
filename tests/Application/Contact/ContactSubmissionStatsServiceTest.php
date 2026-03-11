<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Application\Contact;

use App\Application\Contact\ContactSubmissionStatsService;
use App\Infrastructure\Cache\ArrayCacheStore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Domain\Contact\ContactEmail;
use Showoff\Core\Domain\Contact\ContactMessage;
use Showoff\Core\Domain\Contact\ContactName;
use Showoff\Core\Domain\Contact\ContactSubmission;
use Showoff\Core\Domain\Contact\ContactSubmissionId;
use Showoff\Core\Domain\Contact\ContactSubmissionStatus;
use Showoff\Core\Domain\Contact\Repository\ContactSubmissionRepository;

#[CoversClass(ContactSubmissionStatsService::class)]
final class ContactSubmissionStatsServiceTest extends TestCase
{
    public function testItReturnsCachedStatsAfterFirstRead(): void
    {
        $repository = new InMemorySubmissionRepository();
        $cache = new ArrayCacheStore();
        $service = new ContactSubmissionStatsService($repository, $cache);

        $first = $service->get();
        $second = $service->get();

        self::assertSame($first, $second);
        self::assertSame(1, $repository->countAllCalls);
    }

    public function testInvalidateForcesFreshRead(): void
    {
        $repository = new InMemorySubmissionRepository();
        $cache = new ArrayCacheStore();
        $service = new ContactSubmissionStatsService($repository, $cache);

        $service->get();
        $service->invalidate();
        $service->get();

        self::assertSame(2, $repository->countAllCalls);
    }
}

final class InMemorySubmissionRepository implements ContactSubmissionRepository
{
    public int $countAllCalls = 0;
    public bool $hasLatest = true;

    public function save(ContactSubmission $submission): ContactSubmission
    {
        return $submission;
    }

    public function countAll(): int
    {
        $this->countAllCalls++;

        return 1;
    }

    public function latest(): ?ContactSubmission
    {
        if ($this->hasLatest === false) {
            return null;
        }

        return new ContactSubmission(
            id: new ContactSubmissionId(1),
            name: new ContactName('Test User'),
            email: new ContactEmail('test@example.com'),
            message: new ContactMessage('This is a message for cached stats testing.'),
            status: ContactSubmissionStatus::Received,
            submittedAt: new \DateTimeImmutable('2026-03-10 10:00:00'),
        );
    }
}
