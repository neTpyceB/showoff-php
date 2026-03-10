# Architecture

## Scope

This stage implements public API foundations on top of the existing Symfony MVC codebase:

- strict typing and modern PHP 8.5 conventions
- REST endpoints under `/api/v1/...`
- GraphQL endpoint at `/api/graphql`
- API request validation via Symfony Validator DTOs
- GraphQL schema/resolvers mapped to application services
- domain persistence/repositories reused across web and API surfaces

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

### `src/Application`

Application services used by controllers.

### `src/Http/Form`

Request DTOs with Symfony validation constraints.

### `src/Factory`

Factory services for infrastructure objects (`AppConfig`, `PDO`).

## Extension path

Future stages can scale by versioning APIs (`/api/v2`) and extending GraphQL types/resolvers without rewriting domain/persistence layers.
