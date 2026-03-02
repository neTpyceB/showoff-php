# AGENTS

## Project identity

- Name: `showoff-php/foundational-core`
- Topic: `Persistence Layer & Database Architecture`
- Current stage: PDO-backed persistence with migrations and repositories
- PHP target: `8.5`

## Active architecture constraints

- Keep scope limited to persistence concerns: database config, migrations, repositories, transactions, relational modeling.
- Prefer strict typing, readonly value objects, and explicit validation.
- Avoid introducing ORM-heavy abstractions before they become relevant to a later stage.
- Keep controllers thin and move logic into testable services.

## Current modules

- `packages/config`: environment parsing and immutable config
- `packages/health`: runtime and filesystem checks
- `packages/console`: CLI commands
- `packages/http`: web kernel, controllers, routing, sessions, forms, views
- `packages/persistence`: PDO connections, migrations, repositories, transactions
- `src/Bootstrap`: root application assembly
- `docker/`: PHP-FPM and Nginx runtime configuration
- `env/`: service-scoped environment files

## Quality gates

- PHPUnit coverage for generated logical code
- PHPStan at max level
- PHP CS Fixer with strict typing rule set
- GitHub Actions CI running tests, analysis, and standards

## Run commands

```bash
composer test
composer analyse
composer cs:check
docker compose up --build -d
docker compose exec app php bin/app list
docker compose exec app php bin/app app:database:migrate
composer show showoff/*
```
