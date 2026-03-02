<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Config;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Config\ConfigRedactor;

#[CoversClass(ConfigRedactor::class)]
final class ConfigRedactorTest extends TestCase
{
    public function testItRedactsSensitiveConfigurationValues(): void
    {
        $redacted = new ConfigRedactor()->redact([
            'app_name' => 'Core App',
            'secret' => 'super-secret-value',
            'api_token' => 'abc123',
            'debug' => true,
            'database' => [
                'username' => 'showoff',
                'password' => 'super-secret-password',
            ],
        ]);

        self::assertSame('Core App', $redacted['app_name']);
        self::assertSame('[REDACTED]', $redacted['secret']);
        self::assertSame('[REDACTED]', $redacted['api_token']);
        self::assertTrue($redacted['debug']);
        self::assertIsArray($redacted['database']);
        self::assertSame('[REDACTED]', $redacted['database']['password']);
    }
}
