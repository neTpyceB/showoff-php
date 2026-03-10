<?php

declare(strict_types=1);

namespace App\Security;

use App\Security\Crypto\Encryptor;
use PDO;

final readonly class UserRepository
{
    public function __construct(
        private PDO $connection,
        private Encryptor $encryptor,
    ) {}

    public function findByEmail(string $email): ?User
    {
        $statement = $this->connection->prepare(
            'SELECT id, email_encrypted, password_hash, role FROM app_users WHERE email_hash = :email_hash LIMIT 1',
        );
        $statement->execute([
            'email_hash' => $this->emailHash($email),
        ]);

        $row = $statement->fetch();
        if (!is_array($row)) {
            return null;
        }

        return $this->hydrateUser($row);
    }

    public function findById(int $id): ?User
    {
        $statement = $this->connection->prepare(
            'SELECT id, email_encrypted, password_hash, role FROM app_users WHERE id = :id LIMIT 1',
        );
        $statement->execute(['id' => $id]);

        $row = $statement->fetch();
        if (!is_array($row)) {
            return null;
        }

        return $this->hydrateUser($row);
    }

    public function create(string $email, string $passwordHash, Role $role): User
    {
        $statement = $this->connection->prepare(
            'INSERT INTO app_users (email_hash, email_encrypted, password_hash, role, created_at, updated_at)
             VALUES (:email_hash, :email_encrypted, :password_hash, :role, :created_at, :updated_at)',
        );
        $timestamp = new \DateTimeImmutable()->format('Y-m-d H:i:s.u');
        $statement->execute([
            'email_hash' => $this->emailHash($email),
            'email_encrypted' => $this->encryptor->encrypt($this->normalizeEmail($email)),
            'password_hash' => $passwordHash,
            'role' => $role->value,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $id = $this->connection->lastInsertId();
        if (!is_numeric($id)) {
            throw new \RuntimeException('Unable to fetch inserted user id.');
        }

        return new User((int) $id, $this->normalizeEmail($email), $passwordHash, $role);
    }

    private function emailHash(string $email): string
    {
        return hash('sha256', $this->normalizeEmail($email));
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    /**
     * @param array<mixed, mixed> $row
     */
    private function hydrateUser(array $row): User
    {
        $id = $row['id'] ?? null;
        $emailEncrypted = $row['email_encrypted'] ?? null;
        $passwordHash = $row['password_hash'] ?? null;
        $role = $row['role'] ?? null;

        if (!is_numeric($id) || !is_string($emailEncrypted) || !is_string($passwordHash) || !is_string($role)) {
            throw new \RuntimeException('Invalid user row payload.');
        }

        return new User(
            id: (int) $id,
            email: $this->encryptor->decrypt($emailEncrypted),
            passwordHash: $passwordHash,
            role: Role::from($role),
        );
    }
}
