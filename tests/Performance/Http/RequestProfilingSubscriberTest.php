<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Performance\Http;

use App\Performance\Http\RequestProfilingSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[CoversClass(RequestProfilingSubscriber::class)]
final class RequestProfilingSubscriberTest extends TestCase
{
    public function testItAddsProfilingHeadersOnMainRequest(): void
    {
        $kernel = new class implements HttpKernelInterface {
            public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
            {
                return new Response();
            }
        };

        $subscriber = new RequestProfilingSubscriber(9999);
        $request = Request::create('/api/v1/contact-submissions', 'GET');
        $requestEvent = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
        $subscriber->onRequest($requestEvent);

        usleep(1000);

        $response = new Response('ok');
        $responseEvent = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $subscriber->onResponse($responseEvent);

        self::assertNotNull($response->headers->get('X-Response-Time-Ms'));
        self::assertNotNull($response->headers->get('X-Memory-Delta-Kb'));
        self::assertNotNull($response->headers->get('X-Request-Id'));
    }

    public function testItSkipsSubRequests(): void
    {
        $kernel = new class implements HttpKernelInterface {
            public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
            {
                return new Response();
            }
        };

        $subscriber = new RequestProfilingSubscriber(1);
        $request = Request::create('/internal', 'GET');
        $requestEvent = new RequestEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);
        $subscriber->onRequest($requestEvent);

        $response = new Response('ok');
        $responseEvent = new ResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST, $response);
        $subscriber->onResponse($responseEvent);

        self::assertNull($response->headers->get('X-Response-Time-Ms'));
        self::assertNull($response->headers->get('X-Memory-Delta-Kb'));
    }
}
