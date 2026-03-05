# AGENTS

## Project identity

- Name: `showoff-php/foundational-core`
- Topic: `Object-Oriented Domain Modeling`
- Current stage: strict domain layer with entities, value objects, services, and repository interfaces
- PHP target: `8.5`

## Active architecture constraints

- Keep scope limited to domain modeling concerns: entities, value objects, services, interfaces, and strict boundaries.
- Prefer strict typing, readonly value objects, and explicit validation.
- Keep transport, framework, and persistence details out of domain classes.
- Keep controllers thin and move logic into testable services.

## Current modules

- `packages/config`: environment parsing and immutable config
- `packages/health`: runtime and filesystem checks
- `packages/console`: CLI commands
- `packages/domain`: entities, value objects, repository interfaces, domain services
- `packages/http`: web kernel, controllers, routing, sessions, forms, views
- `packages/persistence`: PDO adapters, migrations, repositories, transaction boundary
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
