# AGENTS

## Project identity

- Name: `showoff-php/foundational-core`
- Topic: `Application Security Architecture`
- Current stage: Authentication, authorization, role model, secure sessions, bearer-token API protection, encryption
- PHP target: `8.5`

## Active architecture constraints

- Keep scope limited to security concerns: authentication flows, authorization policies, secure session/token handling, and encryption.
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
- `src/Controller/Security`: login/logout/admin security controllers
- `src/Application`: application services used by controllers
- `src/Api`: API-layer request/schema components
- `src/Security`: role model, authentication services, token services, crypto helpers
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
docker compose exec app php bin/console app:security:create-user admin@example.com 'VeryStrongPassword123!' admin
composer show showoff/*
```
