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

## Testing policy

All non-trivial generated logic must ship with PHPUnit coverage. New runtime services or commands should include focused unit or command tests before merging.
