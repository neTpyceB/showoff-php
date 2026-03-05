# AGENTS

## Project identity

- Name: `showoff-php/foundational-core`
- Topic: `Design Patterns & Dependency Injection Architecture`
- Current stage: DI container, service configuration, factories, strategies, repositories
- PHP target: `8.5`

## Active architecture constraints

- Keep scope limited to DI/pattern concerns: container composition, factories, strategies, interface-driven services.
- Prefer strict typing, readonly value objects, and explicit validation.
- Keep bootstrap thin and move wiring into container/service config.
- Keep controllers thin and move logic into testable services.

## Current modules

- `packages/config`: environment parsing and immutable config
- `packages/health`: runtime and filesystem checks
- `packages/console`: CLI commands
- `packages/domain`: entities, value objects, repository interfaces, domain services
- `packages/http`: web kernel, controllers, routing, sessions, forms, views
- `packages/persistence`: PDO adapters, migrations, repositories, transaction boundary
- `src/Bootstrap`: root application assembly
- `src/Container`: DI container assembly
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
