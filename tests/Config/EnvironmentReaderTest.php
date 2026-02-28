<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Config;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Config\ConfigurationException;
use Showoff\Core\Config\EnvironmentReader;

#[CoversClass(EnvironmentReader::class)]
final class EnvironmentReaderTest extends TestCase
{
    public function testItPrefersEnvironmentValuesAndTrimsStrings(): void
    {
        $reader = new EnvironmentReader(
            server: ['APP_NAME' => ' Server Value '],
            env: ['APP_NAME' => ' Core App '],
        );

        self::assertSame('Core App', $reader->string('APP_NAME'));
    }

    public function testItParsesBooleanValues(): void
    {
        $reader = new EnvironmentReader(server: [], env: ['APP_DEBUG' => 'yes']);

        self::assertTrue($reader->bool('APP_DEBUG', false));
    }

    public function testItRejectsInvalidBooleanValues(): void
    {
        $reader = new EnvironmentReader(server: [], env: ['APP_DEBUG' => 'sometimes']);

        $this->expectException(ConfigurationException::class);
        $reader->bool('APP_DEBUG', false);
    }
}
