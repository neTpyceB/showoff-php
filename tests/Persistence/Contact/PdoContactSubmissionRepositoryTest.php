<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Persistence\Contact;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Persistence\Contact\ContactSubmission;
use Showoff\Core\Persistence\Contact\ContactSubmissionEvent;
use Showoff\Core\Persistence\Contact\PdoContactSubmissionEventRepository;
use Showoff\Core\Persistence\Contact\PdoContactSubmissionRepository;
use Showoff\Core\Persistence\Migration\PdoMigrator;
use Showoff\Core\Persistence\Migration\Version202603020001;

#[CoversClass(PdoContactSubmissionRepository::class)]
#[CoversClass(PdoContactSubmissionEventRepository::class)]
final class PdoContactSubmissionRepositoryTest extends TestCase
{
    public function testItPersistsAndReadsContactSubmissions(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA foreign_keys = ON');
        new PdoMigrator($pdo, [new Version202603020001()])->migrate();

        $repository = new PdoContactSubmissionRepository($pdo);
        $eventRepository = new PdoContactSubmissionEventRepository($pdo);
        $stored = $repository->add(new ContactSubmission(
            id: null,
            name: 'Ada Lovelace',
            email: 'ada@example.com',
            message: 'Persistent message body.',
            status: 'received',
            submittedAt: new \DateTimeImmutable('2026-03-02 12:00:00'),
        ));
        $eventRepository->add(new ContactSubmissionEvent(
            id: null,
            submissionId: $stored->id ?? 0,
            eventName: 'stored',
            occurredAt: new \DateTimeImmutable('2026-03-02 12:00:00'),
            metadata: ['source' => 'test'],
        ));

        self::assertSame(1, $repository->countAll());
        self::assertSame('ada@example.com', $repository->latest()?->email);
        self::assertSame(1, $eventRepository->countForSubmission($stored->id ?? 0));
    }
}
