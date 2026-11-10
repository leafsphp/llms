<?php

namespace Leaf\Llms;

/**
 * Reads composer.json and the folder layout to describe a project.
 */
class Detector
{
    public const FRAMEWORKS = ['leaf-mvc', 'leaf', 'laravel', 'symfony', 'php'];

    /** Directories worth describing to an agent, checked in this order. */
    protected const DIRECTORIES = [
        'app', 'src', 'lib', 'routes', 'config', 'database', 'migrations',
        'resources', 'templates', 'views', 'public', 'bin', 'tests', 'test',
        'storage', 'var', 'bootstrap', 'docs', '.leaf', 'app/routes', 'app/controllers', 'app/models', 'app/database', 'app/views',
    ];

    /** Starter-kit package names; a project created from one keeps them, so they say nothing about it. */
    protected const SKELETONS = [
        'leafs/mvc', 'leafs/api', 'leafs/skeleton', 'laravel/laravel',
        'symfony/skeleton', 'symfony/website-skeleton',
    ];

    /** Composer hooks that are not commands a person runs. */
    protected const HOOK_PREFIXES = ['pre-', 'post-'];

    public function detect(string $root, ?string $framework = null): Project
    {
        $root = rtrim($root, '/\\');
        $file = $root . DIRECTORY_SEPARATOR . 'composer.json';

        if (!is_file($file)) {
            throw new \RuntimeException("No composer.json found in $root. Run llms from your project root.");
        }

        $composer = json_decode((string) file_get_contents($file), true);

        if (!is_array($composer)) {
            throw new \RuntimeException("Could not parse $file: " . json_last_error_msg());
        }

        $packages = array_merge($composer['require'] ?? [], $composer['require-dev'] ?? []);

        if ($framework !== null && !in_array($framework, self::FRAMEWORKS, true)) {
            throw new \InvalidArgumentException(
                "Unknown framework \"$framework\". Use one of: " . implode(', ', self::FRAMEWORKS)
            );
        }

        $framework ??= $this->framework($packages);
        $scripts = $this->scripts($composer['scripts'] ?? []);
        [$testRunner, $commands] = $this->commands($root, $framework, $packages, $scripts);

        $skeleton = in_array($composer['name'] ?? '', self::SKELETONS, true);

        return new Project(
            root: $root,
            name: $skeleton || empty($composer['name']) ? basename($root) : (string) $composer['name'],
            description: $skeleton ? '' : trim((string) ($composer['description'] ?? '')),
            framework: $framework,
            php: (string) ($composer['require']['php'] ?? ''),
            psr4: $this->psr4($composer),
            testRunner: $testRunner,
            commands: $commands,
            scripts: $scripts,
            directories: array_values(array_filter(
                self::DIRECTORIES,
                fn ($dir) => is_dir($root . DIRECTORY_SEPARATOR . $dir)
            )),
            packages: $packages,
        );
    }

    /**
     * @param array<string, string> $packages
     */
    public function framework(array $packages): string
    {
        return match (true) {
            isset($packages['leafs/mvc-core']) || isset($packages['leafs/mvc']) => 'leaf-mvc',
            isset($packages['laravel/framework']) => 'laravel',
            isset($packages['symfony/framework-bundle']) => 'symfony',
            isset($packages['leafs/leaf']) => 'leaf',
            default => 'php',
        };
    }



    /**
     * @return array<string, string>
     */
    protected function psr4(array $composer): array
    {
        $map = [];

        foreach ([$composer['autoload']['psr-4'] ?? [], $composer['autoload-dev']['psr-4'] ?? []] as $psr4) {
            foreach ($psr4 as $namespace => $paths) {
                $map[$namespace] = implode(', ', (array) $paths);
            }
        }

        // Drop sub-namespaces that just follow their parent's folder
        // (App\Models => app/models/ under App\ => app/); they add noise.
        foreach ($map as $namespace => $path) {
            foreach ($map as $parent => $parentPath) {
                if (
                    $parent !== $namespace
                    && str_starts_with($namespace, $parent)
                    && str_starts_with(strtolower($path), strtolower(rtrim($parentPath, '/') . '/'))
                ) {
                    unset($map[$namespace]);

                    break;
                }
            }
        }

        return $map;
    }

    /**
     * @return array<string, string> script => "composer script"
     */
    protected function scripts(array $scripts): array
    {
        $result = [];

        foreach (array_keys($scripts) as $script) {
            foreach (self::HOOK_PREFIXES as $prefix) {
                if (str_starts_with($script, $prefix)) {
                    continue 2;
                }
            }

            $result[$script] = "composer $script";
        }

        return $result;
    }

    /**
     * @return array{0: ?string, 1: array<string, string>}
     */
    protected function commands(string $root, string $framework, array $packages, array $scripts): array
    {
        $has = fn (string $package) => isset($packages[$package]);
        $commands = ['install' => 'composer install'];

        $runner = match (true) {
            $has('leafs/alchemy') => 'Alchemy (Pest)',
            $has('pestphp/pest') => 'Pest',
            $has('phpunit/phpunit') => 'PHPUnit',
            default => null,
        };

        $commands['test'] = match (true) {
            isset($scripts['test']) => 'composer test',
            $framework === 'laravel' => 'php artisan test',
            $has('leafs/alchemy') => 'vendor/bin/alchemy test',
            $has('pestphp/pest') => 'vendor/bin/pest',
            $has('phpunit/phpunit') && $framework === 'symfony' && is_file("$root/bin/phpunit") => 'php bin/phpunit',
            $has('phpunit/phpunit') => 'vendor/bin/phpunit',
            default => null,
        };

        $commands['lint'] = match (true) {
            isset($scripts['lint']) => 'composer lint',
            $has('laravel/pint') => 'vendor/bin/pint --test',
            $has('leafs/alchemy') => 'vendor/bin/alchemy lint',
            $has('friendsofphp/php-cs-fixer') => 'vendor/bin/php-cs-fixer fix --dry-run --diff',
            $has('squizlabs/php_codesniffer') => 'vendor/bin/phpcs',
            default => null,
        };

        $commands['format'] = match (true) {
            isset($scripts['fmt']) => 'composer fmt',
            isset($scripts['format']) => 'composer format',
            $has('laravel/pint') => 'vendor/bin/pint',
            $has('leafs/alchemy') => 'vendor/bin/alchemy fmt',
            $has('friendsofphp/php-cs-fixer') => 'vendor/bin/php-cs-fixer fix',
            $has('squizlabs/php_codesniffer') => 'vendor/bin/phpcbf',
            default => null,
        };

        $commands['analyse'] = match (true) {
            isset($scripts['analyse']) => 'composer analyse',
            isset($scripts['analyze']) => 'composer analyze',
            isset($scripts['phpstan']) => 'composer phpstan',
            $has('phpstan/phpstan') || $has('larastan/larastan') => 'vendor/bin/phpstan analyse',
            $has('vimeo/psalm') => 'vendor/bin/psalm',
            default => null,
        };

        $commands['refactor'] = match (true) {
            isset($scripts['refactor']) => 'composer refactor',
            $has('rector/rector') => 'vendor/bin/rector process --dry-run',
            default => null,
        };

        return [$runner, array_filter($commands)];
    }
}
