<?php

declare(strict_types=1);

namespace App\Showcase\Infrastructure\Console;

use App\Showcase\Application\Processor\ShowcaseExecutionContext;
use App\Showcase\Application\Processor\ShowcasePipelineRunner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:showcase:pipeline', description: 'Run advanced Symfony showcase processing pipeline.')]
final class RunShowcasePipelineCommand extends Command
{
    public function __construct(
        private readonly ShowcasePipelineRunner $runner,
        private readonly string $moduleName,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $results = $this->runner->run(new ShowcaseExecutionContext(
            moduleName: $this->moduleName,
            triggeredBy: 'console',
        ));

        $output->writeln(sprintf('module=%s processors=%d', $this->moduleName, count($results)));
        foreach ($results as $result) {
            $output->writeln(sprintf('[%s] %s', $result->stage, json_encode($result->payload, JSON_THROW_ON_ERROR)));
        }

        return Command::SUCCESS;
    }
}
