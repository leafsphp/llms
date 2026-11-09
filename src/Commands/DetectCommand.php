<?php

namespace Leaf\Llms\Commands;

use Leaf\Llms\Detector;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'detect', description: 'Show what llms detects about this project, without writing files')]
class DetectCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Project root (defaults to the current directory)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $project = (new Detector())->detect($input->getOption('path') ?: (string) getcwd());
        } catch (\Throwable $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return Command::FAILURE;
        }

        $rows = [
            'Name' => $project->name,
            'Framework' => $project->framework,
            'PHP' => $project->php ?: '(not set)',
            'Tests' => $project->testRunner ?? '(none)',
        ];

        foreach ($project->commands as $label => $command) {
            $rows[ucfirst($label)] = $command;
        }

        foreach ($rows as $label => $value) {
            $output->writeln(sprintf('<info>%-10s</info> %s', $label, $value));
        }

        return Command::SUCCESS;
    }
}
