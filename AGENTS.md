# KleverKat — Agent Guide

Insurance comparison platform. Laravel 13 / Livewire 4 / Filament 5 / Flux UI 2 / Tailwind 4 / Fortify 1 / PHP 8.4 / Pest 4.

## Quick start

All commands must run inside the Docker app container via `docker compose exec app`:

```bash
docker compose exec app composer run setup          # full project bootstrap
docker compose exec app composer run dev             # server + queue + logs + Vite (concurrent)
```

## Dev commands

Prefix every command with `docker compose exec app`:

| What | Command |
|------|---------|
| Format PHP | `docker compose exec app composer run lint` (pint) |
| Check lint | `docker compose exec app composer run lint:check` |
| PHPStan | `docker compose exec app composer run phpstan` (level 6) |
| Full CI | `docker compose exec app composer run ci:check` (lint:check → phpstan → test) |
| Run tests | `docker compose exec app php artisan test --compact` or `docker compose exec app composer run test` |
| Run single test | `docker compose exec app php artisan test --compact --filter=testName` |
| Create test | `docker compose exec app php artisan make:test --pest {Name}Test` |
| Build assets | `npm run build` (host, not container) |
| Read app URL | `laravel-boost_get-absolute-url` or `docker compose exec app php artisan config:show app.url` |

## Architecture

- **Auth**: Fortify (2FA, passkeys, email verification). Registration enabled.
- **Multi-tenancy**: Team-based via `current_team_id` on `users`. Routes scoped to `/{current_team}`.
- **Admin**: Filament panel at `/admin` — manages Companies, Products, Sectors.
- **Public**: Livewire + Flux UI components in `app/Livewire/`, views in `resources/views/pages/`.
- **Database**: SQLite by default (`DB_CONNECTION=sqlite`). Session, cache, queue all use database driver.
- **Domains**: `User`, `Team`, `TeamInvitation`, `Membership`, `Company`, `Sector`, `Product`.

## Conventions

- Format PHP with `docker compose exec app vendor/bin/pint --format agent` after edits.
- Search package docs before writing code: `laravel-boost_search-docs` (version-aware).
- Activate the relevant skill from `.agents/skills/` or `.claude/skills/` before working in a domain (fortify, livewire, fluxui, tailwind, pest, laravel best practices).
- Livewire route syntax: `Route::livewire('path', 'pages::component.name')`.
- Views use `.blade.php` in `resources/views/`. Livewire components use `pages::` namespace.

## Flux UI

Requires auth credentials:
```bash
docker compose exec app composer config http-basic.composer.fluxui.dev "$FLUX_USERNAME" "$FLUX_LICENSE_KEY"
```
Credentials are needed in CI (`lint.yml`, `tests.yml`).

## Quirks

- If frontend changes aren't reflected, run `npm run build` or restart `docker compose exec app composer run dev`.
- Vite error "Unable to locate file in Vite manifest" → run `npm run build`.
- `post-update-cmd` runs `boost:update` — may require database setup.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- filament/filament (FILAMENT) - v5
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- livewire/flux (FLUXUI_FREE) - v2
- livewire/livewire (LIVEWIRE) - v4
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands via `docker compose exec app php artisan` (e.g., `docker compose exec app php artisan route:list`). Use `docker compose exec app php artisan list` to discover available commands and `docker compose exec app php artisan [command] --help` to check parameters.
- Inspect routes with `docker compose exec app php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `docker compose exec app php artisan config:show app.name`, `docker compose exec app php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `docker compose exec app php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `docker compose exec app php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `docker compose exec app php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `docker compose exec app php artisan list` and check their parameters with `docker compose exec app php artisan [command] --help`.
- If you're creating a generic PHP class, use `docker compose exec app php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `docker compose exec app php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `docker compose exec app php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` (host) or ask the user to run `composer run dev`.

=== livewire/core rules ===

# Livewire

- Livewire allow to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `docker compose exec app vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `docker compose exec app vendor/bin/pint --test --format agent`, simply run `docker compose exec app vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `docker compose exec app php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `docker compose exec app php artisan make:test --pest SomeFeatureTest` instead of `docker compose exec app php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `docker compose exec app php artisan test --compact` or filter: `docker compose exec app php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>
