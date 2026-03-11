<?php

declare(strict_types=1);

namespace Showoff\Core\Console\Command;

use App\Messaging\ContactSubmissionConsumer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:worker:contact-events', description: 'Consume queued contact submission events.')]
final class ContactEventsWorkerCommand extends Command
{
    public function __construct(
        private readonly ContactSubmissionConsumer $consumer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum messages to process.', '50');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limitOption = $input->getOption('limit');
        if (!is_string($limitOption) || !is_numeric($limitOption)) {
            $io->error('limit must be numeric.');

            return Command::INVALID;
        }

        $limit = (int) $limitOption;
        if ($limit < 1) {
            $io->error('limit must be >= 1');

            return Command::INVALID;
        }

        $processed = $this->consumer->consume($limit);
        $io->success(sprintf('Processed %d queued event(s).', $processed));

        return Command::SUCCESS;
    }
}
