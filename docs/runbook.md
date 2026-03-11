# Runbook

## Local execution

```bash
cp .env.example .env
docker compose up --build -d
docker compose exec app php bin/console app:database:migrate
```

Stack endpoints:

```text
web: http://127.0.0.1:8081/
db: mysql://showoff:showoff@127.0.0.1:3307/showoff
```

Optional port overrides:

```bash
WEB_EXPOSE_PORT=8090 DB_EXPOSE_PORT=3310 docker compose up --build -d
```

Composer package graph:

```bash
composer show showoff/*
```

Expected modules include `showoff/domain` and `showoff/persistence`.

CLI commands:

```bash
docker compose exec app php bin/console list
docker compose exec app php bin/console app:about
docker compose exec app php bin/console app:config:dump
docker compose exec app php bin/console app:database:status
docker compose exec app php bin/console app:database:migrate
docker compose exec app php bin/console app:security:create-user admin@example.com 'VeryStrongPassword123!' admin
docker compose exec app php bin/console app:worker:contact-events --limit=50
docker compose exec app php bin/console app:health:check
```

Local execution requires PHP 8.5+.

HTTP checks:

```bash
curl -i http://127.0.0.1:8081/
curl -i http://127.0.0.1:8081/contact
curl -i http://127.0.0.1:8081/preferences
curl -i http://127.0.0.1:8081/login
curl -i http://127.0.0.1:8081/admin
curl -i http://127.0.0.1:8081/api/v1/contact-submissions
curl -i -X POST http://127.0.0.1:8081/api/graphql -H 'Content-Type: application/json' -d '{"query":"{ contactSubmissionStats { count latest { id email } } }"}'
curl -i http://127.0.0.1:8081/api/v1/contact-submissions -H 'If-None-Match: "<etag-from-first-response>"'
curl -i -X POST http://127.0.0.1:8081/api/v1/contact-submissions -H 'Authorization: Bearer <token>' -H 'Content-Type: application/json' -H 'Idempotency-Key: demo-key-1' -d '{"name":"Ada Lovelace","email":"ada@example.com","message":"Performance stage payload."}'
```

Container inspection:

```bash
docker compose ps
docker compose exec app sh
docker compose exec db mysql -ushowoff -pshowoff -e 'SHOW DATABASES;'
docker compose exec db mysql -ushowoff -pshowoff -Dshowoff -e 'SHOW TABLES;'
docker compose exec db mysql -ushowoff -pshowoff -Dshowoff -e 'SHOW INDEX FROM contact_submissions;'
docker compose exec redis redis-cli ping
docker compose exec rabbitmq rabbitmqctl list_queues
```

The app container runs `php-fpm` as PID 1. Nginx is the public entrypoint.

## Railway

The repository is Dockerfile-driven and includes `railway.toml`. Railway can build and run the application directly from the repository root.
