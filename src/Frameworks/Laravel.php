<?php

namespace Leaf\Llms\Frameworks;

use Leaf\Llms\Skill;

class Laravel extends Framework
{
    public function label(): string
    {
        return 'Laravel';
    }

    public function package(): ?string
    {
        return 'laravel/framework';
    }

    public function docs(): array
    {
        return [
            'Laravel documentation' => 'https://laravel.com/docs',
        ];
    }

    public function directories(): array
    {
        return [
            'app' => 'application code: `Http/Controllers`, `Models`, `Providers` and your own classes',
            'routes' => 'route files (`web.php`, plus `api.php` and `console.php` when present)',
            'config' => 'config files, read through `config()`',
            'database' => 'migrations, plus factories and seeders for test data',
            'resources' => 'Blade views and front-end sources',
            'public' => 'web root',
            'storage' => 'logs, cache and uploads; not source code',
            'tests' => '`Feature` and `Unit` tests',
            'bootstrap' => 'framework boot files; rarely edited',
        ];
    }

    public function cliCommands(): array
    {
        return [
            'Serve' => 'php artisan serve',
            'Generate' => 'php artisan make:<type>',
            'Migrate' => 'php artisan migrate',
            'Routes' => 'php artisan route:list',
        ];
    }

    public function conventions(): array
    {
        return [
            'Generate classes with `php artisan make:*` so names and locations follow Laravel defaults.',
            'Validate input in Form Requests (`php artisan make:request`) or `$request->validate()`, never by hand.',
            'Use Eloquent models and relationships for data access; avoid raw SQL unless the codebase already does it, and always bind values.',
            'Change the schema with a new migration. Never edit a migration that may already have run in another environment.',
            'Read configuration through `config()`; only `config/*.php` files should call `env()`.',
            'Prefer the framework\'s own tools (queues, events, policies, mail, cache) over custom code that does the same job.',
        ];
    }

    protected function frameworkSkills(): array
    {
        return [
            new Skill(
                'laravel-feature',
                'Add an endpoint or page to a Laravel app. Use when asked for a new route, controller action, form or API endpoint.',
                implode("\n", [
                    '# Add a feature to a Laravel app',
                    '',
                    '1. Check `php artisan route:list` and the existing controllers for a pattern to follow.',
                    '2. Generate what you need: `php artisan make:controller PostController`, `php artisan make:request StorePostRequest`.',
                    '3. Register the route in `routes/web.php` (or `routes/api.php` if the project has it) and name it:',
                    '',
                    '```php',
                    'Route::post(\'/posts\', [PostController::class, \'store\'])->name(\'posts.store\');',
                    '```',
                    '',
                    '4. Put validation rules and authorization in the Form Request and keep the controller action short.',
                    '5. Write a feature test (`php artisan make:test PostTest`) that hits the route, then run the suite.',
                ])
            ),
            new Skill(
                'laravel-database',
                'Create or change tables, models, factories or seeders in a Laravel app. Use for any schema or Eloquent work.',
                implode("\n", [
                    '# Change the database in Laravel',
                    '',
                    '1. Create a model with its migration and factory: `php artisan make:model Post -mf`.',
                    '2. For changes to an existing table, add a new migration: `php artisan make:migration add_status_to_posts_table`. Do not edit old migrations.',
                    '3. Fill in `up()` and a matching `down()`, then run `php artisan migrate`.',
                    '4. Add relationships, `$fillable` (or `$guarded`) and casts to the model.',
                    '5. Update the factory so tests can create valid records, and use it in tests rather than inserting rows by hand.',
                ])
            ),
        ];
    }
}
