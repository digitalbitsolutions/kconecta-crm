# AGENT.md - Kconecta CRM

## Goal
Operate and evolve `kconecta-crm` with focus on:
- stable local Docker workflow,
- sync with GitHub,
- deployment in Dokploy (Hostinger),
- security hardening,
- parity between web CRM and future mobile flows.

## Current Repo Context
- Active GitHub repo: `https://github.com/sttildeveloper/kconecta-crm`
- Active remote: `origin`
- Main branch: `main`
- Last operational update: `2026-04-02`

## Working Rules
- Prefer minimal, testable changes.
- Do not hardcode secrets.
- Keep `.env` out of remote history.
- Record infra and deployment progress in `tasks.md`.
- Validate critical flow locally before remote deploy:
- container up
- DB connection
- login
- For production updates: use `commit -> push -> autodeploy -> verify`.
- Use manual redeploy only if health checks/endpoints fail after push.
- Create annotated release tags on important `main` milestones following `VERSIONING.md`.

## Local Runtime Baseline
- App URL: `http://localhost:8010`
- Containers:
- `kconecta`
- `kconecta-mysql-1`
- DB schema: `kconecta_schema`
- Local office workspace: `C:\MeegDev\kconecta-crm\web`

## Production Runtime Baseline
- Platform: Dokploy on Hostinger VPS
- App URL: `https://kconecta.com/`
- App service pattern: `kconecta-kconectacrm-*`
- DB service pattern: `kconecta-crm-*`
- DB schema: `kconecta-mysql`
- Deploy mode: automatic redeploy on `push` to `main`
- Release tags published:
- `v0.1.0`
- `v0.1.1`
- Production env includes `GOOGLE_MAPS_API_KEY`
- Google Cloud project `kconectacrm` currently requires these APIs enabled:
- `Maps JavaScript API`
- `Places API`
- `Places API (New)`
- `Geocoding API`

## Recent Operations (2026-04-01 to 2026-04-02)
- Office workstation autonomy restored:
- Hostinger access validated
- Dokploy access validated
- SSH access to VPS host validated from this machine
- Production data imported into local Docker DB for parity testing
- Local login validated using production credentials
- Deploy workflow and versioning policy documented:
- `commit -> push -> autodeploy -> verify`
- release tagging policy active
- Apache startup warning fixed in production:
- `AH00558` removed by setting global `ServerName`
- Backoffice navigation label changed:
- `Dashboard` -> `Escritorio`
- Property create flow no longer stops at a stub redirect:
- `POST /post/create` now creates the base property and reuses update flow
- Backend validation now requires:
- non-empty property address
- numeric latitude
- numeric longitude
- Shared Google Maps address JS migrated away from legacy autocomplete to a `Places API (New)` compatible flow
- Production Google Cloud config updated and verified so address suggestions render again in `/post/create_form/1`
- Manual production validation completed for:
- home
- login
- authenticated panel access
- property create route with live address suggestions

## Next Operational Focus
- Complete an end-to-end manual property save in production after selecting a suggested address.
- Decide whether service create flow should receive the same backend hardening as property create flow.
- Continue mobile integration:
- parity of property form fields with CRM web forms
- image pipeline to convert uploads to WebP before persistence
- Rotate exposed or weak credentials and keys.
- Remove legacy plaintext password fallback in auth flow.
- Define recurring backup and restore drill for production DB.

## Known Risks
- Existing fallback login logic still accepts plaintext and rehashes on login.
- Google Maps address UX depends on keeping both Dokploy env and Google Cloud API enablement aligned.
- Legacy dumps may override expected Laravel schema if imported without review.
- Production data can drift from local if sync is repeated without controls.
