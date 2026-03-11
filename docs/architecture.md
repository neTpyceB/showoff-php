# Architecture

## Scope

This stage implements advanced Symfony framework showcase foundations:

- strict typing and modern PHP 8.5 conventions
- custom bundle + dependency injection extension + compiler pass
- tagged service processing pipeline and custom console command
- request/response kernel events and decorated HTTP kernel middleware
- custom validator constraint + voter + form type and form extension
- serializer normalizer customization and Messenger message handling

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

### `src/Realtime`

Realtime publisher abstraction and Mercure-ready adapter.

### `src/Cache` and `src/Infrastructure/Cache`

Cache abstraction with Redis adapter (plus test in-memory adapter).

### `src/Concurrency` and `src/Infrastructure/Lock`

Lock abstraction with Redis and in-memory implementations for idempotent, concurrency-safe write handling.

### `src/Performance`

Idempotency orchestration, JSON HTTP cache responder, and profiling/logging telemetry subscriber.

### `src/Application`

Application services used by controllers.

### `src/Module`

Bounded module public APIs and implementation adapters (`Contact`, `Analytics`).

### `src/Showcase`

Dedicated advanced Symfony showcase module with bundle internals, tagged processors, security voter, form extension, serializer normalizer, and Messenger integration.

### `src/Http/Form`

Request DTOs with Symfony validation constraints.

### `src/Factory`

Factory services for infrastructure objects (`AppConfig`, `PDO`).

## Extension path

Future stages can scale by extracting the showcase bundle into a reusable package and adding reusable recipes for modular Symfony platform teams.
