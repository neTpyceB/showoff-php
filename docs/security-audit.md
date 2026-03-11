# Security Audit

Audit date: `2026-03-11`

## Scope

- Web authentication/session flow (`/login`, `/logout`, `/admin`)
- Public API authentication/token issuance (`/api/v1/auth/token`)
- API protected write routes (`/api/v1/contact-submissions`, GraphQL mutations)
- Security-relevant HTTP response headers
- Secrets/tokens/password handling

## Findings and status

### Implemented hardening (closed)

1. CSRF protection enforced on state-changing web forms:
- Contact form
- Preferences form
- Login form
- Logout form

2. Failed-authentication throttling:
- Login endpoint rate-limited by client IP + email fingerprint
- API token issuance endpoint rate-limited by client IP + email fingerprint
- `Retry-After` returned on lockout responses

3. Security headers baseline applied globally:
- `Content-Security-Policy`
- `X-Content-Type-Options`
- `X-Frame-Options`
- `Referrer-Policy`
- `Permissions-Policy`
- `Cross-Origin-Opener-Policy`
- `Cross-Origin-Resource-Policy`
- `Strict-Transport-Security` on secure requests

4. API bearer token parsing tightened:
- Strict bearer format validation
- Strict token format validation before DB lookup

5. Cookie hardening:
- `last_contact_email` and `theme` cookies switched to `HttpOnly`

## Current control inventory

- Password hashing: Argon2id
- Email-at-rest protection: encrypted ciphertext + deterministic hash lookup
- API token storage: HMAC hash in DB (raw token never persisted)
- Session fixation mitigation: session migration on successful login
- Role-based access control: explicit `Role` checks on protected routes
- Input validation: Symfony Validator on form/API DTOs
- SQL safety: prepared statements
- Concurrency safety: idempotency keys + lock-backed write guards

## Residual risks

1. No MFA for privileged login
2. No dedicated audit log/event sink for auth and authorization events
3. Token lifecycle is minimal (no refresh tokens/device-scoped management)
4. No WAF/rule-based anomaly detection in front of app
5. No formal secret-rotation workflow for `APP_SECRET`

## Validation status

- Automated tests cover CSRF flow, lockout flow, security headers, and existing auth/API protections.
- Static analysis and coding standards are passing in CI.
