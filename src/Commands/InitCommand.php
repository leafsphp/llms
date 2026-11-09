<?php

namespace Leaf\Llms\Commands;

use Leaf\Llms\Detector;
use Leaf\Llms\Generator;
use Leaf\Llms\Sections;
use Leaf\Llms\Writer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'init', description: 'Write or refresh llms.txt, AGENTS.md, CLAUDE.md, Cursor rules and agent skills')]
class InitCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('framework', 'f', InputOption::VALUE_REQUIRED, 'Skip detection: ' . implode(', ', Detector::FRAMEWORKS))
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Project root (defaults to the current directory)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would change without writing anything');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $root = $input->getOption('path') ?: (string) getcwd();
        $dryRun = (bool) $input->getOption('dry-run');

        try {
            $project = (new Detector())->detect($root, $input->getOption('framework') ?: null);
        } catch (\Throwable $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return Command::FAILURE;
        }

        $generator = new Generator($project);
        $output->writeln("Detected <info>{$generator->framework()->label()}</info> in {$project->name}");

        $results = (new Writer($project->root))->write($generator->files(), $dryRun);
        $styles = [
            Sections::CREATED => 'info',
            Sections::UPDATED => 'comment',
            Sections::APPENDED => 'comment',
            Sections::UNCHANGED => 'fg=gray',
        ];

        foreach ($results as $path => $status) {
            $label = $dryRun && $status !== Sections::UNCHANGED ? "would be $status" : $status;
            $output->writeln(sprintf('  <%s>%-18s</> %s', $styles[$status], $label, $path));
        }

        if ($dryRun) {
            $output->writeln('Dry run: nothing was written.');
        }

        return Command::SUCCESS;
    }
}
