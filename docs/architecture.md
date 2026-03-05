# Architecture

## Scope

This stage implements dependency-injection-first architecture on top of the existing foundation:

- strict typing and modern PHP 8.5 conventions
- centralized DI container and service wiring
- factory pattern for console app, HTTP kernel, and Twig environment construction
- strategy pattern for request-driven submission source resolution
- repository interfaces with infrastructure adapters
- explicit interface aliases for runtime, sessions, transactions, and repositories
- thin bootstrap layer delegating composition to the container

## Modules

### `packages/config`

Configuration value objects, readers, validation, and redaction.

### `packages/health`

Runtime inspection and health reporting abstractions.

### `packages/console`

Console commands and CLI-facing behavior.

### `packages/http`

HTTP kernel, controllers, routing, sessions, forms, and view rendering.

### `packages/domain`

Entities, value objects, repository interfaces, and domain services.

### `packages/persistence`

PDO connection factory, transaction manager, migrations, repositories, and persistence services.

### `src/Bootstrap`

Contains composition factories used by service definitions.

### `src/Container`

Builds the application container and loads service configuration.

## Extension path

Future stages can scale by adding new strategies/factories/services through container definitions without rewriting entrypoints.
