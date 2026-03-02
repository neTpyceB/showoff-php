<?php

declare(strict_types=1);

namespace Showoff\Core\Console\Command;

use Showoff\Core\Persistence\Migration\PdoMigrator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:database:migrate', description: 'Run pending database migrations.')]
final class DatabaseMigrateCommand extends Command
{
    public function __construct(
        private readonly PdoMigrator $migrator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $result = $this->migrator->migrate();

        $io->definitionList(
            ['Applied migrations' => (string) count($result->appliedVersions)],
            ['Skipped migrations' => (string) count($result->skippedVersions)],
        );

        if ($result->appliedVersions !== []) {
            $io->listing($result->appliedVersions);
        } else {
            $io->writeln('No pending migrations.');
        }

        return Command::SUCCESS;
    }
}
