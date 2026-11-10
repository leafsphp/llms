<!-- markdownlint-disable no-inline-html -->
<p align="center">
  <br><br>
  <img src="https://leafphp.dev/logo-circle.png" height="100"/>
  <h1 align="center">Leaf LLMs</h1>
  <br><br>
</p>

[![Latest Stable Version](https://poser.pugx.org/leafs/llms/v/stable)](https://packagist.org/packages/leafs/llms)
[![Total Downloads](https://poser.pugx.org/leafs/llms/downloads)](https://packagist.org/packages/leafs/llms)
[![License](https://poser.pugx.org/leafs/llms/license)](https://packagist.org/packages/leafs/llms)

Coding agents do better work when a project tells them how it's built. `leafs/llms` reads your `composer.json` and folder layout, then writes the files coding agents such as Claude Code and Cursor look for. Your agent learns the commands your project really uses, so it doesn't have to guess.

It works with Leaf, Leaf MVC, Laravel, Symfony and plain PHP projects.

## Installation

```bash
composer require --dev leafs/llms
```

## Usage

From your project root:

```bash
vendor/bin/llms init
```

That writes these files:

| File | Read by |
| --- | --- |
| `AGENTS.md` | Most agents. It's the source of truth, covering your stack and commands plus the conventions to follow |
| `CLAUDE.md` | Claude Code. It imports `AGENTS.md` |
| `llms.txt` | Tools that follow [llmstxt.org](https://llmstxt.org), with links to your docs and your framework's docs |
| `.cursor/rules/project.mdc` | Cursor, on every request |
| `.cursor/rules/php.mdc` | Cursor, when a PHP file is involved |
| `.claude/skills/*/SKILL.md` | Claude Code skills |
| `.agents/skills/*/SKILL.md` | The same skills, for agents that read `.agents/` |

Each project gets a `php-tests` skill that uses your test runner, plus skills for its framework:

| Framework | Skills |
| --- | --- |
| Leaf | `leaf-routes` |
| Leaf MVC | `leaf-mvc-feature`, `leaf-database` (schema files, models, seeds) |
| Laravel | `laravel-feature`, `laravel-database` |
| Symfony | `symfony-controller`, `symfony-doctrine` |
| Plain PHP | `php-package` |

To see what was detected without writing anything:

```bash
vendor/bin/llms detect
vendor/bin/llms init --dry-run
```

If detection picks the wrong framework, set it yourself with `--framework=leaf-mvc`, `leaf`, `laravel`, `symfony` or `php`.

## What it detects

- The framework, from `composer.json`
- The PHP version constraint and PSR-4 namespaces
- The test runner (Pest, PHPUnit, or Pest through Alchemy) and the command that runs it
- Code style tools (Pint, PHP CS Fixer, PHP_CodeSniffer) and static analysis (PHPStan, Larastan, Psalm, Rector)
- Your Composer scripts, which it prefers over calling tools directly
- Folders that exist, described for your framework

## Re-running is safe

Generated text sits between two markers:

```md
<!-- leafs/llms:start -->
...
<!-- leafs/llms:end -->
```

Running `init` again only replaces what's between them. Anything you write above or below stays. If a file already exists without markers, such as the `AGENTS.md` that ships with Leaf MVC, the generated section is added at the end and your text is left alone.

Re-run it after you add a tool or change your scripts. You can add it to `composer.json` so it's easy to remember:

```json
"scripts": {
    "llms": "llms init"
}
```

## License

MIT
