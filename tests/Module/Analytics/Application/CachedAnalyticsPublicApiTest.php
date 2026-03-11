<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Module\Analytics\Application;

use App\Infrastructure\Cache\ArrayCacheStore;
use App\Module\Analytics\Application\CachedAnalyticsPublicApi;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CachedAnalyticsPublicApi::class)]
final class CachedAnalyticsPublicApiTest extends TestCase
{
    public function testItReturnsDefaultProjectionWhenNoDataIsAvailable(): void
    {
        $cache = new ArrayCacheStore();
        $api = new CachedAnalyticsPublicApi($cache);

        $projection = $api->contactSubmissionProcessing();

        self::assertSame(0, $projection->processed);
        self::assertNull($projection->lastEmail);
        self::assertNull($projection->lastOccurredAt);
    }

    public function testItReturnsNormalizedProjectionFromCacheValues(): void
    {
        $cache = new ArrayCacheStore();
        $cache->set('analytics:contact_submissions:processed', '9', 3600);
        $cache->set('analytics:contact_submissions:last_email', 'ops@example.com', 3600);
        $cache->set('analytics:contact_submissions:last_occurred_at', '2026-03-11T10:00:00+00:00', 3600);
        $api = new CachedAnalyticsPublicApi($cache);

        $projection = $api->contactSubmissionProcessing();

        self::assertSame(9, $projection->processed);
        self::assertSame('ops@example.com', $projection->lastEmail);
        self::assertSame('2026-03-11T10:00:00+00:00', $projection->lastOccurredAt);
    }
}
