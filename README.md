# Showoff PHP Core

Strict-typed PHP 8.5 application for the `Dockerized PHP Execution Environment` stage. This iteration upgrades the runtime to a reproducible PHP-FPM, Nginx, and MySQL container stack while preserving the existing CLI and HTTP foundations.

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
docker compose up --build -d
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
docker compose exec app php bin/app app:health:check
```

## Tooling

```bash
composer test
composer analyse
composer cs:check
docker compose up --build -d
docker compose exec app sh
docker compose exec app php bin/app app:health:check
docker compose exec app php -m | grep pdo_mysql
docker compose exec db mysql -ushowoff -pshowoff -e 'SHOW DATABASES;'
```

Docker services:

```text
web: http://127.0.0.1:8080/
app: php-fpm on 9000 inside compose
db:  mysql on 127.0.0.1:3306
```

More detail lives in:

- [`docs/architecture.md`](docs/architecture.md)
- [`docs/development.md`](docs/development.md)
- [`docs/runbook.md`](docs/runbook.md)
