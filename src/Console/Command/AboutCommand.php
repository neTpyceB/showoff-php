<?php

declare(strict_types=1);

namespace Showoff\Core\Console\Command;

use Showoff\Core\Config\AppConfig;
use Showoff\Core\Health\RuntimeInspector;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:about', description: 'Display runtime and application metadata.')]
final class AboutCommand extends Command
{
    public function __construct(
        private readonly AppConfig $config,
        private readonly RuntimeInspector $runtimeInspector,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title($this->config->appName);
        $io->definitionList(
            ['CLI name' => $this->config->cliName],
            ['Environment' => $this->config->environment->value],
            ['Debug' => $this->config->debug ? 'enabled' : 'disabled'],
            ['Timezone' => $this->config->timezone],
            ['Log level' => $this->config->logLevel],
            ['Build commit' => $this->config->buildCommit ?? 'n/a'],
            ['PHP version' => $this->runtimeInspector->phpVersion()],
        );

        return Command::SUCCESS;
    }
}
