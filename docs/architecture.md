# Architecture

## Scope

This stage implements the foundational runtime layer only:

- strict typing and modern PHP 8.5 conventions
- deterministic CLI bootstrap
- validated environment-driven configuration
- runtime health checks
- testable command surface

## Modules

### `src/Bootstrap`

Builds the console application and wires runtime dependencies.

### `src/Config`

Defines immutable application configuration, environment parsing, validation, and safe redaction for diagnostics.

### `src/Console`

Contains CLI commands for runtime metadata, effective configuration inspection, and health validation.

### `src/Health`

Encapsulates runtime and filesystem checks behind small interfaces to keep the core logic fully testable.

## Extension path

Future stages can add HTTP delivery, persistence, messaging, or framework integration without rewriting the bootstrap and configuration core.
