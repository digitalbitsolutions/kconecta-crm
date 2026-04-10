# Kconecta CRM - Tasks

## Session Checkpoint (2026-04-10)

### Done
- [x] Project migrated from DameloDamelo to Kconecta in main branding and references.
- [x] Local Docker runtime is operational:
- [x] App: `kconecta`
- [x] DB: `kconecta-mysql-1`
- [x] Local URL: `http://localhost:8010`
- [x] Correct GitHub repo is in use:
- [x] `https://github.com/sttildeveloper/kconecta-crm`
- [x] `main` is published
- [x] final remote is `origin`
- [x] Hostinger and Dokploy access were validated from the office workstation.
- [x] Production was synced into local for testing:
- [x] production dump imported into `kconecta_schema`
- [x] local login validated with production credentials
- [x] Operational policy is documented:
- [x] `commit -> push -> autodeploy -> verify`
- [x] Initial versioning policy with annotated tags is documented:
- [x] `v0.1.0`
- [x] `v0.1.1`
- [x] Apache warning in production was fixed:
- [x] global `ServerName` configured
- [x] `AH00558` removed from startup logs
- [x] Backoffice navigation updated:
- [x] `Dashboard` renamed to `Escritorio`
- [x] Google Maps integration was migrated for new environments:
- [x] legacy autocomplete replaced with a `Places API (New)` compatible flow
- [x] map and reverse geocoding still operate
- [x] Google Cloud project `kconectacrm` updated:
- [x] `Maps JavaScript API` enabled
- [x] `Places API` enabled
- [x] `Places API (New)` enabled
- [x] `Geocoding API` enabled
- [x] `GOOGLE_MAPS_API_KEY` is loaded in production
- [x] Address suggestions were manually verified in production
- [x] Property create flow was hardened:
- [x] `POST /post/create` reuses the real save/update path
- [x] backend requires Google-resolved address with `latitude` and `longitude`
- [x] numeric coordinate validation added
- [x] success/error flash messages visible in backoffice
- [x] Image conversion to `.webp` happens before persistence on the server
- [x] `Seleccione` placeholder bug was fixed:
- [x] frontend no longer submits invalid placeholders
- [x] backend ignores non-numeric values in `_id` fields
- [x] Media handling on edit was fixed:
- [x] replacing cover deletes the old file
- [x] replacing video deletes the old file
- [x] deleting extra images is deferred until submit
- [x] Missing-cover fallback fixed in list, detail, and edit views
- [x] Euro symbol render fixed in property listings
- [x] Property types validated online in production:
- [x] `Casa o chalet`
- [x] `Piso`
- [x] `Local o nave`
- [x] `Garaje`
- [x] `Terreno`
- [x] `Casa rustica`
- [x] Online edit validated at least for `Piso`
- [x] Incomplete `Piso` test records were diagnosed and removed from production
- [x] Hardened video flow was pushed to GitHub `main`:
- [x] form message aligned with the real pre-optimization limit
- [x] supported video formats validated in forms
- [x] total payload summary shown before submit
- [x] browser-side video optimization and auto-trim added with FFmpeg wasm
- [x] backend video validation/storage centralized
- [x] published commit: `6e80a54`

### In Progress
- [ ] Short audit before Gala performs online testing.
- [ ] Post-deploy verification of the new video flow in production.

### Next
- [ ] Run one more manual `Piso` test before asking Gala to test online.
- [ ] Prepare a short Gala test script:
- [ ] property create with Google-suggested address
- [ ] property edit with cover replacement
- [ ] add and delete extra images
- [ ] small video and video that requires optimization
- [ ] Decide whether `createService()` should receive the same hardening as property create.
- [ ] Keep web and mobile property forms aligned by type.
- [ ] Evaluate whether the next milestone deserves tag `v0.1.2` after video-flow verification closes.

### Security Backlog
- [ ] Rotate current secrets (`APP_KEY`, API keys, DB credentials).
- [ ] Force updates of default passwords.
- [ ] Remove legacy login fallback that accepts plaintext password.
- [ ] Verify that no real secrets are pushed to the repo.
- [ ] Move sensitive credentials out of operational notes and old commands.

### Notes
- Keep this file as the source of truth for status and next steps.
- Do not reimport legacy dumps into production without schema validation.
- Do not commit dumps, backups, or secrets.
- `todo.md` stays local and untracked; do not mix it into functional commits.
- `.codex_tmp` stays local and untracked; do not mix it into commits.
- For quick production inspection, prefer the Hostinger browser terminal if direct SSH from the current PC is unreliable.
- Before `commit` or `push`, Codex must always review:
- `git status`
- the difference between local `main` and the real remote head
- if `origin/main` looks stale, verify with `git ls-remote` or an explicit fetch before pushing
