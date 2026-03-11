<?php

declare(strict_types=1);

namespace App\Showcase\Infrastructure\Form;

use App\Showcase\Application\Form\ShowcaseSettingsInput;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ShowcaseSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class)
            ->add('notes', TextType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ShowcaseSettingsInput::class,
            'csrf_protection' => false,
        ]);
    }
}
