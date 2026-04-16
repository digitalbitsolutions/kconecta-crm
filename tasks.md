# Kconecta CRM - Tasks

## Session Checkpoint (2026-04-15)

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
- [x] Acceso Hostinger y Dokploy validados desde este equipo de oficina.
- [x] Produccion sincronizada hacia local para pruebas:
- [x] dump productivo importado en `kconecta_schema`
- [x] login local validado con las mismas credenciales de produccion
- [x] Politica operativa definida y publicada:
- [x] `commit -> push -> autodeploy -> verify`
- [x] Politica inicial de versionado con tags anotados:
- [x] `v0.1.0`
- [x] `v0.1.1`
- [x] Warning de Apache en produccion corregido:
- [x] `ServerName` global configurado
- [x] `AH00558` eliminado de logs de arranque
- [x] Navegacion del backoffice ajustada:
- [x] `Dashboard` renombrado a `Escritorio`
- [x] Integracion Google Maps migrada para proyectos nuevos:
- [x] autocomplete legacy reemplazado por flujo compatible con `Places API (New)`
- [x] mapa y reverse geocoding siguen operativos
- [x] Google Cloud actualizado en proyecto `kconectacrm`:
- [x] `Maps JavaScript API` habilitada
- [x] `Places API` habilitada
- [x] `Places API (New)` habilitada
- [x] `Geocoding API` habilitada
- [x] Dokploy con `GOOGLE_MAPS_API_KEY` cargada en produccion
- [x] Sugerencias de direccion verificadas manualmente en produccion
- [x] Flujo de creacion de propiedades endurecido:
- [x] `POST /post/create` reutiliza el flujo real de guardado
- [x] backend exige direccion resuelta con `latitude` y `longitude`
- [x] validacion numerica de coordenadas agregada
- [x] mensajes flash de exito/error visibles en backoffice
- [x] Conversion de imagenes a `.webp` aplicada antes de persistir archivos en el servidor.
- [x] Bug de placeholders `Seleccione` corregido:
- [x] frontend con placeholders no enviables
- [x] backend ignora valores no numericos en campos `_id`
- [x] Gestion de multimedia en edicion corregida:
- [x] al reemplazar portada se borra el archivo previo
- [x] al reemplazar video se borra el archivo previo
- [x] borrar imagenes adicionales queda diferido al submit
- [x] Fallback de portada faltante corregido en vistas de listado, detalle y edicion.
- [x] Render del simbolo `EUR` corregido en listados de propiedades.
- [x] Tipos de propiedad validados online en produccion:
- [x] `Casa o chalet`
- [x] `Piso`
- [x] `Local o nave`
- [x] `Garaje`
- [x] `Terreno`
- [x] `Casa rustica`
- [x] Edicion online validada al menos para `Piso`.
- [x] Registros incompletos de prueba (`Piso`) diagnosticados y eliminados de produccion.
- [x] `Garaje` validado online tanto en alta como en edicion para venta y alquiler.
- [x] Gala probo online el flujo de `Garaje` y funciono correctamente.
- [x] `Terreno` validado localmente tanto en alta como en edicion para venta y alquiler.
- [x] Fix aplicado en edicion de `Terreno` para el layout de titulo, sitio web y descripcion.
- [x] Respaldo productivo validado en host:
- [x] `/root/kconecta_backups/20260415_1656_pre_commit_sync/db_production.sql`
- [x] `/root/kconecta_backups/20260415_1656_pre_commit_sync/media_production/img_uploads`
- [x] `/root/kconecta_backups/20260415_1656_pre_commit_sync/media_production/video_uploads`
- [x] Incidente de perdida de media post-deploy diagnosticado:
- [x] causa raiz confirmada en almacenamiento efimero del contenedor
- [x] volumen persistente Dokploy configurado para `/var/www/html/public/img/uploads`
- [x] volumen persistente Dokploy configurado para `/var/www/html/public/video/uploads`
- [x] media historica restaurada desde backup en los volumenes persistentes
- [x] redeploy de verificacion completado sin perdida de imagenes
- [x] nueva subida validada online y persistente tras redeploy

### In Progress
- [ ] Revision de si `createService()` necesita el mismo hardening que propiedades.

### Next
- [ ] Respaldar BD local.
- [ ] Comparar media faltante de referencias con respaldo productivo antes de limpiar archivos no trackeados.
- [ ] Implementar plan de video:
- [ ] cambiar mensaje de `50MB` a limite real alineado
- [ ] validar tamano antes de subir
- [ ] comprimir video en frontend antes del submit
- [ ] Endurecer `createService()` si se confirma que debe seguir la misma logica robusta que propiedades.
- [ ] igualar formularios web y movil por tipo de propiedad.

### Security Backlog
- [ ] Rotar secretos actuales (`APP_KEY`, API keys, credenciales DB).
- [ ] Forzar actualizacion de passwords por defecto.
- [ ] Eliminar fallback de login legacy que acepta password en texto plano.
- [ ] Verificar que no se suban secretos reales al repo.
- [ ] Mover credenciales sensibles fuera de notas operativas y comandos historicos.

### Notes
- Mantener este archivo como fuente de verdad para estado y proximos pasos.
- No reimportar dumps legacy en produccion sin validacion de esquema.
- No subir dumps, backups ni secretos al repo.
- `todo.md` sigue como archivo local sin trackear; no mezclarlo en commits funcionales.
- `.codex_tmp` sigue local y sin trackear; no mezclarlo en commits.
- Para inspeccion rapida de produccion, preferir Hostinger browser terminal si el SSH directo desde este PC vuelve a fallar.
- `origin/main` y `HEAD` local quedaron alineados en `32b6035` tras publicar el fix de `Terreno` y la actualizacion de contexto previa.
- Referencias `sadtgnab`, `6ckhqztv` y `cyj5uxrv` tienen media en BD local pero sus archivos no existen en `public/img/uploads`.
- La persistencia productiva de media ya no esta pendiente: quedo resuelta en Dokploy y validada con redeploy mas subida nueva.
