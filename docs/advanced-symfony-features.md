# Advanced Symfony Features & Ecosystem Capabilities

## Showcase module

Dedicated module implemented under `src/Showcase` with framework-level integrations:

- Custom bundle: `App\Showcase\AdvancedShowcaseBundle`
- Configuration extension: `DependencyInjection/AdvancedShowcaseExtension`
- Compiler pass: `DependencyInjection/Compiler/ShowcaseProcessorCompilerPass`
- Tagged processors: `showcase.processor`

## Implemented capabilities

- Event listeners/subscribers:
  - `RequestContextListener` (`#[AsEventListener]`)
  - `ResponseHeaderSubscriber` (`EventSubscriberInterface`)
- Kernel middleware:
  - `ShowcaseKernelMiddleware` (`http_kernel` decorator)
- Console command:
  - `app:showcase:pipeline`
- Custom validator:
  - `LowercaseCode` + `LowercaseCodeValidator`
- Form system extension:
  - `ShowcaseSettingsType`
  - `TrimmedTextTypeExtension`
- Voter:
  - `ShowcaseCapabilityVoter`
  - `ShowcaseAccessDecider`
- Serializer customization:
  - `ShowcaseReportNormalizer`
- Messenger integration:
  - `ShowcaseAuditMessage`
  - `ShowcaseAuditMessageHandler`
- Controller module endpoints:
  - `GET /api/v1/showcase/report`
  - `GET /api/v1/showcase/diagnostics`
  - `POST /api/v1/showcase/audit`
  - `POST /api/v1/showcase/settings/validate`

## Bundle configuration

`config/packages/advanced_showcase.yaml`:

- `module_name`
- `enforce_diagnostics_access`

## Validation commands

```bash
docker compose exec app composer test
docker compose exec app composer analyse
docker compose exec app composer cs:check
docker compose exec app php bin/console app:showcase:pipeline
```
