<?php

declare(strict_types=1);

namespace App\Showcase;

use App\Showcase\DependencyInjection\Compiler\ShowcaseProcessorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class AdvancedShowcaseBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new ShowcaseProcessorCompilerPass());
    }
}
