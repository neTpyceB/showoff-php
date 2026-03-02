# Runbook

## Local execution

```bash
cp .env.example .env
composer install
php -S 127.0.0.1:8080 -t public public/index.php
```

HTTP endpoints:

```text
/
/contact
/preferences
```

CLI commands:

```bash
php bin/app app:about
php bin/app app:config:dump
php bin/app app:health:check
```

Local execution requires PHP 8.5+.

## Docker

```bash
cp .env.example .env
docker compose up --build -d
docker compose exec app sh
docker compose exec app php bin/app app:health:check
curl -i http://127.0.0.1:8080/
curl -i http://127.0.0.1:8080/contact
```

The container runs the PHP built-in server as PID 1, so you can inspect the live process and execute commands interactively.

## Railway

The repository is Dockerfile-driven and includes `railway.toml`. Railway can build and run the application directly from the repository root.
