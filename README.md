# Showoff PHP Core

Strict-typed PHP 8.5 application for the `HTTP Fundamentals & Server Interaction` stage. This iteration adds a controlled web layer with front controller dispatch, routing, request/response handling, sessions, cookies, and form processing while preserving the existing CLI/runtime foundation.

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
├── public/index.php
├── phpstan.neon.dist
├── phpunit.xml
├── railway.toml
├── templates
│   ├── layout
│   └── pages
├── src
│   ├── Bootstrap
│   ├── Config
│   ├── Console
│   ├── Http
│   └── Health
└── tests
    ├── Bootstrap
    ├── Config
    ├── Console
    ├── Http
    └── Health
```

## Local run

```bash
cp .env.example .env
composer install
php -S 127.0.0.1:8080 -t public public/index.php
```

Open:

```text
http://127.0.0.1:8080/
http://127.0.0.1:8080/contact
http://127.0.0.1:8080/preferences
```

CLI tooling remains available:

```bash
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
docker compose up --build -d
docker compose exec app sh
docker compose exec app php bin/app app:health:check
docker compose exec app php -r 'echo file_get_contents("http://127.0.0.1:8080/");'
```

Docker serves the web application on:

```text
http://127.0.0.1:8080/
```

More detail lives in:

- [`docs/architecture.md`](docs/architecture.md)
- [`docs/development.md`](docs/development.md)
- [`docs/runbook.md`](docs/runbook.md)
