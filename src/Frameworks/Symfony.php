<?php

namespace Leaf\Llms\Frameworks;

use Leaf\Llms\Skill;

class Symfony extends Framework
{
    public function label(): string
    {
        return 'Symfony';
    }

    public function package(): ?string
    {
        return 'symfony/framework-bundle';
    }

    public function docs(): array
    {
        return [
            'Symfony documentation' => 'https://symfony.com/doc/current/index.html',
        ];
    }

    public function directories(): array
    {
        return [
            'src' => 'application code: `Controller`, `Entity`, `Repository` and services',
            'config' => 'bundle, routing and service configuration (`services.yaml`)',
            'templates' => 'Twig templates',
            'migrations' => 'Doctrine migrations',
            'public' => 'web root; `public/index.php` is the front controller',
            'bin' => '`bin/console`',
            'tests' => 'tests',
            'var' => 'cache and logs; not source code',
        ];
    }

    public function cliCommands(): array
    {
        $commands = [
            'Console' => 'php bin/console',
            'Routes' => 'php bin/console debug:router',
            'Clear cache' => 'php bin/console cache:clear',
        ];

        if ($this->project->has('symfony/maker-bundle')) {
            $commands['Generate'] = 'php bin/console make:<type>';
        }

        if ($this->project->has('doctrine/doctrine-migrations-bundle')) {
            $commands['Migrate'] = 'php bin/console doctrine:migrations:migrate';
        }

        return $commands;
    }

    public function conventions(): array
    {
        return [
            'Services in `src/` are autowired and autoconfigured; inject dependencies through constructors instead of fetching them from the container.',
            'Define routes with `#[Route]` attributes on controller methods and give every route a name.',
            'Controllers extend `AbstractController` and stay thin; put business logic in services.',
            'Render HTML with Twig templates in `templates/`; return `JsonResponse` or `$this->json()` for APIs.',
            'Change the database by editing entities, then generating a Doctrine migration. Do not edit migrations that may have run elsewhere.',
            'Keep secrets in environment variables or Symfony secrets, never in committed config.',
        ];
    }

    protected function frameworkSkills(): array
    {
        return [
            new Skill(
                'symfony-controller',
                'Add a route or controller to a Symfony app. Use when asked for a new page, form or API endpoint.',
                implode("\n", [
                    '# Add a Symfony controller',
                    '',
                    '1. Check `php bin/console debug:router` for existing routes and naming.',
                    '2. Generate the controller with `php bin/console make:controller PostController` (needs symfony/maker-bundle) or add a method to an existing one.',
                    '3. Route it with an attribute:',
                    '',
                    '```php',
                    '#[Route(\'/posts\', name: \'post_index\', methods: [\'GET\'])]',
                    'public function index(PostRepository $posts): Response',
                    '{',
                    '    return $this->render(\'post/index.html.twig\', [\'posts\' => $posts->findAll()]);',
                    '}',
                    '```',
                    '',
                    '4. Inject services as action or constructor arguments; let autowiring resolve them.',
                    '5. Add a functional test with `WebTestCase`, then run the suite.',
                ])
            ),
            new Skill(
                'symfony-doctrine',
                'Create or change Doctrine entities and migrations in a Symfony app. Use for any schema or entity work.',
                implode("\n", [
                    '# Change the database with Doctrine',
                    '',
                    '1. Create or update an entity with `php bin/console make:entity Post`, or edit the class and its mapping attributes.',
                    '2. Generate a migration from the diff: `php bin/console make:migration`. Read the SQL it produced before keeping it.',
                    '3. Apply it with `php bin/console doctrine:migrations:migrate`.',
                    '4. Put custom queries in the entity\'s repository class using the query builder or DQL with parameters.',
                ])
            ),
        ];
    }
}
