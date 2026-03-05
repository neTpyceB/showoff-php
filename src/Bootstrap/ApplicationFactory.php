<?php

declare(strict_types=1);

namespace Showoff\Core\Bootstrap;

use Showoff\Core\Container\AppContainerFactory;
use Symfony\Component\Console\Application;

final readonly class ApplicationFactory
{
    public function __construct(
        private string $projectRoot,
    ) {}

    public function create(): Application
    {
        $application = new AppContainerFactory($this->projectRoot)->create()->get(Application::class);

        if (!$application instanceof Application) {
            throw new \RuntimeException('Invalid console application service.');
        }

        return $application;
    }
}
