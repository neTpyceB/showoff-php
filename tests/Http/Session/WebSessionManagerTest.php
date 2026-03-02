<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Http\Session;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Http\Session\SessionFactory;
use Showoff\Core\Http\Session\WebSessionManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

#[CoversClass(WebSessionManager::class)]
final class WebSessionManagerTest extends TestCase
{
    public function testItStartsAndPersistsSessionsIntoResponseCookies(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $manager = new WebSessionManager(new StubSessionFactory($session));
        $request = Request::create('/');

        $started = $manager->start($request);
        $response = $manager->finalize($request, new Response('ok'));

        self::assertSame($session, $started);
        self::assertTrue($request->hasSession());
        self::assertCount(0, $response->headers->getCookies());
    }
}

final readonly class StubSessionFactory implements SessionFactory
{
    public function __construct(
        private SessionInterface $session,
    ) {}

    public function create(): SessionInterface
    {
        return $this->session;
    }
}
