# Showoff PHP Core

Strict-typed PHP 8.5 application for the `Symfony Framework Integration (MVC Foundation)` stage. This iteration migrates to Symfony Kernel lifecycle with controllers, services, Twig rendering, and validation.

## Project structure

```text
.
├── .github/workflows/ci.yml
├── AGENTS.md
├── Dockerfile
├── README.md
├── bin/app
├── bin/console
├── composer.json
├── config
│   ├── bootstrap.php
│   ├── bundles.php
│   ├── packages
│   ├── routes.yaml
│   └── services.yaml
├── docker
│   ├── nginx
│   └── php
├── docker-compose.yml
├── env
│   ├── app.env
│   └── mysql.env
├── docs
│   ├── architecture.md
│   ├── development.md
│   └── runbook.md
├── public/index.php
├── phpstan.neon.dist
├── phpunit.xml
├── packages
│   ├── config
│   ├── console
│   ├── domain
│   ├── health
│   ├── http
│   └── persistence
├── railway.toml
├── src
│   ├── Application
│   ├── Controller
│   ├── Factory
│   ├── Http
│   └── Kernel.php
├── templates
│   ├── layout
│   └── pages
└── tests
    ├── Bootstrap
    ├── Config
    ├── Console
    ├── Domain
    ├── Functional
    ├── Http
    ├── Health
    └── Persistence
```

## Local run

```bash
cp .env.example .env
docker compose up --build -d
docker compose exec app php bin/console app:database:migrate
```

Open:

```text
http://127.0.0.1:8081/
http://127.0.0.1:8081/contact
http://127.0.0.1:8081/preferences
```

Port overrides (if needed):

```bash
WEB_EXPOSE_PORT=8090 DB_EXPOSE_PORT=3310 docker compose up --build -d
```

CLI tooling remains available:

```bash
docker compose exec app php bin/console list
docker compose exec app php bin/console app:about
docker compose exec app php bin/console app:config:dump
docker compose exec app php bin/console app:database:status
docker compose exec app php bin/console app:database:migrate
docker compose exec app php bin/console app:health:check
```

## Tooling

```bash
composer test
composer analyse
composer cs:check
docker compose up --build -d
docker compose exec app sh
docker compose exec app php bin/console app:database:migrate
docker compose exec app php bin/console app:health:check
docker compose exec app php -m | grep pdo_mysql
docker compose exec app php -m | grep pdo_sqlite
docker compose exec db mysql -ushowoff -pshowoff -e 'SHOW DATABASES;'
```

Docker services:

```text
web: http://127.0.0.1:8081/
app: php-fpm on 9000 inside compose
db:  mysql on 127.0.0.1:3307
```

Local packages:

```text
showoff/config
showoff/health
showoff/console
showoff/domain
showoff/http
showoff/persistence
```

More detail lives in:

- [`docs/architecture.md`](docs/architecture.md)
- [`docs/development.md`](docs/development.md)
- [`docs/runbook.md`](docs/runbook.md)
