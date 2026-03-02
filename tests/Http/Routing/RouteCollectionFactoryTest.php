<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Http\Routing;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Http\Routing\RouteCollectionFactory;

#[CoversClass(RouteCollectionFactory::class)]
final class RouteCollectionFactoryTest extends TestCase
{
    public function testItRegistersExpectedRoutes(): void
    {
        $routes = new RouteCollectionFactory()->create();

        self::assertNotNull($routes->get('home'));
        self::assertNotNull($routes->get('contact'));
        self::assertNotNull($routes->get('preferences'));
    }
}
