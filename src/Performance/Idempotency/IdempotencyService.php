<?php

declare(strict_types=1);

namespace App\Performance\Idempotency;

use App\Cache\CacheStore;
use App\Concurrency\RequestLockManager;
use Symfony\Component\HttpFoundation\JsonResponse;

final readonly class IdempotencyService
{
    public function __construct(
        private CacheStore $cache,
        private RequestLockManager $locks,
        private int $ttlSeconds,
        private int $lockTtlSeconds,
    ) {}

    /**
     * @param callable(): JsonResponse $operation
     */
    public function execute(string $scope, string $idempotencyKey, callable $operation): JsonResponse
    {
        $cacheKey = $this->cacheKey($scope, $idempotencyKey);
        $cached = $this->cachedResponse($cacheKey);
        if ($cached instanceof JsonResponse) {
            return $this->markReplayed($cached, $idempotencyKey);
        }

        $lockKey = $this->lockKey($scope, $idempotencyKey);
        if (!$this->locks->acquire($lockKey, $this->lockTtlSeconds)) {
            throw new IdempotencyLockException('Another request is already processing this idempotency key.');
        }

        try {
            $cached = $this->cachedResponse($cacheKey);
            if ($cached instanceof JsonResponse) {
                return $this->markReplayed($cached, $idempotencyKey);
            }

            $response = $operation();
            $response->headers->set('Idempotency-Key', $idempotencyKey);
            if ($response->isSuccessful()) {
                $this->persist($cacheKey, $response);
            }

            return $response;
        } finally {
            $this->locks->release($lockKey);
        }
    }

    private function cacheKey(string $scope, string $idempotencyKey): string
    {
        return 'idempotency:response:' . $this->fingerprint($scope, $idempotencyKey);
    }

    private function lockKey(string $scope, string $idempotencyKey): string
    {
        return 'idempotency:lock:' . $this->fingerprint($scope, $idempotencyKey);
    }

    private function fingerprint(string $scope, string $idempotencyKey): string
    {
        return hash('sha256', strtolower(trim($scope)) . ':' . trim($idempotencyKey));
    }

    private function cachedResponse(string $cacheKey): ?JsonResponse
    {
        $payload = $this->cache->get($cacheKey);
        if (!is_string($payload)) {
            return null;
        }

        try {
            $decoded = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (!is_array($decoded)) {
            return null;
        }

        $status = $decoded['status'] ?? null;
        $body = $decoded['body'] ?? null;
        if (!is_int($status) || !is_string($body)) {
            return null;
        }

        $response = new JsonResponse($body, $status, [], true);
        $headers = $decoded['headers'] ?? [];
        if (is_array($headers)) {
            foreach ($headers as $name => $values) {
                if (!is_string($name) || !is_array($values)) {
                    continue;
                }

                $normalized = [];
                foreach ($values as $value) {
                    if (is_string($value)) {
                        $normalized[] = $value;
                    }
                }

                if ($normalized !== []) {
                    $response->headers->set($name, $normalized);
                }
            }
        }

        return $response;
    }

    private function persist(string $cacheKey, JsonResponse $response): void
    {
        $content = $response->getContent();
        if (!is_string($content)) {
            return;
        }

        $payload = json_encode([
            'status' => $response->getStatusCode(),
            'body' => $content,
            'headers' => $response->headers->all(),
        ], JSON_THROW_ON_ERROR);

        $this->cache->set($cacheKey, $payload, $this->ttlSeconds);
    }

    private function markReplayed(JsonResponse $response, string $idempotencyKey): JsonResponse
    {
        $response->headers->set('X-Idempotency-Replayed', 'true');
        $response->headers->set('Idempotency-Key', $idempotencyKey);

        return $response;
    }
}
