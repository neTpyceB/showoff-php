# Development

## Requirements

- PHP 8.5+
- Composer 2.8+
- Docker 29+

## Commands

```bash
composer install
composer test
composer analyse
composer cs:check
composer cs:fix
php -S 127.0.0.1:8080 -t public public/index.php
```

## Environment

Start from `.env.example` and override locally with `.env`.

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

## Testing policy

All non-trivial generated logic must ship with PHPUnit coverage. New web flows should include focused request/form/session tests before merging.
