<?php

declare(strict_types=1);

namespace Showoff\Core\Persistence\Contact;

use PDO;

final readonly class PdoContactSubmissionRepository implements ContactSubmissionRepository
{
    public function __construct(
        private PDO $connection,
    ) {}

    public function add(ContactSubmission $submission): ContactSubmission
    {
        $statement = $this->connection->prepare(
            'INSERT INTO contact_submissions (name, email, message, status, submitted_at) VALUES (:name, :email, :message, :status, :submitted_at)',
        );
        $statement->execute([
            'name' => $submission->name,
            'email' => $submission->email,
            'message' => $submission->message,
            'status' => $submission->status,
            'submitted_at' => $submission->submittedAt->format('Y-m-d H:i:s.u'),
        ]);
        $id = $this->connection->lastInsertId();

        return new ContactSubmission(
            id: is_numeric($id) ? (int) $id : throw new \RuntimeException('Invalid inserted contact submission id.'),
            name: $submission->name,
            email: $submission->email,
            message: $submission->message,
            status: $submission->status,
            submittedAt: $submission->submittedAt,
        );
    }

    public function countAll(): int
    {
        $result = $this->connection->query('SELECT COUNT(*) FROM contact_submissions');
        if ($result === false) {
            throw new \RuntimeException('Unable to count contact submissions.');
        }

        return (int) $result->fetchColumn();
    }

    public function latest(): ?ContactSubmission
    {
        $statement = $this->connection->query(
            'SELECT id, name, email, message, status, submitted_at FROM contact_submissions ORDER BY id DESC LIMIT 1',
        );
        if ($statement === false) {
            throw new \RuntimeException('Unable to query the latest contact submission.');
        }
        $row = $statement->fetch();

        if (!is_array($row)) {
            return null;
        }

        $id = $row['id'];
        $name = $row['name'];
        $email = $row['email'];
        $message = $row['message'];
        $status = $row['status'];
        $submittedAt = $row['submitted_at'];

        return new ContactSubmission(
            id: is_int($id)
                ? $id
                : (is_numeric($id) ? (int) $id : throw new \RuntimeException('Invalid contact submission id.')),
            name: is_string($name) ? $name : throw new \RuntimeException('Invalid contact submission name.'),
            email: is_string($email) ? $email : throw new \RuntimeException('Invalid contact submission email.'),
            message: is_string($message) ? $message : throw new \RuntimeException('Invalid contact submission message.'),
            status: is_string($status) ? $status : throw new \RuntimeException('Invalid contact submission status.'),
            submittedAt: new \DateTimeImmutable(
                is_string($submittedAt) ? $submittedAt : throw new \RuntimeException('Invalid contact submission timestamp.'),
            ),
        );
    }
}
