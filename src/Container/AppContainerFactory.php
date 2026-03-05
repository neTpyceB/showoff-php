<?php

declare(strict_types=1);

namespace Showoff\Core\Container;

use PDO;
use Showoff\Core\Config\AppConfig;
use Showoff\Core\Config\ConfigLoader;
use Showoff\Core\Config\EnvironmentReader;
use Showoff\Core\Persistence\Connection\PdoConnectionFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

final readonly class AppContainerFactory
{
    public function __construct(
        private string $projectRoot,
    ) {}

    public function create(): ContainerInterface
    {
        require_once $this->projectRoot . '/config/bootstrap.php';

        $config = new ConfigLoader($this->projectRoot)->load(EnvironmentReader::fromGlobals());
        date_default_timezone_set($config->timezone);

        $container = new ContainerBuilder();
        $container->setParameter('project_root', $this->projectRoot);
        $container->register(AppConfig::class, AppConfig::class)
            ->setSynthetic(true)
            ->setPublic(true);
        $container->register(PDO::class, PDO::class)
            ->setSynthetic(true)
            ->setPublic(true);

        /** @var callable(ContainerBuilder): void $registrar */
        $registrar = require $this->projectRoot . '/config/services.php';
        $registrar($container);
        $container->compile();
        $container->set(AppConfig::class, $config);
        $container->set(PDO::class, new PdoConnectionFactory()->create($config->database));

        return $container;
    }
}
