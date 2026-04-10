# Kconecta CRM - Roadmap

## Baseline (2026-04-10)
- Repository connected and deployed on Dokploy.
- Production is operational with autodeploy on `main`.
- Home, login, panel, and main listings were verified.
- Property address flow uses `Places API (New)` in production.
- Property create flows by type were validated online.
- Property edit with media replacement was validated online.
- Views are protected against missing cover images.
- The hardened video flow is published on GitHub `main`, pending explicit post-deploy verification.

## Phase 1 - Stabilize Production
- Close the short audit before Gala tests online.
- Execute a guided test with Gala for:
- property create
- property edit
- placeholder behavior when cover image is missing
- Verify the already-published video UX:
- real payload summary
- browser optimization and auto-trim
- final backend validation
- Review whether service create needs the same hardening as property create.

## Phase 2 - Security Hardening
- Rotate production credentials and application secrets.
- Remove legacy plaintext password fallback.
- Force updates of imported/default passwords.

## Phase 3 - Data Governance
- Define the source of truth for seeded data (`seeders` vs SQL snapshots).
- Avoid local-to-production overwrites without explicit approval.
- Formalize backup and restore drills.

## Phase 4 - Web/Mobile Parity
- Keep property forms aligned by type between web CRM and mobile app.
- Maintain a compatible WebP image pipeline for web and mobile.
- Define a compatible video pipeline for web and mobile.
- Review whether the mobile API contract covers all legacy CRM fields.

## Phase 5 - Operational Reliability
- Improve health checks and observability for app and DB.
- Watch for drift between repo migrations and production runtime.
- Document incident response and rollback.
- Keep the habit of reviewing local and remote repo state before every `commit` and `push`.
