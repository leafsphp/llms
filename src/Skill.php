<?php

namespace Leaf\Llms;

/**
 * An agent skill, written as SKILL.md for Claude Code and other agents.
 */
class Skill
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly string $body,
    ) {
        if (!preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $name) || strlen($name) > 64) {
            throw new \InvalidArgumentException("Invalid skill name \"$name\"");
        }
    }

    public function frontmatter(): string
    {
        return "name: {$this->name}\ndescription: " . json_encode($this->description, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
