# AGENTS

## Project identity

- Name: `showoff-php/foundational-core`
- Topic: `DevOps, CI/CD & Production Deployment`
- Current stage: automated pipelines, multi-stage Docker runtime, observability endpoints, and Railway deployment workflow
- PHP target: `8.5`

## Active architecture constraints

- Keep scope limited to DevOps and production operations concerns: CI/CD, container build strategy, deployment, logging, monitoring, and runtime environment setup.
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
- `src/Security/Csrf`: CSRF token lifecycle for state-changing web forms
- `src/Security/RateLimit`: failed-auth throttling controls
- `src/Security/Http`: global response security headers
- `src/Messaging`: queue message contracts, publisher, consumer, handlers
- `src/Cache` + `src/Infrastructure/Cache`: cache abstraction and Redis implementation
- `src/Concurrency` + `src/Infrastructure/Lock`: distributed and test lock managers
- `src/Performance`: idempotency service, HTTP cache response service, request profiling subscriber
- `src/Observability`: request metrics aggregation and Prometheus output
- `src/Operations`: liveness/readiness operational checks
- `src/Http/Form`: request DTOs + Symfony validation attributes
- `src/Factory`: infrastructure factories wired through Symfony DI
- `docker/`: PHP-FPM and Nginx runtime configuration
- `env/`: service-scoped environment files
- `.github/workflows`: quality/build pipeline + Railway deployment workflow

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
curl -i http://127.0.0.1:8081/health/live
curl -i http://127.0.0.1:8081/health/ready
curl -i http://127.0.0.1:8081/metrics
curl -i http://127.0.0.1:8081/api/v1/contact-submissions -H 'If-None-Match: "<etag>"'
curl -i -X POST http://127.0.0.1:8081/api/v1/contact-submissions -H 'Authorization: Bearer <token>' -H 'Content-Type: application/json' -H 'Idempotency-Key: demo-key-1' -d '{"name":"Ada Lovelace","email":"ada@example.com","message":"Performance stage request body."}'
docker compose exec app php bin/console app:security:create-user admin@example.com 'VeryStrongPassword123!' admin
docker compose exec app php bin/console app:worker:contact-events --limit=50
docker build --target production -t showoff-php:prod .
composer show showoff/*
```

## Security docs

- `docs/security-audit.md`
- `docs/security-roadmap.md`

## DevOps docs

- `docs/devops-cicd.md`
