# Kconecta CRM

CRM inmobiliario de Kconecta migrado desde un proyecto legacy.

## Repository
- GitHub: `https://github.com/sttildeveloper/kconecta-crm`
- Branch principal: `main`
- Remote activo: `origin`

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

App local:
- `http://localhost:8010`

## Database
- Schema local docker: `kconecta_schema`
- Schema productivo: `kconecta-mysql`

## Backup Workspace
- Carpeta local de respaldo operativo: `backups/`
- Uso previsto antes de cambios sensibles o limpieza de media:
- dump de BD local
- dump/export de BD productiva
- inventario de media investigada
- copia de media productiva descargada desde host cuando aplique
- Convencion recomendada de carpetas:
- `backups/YYYYMMDD_HHMM_pre_commit_sync/`
- Dentro de cada carpeta:
- `db_local.sql`
- `db_production.sql`
- `media_production/`
- `notes.md`

## Migration Note
Se agrego una migracion para asegurar compatibilidad de hashes de password:
- `database/migrations/2026_03_01_010900_expand_user_password_column.php`

## Production Status (2026-04-15)
- Entorno productivo activo en Dokploy.
- URL: `https://kconecta.com/`
- Deploy automatico activo sobre `main`.
- Home y login operativos.
- Login y panel autenticado validados manualmente en produccion.
- Warning de Apache `ServerName` ya resuelto.
- Las altas y ediciones de propiedades requieren direccion valida resuelta por Google Maps.
- El formulario web de propiedades usa un flujo compatible con `Places API (New)`.
- Las imagenes no WebP se convierten a `.webp` antes de persistirse.
- Los principales flujos de alta por tipo ya fueron validados online en produccion.
- La edicion con reemplazo de multimedia ya fue validada online.
- Alta y edicion de `Garaje` quedaron validadas online tanto para venta como para alquiler.
- Gala probo online el flujo de `Garaje` y reporto funcionamiento correcto.
- Registro local de `Terreno` validado para alquiler y venta.
- Edicion de `Terreno` ajustada para corregir layout de titulo y descripcion.
- Las vistas toleran propiedades sin portada y muestran placeholder sin `500`.

## Production Validation Snapshot
Flujos de alta validados online:
- `Casa o chalet`
- `Piso`
- `Local o nave`
- `Garaje`
- `Terreno`
- `Casa rustica`

Flujos de edicion validados online:
- edicion de `Piso`
- edicion de `Garaje` en venta y alquiler
- reemplazo de portada
- agregado de imagenes adicionales
- borrado diferido de imagenes adicionales
- reemplazo de video

## Google Maps Requirements
Para que el flujo de direcciones funcione en local y produccion:
- Variable de entorno:
- `GOOGLE_MAPS_API_KEY`
- APIs requeridas en Google Cloud:
- `Maps JavaScript API`
- `Places API`
- `Places API (New)`
- `Geocoding API`

## Upload Limit
- Env var:
- `VIDEO_MAX_UPLOAD_MB` (default `40`)
- Keep this value aligned with Dokploy reverse-proxy body size limit to avoid `413 Content Too Large`.

## Deployment Workflow
Politica operativa para cambios que afecten el CRM:
1. Validar el cambio en local.
2. Crear `commit` en `main`.
3. Hacer `push` a `origin/main`.
4. Esperar el `autodeploy` de Dokploy.
5. Verificar rutas criticas, login y el flujo tocado en el entorno desplegado.

Notas:
- Evitar `manual redeploy` salvo que el despliegue automatico falle o queden endpoints caidos.
- No subir dumps, backups ni secretos al repo.

## Version Tags
- Usar tags anotados sobre commits importantes ya listos en `main`.
- Esquema: `vMAJOR.MINOR.PATCH`.
- Tags publicados:
- `v0.1.0`
- `v0.1.1`
- Guia detallada: [VERSIONING.md](./VERSIONING.md)

## Current Priorities
- endurecer seguridad del flujo de autenticacion legacy
- alinear mensaje/limite real de video y preparar compresion frontend
- revisar si `createService()` necesita el mismo hardening que propiedades
- investigar y corregir drift entre referencias en BD y archivos fisicos de media
- igualar formularios web y movil
- definir pipeline consistente de video e imagenes para web y movil

## Project Control Files
- Estado y plan: [tasks.md](./tasks.md)
- Contexto operativo: [agent.md](./agent.md)
- Roadmap operativo: [roadmap.md](./roadmap.md)
