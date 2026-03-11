<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Performance\Http;

use App\Performance\Http\JsonHttpCacheService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(JsonHttpCacheService::class)]
final class JsonHttpCacheServiceTest extends TestCase
{
    public function testItGeneratesEtagAndReturnsNotModifiedWhenTagMatches(): void
    {
        $service = new JsonHttpCacheService(30);
        $payload = ['data' => ['count' => 5]];

        $first = $service->createCacheableResponse(Request::create('/api/v1/contact-submissions', 'GET'), $payload);
        $etag = $first->headers->get('ETag');

        self::assertNotNull($etag);
        self::assertSame(200, $first->getStatusCode());
        self::assertStringContainsString('private', (string) $first->headers->get('Cache-Control'));
        self::assertStringContainsString('max-age=30', (string) $first->headers->get('Cache-Control'));

        $secondRequest = Request::create('/api/v1/contact-submissions', 'GET');
        $secondRequest->headers->set('If-None-Match', $etag);
        $second = $service->createCacheableResponse($secondRequest, $payload);

        self::assertSame(304, $second->getStatusCode());
        self::assertFalse($second->headers->has('Content-Type'));
    }
}
