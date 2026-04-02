# Kconecta CRM - Tasks

## Session Checkpoint (2026-04-02)

### Done
- [x] Proyecto migrado de DameloDamelo a Kconecta en branding y referencias principales.
- [x] Docker local operativo:
- [x] App: `kconecta`
- [x] DB: `kconecta-mysql-1`
- [x] URL local: `http://localhost:8010`
- [x] Repo GitHub correcto y sincronizado:
- [x] `https://github.com/sttildeveloper/kconecta-crm`
- [x] `main` publicado
- [x] remoto final: `origin`
- [x] Acceso Hostinger, Dokploy y SSH al VPS validados desde este equipo de oficina.
- [x] Producción sincronizada hacia local para pruebas:
- [x] dump productivo importado en `kconecta_schema`
- [x] login local validado con las mismas credenciales de producción
- [x] Política operativa definida y publicada:
- [x] `commit -> push -> autodeploy -> verify`
- [x] Política inicial de versionado con tags anotados:
- [x] `v0.1.0`
- [x] `v0.1.1`
- [x] Warning de Apache en producción corregido:
- [x] `ServerName` global configurado
- [x] `AH00558` eliminado de logs de arranque
- [x] Navegación del backoffice ajustada:
- [x] `Dashboard` renombrado a `Escritorio`
- [x] Verificación remota UI/manual ya realizada:
- [x] login operativo
- [x] panel autenticado accesible
- [x] ruta crítica `/post/create_form/1` accesible en producción
- [x] Flujo de direcciones de propiedades endurecido:
- [x] `POST /post/create` dejó de ser stub y reutiliza el flujo real de guardado
- [x] backend exige dirección resuelta con `latitude` y `longitude`
- [x] validación numérica de coordenadas agregada
- [x] mensajes flash de éxito/error visibles en backoffice
- [x] Integración Google Maps migrada para proyectos nuevos:
- [x] autocomplete legacy reemplazado por flujo compatible con `Places API (New)`
- [x] mapa y reverse geocoding siguen operativos
- [x] Google Cloud actualizado en proyecto `kconectacrm`:
- [x] `Maps JavaScript API` habilitada
- [x] `Places API` habilitada
- [x] `Places API (New)` habilitada
- [x] `Geocoding API` habilitada
- [x] Dokploy con `GOOGLE_MAPS_API_KEY` cargada en producción
- [x] Sugerencias de dirección verificadas manualmente en producción

### Next
- [ ] Completar prueba manual end-to-end guardando una propiedad real en producción.
- [ ] Decidir si `createService()` debe endurecerse igual que el flujo de propiedades.
- [ ] Siguiente hito mobile: igualar formulario de propiedades por tipo entre CRM web y app móvil.
- [ ] Siguiente hito mobile: definir y soportar pipeline de imágenes WebP para alta/edición desde app móvil.

### Security Backlog
- [ ] Rotar secretos actuales (`APP_KEY`, API keys, credenciales DB).
- [ ] Forzar actualización de passwords por defecto.
- [ ] Eliminar fallback de login legacy que acepta password en texto plano.
- [ ] Verificar que no se suban secretos reales al repo.
- [ ] Mover credenciales sensibles fuera de notas operativas y comandos históricos.

### Notes
- Mantener este archivo como fuente de verdad para estado y próximos pasos.
- No reimportar dumps legacy en producción sin validación de esquema.
- No subir dumps, backups ni secretos al repo.
- `todo.md` sigue como archivo local sin trackear; no mezclarlo en commits funcionales.
