<?php

use Leaf\Llms\Detector;

test('detects each framework from composer.json', function () {
    $detector = new Detector();

    expect($detector->detect(laravelProject())->framework)->toBe('laravel');
    expect($detector->detect(symfonyProject())->framework)->toBe('symfony');
    expect($detector->detect(leafProject())->framework)->toBe('leaf');
    expect($detector->detect(leafMvcProject())->framework)->toBe('leaf-mvc');
    expect($detector->detect(plainProject())->framework)->toBe('php');
});

test('a framework option skips detection', function () {
    expect((new Detector())->detect(plainProject(), 'leaf')->framework)->toBe('leaf');
});

test('rejects an unknown framework option', function () {
    (new Detector())->detect(plainProject(), 'rails');
})->throws(InvalidArgumentException::class);

test('fails clearly without composer.json', function () {
    $dir = sys_get_temp_dir() . '/llms-empty-' . bin2hex(random_bytes(4));
    mkdir($dir);

    (new Detector())->detect($dir);
})->throws(RuntimeException::class, 'No composer.json');

test('uses the folder name for starter-kit projects', function () {
    $root = laravelProject();
    $project = (new Detector())->detect($root);

    expect($project->name)->toBe(basename($root));
    expect($project->description)->toBe('');
});

test('reads php version, namespaces and test runner', function () {
    $project = (new Detector())->detect(plainProject());

    expect($project->name)->toBe('acme/money');
    expect($project->php)->toBe('^8.1');
    expect($project->psr4)->toBe(['Acme\\Money\\' => 'src/']);
    expect($project->testRunner)->toBe('PHPUnit');
});

test('drops sub-namespaces that follow their parent folder', function () {
    $project = (new Detector())->detect(leafMvcProject());

    expect($project->psr4)->toBe(['App\\' => 'app/']);
});

test('picks the commands each project really uses', function () {
    $detector = new Detector();

    $laravel = $detector->detect(laravelProject());
    expect($laravel->command('test'))->toBe('php artisan test');
    expect($laravel->command('lint'))->toBe('vendor/bin/pint --test');
    expect($laravel->command('analyse'))->toBe('vendor/bin/phpstan analyse');
    expect($laravel->scripts)->toBe(['dev' => 'composer dev']);

    $symfony = $detector->detect(symfonyProject());
    expect($symfony->command('test'))->toBe('php bin/phpunit');

    $leaf = $detector->detect(leafProject());
    expect($leaf->command('test'))->toBe('composer test');
    expect($leaf->command('lint'))->toBe('composer lint');
    expect($leaf->testRunner)->toBe('Alchemy (Pest)');

    $plain = $detector->detect(plainProject());
    expect($plain->command('test'))->toBe('vendor/bin/phpunit');
    expect($plain->command('lint'))->toBe('vendor/bin/php-cs-fixer fix --dry-run --diff');
});
