<?php

declare(strict_types=1);

namespace App\Security;

use PDO;
use Showoff\Core\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

final readonly class ApiTokenService
{
    public function __construct(
        private PDO $connection,
        private AppConfig $config,
        private UserRepository $userRepository,
    ) {}

    public function issueToken(User $user, string $tokenName, \DateInterval $ttl): string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = new \DateTimeImmutable()->add($ttl)->format('Y-m-d H:i:s.u');
        $createdAt = new \DateTimeImmutable()->format('Y-m-d H:i:s.u');

        $statement = $this->connection->prepare(
            'INSERT INTO api_access_tokens (user_id, token_hash, token_name, expires_at, revoked_at, last_used_at, created_at)
             VALUES (:user_id, :token_hash, :token_name, :expires_at, NULL, NULL, :created_at)',
        );
        $statement->execute([
            'user_id' => $user->id,
            'token_hash' => $this->hashToken($token),
            'token_name' => $tokenName,
            'expires_at' => $expiresAt,
            'created_at' => $createdAt,
        ]);

        return $token;
    }

    public function userFromRequest(Request $request): ?User
    {
        $authorization = $request->headers->get('Authorization');
        if (!is_string($authorization) || !str_starts_with($authorization, 'Bearer ')) {
            return null;
        }

        $token = trim(substr($authorization, 7));
        if ($token === '') {
            return null;
        }

        return $this->userFromToken($token);
    }

    public function userFromToken(string $token): ?User
    {
        $statement = $this->connection->prepare(
            'SELECT user_id FROM api_access_tokens
             WHERE token_hash = :token_hash
               AND revoked_at IS NULL
               AND expires_at > :now
             LIMIT 1',
        );
        $statement->execute([
            'token_hash' => $this->hashToken($token),
            'now' => new \DateTimeImmutable()->format('Y-m-d H:i:s.u'),
        ]);

        $row = $statement->fetch();
        if (!is_array($row)) {
            return null;
        }

        $userId = $row['user_id'] ?? null;
        if (!is_numeric($userId)) {
            return null;
        }

        $this->touchTokenUsage($token);

        return $this->userRepository->findById((int) $userId);
    }

    private function touchTokenUsage(string $token): void
    {
        $statement = $this->connection->prepare(
            'UPDATE api_access_tokens SET last_used_at = :last_used_at WHERE token_hash = :token_hash',
        );
        $statement->execute([
            'last_used_at' => new \DateTimeImmutable()->format('Y-m-d H:i:s.u'),
            'token_hash' => $this->hashToken($token),
        ]);
    }

    private function hashToken(string $token): string
    {
        return hash_hmac('sha256', $token, $this->config->secret);
    }
}
