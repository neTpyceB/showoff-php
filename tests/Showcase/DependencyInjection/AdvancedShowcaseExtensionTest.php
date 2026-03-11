<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Showcase\DependencyInjection;

use App\Showcase\DependencyInjection\AdvancedShowcaseExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[CoversClass(AdvancedShowcaseExtension::class)]
final class AdvancedShowcaseExtensionTest extends TestCase
{
    public function testItRegistersConfigurationParameters(): void
    {
        $extension = new AdvancedShowcaseExtension();
        $container = new ContainerBuilder();
        $extension->load([
            [
                'module_name' => 'module_x',
                'enforce_diagnostics_access' => false,
            ],
        ], $container);

        self::assertSame('module_x', $container->getParameter('advanced_showcase.module_name'));
        self::assertFalse($container->getParameter('advanced_showcase.enforce_diagnostics_access'));
    }
}
