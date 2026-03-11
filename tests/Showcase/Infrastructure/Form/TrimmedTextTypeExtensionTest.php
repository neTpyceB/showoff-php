<?php

declare(strict_types=1);

namespace Showoff\Core\Tests\Showcase\Infrastructure\Form;

use App\Showcase\Infrastructure\Form\TrimmedTextTypeExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

#[CoversClass(TrimmedTextTypeExtension::class)]
final class TrimmedTextTypeExtensionTest extends TestCase
{
    public function testItProvidesShowcaseDefaultsForTextFields(): void
    {
        $extension = new TrimmedTextTypeExtension();
        $resolver = new OptionsResolver();
        $extension->configureOptions($resolver);

        $options = $resolver->resolve();

        self::assertTrue($options['trim']);
        self::assertSame(['data-showcase-field' => '1'], $options['attr']);
    }
}
