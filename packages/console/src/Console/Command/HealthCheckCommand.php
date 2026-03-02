<?php

declare(strict_types=1);

namespace Showoff\Core\Console\Command;

use Showoff\Core\Config\AppConfig;
use Showoff\Core\Health\SystemHealthChecker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:health:check', description: 'Run foundational runtime health checks.')]
final class HealthCheckCommand extends Command
{
    public function __construct(
        private readonly AppConfig $config,
        private readonly SystemHealthChecker $healthChecker,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $report = $this->healthChecker->check($this->config);

        $rows = [];

        foreach ($report->checks as $check) {
            $rows[] = [$check->name, $check->passed ? 'OK' : 'FAILED', $check->message];
        }

        $io->table(['Check', 'Status', 'Details'], $rows);

        if ($report->isHealthy()) {
            $io->success('All health checks passed.');

            return Command::SUCCESS;
        }

        $io->error('One or more health checks failed.');

        return Command::FAILURE;
    }
}
