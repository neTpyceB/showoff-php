<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Controller\Operations;

use App\Controller\Operations\MetricsController;
use App\Infrastructure\Cache\ArrayCacheStore;
use App\Observability\Metrics\RequestMetricsStore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(MetricsController::class)]
final class MetricsControllerTest extends TestCase
{
    public function testItRequiresTokenWhenConfigured(): void
    {
        $store = new RequestMetricsStore(new ArrayCacheStore(), 100);
        $controller = new MetricsController($store, 'secret-token');

        $response = $controller->__invoke(Request::create('/metrics', 'GET'));

        self::assertSame(403, $response->getStatusCode());
    }

    public function testItReturnsPrometheusPayload(): void
    {
        $store = new RequestMetricsStore(new ArrayCacheStore(), 100);
        $store->record(200, 20.0);
        $controller = new MetricsController($store, '');

        $response = $controller->__invoke(Request::create('/metrics', 'GET'));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('app_http_requests_total', (string) $response->getContent());
    }
}
