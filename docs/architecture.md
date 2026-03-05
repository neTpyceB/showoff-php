# Architecture

## Scope

This stage implements Symfony MVC foundation on top of the existing modular codebase:

- strict typing and modern PHP 8.5 conventions
- Symfony Kernel + FrameworkBundle lifecycle
- attribute-routed controllers in `src/Controller`
- service wiring via `config/services.yaml`
- Twig rendering via `templates/`
- Symfony Validator-backed request DTO validation
- thin controllers delegating business behavior to application/domain services

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

### `src/Application`

Application services used by controllers.

### `src/Http/Form`

Request DTOs with Symfony validation constraints.

### `src/Factory`

Factory services for infrastructure objects (`AppConfig`, `PDO`).

## Extension path

Future stages can scale by adding bundles/services/events on top of this kernel without changing runtime entrypoints.
