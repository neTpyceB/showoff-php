<?php

declare(strict_types=1);

namespace App\Factory;

use PDO;
use Showoff\Core\Config\AppConfig;
use Showoff\Core\Persistence\Connection\PdoConnectionFactory;

final readonly class PdoFactory
{
    public function create(AppConfig $config): PDO
    {
        return new PdoConnectionFactory()->create($config->database);
    }
}
