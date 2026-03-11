# Showoff PHP Core

Strict-typed PHP 8.5 application for the `DevOps, CI/CD & Production Deployment` stage. This iteration adds automated pipelines, production container builds, operational health/metrics endpoints, structured request logging, and Railway deployment automation.

## Project structure

```text
.
├── .github/workflows/ci.yml
├── .github/workflows/deploy-railway.yml
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
│   ├── app.prod.env.example
│   └── mysql.env
├── docs
│   ├── architecture.md
│   ├── devops-cicd.md
│   ├── development.md
│   ├── security-audit.md
│   ├── security-roadmap.md
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
│   ├── Api
│   ├── Cache
│   ├── Concurrency
│   ├── Controller
│   ├── Factory
│   ├── Http
│   ├── Observability
│   ├── Operations
│   ├── Performance
│   ├── Messaging
│   ├── Security
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
http://127.0.0.1:8081/api/v1/contact-submissions
POST http://127.0.0.1:8081/api/graphql
http://127.0.0.1:8081/login
http://127.0.0.1:8081/admin
http://127.0.0.1:8081/health/live
http://127.0.0.1:8081/health/ready
http://127.0.0.1:8081/metrics
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
docker compose exec app php bin/console app:security:create-user admin@example.com 'VeryStrongPassword123!' admin
docker compose exec app php bin/console app:worker:contact-events --limit=50
docker compose exec app php bin/console app:health:check
curl -i http://127.0.0.1:8081/api/v1/contact-submissions
curl -i -X POST http://127.0.0.1:8081/api/graphql -H 'Content-Type: application/json' -d '{"query":"{ contactSubmissionStats { count latest { id email } } }"}'
curl -i http://127.0.0.1:8081/health/live
curl -i http://127.0.0.1:8081/health/ready
curl -i http://127.0.0.1:8081/metrics
curl -i http://127.0.0.1:8081/api/v1/contact-submissions -H 'If-None-Match: "<etag-from-previous-response>"'
curl -i -X POST http://127.0.0.1:8081/api/v1/contact-submissions -H 'Authorization: Bearer <token>' -H 'Content-Type: application/json' -H 'Idempotency-Key: demo-key-1' -d '{"name":"Ada Lovelace","email":"ada@example.com","message":"Deployment stage idempotency payload."}'
docker build --target production -t showoff-php:prod .
docker compose exec app php -m | grep pdo_mysql
docker compose exec app php -m | grep pdo_sqlite
docker compose exec db mysql -ushowoff -pshowoff -e 'SHOW DATABASES;'
```

Docker services:

```text
web: http://127.0.0.1:8081/
app: php-fpm on 9000 inside compose
db:  mysql on 127.0.0.1:3307
redis: redis on 127.0.0.1:6380
rabbitmq: amqp on 127.0.0.1:5673, management on 127.0.0.1:15673
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
- [`docs/devops-cicd.md`](docs/devops-cicd.md)
- [`docs/development.md`](docs/development.md)
- [`docs/runbook.md`](docs/runbook.md)
- [`docs/security-audit.md`](docs/security-audit.md)
- [`docs/security-roadmap.md`](docs/security-roadmap.md)
