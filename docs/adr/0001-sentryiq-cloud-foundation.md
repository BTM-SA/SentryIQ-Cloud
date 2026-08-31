# ADR 0001: SentryIQ Cloud Foundation

## Status
Proposed

## Problem
The existing gallery application contains useful photo-ingestion and album-management functionality, but its authentication, configuration, JSON persistence, and image delivery are independent of SentryIQ.

## Decision
Build SentryIQ Cloud as a separate repository and make Gallery its first module. Reuse SentryIQ's established security and application framework at integration time rather than introducing a second authentication/security stack.

Gallery media will be stored outside the web root. Uploaded images will be validated, normalized to WebP, identified by content hash, and represented by an internal identifier. Album assignment is application metadata, not filesystem identity.

## Reasoning
This preserves the working SentryIQ Vault while giving Cloud services an extensible boundary. It also avoids carrying forward the gallery's filename-only duplicate detection, Base64 image embedding, and standalone admin authentication.

## Consequences
- Gallery can evolve independently during development.
- Integration requires a defined SentryIQ module/service boundary.
- Existing gallery data may require migration if retained.
- Future Cloud modules can use the same foundation.
