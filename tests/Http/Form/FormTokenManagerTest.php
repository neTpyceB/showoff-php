<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Http\Form;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Http\Form\FormTokenManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

#[CoversClass(FormTokenManager::class)]
final class FormTokenManagerTest extends TestCase
{
    public function testItReturnsStableTokensPerForm(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $session->start();
        $manager = new FormTokenManager();

        $first = $manager->tokenFor($session, 'contact_form');
        $second = $manager->tokenFor($session, 'contact_form');

        self::assertSame($first, $second);
        self::assertTrue($manager->isValid($session, 'contact_form', $first));
    }
}
