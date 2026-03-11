<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Performance\Idempotency;

use App\Concurrency\RequestLockManager;
use App\Infrastructure\Cache\ArrayCacheStore;
use App\Infrastructure\Lock\ArrayRequestLockManager;
use App\Performance\Idempotency\IdempotencyLockException;
use App\Performance\Idempotency\IdempotencyService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

#[CoversClass(IdempotencyService::class)]
#[CoversClass(IdempotencyLockException::class)]
final class IdempotencyServiceTest extends TestCase
{
    public function testItReplaysSuccessfulResponseForSameIdempotencyKey(): void
    {
        $service = new IdempotencyService(
            new ArrayCacheStore(),
            new ArrayRequestLockManager(),
            600,
            15,
        );

        $runs = 0;
        $first = $service->execute('rest.contact_submission.store', 'same-key', function () use (&$runs): JsonResponse {
            $runs++;

            return new JsonResponse(['data' => ['id' => 42]], JsonResponse::HTTP_CREATED);
        });
        $second = $service->execute('rest.contact_submission.store', 'same-key', function () use (&$runs): JsonResponse {
            $runs++;

            return new JsonResponse(['data' => ['id' => 77]], JsonResponse::HTTP_CREATED);
        });

        self::assertSame(1, $runs);
        self::assertSame(JsonResponse::HTTP_CREATED, $first->getStatusCode());
        self::assertSame(JsonResponse::HTTP_CREATED, $second->getStatusCode());
        self::assertSame($first->getContent(), $second->getContent());
        self::assertSame('true', $second->headers->get('X-Idempotency-Replayed'));
        self::assertSame('same-key', $second->headers->get('Idempotency-Key'));
    }

    public function testItThrowsWhenLockCannotBeAcquired(): void
    {
        $service = new IdempotencyService(
            new ArrayCacheStore(),
            new LockedRequestLockManager(),
            600,
            15,
        );

        $this->expectException(IdempotencyLockException::class);

        $service->execute(
            'rest.contact_submission.store',
            'key',
            static fn(): JsonResponse => new JsonResponse(['data' => ['id' => 1]], JsonResponse::HTTP_CREATED),
        );
    }

    public function testItDoesNotCacheFailedResponses(): void
    {
        $service = new IdempotencyService(
            new ArrayCacheStore(),
            new ArrayRequestLockManager(),
            600,
            15,
        );

        $runs = 0;
        $service->execute('graphql.mutation', 'error-key', function () use (&$runs): JsonResponse {
            $runs++;

            return new JsonResponse(['errors' => [['message' => 'invalid']]], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        });
        $service->execute('graphql.mutation', 'error-key', function () use (&$runs): JsonResponse {
            $runs++;

            return new JsonResponse(['errors' => [['message' => 'invalid']]], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        });

        self::assertSame(2, $runs);
    }
}

final class LockedRequestLockManager implements RequestLockManager
{
    public function acquire(string $key, int $ttlSeconds): bool
    {
        return false;
    }

    public function release(string $key): void {}
}
