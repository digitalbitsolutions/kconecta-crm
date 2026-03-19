# Kconecta CRM - Roadmap

## Active Initiative - Mobile API Contract v1 (2026-03-19)

Goal: make this CRM the single backend for React Native manager/providers apps with a stable mobile API contract.

### Phase A - Contract Foundation (Auth + Session)

- Add mobile auth/session endpoints:
  - `POST /api/auth/login`
  - `POST /api/auth/refresh`
  - `GET /api/auth/me`
- Return deterministic payload envelopes for success and error.
- Enforce role-aware session metadata for manager/provider/admin.

### Phase B - Provider Mobile Domain

- Add provider domain endpoints:
  - `GET /api/providers`
  - `GET /api/providers/{id}`
  - `GET /api/providers/{id}/availability`
  - `PATCH /api/providers/{id}/availability`
- Enforce provider identity and role guards.
- Keep DB-first reads with safe fallback behavior when data shape is incomplete.

### Phase C - Manager Property Mobile Domain

- Add manager property endpoints:
  - `GET /api/properties/summary`
  - `GET /api/properties/priorities/queue`
  - `POST /api/properties/priorities/queue/{queueItemId}/complete`
  - `GET /api/properties/{id}`
  - `POST /api/properties/{id}/reserve`
  - `POST /api/properties/{id}/release`
  - `GET /api/properties/{id}/provider-candidates`
  - `POST /api/properties/{id}/assign-provider`
  - `GET /api/properties/{id}/assignment-context`
- Keep non-destructive mutation semantics and deterministic conflict/validation responses.

### Phase D - Verification + Mobile Integration

- Add feature/smoke coverage for auth/providers/properties contracts.
- Validate against local Docker runtime (`app` + `mysql`).
- Confirm React Native apps can consume endpoints without compatibility shims.

### Delivery Guardrails

- Do not remove or break existing web/legacy API routes.
- Do not run destructive DB operations against production data.
- All mobile endpoints must keep response envelopes stable:
  - success: `data`, `meta`
  - error: `error`, `meta` (`contract`, `flow`, `reason`, `retryable`)

## Baseline (2026-03-04)
- Repository connected and deployed in Dokploy.
- Production DB exists and is populated with current local snapshot.
- Core schema migrations are up to date.
- Public branding in home metadata is aligned with `Kconecta`.

## Phase 1 - Stabilize Production (Now)
- Complete manual end-to-end login validation in browser.
- Validate critical routes and admin actions with real user flow.
- Add a simple release checklist per deploy (build, migrate, smoke test, rollback pointer).
- Ensure deploy automation refreshes runtime immediately after `main` updates.

## Phase 2 - Security Hardening
- Rotate production credentials and application secrets.
- Remove legacy plaintext-password fallback from authentication flow.
- Enforce password reset policy for default or imported accounts.

## Phase 3 - Data Governance
- Define source of truth for data seeding (`seeders` vs SQL snapshots).
- Prevent accidental local-to-production overwrite by requiring explicit approval path.
- Schedule automated backups and test restore procedure regularly.

## Phase 4 - Operational Reliability
- Add health checks and alerting for app + DB.
- Track migration drift between repo and production runtime.
- Document incident response and rollback steps in ops runbook.
