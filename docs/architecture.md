# Architecture

## Scope

This stage implements async processing, messaging, and caching foundations:

- strict typing and modern PHP 8.5 conventions
- Redis-backed cache abstraction for API submission stats
- cache invalidation on writes through application workflow service
- RabbitMQ message publishing after contact submission writes
- queue consumer + worker command for background processing
- asynchronous handler updating analytics cache keys

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

### `src/Application`

Application services used by controllers.

### `src/Http/Form`

Request DTOs with Symfony validation constraints.

### `src/Factory`

Factory services for infrastructure objects (`AppConfig`, `PDO`).

## Extension path

Future stages can scale by adding multiple queues/exchanges, retry/dead-letter policies, and richer cache domains.
