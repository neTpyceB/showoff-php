<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Observability\Metrics;

use App\Infrastructure\Cache\ArrayCacheStore;
use App\Observability\Metrics\RequestMetricsStore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RequestMetricsStore::class)]
final class RequestMetricsStoreTest extends TestCase
{
    public function testItAggregatesRequestCounters(): void
    {
        $store = new RequestMetricsStore(new ArrayCacheStore(), 100);

        $store->record(200, 10.0);
        $store->record(404, 20.0);
        $store->record(500, 150.0);

        $snapshot = $store->snapshot();

        self::assertSame(3, $snapshot['requestsTotal']);
        self::assertSame(1, $snapshot['clientErrorsTotal']);
        self::assertSame(1, $snapshot['serverErrorsTotal']);
        self::assertSame(1, $snapshot['slowRequestsTotal']);
        self::assertStringContainsString('app_http_requests_total 3', $store->toPrometheus());
    }
}
