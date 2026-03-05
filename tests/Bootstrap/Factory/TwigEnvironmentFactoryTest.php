<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Bootstrap\Factory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Showoff\Core\Bootstrap\Factory\TwigEnvironmentFactory;

#[CoversClass(TwigEnvironmentFactory::class)]
final class TwigEnvironmentFactoryTest extends TestCase
{
    public function testItCreatesTwigEnvironment(): void
    {
        $projectRoot = sys_get_temp_dir() . '/showoff-twig-factory-test';
        @mkdir($projectRoot . '/templates', 0o777, true);

        $factory = new TwigEnvironmentFactory();
        $environment = $factory->create($projectRoot);

        self::assertTrue($environment->isStrictVariables());
    }
}
