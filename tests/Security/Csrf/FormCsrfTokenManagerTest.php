<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Security\Csrf;

use App\Security\Csrf\FormCsrfTokenManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

#[CoversClass(FormCsrfTokenManager::class)]
final class FormCsrfTokenManagerTest extends TestCase
{
    public function testItGeneratesStableTokenPerFormName(): void
    {
        $manager = new FormCsrfTokenManager();
        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $first = $manager->tokenFor($request, 'login_form');
        $second = $manager->tokenFor($request, 'login_form');

        self::assertSame($first, $second);
    }

    public function testItValidatesAndRejectsInvalidTokens(): void
    {
        $manager = new FormCsrfTokenManager();
        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $token = $manager->tokenFor($request, 'contact_form');

        self::assertTrue($manager->isValid($request, 'contact_form', $token));
        self::assertFalse($manager->isValid($request, 'contact_form', 'invalid'));
    }
}
