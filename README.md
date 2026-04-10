# Kconecta CRM

CRM inmobiliario de Kconecta migrado desde un proyecto legacy.

## Repository
- GitHub: `https://github.com/sttildeveloper/kconecta-crm`
- Main branch: `main`
- Active remote: `origin`

## Stack
- Laravel 12
- PHP 8.2
- MySQL 8
- Docker Compose

## Local Run
```powershell
cd C:\MeegDev\kconecta-crm\web
docker compose -p kconecta up -d --build
```

Local app:
- `http://localhost:8010`

## Database
- Local docker schema: `kconecta_schema`
- Production schema: `kconecta-mysql`

## Migration Note
- Password hash compatibility migration:
- `database/migrations/2026_03_01_010900_expand_user_password_column.php`

## Production Status (2026-04-10)
- Production runs on Dokploy.
- URL: `https://kconecta.com/`
- Autodeploy is active on `main`.
- Home and login are operational.
- Authenticated panel was manually validated in production.
- Apache `ServerName` warning is resolved.
- Property create/edit flows require a valid Google-resolved address.
- Property forms use a `Places API (New)` compatible flow.
- Non-WebP image uploads are converted to `.webp` before persistence.
- Main property create flows by type were validated online in production.
- Property edit with media replacement was validated online in production.
- Views tolerate missing cover images and show a safe placeholder.
- The browser-side video optimization and hardened upload flow was pushed to GitHub `main` in commit `6e80a54`.
- Explicit post-deploy verification of the new video flow is still pending.

## Production Validation Snapshot
Validated create flows:
- `Casa o chalet`
- `Piso`
- `Local o nave`
- `Garaje`
- `Terreno`
- `Casa rustica`

Validated edit flows:
- edit of `Piso`
- replace cover image
- add extra images
- deferred delete of extra images
- replace video

## Google Maps Requirements
- Env var:
- `GOOGLE_MAPS_API_KEY`
- Required Google Cloud APIs:
- `Maps JavaScript API`
- `Places API`
- `Places API (New)`
- `Geocoding API`

## Deployment Workflow
Operational policy for CRM changes:
1. Validate the change locally.
2. Review local and remote repo state before `commit` and `push`.
3. Create the `commit` on `main`.
4. Push to `origin/main`.
5. Wait for Dokploy autodeploy.
6. Verify critical routes, login, and the touched flow in production.

Notes:
- Avoid manual redeploy unless autodeploy fails or health checks remain broken.
- Do not commit dumps, backups, or secrets.
- Before `commit` or `push`, review `git status` and compare local `main` with the real remote head.
- If `origin/main` may be stale or local SSH is failing, verify the remote with:
- `git ls-remote https://github.com/sttildeveloper/kconecta-crm.git HEAD refs/heads/main`

## Version Tags
- Use annotated tags for important stable commits already on `main`.
- Format: `vMAJOR.MINOR.PATCH`
- Published tags:
- `v0.1.0`
- `v0.1.1`
- Detailed guide: [VERSIONING.md](./VERSIONING.md)

## Current Priorities
- close the short audit before Gala tests online
- harden the legacy auth flow
- verify the new video optimization/upload flow in production after the `main` push
- keep web and mobile forms aligned
- define a consistent image/video pipeline for web and mobile

## Project Control Files
- State and next steps: [tasks.md](./tasks.md)
- Operational context: [agent.md](./agent.md)
- Operational roadmap: [roadmap.md](./roadmap.md)
