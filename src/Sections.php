<?php

namespace Leaf\Llms;

/**
 * Keeps generated text inside marked sections so re-runs never touch
 * what people write around it.
 */
class Sections
{
    public const START = '<!-- leafs/llms:start -->';
    public const END = '<!-- leafs/llms:end -->';

    public const CREATED = 'created';
    public const UPDATED = 'updated';
    public const APPENDED = 'appended';
    public const UNCHANGED = 'unchanged';

    /**
     * Merge a generated body into a file's current contents.
     *
     * - New file: frontmatter (if any), then the marked section.
     * - File with markers: only the text between them is replaced.
     * - File without markers: the marked section is added at the end.
     *
     * @return array{0: string, 1: string} new contents and what happened
     */
    public static function merge(?string $existing, string $body, ?string $frontmatter = null): array
    {
        $block = self::START . "\n" . trim($body) . "\n" . self::END;

        if ($existing === null) {
            $head = $frontmatter !== null ? "---\n" . trim($frontmatter) . "\n---\n\n" : '';

            return [$head . $block . "\n", self::CREATED];
        }

        $start = strpos($existing, self::START);
        $end = $start === false ? false : strpos($existing, self::END, $start);

        if ($start !== false && $end !== false) {
            $updated = substr($existing, 0, $start) . $block . substr($existing, $end + strlen(self::END));

            return [$updated, $updated === $existing ? self::UNCHANGED : self::UPDATED];
        }

        return [rtrim($existing) . "\n\n" . $block . "\n", self::APPENDED];
    }
}
