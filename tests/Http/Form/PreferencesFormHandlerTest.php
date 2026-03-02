<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Http\Form;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Http\Form\FormTokenManager;
use Showoff\Core\Http\Form\PreferencesFormHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

#[CoversClass(PreferencesFormHandler::class)]
final class PreferencesFormHandlerTest extends TestCase
{
    public function testItRejectsUnsupportedThemes(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $session->start();
        $handler = new PreferencesFormHandler(new FormTokenManager());

        $result = $handler->handle(new Request([], [
            'theme' => 'violet',
            '_token' => 'invalid',
        ]), $session);

        self::assertFalse($result->isValid);
        self::assertArrayHasKey('_token', $result->errors);
        self::assertArrayHasKey('theme', $result->errors);
    }

    public function testItAcceptsSupportedThemes(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $session->start();
        $tokenManager = new FormTokenManager();
        $token = $tokenManager->tokenFor($session, PreferencesFormHandler::FORM_NAME);
        $handler = new PreferencesFormHandler($tokenManager);

        $result = $handler->handle(new Request([], [
            'theme' => 'dark',
            '_token' => $token,
        ]), $session);

        self::assertTrue($result->isValid);
        self::assertSame('dark', $result->theme);
    }
}
