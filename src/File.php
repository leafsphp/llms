<?php

namespace Leaf\Llms;

/**
 * One file leafs/llms wants to write.
 */
class File
{
    public function __construct(
        public readonly string $path,
        public readonly string $body,
        public readonly ?string $frontmatter = null,
    ) {
    }
}
