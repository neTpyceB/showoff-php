# Runbook

## Local execution

```bash
cp .env.example .env
docker compose up --build -d
docker compose exec app php bin/app app:database:migrate
```

Stack endpoints:

```text
web: http://127.0.0.1:8080/
db: mysql://showoff:showoff@127.0.0.1:3306/showoff
```

Composer package graph:

```bash
composer show showoff/*
```

CLI commands:

```bash
docker compose exec app php bin/app list
docker compose exec app php bin/app app:about
docker compose exec app php bin/app app:config:dump
docker compose exec app php bin/app app:database:status
docker compose exec app php bin/app app:database:migrate
docker compose exec app php bin/app app:health:check
```

Local execution requires PHP 8.5+.

HTTP checks:

```bash
curl -i http://127.0.0.1:8080/
curl -i http://127.0.0.1:8080/contact
curl -i http://127.0.0.1:8080/preferences
```

Container inspection:

```bash
docker compose ps
docker compose exec app sh
docker compose exec db mysql -ushowoff -pshowoff -e 'SHOW DATABASES;'
docker compose exec db mysql -ushowoff -pshowoff -Dshowoff -e 'SHOW TABLES;'
```

The app container runs `php-fpm` as PID 1. Nginx is the public entrypoint.

## Railway

The repository is Dockerfile-driven and includes `railway.toml`. Railway can build and run the application directly from the repository root.
