# AGENT.md - Kconecta CRM

## Goal
Operate and evolve `kconecta-crm` with focus on:
- stable local Docker workflow
- sync with GitHub
- deployment in Dokploy on Hostinger
- security hardening
- parity between web CRM and future mobile flows

## Current Repo Context
- Active GitHub repo: `https://github.com/sttildeveloper/kconecta-crm`
- Active remote: `origin`
- Main branch: `main`
- Last operational update: `2026-04-10`

## Working Rules
- Prefer minimal, testable changes.
- Do not hardcode secrets.
- Keep `.env` out of remote history.
- Record infra, deploy progress, and validation results in `tasks.md`.
- Before `commit` or `push`, always review repo state:
- inspect `git status`
- compare local `main` with the real remote head
- if `origin/main` may be stale, verify with `git ls-remote` or a fresh fetch
- Validate the critical local flow before remote deploy:
- containers up
- DB connection
- login
- key form flow touched by the change
- For production updates, use `commit -> push -> autodeploy -> verify`.
- Use manual redeploy only if health checks or critical endpoints fail after push.
- Create annotated release tags for important `main` milestones using [VERSIONING.md](./VERSIONING.md).

## Local Runtime Baseline
- App URL: `http://localhost:8010`
- Containers:
- `kconecta`
- `kconecta-mysql-1`
- Local DB schema: `kconecta_schema`
- Office workspace path: `C:\MeegDev\kconecta-crm\web`

## Production Runtime Baseline
- Platform: Dokploy on Hostinger VPS
- App URL: `https://kconecta.com/`
- App service pattern: `kconecta-kconectacrm-*`
- DB service pattern: `kconecta-crm-*`
- Production DB schema: `kconecta-mysql`
- Deploy mode: autodeploy on push to `main`
- Published tags:
- `v0.1.0`
- `v0.1.1`
- Production env includes `GOOGLE_MAPS_API_KEY`
- Required Google Cloud APIs:
- `Maps JavaScript API`
- `Places API`
- `Places API (New)`
- `Geocoding API`

## Access Notes
- Hostinger access was validated from the office workstation.
- Dokploy access was validated from the office workstation.
- Host-level troubleshooting can be done through the Hostinger browser terminal.
- Direct SSH from the office workstation to the VPS is not always reliable.
- On the personal workstation, GitHub SSH auth may fail even when the repo is healthy.
- On that machine, HTTPS push can be used as a fallback without changing the stored remote.

## Recent Operations (2026-04-01 to 2026-04-10)
- Office workstation autonomy was restored for day-to-day operations.
- Production data was imported into local Docker DB for parity testing.
- Local login was validated with production credentials.
- The deploy workflow and versioning policy were documented.
- Apache `AH00558` warning was fixed in production through global `ServerName`.
- Backoffice navigation label changed from `Dashboard` to `Escritorio`.
- Property create flow stopped redirecting to a stub and now reuses the real save/update path.
- Backend create validation now requires:
- non-empty property address
- numeric latitude
- numeric longitude
- Shared Google Maps JS was migrated to a `Places API (New)` compatible flow.
- Production Google Cloud config was updated so address suggestions render again.
- Property registration now:
- converts non-WebP uploads to `.webp`
- handles Google address payload safely
- ignores non-numeric placeholder values for integer `_id` fields
- Editing flow now:
- deletes old cover/video files when replaced
- defers deletion of extra images until submit
- uses placeholder fallback when a cover image is missing
- Property create flows by type were validated online.
- Property edit with media replacement was validated online.
- Browser-side video optimization and upload guards were pushed to GitHub `main` on `2026-04-10`.
- Published commit: `6e80a54`
- Video changes include:
- FFmpeg wasm optimization and auto-trim in browser
- payload-size summary before submit
- aligned accepted formats and user-facing limits
- centralized backend video validation and storage

## Known Recent Incidents
- Incomplete `Piso` draft records were created on `2026-04-07` when the form submitted literal `Seleccione` into integer fields such as `emissions_rating_id` and `power_consumption_rating_id`.
- Those incomplete records were cleaned from production after diagnosis.
- Missing-cover records previously caused `500` in edit/detail views; the placeholder fallback fix is already deployed.

## Current Audit Status
- Property create flows by type are considered production-validated.
- Property edit flow is considered production-validated for:
- replace cover image
- add more images
- mark extra images for deletion
- replace video
- Property/service views with missing cover image now fall back to a placeholder instead of failing.
- The new browser-side video flow is published to GitHub `main`, but still needs explicit post-deploy verification in production.

## Next Operational Focus
- Run one more operator-side audit before asking Gala to test online.
- Verify post-deploy behavior of the new video upload flow in production.
- Update context files after significant production validations.
- Decide whether `createService()` needs the same hardening as property create flow.
- Rotate exposed or weak credentials and keys.
- Remove the legacy plaintext password fallback in auth flow.
- Define a recurring backup and restore drill for production DB.

## Known Risks
- Existing fallback login logic still accepts plaintext and rehashes on login.
- Google Maps address UX depends on both Dokploy env vars and Google Cloud API enablement staying aligned.
- The new video upload flow still needs explicit production verification.
- Legacy dumps may override expected Laravel schema if imported without review.
- Production data can drift from local if sync is repeated without controls.
