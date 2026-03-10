# Architecture

## Scope

This stage implements security architecture foundations on top of the existing Symfony MVC + API codebase:

- strict typing and modern PHP 8.5 conventions
- session-based web authentication (`/login`, `/logout`)
- role-based authorization (`admin`, `user`)
- bearer token API protection for write operations
- encrypted persistence for sensitive user attributes
- password hashing with Argon2id
- dedicated security migration for users and API tokens

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

### `src/Application`

Application services used by controllers.

### `src/Http/Form`

Request DTOs with Symfony validation constraints.

### `src/Factory`

Factory services for infrastructure objects (`AppConfig`, `PDO`).

## Extension path

Future stages can scale by adding policy abstractions (voters/permissions), MFA, and external identity providers without rewriting domain/persistence layers.
