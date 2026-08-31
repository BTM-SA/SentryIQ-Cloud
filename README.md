# SentryIQ Cloud

SentryIQ Cloud is the extensible services layer for SentryIQ. Gallery is the first Cloud module.

## Gallery pipeline

Email attachment → validation → normalized WebP → SHA-256 duplicate detection → private storage → thumbnail → Unassigned → albums.

The Cloud project is intentionally separate from the SentryIQ Vault. Integration with SentryIQ will reuse its authentication, security, configuration, and logging architecture instead of creating a second security stack.

## Planned modules

- Gallery
- Notes (future)
- Contacts (future)
- Documents (future)

## Status

Foundation implementation in progress.
