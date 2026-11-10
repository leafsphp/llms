<?php

namespace Leaf\Llms\Frameworks;

use Leaf\Llms\Skill;

class Leaf extends Framework
{
    public function label(): string
    {
        return 'Leaf';
    }

    public function package(): ?string
    {
        return 'leafs/leaf';
    }

    public function docs(): array
    {
        return [
            'Leaf docs for LLMs' => 'https://leafphp.dev/llms.txt',
            'Leaf agent skill' => 'https://leafphp.dev/ai/SKILL.md',
            'Leaf documentation' => 'https://leafphp.dev/docs/',
        ];
    }

    public function directories(): array
    {
        return [
            'app' => 'application code',
            'src' => 'application code',
            'routes' => 'route files',
            'public' => 'web root; `public/index.php` boots the app',
            'views' => 'templates',
            'tests' => 'tests',
            'storage' => 'logs, cache and uploads; not source code',
        ];
    }

    public function conventions(): array
    {
        return [
            'Leaf 5 exposes its services through global helpers: `app()`, `request()`, `response()`, `db()`, `auth()`. Use them instead of building objects by hand.',
            'Define routes with `app()->get()`, `->post()`, `->put()`, `->delete()` or `->match()`; group them with `app()->group()` and protect them with middleware.',
            'Read input with `request()->get(\'key\')` or `request()->validate([...])`, and reply with `response()->json(...)`, `response()->markup(...)` or `response()->redirect(...)`.',
            'Leaf is modular: add features with `leaf install <module>` or `composer require leafs/<module>` (db, auth, session, mail, http...) instead of writing them from scratch.',
            'Query the database through `db()` (leafs/db) with bindings; never build SQL from raw input.',
        ];
    }

    protected function frameworkSkills(): array
    {
        return [new Skill(
            'leaf-routes',
            'Add or change an HTTP route in a Leaf app. Use when asked for a new endpoint, page or API route.',
            implode("\n", [
                '# Add a Leaf route',
                '',
                '1. Find where routes live (usually `public/index.php`, `index.php` or a `routes/` folder) and follow the existing pattern.',
                '2. Register the route:',
                '',
                '```php',
                'app()->post(\'/users\', function () {',
                '    $data = request()->validate([',
                '        \'name\' => \'string\',',
                '        \'email\' => \'email\',',
                '    ]);',
                '',
                '    if (!$data) {',
                '        response()->exit(request()->errors(), 400);',
                '    }',
                '',
                '    db()->insert(\'users\')->params($data)->execute();',
                '',
                '    response()->json([\'created\' => true], 201);',
                '});',
                '```',
                '',
                '3. Put shared logic in middleware (`app()->use()` or a route `middleware` option) rather than repeating it in handlers.',
                '4. Add a test for the route, then run the suite.',
                '',
                'Check https://leafphp.dev/llms.txt for the current API before using a helper you have not seen in this codebase.',
            ])
        )];
    }
}
