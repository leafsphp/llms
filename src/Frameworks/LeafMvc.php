<?php

namespace Leaf\Llms\Frameworks;

use Leaf\Llms\Skill;

class LeafMvc extends Leaf
{
    public function label(): string
    {
        return 'Leaf MVC';
    }

    public function package(): ?string
    {
        return $this->project->has('leafs/mvc-core') ? 'leafs/mvc-core' : 'leafs/mvc';
    }

    public function docs(): array
    {
        return parent::docs() + [
            'Leaf MVC' => 'https://leafphp.dev/docs/mvc/',
            'Models (Eloquent)' => 'https://leafphp.dev/docs/database/models',
            'Schema files' => 'https://leafphp.dev/docs/database/files',
        ];
    }

    public function readFirst(): array
    {
        $lines = [];

        if ($this->project->hasPath('.leaf/CONTEXT.md')) {
            $lines[] = 'Read `.leaf/CONTEXT.md` for the project goals and decisions, and update it when you finish a task.';
        }

        $lines[] = 'Run `leaf context` for a short map of the app.';

        return $lines;
    }

    public function directories(): array
    {
        return [
            'app' => 'application code, split into the folders below',
            'app/routes' => 'route files; files starting with `_` are loaded automatically',
            'app/controllers' => 'controllers, extending `App\\Controllers\\Controller`',
            'app/models' => 'Eloquent models, extending `App\\Models\\Model`',
            'app/database' => 'schema files, one `<table>.yml` per table',
            'app/views' => 'views (Blade by default)',
            'config' => 'config files, published with `leaf config:publish`',
            'public' => 'web root and built assets',
            'storage' => 'logs, cache and uploads; not source code',
            'tests' => 'tests',
            '.leaf' => 'project context for agents (`CONTEXT.md`)',
        ];
    }

    public function cliCommands(): array
    {
        return [
            'Serve' => 'leaf serve',
            'Generate' => ['leaf g:controller <Name>', 'leaf g:model <Name>', 'leaf g:schema <table>', 'leaf g:middleware <Name>'],
            'Migrate' => 'leaf db:migrate',
            'Seed' => 'leaf db:seed',
            'Roll back' => 'leaf db:rollback',
        ];
    }

    public function conventions(): array
    {
        return [
            'Use the global `leaf` binary for console commands (no `php` prefix). It forwards project commands, and some commands such as `leaf context` only exist there.',
            'Generate files with `leaf g:*` instead of creating them by hand, so namespaces and base classes match.',
            'Controllers extend `App\\Controllers\\Controller` and reach the request and response through `request()` and `response()`. Keep them thin; put reusable logic in models or classes under `app/`.',
            'Models are Eloquent, so relationships and casts work the same way as in Laravel.',
            'Change the database through schema files in `app/database/*.yml`, then run `leaf db:migrate`. Do not hand-write migrations unless the project already has them.',
            'Configuration comes from `.env` and `config/`. Never commit secrets or a real `.env`.',
        ];
    }

    protected function frameworkSkills(): array
    {
        return [
            new Skill(
                'leaf-mvc-feature',
                'Add a page, endpoint or feature to a Leaf MVC app. Use when asked to build something that needs a route, controller or view.',
                implode("\n", [
                    '# Add a feature to a Leaf MVC app',
                    '',
                    '1. Run `leaf context` to see what already exists.',
                    '2. Generate the controller: `leaf g:controller Posts` (add `--resource` for a CRUD controller).',
                    '3. Register routes in a partial such as `app/routes/_posts.php`:',
                    '',
                    '```php',
                    'app()->get(\'/posts\', \'PostsController@index\');',
                    'app()->post(\'/posts\', \'PostsController@store\');',
                    '```',
                    '',
                    '4. In the controller, validate with `request()->validate([...])` and reply with `response()->json(...)` or `response()->render(\'posts.index\', [...])`.',
                    '5. If the feature needs a table, follow the `leaf-database` skill.',
                    '6. Add a test, run the suite, and note the change in `.leaf/CONTEXT.md` if the project has one.',
                ])
            ),
            new Skill(
                'leaf-database',
                'Create or change a database table, model, seed or factory in a Leaf MVC app. Use for any schema or model work.',
                implode("\n", [
                    '# Change the database in Leaf MVC',
                    '',
                    'Leaf MVC keeps each table in one schema file, `app/database/<table>.yml`.',
                    '',
                    '1. Create one with `leaf g:schema posts`, or edit the existing file.',
                    '',
                    '```yml',
                    'columns:',
                    '  title: string',
                    '  body: text',
                    '  published_at:',
                    '    type: timestamp',
                    '    nullable: true',
                    '',
                    'relationships:',
                    '  - User',
                    '',
                    'seeds:',
                    '  count: 10',
                    '  data:',
                    '    title: \'@faker.sentence\'',
                    '    body: \'@faker.paragraph\'',
                    '```',
                    '',
                    '2. Apply it with `leaf db:migrate` (or `leaf db:migrate posts` for one table). Leaf works out what changed.',
                    '3. Seed with `leaf db:seed`. Undo with `leaf db:rollback`.',
                    '4. Generate the model with `leaf g:model Post` and add relationships (`hasMany`, `belongsTo`) to match the schema file.',
                    '',
                    'Read https://leafphp.dev/docs/database/files before using a schema key you have not seen in this project.',
                ])
            ),
        ];
    }
}
