<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Persistence\Contact;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Domain\Contact\ContactEmail;
use Showoff\Core\Domain\Contact\ContactMessage;
use Showoff\Core\Domain\Contact\ContactName;
use Showoff\Core\Domain\Contact\ContactSubmission;
use Showoff\Core\Domain\Contact\ContactSubmissionEvent;
use Showoff\Core\Domain\Contact\ContactSubmissionStatus;
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
        $stored = $repository->save(new ContactSubmission(
            id: null,
            name: new ContactName('Ada Lovelace'),
            email: new ContactEmail('ada@example.com'),
            message: new ContactMessage('Persistent message body.'),
            status: ContactSubmissionStatus::Received,
            submittedAt: new \DateTimeImmutable('2026-03-02 12:00:00'),
        ));
        $eventRepository->save(new ContactSubmissionEvent(
            id: null,
            submissionId: $stored->id ?? throw new \RuntimeException('Missing stored id.'),
            name: 'stored',
            occurredAt: new \DateTimeImmutable('2026-03-02 12:00:00'),
            metadata: ['source' => 'test'],
        ));

        self::assertSame(1, $repository->countAll());
        self::assertSame('ada@example.com', $repository->latest()?->email->value);
        self::assertSame(1, $eventRepository->countForSubmission($stored->id ?? throw new \RuntimeException('Missing stored id.')));
    }
}
