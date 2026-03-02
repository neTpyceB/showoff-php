# Showoff PHP Core

Strict-typed PHP 8.5 application for the `Persistence Layer & Database Architecture` stage. This iteration adds a real PDO-backed data layer with migrations, repositories, transactions, and relational modeling on top of the existing modularized runtime.

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
│   ├── health
│   ├── http
│   └── persistence
├── railway.toml
├── templates
│   ├── layout
│   └── pages
├── src
│   └── Bootstrap
└── tests
    ├── Bootstrap
    ├── Config
    ├── Console
    ├── Http
    ├── Health
    └── Persistence
```

## Local run

```bash
cp .env.example .env
docker compose up --build -d
docker compose exec app php bin/app app:database:migrate
```

Open:

```text
http://127.0.0.1:8080/
http://127.0.0.1:8080/contact
http://127.0.0.1:8080/preferences
```

CLI tooling remains available:

```bash
docker compose exec app php bin/app list
docker compose exec app php bin/app app:about
docker compose exec app php bin/app app:config:dump
docker compose exec app php bin/app app:database:status
docker compose exec app php bin/app app:database:migrate
docker compose exec app php bin/app app:health:check
```

## Tooling

```bash
composer test
composer analyse
composer cs:check
docker compose up --build -d
docker compose exec app sh
docker compose exec app php bin/app app:database:migrate
docker compose exec app php bin/app app:health:check
docker compose exec app php -m | grep pdo_mysql
docker compose exec app php -m | grep pdo_sqlite
docker compose exec db mysql -ushowoff -pshowoff -e 'SHOW DATABASES;'
```

Docker services:

```text
web: http://127.0.0.1:8080/
app: php-fpm on 9000 inside compose
db:  mysql on 127.0.0.1:3306
```

Local packages:

```text
showoff/config
showoff/health
showoff/console
showoff/http
showoff/persistence
```

More detail lives in:

- [`docs/architecture.md`](docs/architecture.md)
- [`docs/development.md`](docs/development.md)
- [`docs/runbook.md`](docs/runbook.md)
