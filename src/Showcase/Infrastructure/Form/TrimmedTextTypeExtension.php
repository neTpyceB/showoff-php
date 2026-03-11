<?php

declare(strict_types=1);

namespace App\Showcase\Infrastructure\Form;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TrimmedTextTypeExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('trim', true);
        $resolver->setDefault('attr', ['data-showcase-field' => '1']);
    }

    public static function getExtendedTypes(): iterable
    {
        return [TextType::class];
    }
}
