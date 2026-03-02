# AGENTS

## Project identity

- Name: `showoff-php/foundational-core`
- Topic: `Dockerized PHP Execution Environment`
- Current stage: PHP-FPM + Nginx + MySQL runtime
- PHP target: `8.5`

## Active architecture constraints

- Keep scope limited to runtime/containerization concerns: PHP-FPM, Nginx, MySQL, env separation, reproducible setup.
- Prefer strict typing, readonly value objects, and explicit validation.
- Avoid introducing ORM, queues, APIs, or framework-heavy abstractions before they become relevant to a later stage.
- Keep controllers thin and move logic into testable services.

## Current modules

- `src/Bootstrap`: console and HTTP application assembly
- `src/Config`: environment parsing and immutable config
- `src/Console`: CLI commands
- `src/Http`: web kernel, controllers, routing, sessions, forms, views
- `src/Health`: runtime and filesystem checks
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
```
