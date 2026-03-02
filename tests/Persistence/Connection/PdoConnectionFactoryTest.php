<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Persistence\Connection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Config\DatabaseConfig;
use Showoff\Core\Persistence\Connection\PdoConnectionFactory;

#[CoversClass(PdoConnectionFactory::class)]
final class PdoConnectionFactoryTest extends TestCase
{
    public function testItCreatesSqliteConnections(): void
    {
        $connection = new PdoConnectionFactory()->create(new DatabaseConfig(
            driver: 'sqlite',
            dsn: 'sqlite::memory:',
            host: null,
            port: null,
            database: null,
            username: null,
            password: null,
            charset: null,
        ));

        self::assertSame('sqlite', $connection->getAttribute(\PDO::ATTR_DRIVER_NAME));
    }
}
