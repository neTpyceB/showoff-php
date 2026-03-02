# Architecture

## Scope

This stage implements the first real data layer on top of the existing application foundation:

- strict typing and modern PHP 8.5 conventions
- environment-driven database configuration
- PDO connection management with explicit transaction boundaries
- migration execution and status reporting
- repository abstractions backed by MySQL-compatible SQL
- relational modeling for contact submissions and submission events
- thin HTTP and CLI adapters over testable persistence services

## Modules

### `packages/config`

Configuration value objects, readers, validation, and redaction.

### `packages/health`

Runtime inspection and health reporting abstractions.

### `packages/console`

Console commands and CLI-facing behavior.

### `packages/http`

HTTP kernel, controllers, routing, sessions, forms, and view rendering.

### `packages/persistence`

PDO connection factory, transaction manager, migrations, repositories, and persistence services.

### `src/Bootstrap`

Builds the root application by composing the reusable packages.

## Extension path

Future stages can layer Doctrine ORM/DBAL, richer aggregates, or async workflows onto the current repository and migration boundaries without rewriting the application entrypoints.
