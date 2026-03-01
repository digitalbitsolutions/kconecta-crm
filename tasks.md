# Kconecta CRM - Tasks

## Session Checkpoint (2026-03-01)

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

### Next - Dokploy (Hostinger)
- [ ] Crear proyecto en Dokploy y conectar repo `digitalbitsolutions/kconecta-crm`.
- [ ] Definir estrategia de deploy (build desde Dockerfile o compose).
- [ ] Cargar variables de entorno de produccion.
- [ ] Configurar base de datos de produccion.
- [ ] Ejecutar migraciones en entorno remoto.
- [ ] Configurar dominio, SSL y health checks.
- [ ] Probar login, panel y rutas criticas en entorno remoto.

### Security Backlog
- [ ] Rotar secretos actuales (`APP_KEY`, API keys, credenciales DB).
- [ ] Forzar actualizacion de passwords por defecto.
- [ ] Eliminar fallback de login legacy que acepta password en texto plano.
- [ ] Verificar que no se suban secretos reales al repo.

### Notes
- Mantener este archivo como fuente de verdad para estado y proximos pasos.
- No reimportar dumps legacy en produccion sin validacion de esquema.
