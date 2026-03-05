<?php

declare(strict_types=1);

namespace Showoff\Core\Bootstrap;

use Showoff\Core\Container\AppContainerFactory;
use Showoff\Core\Http\HttpKernel;

final readonly class HttpApplicationFactory
{
    public function __construct(
        private string $projectRoot,
    ) {}

    public function create(): HttpKernel
    {
        $kernel = new AppContainerFactory($this->projectRoot)->create()->get(HttpKernel::class);

        if (!$kernel instanceof HttpKernel) {
            throw new \RuntimeException('Invalid HTTP kernel service.');
        }

        return $kernel;
    }
}
