<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Http\Contact;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Http\Contact\HeaderSubmissionSourceStrategy;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(HeaderSubmissionSourceStrategy::class)]
final class HeaderSubmissionSourceStrategyTest extends TestCase
{
    public function testItReturnsDefaultSourceWithoutHeader(): void
    {
        $strategy = new HeaderSubmissionSourceStrategy();

        self::assertSame('web_contact', $strategy->resolve(Request::create('/contact')));
    }

    public function testItUsesHeaderWhenValid(): void
    {
        $request = Request::create('/contact');
        $request->headers->set('X-Submission-Source', 'mobile_app');
        $strategy = new HeaderSubmissionSourceStrategy();

        self::assertSame('mobile_app', $strategy->resolve($request));
    }
}
