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

## Migration Note
Se agregó una migración para asegurar compatibilidad de hashes de password:
- `database/migrations/2026_03_01_010900_expand_user_password_column.php`

## Production Status (2026-04-02)
- Entorno productivo activo en Dokploy.
- URL: `https://kconecta.com/`
- Deploy automático activo sobre `main`.
- Home y login responden `HTTP 200`.
- Login y panel autenticado validados manualmente en producción.
- Warning de Apache `ServerName` ya resuelto.
- Las altas/ediciones de propiedades requieren dirección válida resuelta por Google Maps.
- El formulario web de propiedades ya usa un flujo compatible con `Places API (New)`.

## Google Maps Requirements
Para que el flujo de direcciones funcione en local y producción:
- Variable de entorno:
- `GOOGLE_MAPS_API_KEY`
- APIs requeridas en Google Cloud:
- `Maps JavaScript API`
- `Places API`
- `Places API (New)`
- `Geocoding API`

## Deployment Workflow
Política operativa para cambios que afecten el CRM:
1. Validar el cambio en local.
2. Crear `commit` en `main`.
3. Hacer `push` a `origin/main`.
4. Esperar el `autodeploy` de Dokploy.
5. Verificar rutas críticas y login en el entorno desplegado.

Notas:
- Evitar `manual redeploy` salvo que el despliegue automático falle o queden endpoints caídos.
- No subir dumps, backups ni secretos al repo.

## Version Tags
- Usar tags anotados sobre commits importantes ya listos en `main`.
- Esquema: `vMAJOR.MINOR.PATCH`.
- Tags publicados:
- `v0.1.0`
- `v0.1.1`
- Guía detallada: [VERSIONING.md](./VERSIONING.md)

## Current Priorities
- completar prueba end-to-end de alta de propiedad en producción
- endurecer seguridad del flujo de autenticación legacy
- igualar formularios web y móvil
- definir pipeline consistente de imágenes WebP

## Project Control Files
- Estado y plan: [tasks.md](./tasks.md)
- Contexto operativo: [agent.md](./agent.md)
- Roadmap operativo: [roadmap.md](./roadmap.md)
