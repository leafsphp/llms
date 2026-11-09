<?php

namespace Leaf\Llms\Frameworks;

use Leaf\Llms\Project;
use Leaf\Llms\Skill;

/**
 * What leafs/llms tells an agent about one framework.
 */
abstract class Framework
{
    public function __construct(protected Project $project)
    {
    }

    public static function for(Project $project): self
    {
        return match ($project->framework) {
            'leaf-mvc' => new LeafMvc($project),
            'leaf' => new Leaf($project),
            'laravel' => new Laravel($project),
            'symfony' => new Symfony($project),
            default => new PlainPhp($project),
        };
    }

    /** Human name, e.g. "Laravel". */
    abstract public function label(): string;

    /** Composer package that identifies the framework, if any. */
    abstract public function package(): ?string;

    /**
     * Documentation links, title => url.
     *
     * @return array<string, string>
     */
    abstract public function docs(): array;

    /**
     * What each well-known directory holds, directory => description.
     *
     * @return array<string, string>
     */
    abstract public function directories(): array;

    /**
     * Rules an agent should follow in this framework.
     *
     * @return string[]
     */
    abstract public function conventions(): array;

    /**
     * Framework-specific skills, on top of the shared ones.
     *
     * @return Skill[]
     */
    abstract protected function frameworkSkills(): array;

    /**
     * Extra lines for the top of AGENTS.md, such as other files to read first.
     *
     * @return string[]
     */
    public function readFirst(): array
    {
        return [];
    }

    /**
     * Framework CLI commands worth listing, label => command or commands.
     *
     * @return array<string, string|string[]>
     */
    public function cliCommands(): array
    {
        return [];
    }

    /**
     * @return Skill[]
     */
    public function skills(): array
    {
        return [$this->testSkill(), ...$this->frameworkSkills()];
    }

    protected function testSkill(): Skill
    {
        $test = $this->project->command('test');
        $runner = $this->project->testRunner;
        $lines = [];

        $lines[] = '# Write and run tests';
        $lines[] = '';

        if ($test === null) {
            $lines[] = 'This project has no test runner installed yet. Ask before adding one. For a new setup, '
                . ($this->project->framework === 'leaf' || $this->project->framework === 'leaf-mvc'
                    ? '`composer require --dev leafs/alchemy` then `vendor/bin/alchemy init` sets up Pest with Leaf defaults.'
                    : '`composer require --dev pestphp/pest` then `vendor/bin/pest --init` is the usual start.');
        } else {
            $lines[] = "Run the suite with `$test`" . ($runner ? " ($runner)." : '.');
            $lines[] = '';
            $lines[] = '1. Find the existing test closest to what you are changing and copy its style: file location, naming, helpers and assertions.';
            $lines[] = '2. Write a failing test for the behaviour first when you fix a bug, so the fix is proven.';
            $lines[] = '3. Run only the file you are working on while iterating, then the full suite before you finish.';
            $lines[] = '4. Do not delete or skip a failing test to get a green run. Fix the code, or explain why the test is wrong.';
        }

        foreach ($this->testHints() as $hint) {
            $lines[] = '';
            $lines[] = $hint;
        }

        return new Skill(
            'php-tests',
            'Write and run tests for this PHP project. Use when adding a feature, fixing a bug, or when asked to test something.',
            implode("\n", $lines)
        );
    }

    /**
     * @return string[]
     */
    protected function testHints(): array
    {
        $runner = $this->project->testRunner;

        if ($runner === null) {
            return [];
        }

        if (str_contains($runner, 'Pest')) {
            return ['Tests use Pest syntax: `it(\'does something\', function () { expect($value)->toBe(1); });`. Filter with `--filter="name"`.'];
        }

        return ['Tests are PHPUnit classes extending `PHPUnit\\Framework\\TestCase` with `test*` methods. Filter with `--filter=testName`.'];
    }
}
