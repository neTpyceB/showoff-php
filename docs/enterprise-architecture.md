# Enterprise Architecture & Distributed Evolution

## Scope delivered

- Modular monolith boundaries introduced through explicit module public APIs:
  - `App\Module\Contact\Api\ContactPublicApi`
  - `App\Module\Analytics\Api\AnalyticsPublicApi`
- REST and GraphQL adapters now depend on module contracts, not lower-level orchestration services.
- Async integration event contract hardened with versioned event metadata on `ContactSubmissionStoredMessage`.
- Realtime update publishing added through `RealtimePublisher` abstraction and Mercure-ready implementation.

## Module boundaries

- Contact module exposes:
  - `ContactSubmissionInput`
  - `ContactSubmissionView`
  - `ContactSubmissionStatsView`
  - `ContactPublicApi`
- Analytics module exposes:
  - `ContactSubmissionProcessingView`
  - `AnalyticsPublicApi`

These APIs form internal service boundaries suitable for extraction to independent services later without changing controller contracts.

## API boundaries

- New REST projection endpoint:
  - `GET /api/v1/analytics/contact-submissions`
- GraphQL query extension:
  - `contactSubmissionProcessing { processed lastEmail lastOccurredAt }`
- Existing contact submission REST/GraphQL behavior remains backward compatible.

## Realtime workflow

1. Contact submission is stored.
2. Async workflow publishes queue message (`contact.submission.stored`).
3. Consumer handles message and updates analytics projection cache.
4. Handler publishes realtime event through `RealtimePublisher`.
5. `MercureRealtimePublisher` posts update to configured Mercure hub topic.

## Environment variables

- `REALTIME_MERCURE_HUB_URL`
- `REALTIME_MERCURE_JWT`
- `REALTIME_CONTACT_TOPIC`
- `REALTIME_PUBLISH_TIMEOUT_MS`

When hub URL is empty, realtime publishing is a no-op.

## Run checks

```bash
docker compose exec app composer test
docker compose exec app composer analyse
docker compose exec app composer cs:check
```
