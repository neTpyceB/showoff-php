# Showoff PHP Core

Strict-typed foundational PHP 8.5 CLI application for the `PHP Language Core & Runtime Basics` stage. The project establishes the base runtime, configuration model, project conventions, developer tooling, and automated verification for later stages.

## Project structure

```text
.
├── .github/workflows/ci.yml
├── AGENTS.md
├── Dockerfile
├── README.md
├── bin/app
├── composer.json
├── config/bootstrap.php
├── docker-compose.yml
├── docs
│   ├── architecture.md
│   ├── development.md
│   └── runbook.md
├── phpstan.neon.dist
├── phpunit.xml
├── railway.toml
├── src
│   ├── Bootstrap
│   ├── Config
│   ├── Console
│   └── Health
└── tests
    ├── Bootstrap
    ├── Config
    ├── Console
    └── Health
```

## Local run

```bash
cp .env.example .env
composer install
php bin/app list
php bin/app app:about
php bin/app app:config:dump
php bin/app app:health:check
```

## Tooling

```bash
composer test
composer analyse
composer cs:check
docker compose up --build
docker compose exec app sh
docker compose exec app php bin/app app:health:check
```

More detail lives in:

- [`docs/architecture.md`](docs/architecture.md)
- [`docs/development.md`](docs/development.md)
- [`docs/runbook.md`](docs/runbook.md)
