<?php

declare(strict_types=1);

namespace Showoff\Core\Health;

use Showoff\Core\Config\AppConfig;

final readonly class SystemHealthChecker
{
    /**
     * @param list<string> $requiredExtensions
     */
    public function __construct(
        private RuntimeInspector $runtimeInspector,
        private DirectoryManager $directoryManager,
        private array $requiredExtensions = ['json', 'mbstring', 'pdo', 'pdo_mysql'],
    ) {}

    public function check(AppConfig $config): HealthReport
    {
        $checks = [];
        $phpVersion = $this->runtimeInspector->phpVersion();

        $checks[] = new HealthCheck(
            name: 'PHP runtime',
            passed: version_compare($phpVersion, '8.5.0', '>='),
            message: sprintf('Detected PHP %s.', $phpVersion),
        );

        foreach ($this->requiredExtensions as $extension) {
            $checks[] = new HealthCheck(
                name: sprintf('Extension: %s', $extension),
                passed: $this->runtimeInspector->isExtensionLoaded($extension),
                message: sprintf('Extension "%s" must be loaded.', $extension),
            );
        }

        $checks[] = new HealthCheck(
            name: 'Cache directory',
            passed: $this->directoryManager->ensureWritable($config->cacheDir),
            message: sprintf('Cache directory "%s" must exist and be writable.', $config->cacheDir),
        );

        return new HealthReport($checks);
    }
}
