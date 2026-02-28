# Runbook

## Local execution

```bash
cp .env.example .env
composer install
php bin/app app:about
php bin/app app:config:dump
php bin/app app:health:check
```

Local execution requires PHP 8.5+.

## Docker

```bash
cp .env.example .env
docker compose up --build
docker compose exec app sh
docker compose exec app php bin/app app:health:check
```

The container stays running by default with a long-lived foreground process so you can inspect it and execute commands interactively.

## Railway

The repository is Dockerfile-driven and includes `railway.toml`. Railway can build and run the application directly from the repository root.
