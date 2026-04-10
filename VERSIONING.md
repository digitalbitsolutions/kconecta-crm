# Versioning

## Goal
Define a simple and stable release policy for the CRM without breaking the current Dokploy flow.

## Deployment Policy
For normal project changes:
1. Validate locally.
2. Review local and remote repo state before `commit` and `push`.
3. Create the `commit` on `main`.
4. Push to `origin/main`.
5. Wait for Dokploy autodeploy.
6. Verify login, panel, and the affected functional flow.

Use manual redeploy only if autodeploy fails or an endpoint remains unstable.

Remote verification note:
- If `origin/main` may be stale or SSH is failing on the current machine, compare against the real remote with:
- `git ls-remote https://github.com/sttildeveloper/kconecta-crm.git HEAD refs/heads/main`

## Tag Policy
- Tag type: annotated (`git tag -a`)
- Base branch: `main`
- Format: `vMAJOR.MINOR.PATCH`

## Current Baseline
- `v0.1.0`: initial stable production baseline
- `v0.1.1`: production fix for Apache `ServerName`

## Current State
- Since `v0.1.1`, several production-facing fixes were shipped without a new tag:
- robust property create flow
- `Places API (New)` migration
- WebP image conversion
- placeholder hardening for `Seleccione`
- media cleanup on edit
- fallback for missing cover images
- euro symbol render fix
- browser-side video optimization and backend upload guards (`6e80a54`)

## Version Meaning
- `PATCH`: small fixes or narrow adjustments
- `MINOR`: important compatible functional changes
- `MAJOR`: large or incompatible changes

## When To Create A Tag
Create a tag when the commit represents one of these milestones:
- stable production baseline
- sensitive auth or database change
- important functional delivery
- safe rollback point before a large change set

Before creating a tag:
- verify the local tree is in the expected state
- verify the real remote head matches what you think is deployed

## Suggested Commands
```bash
git commit -m "Describe change"
git push origin main
git tag -a v0.1.2 -m "Stable production milestone"
git push origin v0.1.2
```
