<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Domain\Contact;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Domain\Clock\Clock;
use Showoff\Core\Domain\Contact\ContactEmail;
use Showoff\Core\Domain\Contact\ContactMessage;
use Showoff\Core\Domain\Contact\ContactName;
use Showoff\Core\Domain\Contact\ContactSubmission;
use Showoff\Core\Domain\Contact\ContactSubmissionEvent;
use Showoff\Core\Domain\Contact\ContactSubmissionId;
use Showoff\Core\Domain\Contact\Repository\ContactSubmissionEventRepository;
use Showoff\Core\Domain\Contact\Repository\ContactSubmissionRepository;
use Showoff\Core\Domain\Contact\SubmitContactSubmission;
use Showoff\Core\Domain\Shared\TransactionBoundary;

#[CoversClass(SubmitContactSubmission::class)]
final class SubmitContactSubmissionTest extends TestCase
{
    public function testItStoresSubmissionAndEventInASingleTransaction(): void
    {
        $repository = new InMemoryDomainSubmissionRepository();
        $eventRepository = new InMemoryDomainEventRepository();
        $service = new SubmitContactSubmission(
            transactionBoundary: new InMemoryTransactionBoundary(),
            submissionRepository: $repository,
            eventRepository: $eventRepository,
            clock: new FixedDomainClock(),
        );

        $stored = $service->submit(
            name: new ContactName('Ada Lovelace'),
            email: new ContactEmail('ada@example.com'),
            message: new ContactMessage('This message is stored in the database.'),
        );

        self::assertSame(1, $stored->id?->value);
        self::assertSame(1, $eventRepository->countForSubmission(new ContactSubmissionId(1)));
        self::assertSame(1, $repository->countAll());
    }
}

final readonly class FixedDomainClock implements Clock
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('2026-03-05 12:00:00');
    }
}

final class InMemoryDomainSubmissionRepository implements ContactSubmissionRepository
{
    /** @var list<ContactSubmission> */
    private array $submissions = [];

    public function save(ContactSubmission $submission): ContactSubmission
    {
        $stored = $submission->withId(new ContactSubmissionId(count($this->submissions) + 1));
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

final class InMemoryDomainEventRepository implements ContactSubmissionEventRepository
{
    /** @var list<ContactSubmissionEvent> */
    private array $events = [];

    public function save(ContactSubmissionEvent $event): ContactSubmissionEvent
    {
        $stored = new ContactSubmissionEvent(
            id: count($this->events) + 1,
            submissionId: $event->submissionId,
            name: $event->name,
            occurredAt: $event->occurredAt,
            metadata: $event->metadata,
        );
        $this->events[] = $stored;

        return $stored;
    }

    public function countForSubmission(ContactSubmissionId $submissionId): int
    {
        return count(array_filter(
            $this->events,
            static fn(ContactSubmissionEvent $event): bool => $event->submissionId->value === $submissionId->value,
        ));
    }
}

final readonly class InMemoryTransactionBoundary implements TransactionBoundary
{
    public function transactional(callable $operation): mixed
    {
        return $operation();
    }
}
