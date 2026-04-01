# AGENT.md - Kconecta CRM

## Goal
Operate and evolve `kconecta-crm` with focus on:
- stable local Docker workflow,
- sync with GitHub,
- deployment in Dokploy (Hostinger),
- security hardening.

## Current Repo Context
- GitHub repo: `https://github.com/digitalbitsolutions/kconecta-crm`
- Active remote: `origin` only
- Main branch: `main`
- Last operational update: `2026-03-04`
- GitHub reports repository move to: `https://github.com/sttildeveloper/kconecta-crm`

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

## Production Runtime Baseline
- Platform: Dokploy (Hostinger)
- App URL: `https://kconecta.com/`
- App service pattern: `kconecta-kconectacrm-*`
- DB service pattern: `kconecta-crm-*`
- DB schema: `kconecta-mysql`
- Deploy mode: automatic redeploy on `push` to `main`.
- Release tag policy: annotated tags on important stable commits in `main`.

## Recent Operations (2026-03-04)
- Password reset applied for `user.id=1` (`info@sttil.com`) with bcrypt hash.
- Password validation on server via Laravel `Hash::check(...)` returned `true`.
- Migration state verified in production:
- `php artisan migrate --force` => `Nothing to migrate`
- Production data populated from local snapshot (`kconecta_schema` -> `kconecta-mysql`).
- Runtime incident fixed in production:
- app service was crashing with SQL error on missing table `cache`.
- created `cache`, `cache_locks` and `sessions` tables in production DB.
- service recovered and web responded `HTTP 200` on home/login.
- Branding cleanup completed in runtime:
- favicon updated to `ico.png`.
- dashboard logout button rendered full width.
- legacy `Dámelo Dámelo` OG metadata replaced with `Kconecta`.
- Safety backup created before sync in local machine path:
- `D:\still\kconecta.com\backups\prod_kconecta_mysql_before_sync_20260304_180633.sql`
- Imported snapshot stored in local machine path:
- `D:\still\kconecta.com\backups\local_kconecta_schema_sync_20260304_180659.sql`

## Next Operational Focus
- Validate full browser login flow on production.
- Rotate exposed or weak credentials and keys.
- Remove legacy plaintext password fallback in auth flow.
- Define recurring backup and restore drill for production DB.
- Mobile integration next step: parity of property form fields with CRM web forms.
- Mobile integration next step: define image pipeline (convert to WebP before upload and persist media on server).

## Known Risks
- Legacy dumps may override expected Laravel schema.
- Imported users may include plaintext passwords.
- Existing fallback login logic accepts plaintext and rehashes on login.
- Production data can drift from local if sync is repeated without controls.
