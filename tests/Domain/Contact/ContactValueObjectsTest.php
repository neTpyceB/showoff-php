<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Domain\Contact;

use PHPUnit\Framework\TestCase;
use Showoff\Core\Domain\Contact\ContactEmail;
use Showoff\Core\Domain\Contact\ContactMessage;
use Showoff\Core\Domain\Contact\ContactName;
use Showoff\Core\Domain\Contact\ContactSubmissionId;
use Showoff\Core\Domain\Contact\ContactSubmissionSource;

final class ContactValueObjectsTest extends TestCase
{
    public function testItValidatesContactName(): void
    {
        $name = new ContactName('Ada Lovelace');

        self::assertSame('Ada Lovelace', $name->value);
    }

    public function testItRejectsInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ContactEmail('invalid-email');
    }

    public function testItRejectsShortMessages(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ContactMessage('short');
    }

    public function testItRejectsInvalidSubmissionId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ContactSubmissionId(0);
    }

    public function testItAcceptsSubmissionSource(): void
    {
        $source = new ContactSubmissionSource('mobile_app');

        self::assertSame('mobile_app', $source->value);
    }
}
