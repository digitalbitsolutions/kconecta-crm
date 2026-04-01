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
cd D:\still\kconecta.com\web
docker compose -p kconecta up -d --build
```

App local:
- `http://localhost:8010`

## Database
- Schema local docker: `kconecta_schema`

Import manual de SQL legacy (si se necesita):
```powershell
docker cp D:\still\kconecta.com\assets\damelodamelo_damelo.sql kconecta-mysql-1:/tmp/damelodamelo_damelo.sql
docker exec kconecta-mysql-1 mysql -uroot -psecret -e "DROP DATABASE IF EXISTS kconecta_schema; CREATE DATABASE kconecta_schema CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
docker exec kconecta-mysql-1 sh -lc "mysql -uroot -psecret kconecta_schema < /tmp/damelodamelo_damelo.sql"
```

## Migration Note
Se agrego una migracion para asegurar compatibilidad de hashes de password:
- `database/migrations/2026_03_01_010900_expand_user_password_column.php`

## Production Status (2026-03-04)
- Entorno productivo activo en Dokploy.
- URL: `https://kconecta.com/`
- Migraciones en estado estable:
- `php artisan migrate --force` => `Nothing to migrate`
- Base de datos productiva poblada desde snapshot local validado.
- Incidente 500 resuelto:
- causa: faltaban tablas runtime (`cache`, `cache_locks`, `sessions`).
- estado: servicio estable y rutas principales (`/`, `/login`) en `HTTP 200`.
- Branding de home alineado a `Kconecta` en metadatos OG.
- Conteos de referencia tras sync:
- `user=5`
- `user_level=5`
- `property=31`
- `category=3`
- `city=61`
- Backup previo de produccion y dump usado para sync guardados fuera del repo:
- `D:\still\kconecta.com\backups\prod_kconecta_mysql_before_sync_20260304_180633.sql`
- `D:\still\kconecta.com\backups\local_kconecta_schema_sync_20260304_180659.sql`

## Deployment Workflow
Politica operativa para cambios que afecten el CRM:
1. Validar el cambio en local.
2. Crear `commit` en `main`.
3. Hacer `push` a `origin/main`.
4. Esperar el `autodeploy` de Dokploy.
5. Verificar rutas criticas y login en el entorno desplegado.

Notas:
- Evitar `manual redeploy` salvo que el despliegue automatico falle o queden endpoints caidos.
- No subir dumps, backups ni secretos al repo.

## Version Tags
- Usar tags anotados sobre commits importantes ya listos en `main`.
- Esquema inicial: `vMAJOR.MINOR.PATCH`.
- Punto de partida de versionado: `v0.1.0`.
- Guia detallada: [VERSIONING.md](./VERSIONING.md)

## Next Phase
Operaciones y hardening:
- validar login y flujos criticos en UI de produccion
- rotar secretos y credenciales
- remover fallback legacy de password en texto plano
- estandarizar procedimiento de backups y restauracion

## Project Control Files
- Estado y plan: [tasks.md](./tasks.md)
- Contexto operativo: [agent.md](./agent.md)
- Roadmap operativo: [roadmap.md](./roadmap.md)
