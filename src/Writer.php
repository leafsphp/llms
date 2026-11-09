<?php

namespace Leaf\Llms;

/**
 * Writes generated files, touching only their marked sections.
 */
class Writer
{
    public function __construct(protected string $root)
    {
        $this->root = rtrim($root, '/\\');
    }

    /**
     * @param File[] $files
     * @return array<string, string> path => status (see Sections constants)
     */
    public function write(array $files, bool $dryRun = false): array
    {
        $results = [];

        foreach ($files as $file) {
            $target = $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file->path);
            $existing = is_file($target) ? (string) file_get_contents($target) : null;

            if ($existing !== null) {
                $existing = str_replace("\r\n", "\n", $existing);
            }

            [$contents, $status] = Sections::merge($existing, $file->body, $file->frontmatter);
            $results[$file->path] = $status;

            if ($dryRun || $status === Sections::UNCHANGED) {
                continue;
            }

            $dir = dirname($target);

            if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new \RuntimeException("Could not create $dir");
            }

            if (file_put_contents($target, $contents) === false) {
                throw new \RuntimeException("Could not write $target");
            }
        }

        return $results;
    }
}
