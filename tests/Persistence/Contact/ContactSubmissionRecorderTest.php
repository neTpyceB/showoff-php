<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Persistence\Contact;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Persistence\Clock\Clock;
use Showoff\Core\Persistence\Connection\TransactionManager;
use Showoff\Core\Persistence\Contact\ContactSubmission;
use Showoff\Core\Persistence\Contact\ContactSubmissionEvent;
use Showoff\Core\Persistence\Contact\ContactSubmissionEventRepository;
use Showoff\Core\Persistence\Contact\ContactSubmissionRecorder;
use Showoff\Core\Persistence\Contact\ContactSubmissionRepository;
use Showoff\Core\Persistence\Contact\NewContactSubmission;

#[CoversClass(ContactSubmissionRecorder::class)]
final class ContactSubmissionRecorderTest extends TestCase
{
    public function testItStoresSubmissionAndEventInASingleTransaction(): void
    {
        $repository = new RecorderSubmissionRepository();
        $eventRepository = new RecorderEventRepository();
        $recorder = new ContactSubmissionRecorder(
            transactionManager: new RecorderTransactionManager(),
            submissionRepository: $repository,
            eventRepository: $eventRepository,
            clock: new RecorderClock(),
        );

        $stored = $recorder->record(new NewContactSubmission(
            name: 'Ada Lovelace',
            email: 'ada@example.com',
            message: 'This message is stored in the database.',
        ));

        self::assertSame(1, $stored->id);
        self::assertSame(1, $eventRepository->countForSubmission(1));
        self::assertSame(1, $repository->countAll());
    }
}

final readonly class RecorderClock implements Clock
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('2026-03-02 12:00:00');
    }
}

final class RecorderSubmissionRepository implements ContactSubmissionRepository
{
    /** @var list<ContactSubmission> */
    private array $submissions = [];

    public function add(ContactSubmission $submission): ContactSubmission
    {
        $stored = new ContactSubmission(
            id: count($this->submissions) + 1,
            name: $submission->name,
            email: $submission->email,
            message: $submission->message,
            status: $submission->status,
            submittedAt: $submission->submittedAt,
        );
        $this->submissions[] = $stored;

        return $stored;
    }

    public function countAll(): int
    {
        return count($this->submissions);
    }

    public function latest(): ?ContactSubmission
    {
        if ($this->submissions === []) {
            return null;
        }

        return $this->submissions[count($this->submissions) - 1];
    }
}

final class RecorderEventRepository implements ContactSubmissionEventRepository
{
    /** @var list<ContactSubmissionEvent> */
    private array $events = [];

    public function add(ContactSubmissionEvent $event): ContactSubmissionEvent
    {
        $stored = new ContactSubmissionEvent(
            id: count($this->events) + 1,
            submissionId: $event->submissionId,
            eventName: $event->eventName,
            occurredAt: $event->occurredAt,
            metadata: $event->metadata,
        );
        $this->events[] = $stored;

        return $stored;
    }

    public function countForSubmission(int $submissionId): int
    {
        return count(array_filter(
            $this->events,
            static fn(ContactSubmissionEvent $event): bool => $event->submissionId === $submissionId,
        ));
    }
}

final readonly class RecorderTransactionManager implements TransactionManager
{
    public function transactional(callable $operation): mixed
    {
        return $operation();
    }
}
