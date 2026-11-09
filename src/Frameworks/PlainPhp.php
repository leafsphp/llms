<?php

namespace Leaf\Llms\Frameworks;

use Leaf\Llms\Skill;

class PlainPhp extends Framework
{
    public function label(): string
    {
        return 'PHP';
    }

    public function package(): ?string
    {
        return null;
    }

    public function docs(): array
    {
        return [
            'PHP manual' => 'https://www.php.net/manual/en/',
            'Composer documentation' => 'https://getcomposer.org/doc/',
            'PSR-12 coding style' => 'https://www.php-fig.org/psr/psr-12/',
        ];
    }

    public function directories(): array
    {
        return [
            'src' => 'library or application code',
            'lib' => 'library code',
            'app' => 'application code',
            'public' => 'web root',
            'bin' => 'executables',
            'config' => 'configuration',
            'tests' => 'tests',
            'test' => 'tests',
            'docs' => 'documentation',
        ];
    }

    public function conventions(): array
    {
        return [
            'Classes follow PSR-4, with one class per file and the namespace matching the folder mapped in `composer.json`.',
            'After adding a namespace or moving classes, run `composer dump-autoload`.',
            'Follow PSR-12 formatting unless the project\'s formatter says otherwise.',
            'Add dependencies with `composer require` (or `--dev` for tooling) and commit `composer.json`; check before adding a package for something a few lines of code can do.',
            'Keep the public API stable: a library\'s public classes and methods are a contract, so mark breaking changes clearly.',
        ];
    }

    protected function frameworkSkills(): array
    {
        return [new Skill(
            'php-package',
            'Add a class, dependency or public API to a plain PHP project or Composer library. Use when the change is not tied to a framework.',
            implode("\n", [
                '# Change a PHP project or library',
                '',
                '1. Find the namespace in `composer.json` (`autoload.psr-4`) and put new classes in the matching folder.',
                '2. Type everything: parameter, return and property types. Add `declare(strict_types=1);` if the surrounding files use it.',
                '3. Add a dependency only with `composer require vendor/package`, and explain why it is needed.',
                '4. Update the README when the public API changes.',
                '5. Add tests and run the suite before you finish.',
            ])
        )];
    }
}
