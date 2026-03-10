# AGENTS

## Project identity

- Name: `showoff-php/foundational-core`
- Topic: `API Layer (REST + GraphQL Foundations)`
- Current stage: Public API via REST endpoints and GraphQL schema on Symfony MVC foundation
- PHP target: `8.5`

## Active architecture constraints

- Keep scope limited to API concerns: REST endpoints, GraphQL schema/resolvers, request validation, and stable response contracts.
- Prefer strict typing, readonly value objects, and explicit validation.
- Keep framework entrypoints thin and move logic into testable services.
- Keep controllers thin and move logic into testable services.

## Current modules

- `packages/config`: environment parsing and immutable config
- `packages/health`: runtime and filesystem checks
- `packages/console`: CLI commands
- `packages/domain`: entities, value objects, repository interfaces, domain services
- `packages/http`: HTTP support services shared with application layer
- `packages/persistence`: PDO adapters, migrations, repositories, transaction boundary
- `src/Kernel.php`: Symfony application kernel
- `src/Controller`: Symfony controllers
- `src/Controller/Api`: REST + GraphQL API controllers
- `src/Application`: application services used by controllers
- `src/Api`: API-layer request/schema components
- `src/Http/Form`: request DTOs + Symfony validation attributes
- `src/Factory`: infrastructure factories wired through Symfony DI
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
docker compose exec app php bin/console list
docker compose exec app php bin/console app:database:migrate
curl -i http://127.0.0.1:8081/api/v1/contact-submissions
curl -i -X POST http://127.0.0.1:8081/api/graphql -H 'Content-Type: application/json' -d '{"query":"{ contactSubmissionStats { count } }"}'
composer show showoff/*
```
