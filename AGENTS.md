# AGENTS

## Project identity

- Name: `showoff-php/foundational-core`
- Topic: `Advanced Symfony Features & Ecosystem Capabilities`
- Current stage: showcase module with custom bundle, compiler pass, tagged services, kernel events/middleware, forms, validators, voters, serializer customization, and Messenger integration
- PHP target: `8.5`

## Active architecture constraints

- Keep scope limited to advanced Symfony framework capabilities in a cohesive application module.
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
- `src/Module/Contact`: module-level contact public API contracts and implementations
- `src/Module/Analytics`: module-level analytics public API contracts and implementations
- `src/Messaging`: queue message contracts, publisher, consumer, handlers
- `src/Realtime`: realtime publishing contract with Mercure adapter
- `src/Showcase`: advanced Symfony showcase module (bundle, DI extension/compiler pass, processors, voter, validator, form extension, serializer normalizer, messenger handlers)
- `src/Cache` + `src/Infrastructure/Cache`: cache abstraction and Redis implementation
- `src/Concurrency` + `src/Infrastructure/Lock`: distributed and test lock managers
- `src/Performance`: idempotency service, HTTP cache response service, request profiling subscriber
- `src/Observability`: request metrics aggregation and Prometheus output
- `src/Operations`: liveness/readiness operational checks
- `src/Http/Form`: request DTOs + Symfony validation attributes
- `src/Factory`: infrastructure factories wired through Symfony DI
- `docker/`: PHP-FPM and Nginx runtime configuration
- `env/`: service-scoped environment files
- `.github/workflows`: quality/build pipeline

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
curl -i http://127.0.0.1:8081/api/v1/analytics/contact-submissions
curl -i http://127.0.0.1:8081/api/v1/showcase/report
curl -i http://127.0.0.1:8081/api/v1/showcase/diagnostics -H 'X-Showcase-Roles: ROLE_ADMIN'
curl -i -X POST http://127.0.0.1:8081/api/v1/showcase/audit -H 'Content-Type: application/json' -d '{"action":"pipeline.started"}'
curl -i -X POST http://127.0.0.1:8081/api/v1/showcase/settings/validate -H 'Content-Type: application/json' -d '{"code":"valid-code-101","notes":"ok"}'
curl -i -X POST http://127.0.0.1:8081/api/graphql -H 'Content-Type: application/json' -d '{"query":"{ contactSubmissionStats { count } }"}'
curl -i -X POST http://127.0.0.1:8081/api/graphql -H 'Content-Type: application/json' -d '{"query":"{ contactSubmissionProcessing { processed lastEmail lastOccurredAt } }"}'
curl -i http://127.0.0.1:8081/health/live
curl -i http://127.0.0.1:8081/health/ready
curl -i http://127.0.0.1:8081/metrics
curl -i http://127.0.0.1:8081/api/v1/contact-submissions -H 'If-None-Match: "<etag>"'
curl -i -X POST http://127.0.0.1:8081/api/v1/contact-submissions -H 'Authorization: Bearer <token>' -H 'Content-Type: application/json' -H 'Idempotency-Key: demo-key-1' -d '{"name":"Ada Lovelace","email":"ada@example.com","message":"Performance stage request body."}'
docker compose exec app php bin/console app:security:create-user admin@example.com 'VeryStrongPassword123!' admin
docker compose exec app php bin/console app:worker:contact-events --limit=50
docker compose exec app php bin/console app:showcase:pipeline
docker build --target production -t showoff-php:prod .
composer show showoff/*
```

## Security docs

- `docs/security-audit.md`
- `docs/security-roadmap.md`

## DevOps docs

- `docs/devops-cicd.md`
- `docs/enterprise-architecture.md`
- `docs/advanced-symfony-features.md`
