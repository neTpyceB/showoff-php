# AGENTS

## Project identity

- Name: `showoff-php/foundational-core`
- Topic: `HTTP Fundamentals & Server Interaction`
- Current stage: controlled web layer
- PHP target: `8.5`

## Active architecture constraints

- Keep scope limited to HTTP fundamentals: request/response, routing, sessions, cookies, and form handling.
- Prefer strict typing, readonly value objects, and explicit validation.
- Avoid introducing ORM, queues, APIs, or framework-heavy abstractions before they become relevant to a later stage.
- Keep controllers thin and move logic into testable services.

## Current modules

- `src/Bootstrap`: console and HTTP application assembly
- `src/Config`: environment parsing and immutable config
- `src/Console`: CLI commands
- `src/Http`: web kernel, controllers, routing, sessions, forms, views
- `src/Health`: runtime and filesystem checks

## Quality gates

- PHPUnit coverage for generated logical code
- PHPStan at max level
- PHP CS Fixer with strict typing rule set
- GitHub Actions CI running tests, analysis, and standards

## Run commands

```bash
composer install
composer test
composer analyse
composer cs:check
php bin/app list
php -S 127.0.0.1:8080 -t public public/index.php
```
