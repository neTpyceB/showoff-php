<?php

declare(strict_types=1);

namespace Showoff\Core\Console\Command;

use JsonException;
use Showoff\Core\Config\AppConfig;
use Showoff\Core\Config\ConfigRedactor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:config:dump', description: 'Dump the effective application configuration.')]
final class ConfigDumpCommand extends Command
{
    public function __construct(
        private readonly AppConfig $config,
        private readonly ConfigRedactor $configRedactor,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $payload = json_encode(
                $this->configRedactor->redact($this->config->toArray()),
                JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->writeln($payload);

        return Command::SUCCESS;
    }
}
