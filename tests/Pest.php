<?php

/**
 * Create a throwaway project with the given composer.json and folders.
 *
 * @param string[] $dirs
 * @param array<string, string> $files
 */
function makeProject(array $composer, array $dirs = [], array $files = []): string
{
    $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'llms-' . bin2hex(random_bytes(6));
    mkdir($root, 0755, true);
    file_put_contents("$root/composer.json", json_encode($composer, JSON_PRETTY_PRINT));

    foreach ($dirs as $dir) {
        mkdir("$root/$dir", 0755, true);
    }

    foreach ($files as $path => $contents) {
        if (!is_dir(dirname("$root/$path"))) {
            mkdir(dirname("$root/$path"), 0755, true);
        }

        file_put_contents("$root/$path", $contents);
    }

    return $root;
}

function laravelProject(): string
{
    return makeProject([
        'name' => 'laravel/laravel',
        'description' => 'The skeleton application for the Laravel framework.',
        'require' => ['php' => '^8.2', 'laravel/framework' => '^12.0'],
        'require-dev' => ['laravel/pint' => '^1.13', 'pestphp/pest' => '^3.0', 'larastan/larastan' => '^3.0'],
        'autoload' => ['psr-4' => ['App\\' => 'app/', 'Database\\Factories\\' => 'database/factories/']],
        'scripts' => ['dev' => ['npx concurrently'], 'post-autoload-dump' => ['@php artisan package:discover']],
    ], ['app/Models', 'routes', 'database/migrations', 'resources', 'tests/Feature', 'bootstrap']);
}

function symfonyProject(): string
{
    return makeProject([
        'name' => 'acme/shop',
        'description' => 'Acme shop',
        'require' => [
            'php' => '>=8.2',
            'symfony/framework-bundle' => '7.2.*',
            'doctrine/doctrine-migrations-bundle' => '^3.3',
        ],
        'require-dev' => ['phpunit/phpunit' => '^11.0', 'symfony/maker-bundle' => '^1.61', 'phpstan/phpstan' => '^2.0'],
        'autoload' => ['psr-4' => ['App\\' => 'src/']],
    ], ['src/Controller', 'config', 'templates', 'migrations', 'bin', 'tests', 'var'], ['bin/phpunit' => '']);
}

function leafProject(): string
{
    return makeProject([
        'name' => 'acme/api',
        'description' => 'A tiny Leaf API',
        'require' => ['php' => '^8.2', 'leafs/leaf' => '^5.0', 'leafs/db' => '^5.1'],
        'require-dev' => ['leafs/alchemy' => '^5.0'],
        'scripts' => ['test' => '@php vendor/bin/alchemy test', 'lint' => '@php vendor/bin/alchemy lint'],
    ], ['public', 'tests']);
}

function leafMvcProject(): string
{
    return makeProject([
        'name' => 'leafs/mvc',
        'description' => 'A lightweight framework for people who just want to ship',
        'require' => ['php' => '^8.2', 'leafs/mvc-core' => '^5.0', 'leafs/leaf' => '^5.0'],
        'autoload' => ['psr-4' => ['App\\' => 'app/', 'App\\Models\\' => 'app/models/']],
    ], ['app/routes', 'app/controllers', 'app/models', 'app/database', 'public', '.leaf'], [
        '.leaf/CONTEXT.md' => "# Project Context\n",
        'AGENTS.md' => "# Agent Instructions\n\nKeep this line.\n",
    ]);
}

function plainProject(): string
{
    return makeProject([
        'name' => 'acme/money',
        'description' => 'Money value objects',
        'require' => ['php' => '^8.1'],
        'require-dev' => ['phpunit/phpunit' => '^10.5', 'friendsofphp/php-cs-fixer' => '^3.0'],
        'autoload' => ['psr-4' => ['Acme\\Money\\' => 'src/']],
    ], ['src', 'tests'], ['README.md' => "# Money\n"]);
}

function projectFile(string $root, string $path): string
{
    return (string) file_get_contents("$root/$path");
}
