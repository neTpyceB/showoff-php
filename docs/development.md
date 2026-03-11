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
docker compose exec app php bin/console app:showcase:pipeline
docker compose exec app php -S 0.0.0.0:8080 -t public public/index.php
curl -i http://127.0.0.1:8081/api/v1/contact-submissions
curl -i http://127.0.0.1:8081/api/v1/analytics/contact-submissions
curl -i http://127.0.0.1:8081/api/v1/showcase/report
curl -i http://127.0.0.1:8081/api/v1/showcase/diagnostics -H 'X-Showcase-Roles: ROLE_ADMIN'
curl -i -X POST http://127.0.0.1:8081/api/v1/showcase/audit -H 'Content-Type: application/json' -d '{"action":"pipeline.started"}'
curl -i -X POST http://127.0.0.1:8081/api/v1/showcase/settings/validate -H 'Content-Type: application/json' -d '{"code":"valid-code-101","notes":"ok"}'
curl -i -X POST http://127.0.0.1:8081/api/graphql -H 'Content-Type: application/json' -d '{"query":"{ contactSubmissionStats { count } }"}'
curl -i -X POST http://127.0.0.1:8081/api/graphql -H 'Content-Type: application/json' -d '{"query":"{ contactSubmissionProcessing { processed lastEmail lastOccurredAt } }"}'
curl -i http://127.0.0.1:8081/api/v1/contact-submissions -H 'If-None-Match: "<etag>"'
curl -i -X POST http://127.0.0.1:8081/api/v1/contact-submissions -H 'Authorization: Bearer <token>' -H 'Content-Type: application/json' -H 'Idempotency-Key: demo-key-1' -d '{"name":"Ada Lovelace","email":"ada@example.com","message":"Performance stage payload."}'
curl -i http://127.0.0.1:8081/health/live
curl -i http://127.0.0.1:8081/health/ready
curl -i http://127.0.0.1:8081/metrics
docker build --target production -t showoff-php:prod .
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
- `REDIS_DSN`
- `RABBITMQ_HOST`
- `RABBITMQ_PORT`
- `RABBITMQ_USER`
- `RABBITMQ_PASSWORD`
- `RABBITMQ_VHOST`
- `RABBITMQ_QUEUE`
- `PERFORMANCE_HTTP_CACHE_MAX_AGE`
- `PERFORMANCE_IDEMPOTENCY_TTL_SECONDS`
- `PERFORMANCE_LOCK_TTL_SECONDS`
- `PERFORMANCE_SLOW_REQUEST_MS`
- `OBSERVABILITY_STRUCTURED_LOGGING_ENABLED`
- `OBSERVABILITY_METRICS_TOKEN`
- `REALTIME_MERCURE_HUB_URL`
- `REALTIME_MERCURE_JWT`
- `REALTIME_CONTACT_TOPIC`
- `REALTIME_PUBLISH_TIMEOUT_MS`
- `SECURITY_LOGIN_MAX_ATTEMPTS`
- `SECURITY_LOGIN_WINDOW_SECONDS`
- `SECURITY_API_TOKEN_MAX_ATTEMPTS`
- `SECURITY_API_TOKEN_WINDOW_SECONDS`

## Testing policy

All non-trivial generated logic must ship with PHPUnit coverage. Domain changes must include value object/service tests, and infrastructure changes must keep adapter tests green.

Functional Symfony coverage:

- `tests/Functional/SymfonyMvcFoundationTest.php`
- `tests/Functional/ApiLayerTest.php`
- `tests/Functional/EnterpriseArchitectureTest.php`
- `tests/Functional/AdvancedSymfonyFeaturesTest.php`
