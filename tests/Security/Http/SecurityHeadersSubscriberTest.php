<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Security\Http;

use App\Security\Http\SecurityHeadersSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[CoversClass(SecurityHeadersSubscriber::class)]
final class SecurityHeadersSubscriberTest extends TestCase
{
    public function testItAddsSecurityHeaders(): void
    {
        $kernel = new class implements HttpKernelInterface {
            public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
            {
                return new Response();
            }
        };

        $request = Request::create('/home', 'GET');
        $response = new Response('ok');
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);

        $subscriber = new SecurityHeadersSubscriber();
        $subscriber->onResponse($event);

        self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        self::assertSame('DENY', $response->headers->get('X-Frame-Options'));
        self::assertNotNull($response->headers->get('Content-Security-Policy'));
    }

    public function testItSkipsSubRequests(): void
    {
        $kernel = new class implements HttpKernelInterface {
            public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
            {
                return new Response();
            }
        };

        $request = Request::create('/internal', 'GET');
        $response = new Response('ok');
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST, $response);

        $subscriber = new SecurityHeadersSubscriber();
        $subscriber->onResponse($event);

        self::assertNull($response->headers->get('X-Content-Type-Options'));
    }
}
