<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Contact;

use JsonException;
use PDO;
use Showoff\Core\Domain\Contact\ContactSubmissionEvent;
use Showoff\Core\Domain\Contact\ContactSubmissionId;
use Showoff\Core\Domain\Contact\Repository\ContactSubmissionEventRepository;

final readonly class PdoContactSubmissionEventRepository implements ContactSubmissionEventRepository
{
    public function __construct(
        private PDO $connection,
    ) {}

    public function save(ContactSubmissionEvent $event): ContactSubmissionEvent
    {
        $statement = $this->connection->prepare(
            'INSERT INTO contact_submission_events (submission_id, event_name, occurred_at, metadata_json) VALUES (:submission_id, :event_name, :occurred_at, :metadata_json)',
        );

        try {
            $metadata = json_encode($event->metadata, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new \RuntimeException('Unable to encode contact submission metadata.', 0, $exception);
        }

        $statement->execute([
            'submission_id' => $event->submissionId->value,
            'event_name' => $event->name,
            'occurred_at' => $event->occurredAt->format('Y-m-d H:i:s.u'),
            'metadata_json' => $metadata,
        ]);
        $id = $this->connection->lastInsertId();

        return new ContactSubmissionEvent(
            id: is_numeric($id) ? (int) $id : throw new \RuntimeException('Invalid inserted contact submission event id.'),
            submissionId: $event->submissionId,
            name: $event->name,
            occurredAt: $event->occurredAt,
            metadata: $event->metadata,
        );
    }

    public function countForSubmission(ContactSubmissionId $submissionId): int
    {
        $statement = $this->connection->prepare(
            'SELECT COUNT(*) FROM contact_submission_events WHERE submission_id = :submission_id',
        );
        $statement->execute(['submission_id' => $submissionId->value]);

        return (int) $statement->fetchColumn();
    }
}
