# Architecture

## Scope

This stage implements a reproducible container runtime on top of the existing application foundation:

- strict typing and modern PHP 8.5 conventions
- PHP-FPM application runtime
- Nginx reverse proxy and FastCGI handoff
- MySQL service for local infrastructure parity
- service-scoped environment separation
- persistent volumes and health checks
- validated environment-driven configuration
- runtime health checks and CLI diagnostics remain available

## Modules

### `docker/`

Contains PHP-FPM and Nginx runtime configuration.

### `env/`

Contains service-specific environment files for the compose stack.

### `src/Bootstrap`

Builds both the console application and the HTTP kernel wiring.

### `src/Config`

Defines immutable application configuration, environment parsing, validation, and safe redaction for diagnostics.

### `src/Console`

Contains CLI commands for runtime metadata, effective configuration inspection, and health validation.

### `src/Http`

Contains the web kernel, controllers, routing, session orchestration, view rendering, and form processing.

### `src/Health`

Encapsulates runtime and filesystem checks behind small interfaces to keep the core logic fully testable.

## Extension path

Future stages can add persistence and infrastructure integrations without replacing the runtime topology again.
