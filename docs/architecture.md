# Architecture

## Scope

This stage implements DevOps, CI/CD, and production deployment foundations:

- strict typing and modern PHP 8.5 conventions
- multi-stage Docker image strategy (development and production targets)
- GitHub Actions quality and container build pipelines
- Railway deployment workflow and service configuration
- runtime observability endpoints (`/health/live`, `/health/ready`, `/metrics`)
- structured request logging with per-request correlation IDs
- request-level telemetry counters for monitoring ingestion

## Modules

### `packages/config`

Configuration value objects, readers, validation, and redaction.

### `packages/health`

Runtime inspection and health reporting abstractions.

### `packages/console`

Console commands and CLI-facing behavior.

### `packages/http`

HTTP support services shared with the Symfony app layer.

### `packages/domain`

Entities, value objects, repository interfaces, and domain services.

### `packages/persistence`

PDO connection factory, transaction manager, migrations, repositories, and persistence services.

### `src/Kernel.php`

Symfony application kernel and lifecycle integration.

### `src/Controller`

Framework controllers for home/contact/preferences flows.

### `src/Controller/Api`

REST and GraphQL HTTP entrypoints.

### `src/Api`

API layer request DTOs and GraphQL schema provider.

### `src/Security`

Authentication, authorization, role model, token issuance/validation, and crypto utilities.

### `src/Operations`

Operational readiness logic and deployment-facing health checks.

### `src/Observability`

Metrics aggregation and monitoring payload generation.

### `src/Messaging`

Message contracts, RabbitMQ publisher, queue consumer, and event handlers.

### `src/Cache` and `src/Infrastructure/Cache`

Cache abstraction with Redis adapter (plus test in-memory adapter).

### `src/Concurrency` and `src/Infrastructure/Lock`

Lock abstraction with Redis and in-memory implementations for idempotent, concurrency-safe write handling.

### `src/Performance`

Idempotency orchestration, JSON HTTP cache responder, and profiling/logging telemetry subscriber.

### `src/Application`

Application services used by controllers.

### `src/Http/Form`

Request DTOs with Symfony validation constraints.

### `src/Factory`

Factory services for infrastructure objects (`AppConfig`, `PDO`).

### `.github/workflows`

Quality gate pipeline (`ci.yml`) and Railway deployment workflow (`deploy-railway.yml`).

### `railway.toml`

Railway deployment runtime contract, healthcheck path, and startup command.

## Extension path

Future stages can scale by adding OpenTelemetry exporters, centralized log sinks, canary deploys, and rollback automation.
