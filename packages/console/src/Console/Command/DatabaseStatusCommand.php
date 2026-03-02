<?php

declare(strict_types=1);

namespace Showoff\Core\Console\Command;

use Showoff\Core\Persistence\Migration\PdoMigrator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:database:status', description: 'Display the database migration status.')]
final class DatabaseStatusCommand extends Command
{
    public function __construct(
        private readonly PdoMigrator $migrator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $status = $this->migrator->status();

        $io->definitionList(
            ['Applied migrations' => (string) count($status->appliedVersions)],
            ['Pending migrations' => (string) count($status->pendingVersions)],
        );

        if ($status->pendingVersions !== []) {
            $io->listing($status->pendingVersions);
        }

        return Command::SUCCESS;
    }
}
