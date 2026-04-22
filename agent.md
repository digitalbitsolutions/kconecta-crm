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
- Last operational update: `2026-04-21` (post pushes `703ae94`, `105c0b3`, `9cd087e`)

## Working Rules
- Prefer minimal, testable changes.
- Do not hardcode secrets.
- Keep `.env` out of remote history.
- Record infra, deploy progress, and validation results in `tasks.md`.
- Validate critical flow locally before remote deploy:
- container up
- DB connection
- login
- key form flow affected by the change
- For production updates: use `commit -> push -> autodeploy -> verify`.
- Use manual redeploy only if health checks or critical endpoints fail after push.
- Create annotated release tags on important `main` milestones following `VERSIONING.md`.

## Local Runtime Baseline
- App URL: `http://localhost:8010`
- Containers:
- `kconecta`
- `kconecta-mysql-1`
- DB schema: `kconecta_schema`
- Local office workspace: `C:\MeegDev\kconecta-crm\web`
- Local backup workspace: `C:\MeegDev\kconecta-crm\web\backups`

## Production Runtime Baseline
- Platform: Dokploy on Hostinger VPS
- App URL: `https://kconecta.com/`
- App service pattern: `kconecta-kconectacrm-*`
- DB service pattern: `kconecta-crm-*`
- Current production app container used on `2026-04-20/21`:
- `kconecta-kconectacrm-5oikfs.1.8j4e7feeo9l3yxw5hap9vhw8k`
- Current production app container validated after terrain-qualification deploy on `2026-04-21`:
- `kconecta-kconectacrm-5oikfs.1.r7nuo2pf6d5y46mu7ij1t5nrw`
- Current production MySQL container used on `2026-04-20/21`:
- `kconecta-crm-b8ejyl.1.uhlwrkdsmasxw6hmpnkio19y3`
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

## Access Notes
- Hostinger access validated from this office workstation.
- Dokploy access validated from this office workstation.
- Host-level troubleshooting can be done reliably through Hostinger browser terminal.
- Direct SSH from this workstation to the VPS is not currently reliable; prefer Hostinger terminal when production log inspection is needed.

## Recent Operations (2026-04-01 to 2026-04-15)
- Office workstation autonomy restored for day-to-day operations:
- Hostinger access validated
- Dokploy access validated
- Production data imported into local Docker DB for parity testing
- Local login validated using production credentials
- Deploy workflow and versioning policy documented:
- `commit -> push -> autodeploy -> verify`
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
- Production Google Cloud config updated and verified so address suggestions render again in property create forms
- Property registration now:
- converts uploaded non-WebP images to `.webp` before persistence
- handles Google address payload safely
- ignores non-numeric placeholder values for integer `_id` fields
- Editing flow now:
- deletes old cover/video files when replaced
- defers deletion of extra images until form submit
- Missing cover image fallback added to edit/detail/listing views to avoid `500`
- Euro symbol rendering fixed in property lists
- Manual and controlled production validations completed for:
- home
- login
- authenticated panel access
- property create route with live address suggestions
- all property types registration:
- `Casa o chalet`
- `Piso`
- `Local o nave`
- `Garaje`
- `Terreno`
- `Casa rustica`
- property editing with multimedia replacement
- Additional validation completed on `2026-04-15`:
- `Garaje` create flow validated online for both sale and rent
- `Garaje` edit flow validated online for both sale and rent
- Gala performed online operator testing on `Garaje` and confirmed the flow works correctly
- `Terreno` create flow validated locally for both sale and rent
- `Terreno` edit layout adjusted to match the fixed `Garaje` description section
- Additional validation completed on `2026-04-18`:
- multiple gallery image selection in property edit forms was enabled for the remaining property types
- `Piso` edit flow validated locally with multiple gallery image selection
- `Local o nave` edit flow validated locally with multiple gallery image selection
- production deploy completed from `main`
- online validation after deploy confirmed the multiple gallery image selection fix works satisfactorily
- Additional implementation and validation completed on `2026-04-20/21`:
- production backup created at `/root/kconecta_backups/20260420_2313_pre_terreno`
- production snapshots captured for `type_of_terrain` and current `Terreno` records
- `Terreno` was refactored to separate `Tipo de terreno` from `Uso`
- new data model introduced:
- table `terrain_use`
- nullable column `property.terrain_use_id`
- `Terreno` web create/edit forms now show:
- `Tipo de terreno`: `Urbano`, `Urbanizable`, `Rústico`
- `Uso`: `Servicios`, `Residencial`, `Industrial`, `Agrícola`
- public property detail now shows `Tipo de terreno` and `Uso` cards for `Terreno`
- production `500` on `/post/create_form/9` diagnosed after deploy
- root cause was pending schema change in production, not the Blade form itself
- production schema was updated and cache cleared successfully
- online tests for `Terreno` were reported as successful after schema update
- production `Terreno` records at backup time used only `type_of_terrain_id = Urbano`
- legacy values `Servicios`, `Industrial`, `Afectado` remain in `type_of_terrain` for compatibility, but are no longer exposed by the `Terreno` form
- production migration workflow required manual registration in `migrations` table after each executed migration because the table expects extra fields:
- `version`
- `class`
- `group`
- `namespace`
- `time`
- Production backup created on host at `/root/kconecta_backups/20260415_1656_pre_commit_sync`
- Post-deploy media incident diagnosed in production:
- root cause was ephemeral container storage for uploaded media
- Dokploy was updated with persistent volume mounts for:
- `/var/www/html/public/img/uploads`
- `/var/www/html/public/video/uploads`
- Historical media was restored from `/root/kconecta_backups/20260415_1656_pre_commit_sync`
- Media persistence was validated by:
- redeploy after restore
- new upload after fix
- redeploy after new upload
- Additional production hardening validated on `2026-04-22`:
- email verification flow fixed after adding missing `user.email_verified_at` in production
- post-verification redirect now sends users directly to role-specific destination
- Dokploy persistent volume added for provider profile logos:
- `/var/www/html/public/img/photo_profile`
- backup and restore drill validated for DB + media at:
- `/root/kconecta_backups/20260422_1739_pre_persist_media`

## Known Recent Incidents
- Incomplete `Piso` draft records were created on `2026-04-07` when the form submitted literal `Seleccione` into integer fields such as `emissions_rating_id` and `power_consumption_rating_id`.
- Those incomplete records were cleaned from production after diagnosis.
- Missing-cover records previously caused `500` in edit/detail views; fallback placeholder fix is already deployed.

## Current Audit Status
- Property create flows by type are considered production-validated.
- Property edit flow is considered production-validated for:
- replace cover image
- add more images
- mark existing more images for deletion
- replace video
- Garage-specific create/edit flow is production-validated for:
- sale
- rent
- operator-side online validation by Gala
- Property edit forms are now aligned to allow multiple gallery image selection in one action.
- `Terreno` create flow now includes isolated support for `terrain_use_id`.
- Property/service views with missing cover image now fall back to placeholder instead of failing.
- Production media persistence is now considered validated for both restored and newly uploaded files.

## Next Operational Focus
- Update context files and operational notes after major production validations.
- Decide whether to normalize or migrate legacy `type_of_terrain` values in production later, once it is safe to remove compatibility leftovers.
- Decide whether `createService()` should receive the same backend hardening as property create flow.
- Implement planned video upload hardening:
- align frontend/backend messaging with real limits
- validate video size before upload
- compress video in frontend before submit
- Rotate exposed or weak credentials and keys.
- Remove legacy plaintext password fallback in auth flow.
- Define recurring backup and restore drill for production DB and media volumes.

## Recently Closed
- `Terreno`:
- separation of `Tipo de terreno` and `Uso`
- web create/edit support
- API support
- public detail cards
- production schema update
- production validation after fix
- operational notes and backup procedure documented
- `Terreno` (extra round `2026-04-21`):
- support for `Tipo de calificación` in create/edit and API
- public detail now renders `Tipo de calificación` (badge + detail block)
- new schema objects deployed:
- `terrain_qualification`
- `terrain_qualifications`
- new terrain feature options seeded for `id_type=9`
- online validation confirmed with published terrain detail showing qualification values
- `Terreno` (follow-up round `2026-04-21`):
- create/edit forms now conditionally show additional area fields by terrain type
- `Urbano` and `Urbanizable` enable:
- `Superficie edificable` (`plot_meters`)
- `Superficie minima vende/alquila` (`useful_meters`)
- local validation reported successful in create and edit after fixing a Blade variable scope error in `form_5_update`
- Provider/services module (round `2026-04-22`):
- provider registration and profile flow aligned to business rules:
- allowed user types for signup constrained to `Proveedor de servicios` and `Agente inmobiliario`
- CIF/DNI/NIE validation hardened in register flow
- `username` aligned with `Razon social` and locked in profile update
- provider profile photo upload now processed server-side to `350x350` WebP
- services create form for providers simplified:
- removed duplicated user-data block
- service address now resolved from provider profile (`user_address`) instead of form input
- provider landing (`/post/services`) improved:
- gallery slider supports multiple images with prev/next controls and dots
- first render stabilized to avoid post-login visual glitch before full style hydration
- service detail public page fix:
- `result_service` video lookup corrected from `property_id` to `service_id`
- map markers branding update:
- result maps now use `kconecta` icon on both Google Maps and Leaflet in property/service result pages

## Known Risks
- Existing fallback login logic still accepts plaintext and rehashes on login.
- Google Maps address UX depends on keeping both Dokploy env and Google Cloud API enablement aligned.
- Video upload UX is still inconsistent:
- forms still say `50MB`
- frontend compression is not implemented yet
- backend/server limits can still surprise users with large uploads
- Some property records can reference image files that are missing from the current workspace or deployment media set.
- Existing production backup set is available before cleanup under `/root/kconecta_backups/20260415_1656_pre_commit_sync`.
- Backup set specific to the `Terreno` schema change is available at `/root/kconecta_backups/20260420_2313_pre_terreno`.
- Production migrations are operationally riskier than standard Laravel because the legacy `migrations` table shape forces manual registration if `php artisan migrate` only partially completes.
- Future Dokploy service changes must preserve the configured media volume mounts or the same class of incident can return.
- Legacy dumps may override expected Laravel schema if imported without review.
- Production data can drift from local if sync is repeated without controls.
- Some backoffice/service views still show legacy mojibake text (`Ã...`) and require final UTF-8 cleanup pass.
