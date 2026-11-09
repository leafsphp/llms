<?php

namespace Leaf\Llms;

/**
 * Everything leafs/llms knows about the project it runs in.
 */
class Project
{
    /**
     * @param array<string, string> $psr4 namespace => directory
     * @param array<string, string> $commands label => command
     * @param array<string, string> $scripts composer script => command to run it
     * @param string[] $directories top-level directories that exist
     */
    public function __construct(
        public readonly string $root,
        public readonly string $name,
        public readonly string $description,
        public readonly string $framework,
        public readonly string $php,
        public readonly array $psr4,
        public readonly ?string $testRunner,
        public readonly array $commands,
        public readonly array $scripts,
        public readonly array $directories,
        public readonly array $packages,
    ) {
    }

    public function has(string $package): bool
    {
        return isset($this->packages[$package]);
    }

    public function hasPath(string $path): bool
    {
        return file_exists($this->root . DIRECTORY_SEPARATOR . $path);
    }

    public function command(string $label): ?string
    {
        return $this->commands[$label] ?? null;
    }
}
