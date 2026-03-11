# Security Roadmap

## Near-term improvements

1. Add account lock notifications and structured security logs for:
- failed login lockouts
- failed API token issuance lockouts
- access denied events

2. Add secure password policy enforcement in all user-management write paths:
- minimum length + entropy checks
- compromised-password list check

3. Add token lifecycle management:
- revoke endpoint
- list active tokens by user
- rotate token support

4. Add CSRF one-time token rotation on successful POST to reduce replay window.

5. Introduce origin checks for browser POST endpoints as a defense-in-depth layer.

## Mid-term improvements

1. Add MFA (TOTP/WebAuthn) for admin role.
2. Add IP/device-aware risk scoring and adaptive challenge policies.
3. Add optional signed JWT access tokens with short TTL and key rotation.
4. Add centralized audit trail storage with immutable append-only events.
5. Add secret management integration for production (external KMS/vault).

## Longer-term security features

1. Fine-grained permission model beyond role enum (`admin`, `user`).
2. Policy-as-code authorization (resource/action-based checks).
3. Security observability stack:
- anomaly detection alerts
- threat dashboards
- incident runbooks with automated enrichments

4. Supply-chain controls:
- dependency vulnerability gates in CI
- SBOM generation and attestations
- signed container image policy
