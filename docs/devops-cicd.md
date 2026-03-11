# DevOps, CI/CD & Deployment

## Pipelines

### CI (`.github/workflows/ci.yml`)

- Runs PHPUnit, PHPStan, and PHP CS Fixer checks.
- Validates Docker Compose configuration.
- Builds production Docker image (`target: production`) with Buildx caching.
- Pushes image to GHCR on `main`/`master` pushes.

### Railway deploy (`.github/workflows/deploy-railway.yml`)

- Supports `workflow_dispatch` and `main`/`master` push deployment.
- Uses Railway CLI (`railway up`) and deploys from repository Dockerfile.
- Requires these secrets:
  - `RAILWAY_TOKEN`
  - `RAILWAY_PROJECT_ID`
  - `RAILWAY_ENVIRONMENT_ID`
  - `RAILWAY_SERVICE_NAME` (optional)

## Container build strategy

- `Dockerfile` provides two targets:
  - `development`: includes dev dependencies and debug PHP config.
  - `production`: optimized for runtime (`--no-dev`, classmap authoritative, opcache enabled).
- Local `docker-compose.yml` explicitly builds `target: development`.
- Production deployments (Railway/CI image build) use `target: production`.

## Environment configuration

- Local baseline: `.env.example`
- Container-local runtime: `env/app.env`
- Production baseline template: `env/app.prod.env.example`
- Production-only framework overrides: `config/packages/prod/framework.yaml`

## Logging and monitoring

- Structured request logs emitted via `RequestProfilingSubscriber` with:
  - request id
  - method/path/status
  - duration and memory delta
  - slow-request marker
- Operational endpoints:
  - `GET /health/live`
  - `GET /health/ready`
  - `GET /metrics` (Prometheus text format)
- Metrics endpoint supports optional protection using `OBSERVABILITY_METRICS_TOKEN` + `X-Metrics-Token`.
