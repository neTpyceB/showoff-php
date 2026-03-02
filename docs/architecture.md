# Architecture

## Scope

This stage implements the application web layer on top of the existing runtime foundation:

- strict typing and modern PHP 8.5 conventions
- front controller HTTP bootstrap
- explicit request routing and controller resolution
- response generation for HTML pages and redirects
- session-backed state and flash messaging
- cookie-backed preference persistence
- form processing with CSRF validation
- validated environment-driven configuration
- runtime health checks and CLI diagnostics remain available

## Modules

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

Future stages can add persistence, authentication, messaging, or framework integration without rewriting the current bootstrap, routing, or configuration core.
