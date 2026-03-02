<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Http\Form;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Http\Form\ContactFormHandler;
use Showoff\Core\Http\Form\FormTokenManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

#[CoversClass(ContactFormHandler::class)]
final class ContactFormHandlerTest extends TestCase
{
    public function testItReturnsValidationErrorsForInvalidInput(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $session->start();
        $handler = new ContactFormHandler(new FormTokenManager());

        $result = $handler->handle(new Request([], [
            'name' => '',
            'email' => 'bad',
            'message' => 'short',
            '_token' => 'invalid',
        ]), $session);

        self::assertFalse($result->isValid);
        self::assertArrayHasKey('_token', $result->errors);
        self::assertArrayHasKey('name', $result->errors);
        self::assertArrayHasKey('email', $result->errors);
        self::assertArrayHasKey('message', $result->errors);
    }

    public function testItBuildsContactDataForValidInput(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $session->start();
        $tokenManager = new FormTokenManager();
        $token = $tokenManager->tokenFor($session, ContactFormHandler::FORM_NAME);
        $handler = new ContactFormHandler($tokenManager);

        $result = $handler->handle(new Request([], [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'message' => 'This is a valid inquiry message.',
            '_token' => $token,
        ]), $session);

        self::assertTrue($result->isValid);
        self::assertNotNull($result->data);
        self::assertSame('Ada Lovelace', $result->data->name);
        self::assertSame('ada@example.com', $result->data->email);
    }
}
