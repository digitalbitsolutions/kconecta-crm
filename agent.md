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

## Working Rules
- Prefer minimal, testable changes.
- Do not hardcode secrets.
- Keep `.env` out of remote history.
- Record infra and deployment progress in `tasks.md`.
- Validate critical flow locally before remote deploy:
- container up
- DB connection
- login

## Local Runtime Baseline
- App URL: `http://localhost:8010`
- Containers:
- `kconecta`
- `kconecta-mysql-1`
- DB schema: `kconecta_schema`

## Dokploy Execution Checklist
- Connect Dokploy project to GitHub repo.
- Set runtime env vars in Dokploy.
- Configure remote MySQL and credentials.
- Run migrations safely.
- Configure domain + SSL.
- Validate health checks and login flow.

## Known Risks
- Legacy dumps may override expected Laravel schema.
- Imported users may include plaintext passwords.
- Existing fallback login logic accepts plaintext and rehashes on login.
