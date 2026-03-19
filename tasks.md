# Kconecta CRM - Tasks

## Mobile API Contract v1 (Start: 2026-03-19)

### Branch + Safety
- [x] Create branch `feat/mobile-api-contract-v1`.
- [x] Keep `package-lock.json` out of scope for this initiative.
- [ ] Commit changes in slices (auth -> providers -> manager-properties -> tests/docs).

### Auth + Session (Priority P0)
- [x] Add routes in `routes/api.php`:
- [x] `POST /api/auth/login`
- [x] `POST /api/auth/refresh`
- [x] `GET /api/auth/me`
- [x] Add `AuthController` mobile contract methods.
- [x] Add session/auth service for deterministic token lifecycle.
- [x] Return stable envelopes (`auth-session-v1`).
- [ ] Add feature tests for login success/failure, refresh invalid/expired, me unauthorized/forbidden.

### Providers Domain (Priority P1)
- [x] Add routes:
- [x] `GET /api/providers`
- [x] `GET /api/providers/{id}`
- [x] `GET /api/providers/{id}/availability`
- [x] `PATCH /api/providers/{id}/availability`
- [x] Add/extend provider service (DB-first + deterministic fallback if required).
- [x] Enforce role/identity checks for provider availability writes.
- [x] Add validation and revision-conflict behavior for availability updates.
- [ ] Add feature tests for list/detail/availability success + 401/403/404/409/422 paths.

### Manager Properties Domain (Priority P2)
- [x] Add routes:
- [x] `GET /api/properties/summary`
- [x] `GET /api/properties/priorities/queue`
- [x] `POST /api/properties/priorities/queue/{queueItemId}/complete`
- [x] `GET /api/properties/{id}`
- [x] `POST /api/properties/{id}/reserve`
- [x] `POST /api/properties/{id}/release`
- [x] `GET /api/properties/{id}/provider-candidates`
- [x] `POST /api/properties/{id}/assign-provider`
- [x] `GET /api/properties/{id}/assignment-context`
- [x] Add deterministic conflict/validation/error envelopes.
- [ ] Add feature tests for happy path + guardrail matrix.

### Integration Validation (Priority P0 after endpoint delivery)
- [x] Smoke via Docker local runtime (`http://localhost:8010/api`).
- [ ] Validate from React Native emulators:
- [ ] manager login/session + dashboard bootstrap paths
- [ ] provider list/detail/availability paths
- [x] Capture endpoint evidence (request, status, payload shape).

### Release Readiness
- [ ] Document final endpoint contract in repo docs.
- [ ] Define versioning/change policy for mobile API contract.
- [ ] Open PR with checklist evidence and rollback notes.

## Session Checkpoint (2026-03-04)

### Done
- [x] Proyecto migrado de DameloDamelo a Kconecta (branding y referencias principales).
- [x] Docker local operativo:
- [x] App: `kconecta`
- [x] DB: `kconecta-mysql-1`
- [x] URL local: `http://localhost:8010`
- [x] Import SQL legacy en MySQL de Docker (`damelodamelo_damelo.sql`).
- [x] Fix de esquema para login:
- [x] `user.password` cambiado a `VARCHAR(255)`.
- [x] Login validado despues del fix.
- [x] Tabla `migrations` compatible con Laravel para evitar fallo de `artisan migrate`.
- [x] Repo GitHub creado y sincronizado:
- [x] `https://github.com/digitalbitsolutions/kconecta-crm`
- [x] `main` publicado.
- [x] remoto final: `origin` (unico remoto).
- [x] Acceso SSH al servidor Dokploy validado.
- [x] Estado de migraciones en produccion validado.
- [x] `php artisan migrate --force` en produccion sin pendientes (`Nothing to migrate`).
- [x] Password actualizado para usuario admin:
- [x] `user.id=1`
- [x] `email=info@sttil.com`
- [x] Password objetivo validado en servidor con `Hash::check(...)`.
- [x] Produccion poblada desde snapshot local (`kconecta_schema` -> `kconecta-mysql`).
- [x] Backup previo de produccion generado.
- [x] Incidente `500` en produccion diagnosticado y corregido:
- [x] causa: tabla `cache` inexistente al ejecutar `cache:clear` en arranque.
- [x] accion: creadas tablas runtime `cache`, `cache_locks` y `sessions`.
- [x] servicio web estable en `1/1` y home/login respondiendo `HTTP 200`.
- [x] Branding visual/SEO actualizado:
- [x] favicon reemplazado por `public/img/ico.png` y referenciado en layouts.
- [x] boton `Cerrar sesion` del dashboard ajustado a ancho completo.
- [x] metadatos OG de home migrados de `Dámelo Dámelo` a `Kconecta`.
- [x] labels visibles de tarjetas actualizados a `Propiedad Kconecta`.
- [x] Verificacion en vivo:
- [x] `https://kconecta.com/` devuelve `og:title` y `og:site_name` con `Kconecta`.

### Next - Dokploy (Hostinger)
- [x] Crear proyecto en Dokploy y conectar repo `digitalbitsolutions/kconecta-crm`.
- [x] Definir estrategia de deploy (build desde Dockerfile o compose).
- [x] Cargar variables de entorno de produccion.
- [x] Configurar base de datos de produccion.
- [x] Ejecutar migraciones en entorno remoto.
- [x] Configurar dominio, SSL y health checks.
- [ ] Probar login, panel y rutas criticas en entorno remoto (validacion manual UI end-to-end pendiente).
- [ ] Definir politica de sync local->produccion para evitar sobreescrituras accidentales.
- [ ] Configurar deploy automatizado con redeploy inmediato tras `push` para evitar drift entre repo y runtime.

### Security Backlog
- [ ] Rotar secretos actuales (`APP_KEY`, API keys, credenciales DB).
- [ ] Forzar actualizacion de passwords por defecto.
- [ ] Eliminar fallback de login legacy que acepta password en texto plano.
- [ ] Verificar que no se suban secretos reales al repo.
- [ ] Mover credenciales sensibles fuera de notas operativas y comandos historicos.

### Notes
- Mantener este archivo como fuente de verdad para estado y proximos pasos.
- No reimportar dumps legacy en produccion sin validacion de esquema.
- Backups de la operacion del `2026-03-04`:
- `D:\still\kconecta.com\backups\prod_kconecta_mysql_before_sync_20260304_180633.sql`
- `D:\still\kconecta.com\backups\local_kconecta_schema_sync_20260304_180659.sql`
