<?php

declare(strict_types=1);

namespace App\Factory;

use Showoff\Core\Config\AppConfig;
use Showoff\Core\Config\ConfigLoader;
use Showoff\Core\Config\EnvironmentReader;

final readonly class AppConfigFactory
{
    public function create(string $projectRoot): AppConfig
    {
        return new ConfigLoader($projectRoot)->load(EnvironmentReader::fromGlobals());
    }
}
