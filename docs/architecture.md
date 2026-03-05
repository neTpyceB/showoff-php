# Architecture

## Scope

This stage implements object-oriented domain modeling on top of the existing foundation:

- strict typing and modern PHP 8.5 conventions
- explicit domain entities and value objects
- repository interfaces defined at the domain boundary
- domain service orchestrating use-case-level behavior
- infrastructure adapters implementing domain interfaces with PDO
- thin HTTP and bootstrap composition that depend on abstractions

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

Builds the root application by composing the reusable packages.

## Extension path

Future stages can add richer aggregates, application services, and messaging without crossing domain boundaries or leaking transport/database concerns into business logic.
