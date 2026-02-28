# AGENTS

## Project identity

- Name: `showoff-php/foundational-core`
- Topic: `PHP Language Core & Runtime Basics`
- Current stage: foundational CLI runtime
- PHP target: `8.5`

## Active architecture constraints

- Keep scope limited to runtime/bootstrap/configuration concerns.
- Prefer strict typing, readonly value objects, and explicit validation.
- Avoid introducing HTTP, ORM, queue, cache, or framework-heavy layers before they become relevant to a later stage.
- Keep console commands thin and move logic into testable services.

## Current modules

- `src/Bootstrap`: console application assembly
- `src/Config`: environment parsing and immutable config
- `src/Console`: CLI commands
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
```
