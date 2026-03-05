<?php

declare(strict_types=1);

namespace Showoff\Core\Bootstrap\Factory;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final readonly class TwigEnvironmentFactory
{
    public function create(string $projectRoot): Environment
    {
        return new Environment(
            new FilesystemLoader($projectRoot . '/templates'),
            [
                'cache' => false,
                'strict_variables' => true,
            ],
        );
    }
}
