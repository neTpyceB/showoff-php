<?php

declare(strict_types=1);

namespace App\Application\Contact;

use App\Cache\CacheStore;
use Showoff\Core\Domain\Contact\ContactSubmission;
use Showoff\Core\Domain\Contact\Repository\ContactSubmissionRepository;

final readonly class ContactSubmissionStatsService
{
    private const CACHE_KEY = 'api:contact_submission:stats:v1';

    public function __construct(
        private ContactSubmissionRepository $submissions,
        private CacheStore $cache,
    ) {}

    /**
     * @return array{count: int, latest: array{id: int, name: string, email: string, message: string, status: string, submittedAt: string}|null}
     */
    public function get(): array
    {
        $cached = $this->cache->get(self::CACHE_KEY);
        if (is_string($cached)) {
            $decoded = json_decode($cached, true);
            if (is_array($decoded)) {
                $count = $decoded['count'] ?? null;
                $latest = $decoded['latest'] ?? null;
                if (is_int($count) && (is_array($latest) || $latest === null)) {
                    return [
                        'count' => $count,
                        'latest' => is_array($latest) ? $this->normalizeArray($latest) : null,
                    ];
                }
            }
        }

        $stats = [
            'count' => $this->submissions->countAll(),
            'latest' => $this->normalizeSubmission($this->submissions->latest()),
        ];

        $this->cache->set(
            self::CACHE_KEY,
            json_encode($stats, JSON_THROW_ON_ERROR),
            60,
        );

        return $stats;
    }

    public function invalidate(): void
    {
        $this->cache->delete(self::CACHE_KEY);
    }

    /**
     * @param array<mixed, mixed> $latest
     *
     * @return array{id: int, name: string, email: string, message: string, status: string, submittedAt: string}
     */
    private function normalizeArray(array $latest): array
    {
        return [
            'id' => is_int($latest['id'] ?? null) ? $latest['id'] : 0,
            'name' => is_string($latest['name'] ?? null) ? $latest['name'] : '',
            'email' => is_string($latest['email'] ?? null) ? $latest['email'] : '',
            'message' => is_string($latest['message'] ?? null) ? $latest['message'] : '',
            'status' => is_string($latest['status'] ?? null) ? $latest['status'] : '',
            'submittedAt' => is_string($latest['submittedAt'] ?? null) ? $latest['submittedAt'] : '',
        ];
    }

    /**
     * @return array{id: int, name: string, email: string, message: string, status: string, submittedAt: string}|null
     */
    private function normalizeSubmission(?ContactSubmission $submission): ?array
    {
        if ($submission === null || $submission->id === null) {
            return null;
        }

        return [
            'id' => $submission->id->value,
            'name' => $submission->name->value,
            'email' => $submission->email->value,
            'message' => $submission->message->value,
            'status' => $submission->status->value,
            'submittedAt' => $submission->submittedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
