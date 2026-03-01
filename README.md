# Kconecta CRM

CRM inmobiliario de Kconecta migrado desde un proyecto legacy.

## Repository
- GitHub: `https://github.com/digitalbitsolutions/kconecta-crm`
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

## Next Phase
Deploy y sincronizacion en Dokploy (Hostinger):
- conectar repo
- configurar env vars
- configurar DB de produccion
- ejecutar migraciones
- configurar dominio y SSL
- validar health checks

## Project Control Files
- Estado y plan: [tasks.md](./tasks.md)
- Contexto operativo: [agent.md](./agent.md)
