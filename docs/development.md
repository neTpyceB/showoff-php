# Development

## Requirements

- PHP 8.5+
- Composer 2.8+
- Docker 29+

## Commands

```bash
composer test
composer analyse
composer cs:check
composer cs:fix
docker compose up --build -d
docker compose exec app sh
docker compose exec app php bin/console app:database:status
docker compose exec app php bin/console app:database:migrate
docker compose exec app php bin/console app:security:create-user admin@example.com 'VeryStrongPassword123!' admin
docker compose exec app php bin/console app:worker:contact-events --limit=50
curl -i http://127.0.0.1:8081/api/v1/contact-submissions
curl -i -X POST http://127.0.0.1:8081/api/graphql -H 'Content-Type: application/json' -d '{"query":"{ contactSubmissionStats { count } }"}'
```

## Environment

Start from `.env.example` and override locally with `.env`.

Container runtime uses:

- `env/app.env`
- `env/mysql.env`

Local packages:

- `packages/config`
- `packages/health`
- `packages/console`
- `packages/domain`
- `packages/http`
- `packages/persistence`

Key variables:

- `APP_NAME`
- `APP_CLI_NAME`
- `APP_ENV`
- `APP_DEBUG`
- `APP_TIMEZONE`
- `APP_CACHE_DIR`
- `APP_LOG_LEVEL`
- `APP_SECRET`
- `APP_BUILD_COMMIT`
- `APP_URL`
- `APP_SESSION_NAME`
- `APP_SESSION_COOKIE_SECURE`
- `DATABASE_HOST`
- `DATABASE_DRIVER`
- `DATABASE_PORT`
- `DATABASE_NAME`
- `DATABASE_USER`
- `DATABASE_PASSWORD`
- `DATABASE_CHARSET`

## Testing policy

All non-trivial generated logic must ship with PHPUnit coverage. Domain changes must include value object/service tests, and infrastructure changes must keep adapter tests green.

Functional Symfony coverage:

- `tests/Functional/SymfonyMvcFoundationTest.php`
- `tests/Functional/ApiLayerTest.php`
