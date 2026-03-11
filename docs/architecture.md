# Architecture

## Scope

This stage implements performance and scalability foundations:

- strict typing and modern PHP 8.5 conventions
- request-level profiling via Symfony kernel events
- HTTP response caching with ETag and conditional requests
- idempotent API writes backed by lock-based concurrency control
- Redis-backed lock and cache implementations with test-safe in-memory adapters
- migration-driven database index tuning for read-heavy contact submission queries

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

### `src/Messaging`

Message contracts, RabbitMQ publisher, queue consumer, and event handlers.

### `src/Cache` and `src/Infrastructure/Cache`

Cache abstraction with Redis adapter (plus test in-memory adapter).

### `src/Concurrency` and `src/Infrastructure/Lock`

Lock abstraction with Redis and in-memory implementations for idempotent, concurrency-safe write handling.

### `src/Performance`

Idempotency orchestration, JSON HTTP cache responder, and request profiling subscriber.

### `src/Application`

Application services used by controllers.

### `src/Http/Form`

Request DTOs with Symfony validation constraints.

### `src/Factory`

Factory services for infrastructure objects (`AppConfig`, `PDO`).

## Extension path

Future stages can scale by adding read replicas, advanced query telemetry, adaptive cache TTL policies, and distributed tracing integration.
