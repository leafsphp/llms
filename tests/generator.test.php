<?php

use Leaf\Llms\Detector;
use Leaf\Llms\Generator;
use Leaf\Llms\Sections;
use Leaf\Llms\Writer;

function runInit(string $root): array
{
    $project = (new Detector())->detect($root);

    return (new Writer($root))->write((new Generator($project))->files());
}

test('writes every file for every framework', function (string $root) {
    $results = runInit($root);

    foreach (['AGENTS.md', 'CLAUDE.md', 'llms.txt', '.cursor/rules/project.mdc', '.cursor/rules/php.mdc', '.claude/skills/php-tests/SKILL.md', '.agents/skills/php-tests/SKILL.md'] as $file) {
        expect($results)->toHaveKey($file);
        expect(is_file("$root/$file"))->toBeTrue();
    }

    $skills = array_filter(array_keys($results), fn ($path) => str_ends_with($path, 'SKILL.md'));
    expect(count($skills))->toBeGreaterThanOrEqual(4);
})->with([
    'laravel' => fn () => laravelProject(),
    'symfony' => fn () => symfonyProject(),
    'leaf' => fn () => leafProject(),
    'leaf mvc' => fn () => leafMvcProject(),
    'plain php' => fn () => plainProject(),
]);

test('a second run changes nothing', function () {
    $root = laravelProject();
    runInit($root);

    expect(array_unique(array_values(runInit($root))))->toBe([Sections::UNCHANGED]);
});

test('re-runs keep edits made outside the markers', function () {
    $root = symfonyProject();
    runInit($root);

    file_put_contents("$root/AGENTS.md", "Team note at the top.\n\n" . projectFile($root, 'AGENTS.md') . "\nTeam note at the bottom.\n");
    file_put_contents("$root/composer.json", str_replace('Acme shop', 'Acme online shop', projectFile($root, 'composer.json')));

    $results = runInit($root);
    $agents = projectFile($root, 'AGENTS.md');

    expect($results['AGENTS.md'])->toBe(Sections::UPDATED);
    expect($agents)->toStartWith('Team note at the top.');
    expect($agents)->toContain('Acme online shop');
    expect($agents)->toEndWith("Team note at the bottom.\n");
});

test('existing AGENTS.md without markers is kept', function () {
    $root = leafMvcProject();
    $results = runInit($root);
    $agents = projectFile($root, 'AGENTS.md');

    expect($results['AGENTS.md'])->toBe(Sections::APPENDED);
    expect($agents)->toStartWith("# Agent Instructions\n\nKeep this line.");
    expect($agents)->toContain(Sections::START);
});

test('AGENTS.md lists the real commands and layout', function () {
    $root = laravelProject();
    runInit($root);
    $agents = projectFile($root, 'AGENTS.md');

    expect($agents)
        ->toContain('Laravel (`laravel/framework` `^12.0`)')
        ->toContain('- Test: `php artisan test`')
        ->toContain('- Lint: `vendor/bin/pint --test`')
        ->toContain('- Composer scripts: `composer dev`')
        ->toContain('- `app/`:')
        ->toContain('- `database/`:')
        ->not->toContain('- `config/`:')
        ->not->toContain('post-autoload-dump');
});

test('Leaf MVC output points at CONTEXT.md and the leaf CLI', function () {
    $root = leafMvcProject();
    runInit($root);

    expect(projectFile($root, 'AGENTS.md'))
        ->toContain('.leaf/CONTEXT.md')
        ->toContain('`leaf g:schema <table>`')
        ->toContain('https://leafphp.dev/llms.txt');
    expect(is_file("$root/.claude/skills/leaf-database/SKILL.md"))->toBeTrue();
});

test('CLAUDE.md imports AGENTS.md', function () {
    $root = plainProject();
    runInit($root);

    expect(projectFile($root, 'CLAUDE.md'))->toContain("\n@AGENTS.md\n");
});

test('skills have valid frontmatter for Claude Code', function () {
    $root = symfonyProject();
    runInit($root);

    foreach (glob("$root/.claude/skills/*/SKILL.md") as $file) {
        $contents = (string) file_get_contents($file);
        $folder = basename(dirname($file));

        expect($contents)->toMatch('/^---\nname: ' . preg_quote($folder, '/') . "\ndescription: \"[^\"]{20,1024}\"\n---\n/");
        expect(projectFile($root, ".agents/skills/$folder/SKILL.md"))->toBe($contents);
    }
});

test('cursor rules have mdc frontmatter', function () {
    $root = leafProject();
    runInit($root);

    expect(projectFile($root, '.cursor/rules/project.mdc'))->toStartWith("---\ndescription: ")->toContain("alwaysApply: true\n---");
    expect(projectFile($root, '.cursor/rules/php.mdc'))->toContain("globs: **/*.php\nalwaysApply: false\n---");
});

test('llms.txt follows the llmstxt.org shape', function () {
    $root = plainProject();
    runInit($root);

    expect(projectFile($root, 'llms.txt'))
        ->toContain("# acme/money\n\n> Money value objects\n")
        ->toContain('- [README](README.md)')
        ->toContain('## Docs');
});

test('dry run writes nothing', function () {
    $root = plainProject();
    $project = (new Detector())->detect($root);
    $results = (new Writer($root))->write((new Generator($project))->files(), true);

    expect($results['AGENTS.md'])->toBe(Sections::CREATED);
    expect(is_file("$root/AGENTS.md"))->toBeFalse();
});
