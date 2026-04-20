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

## Production Status (2026-04-21)
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
- La edicion de propiedades ya permite seleccionar multiples imagenes para la galeria en un solo paso.
- El fix de carga multiple en galeria fue validado en local para `Piso` y `Local o nave`.
- El fix de carga multiple en galeria fue validado online tras deploy sin incidencias reportadas.
- Registro local de `Terreno` validado para alquiler y venta.
- Edicion de `Terreno` ajustada para corregir layout de titulo y descripcion.
- El modulo de `Terreno` fue ampliado para separar `Tipo de terreno` de `Uso`.
- `Terreno` ahora usa:
- `Tipo de terreno`: `Urbano`, `Urbanizable`, `Rústico`
- `Uso`: `Servicios`, `Residencial`, `Industrial`, `Agrícola`
- El detalle publico de `Terreno` ahora muestra arriba los recuadros `Tipo de terreno` y `Uso`.
- El alta de `Terreno` en produccion quedo validada despues de aplicar migraciones y limpiar cache.
- Las vistas toleran propiedades sin portada y muestran placeholder sin `500`.
- Backup operativo previo a cambios productivos creado en host:
- `/root/kconecta_backups/20260415_1656_pre_commit_sync`
- Backup operativo previo al cambio de `Terreno` creado en host:
- `/root/kconecta_backups/20260420_2313_pre_terreno`
- Contenedor MySQL usado para backup y verificacion en produccion:
- `kconecta-crm-b8ejyl.1.uhlwrkdsmasxw6hmpnkio19y3`
- Contenedor app usado para migraciones y cache clear en produccion:
- `kconecta-kconectacrm-5oikfs.1.8j4e7feeo9l3yxw5hap9vhw8k`
- Persistencia de media en produccion corregida en Dokploy con volumenes para:
- `/var/www/html/public/img/uploads`
- `/var/www/html/public/video/uploads`
- La restauracion de media desde backup fue validada.
- Un redeploy posterior mantuvo las imagenes existentes.
- Una subida nueva posterior al fix tambien persistio tras redeploy.

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
- seleccion multiple de imagenes en galeria al editar `Piso`
- seleccion multiple de imagenes en galeria al editar `Local o nave`
- edicion de `Garaje` en venta y alquiler
- reemplazo de portada
- agregado de imagenes adicionales
- borrado diferido de imagenes adicionales
- reemplazo de video
- alta de `Terreno` con `Uso` y `Tipo de terreno` separados
- detalle publico de `Terreno` con recuadros superiores de `Tipo de terreno` y `Uso`

## Terrain Change (2026-04-21)
- Commit publicado:
- `eadae0a` - `Add terrain use support and normalize land forms`
- Cambio de datos implementado:
- nueva tabla `terrain_use`
- nueva columna nullable `property.terrain_use_id`
- migracion que garantiza la presencia de `Urbanizable` en `type_of_terrain`
- Cambio funcional implementado solo para `Terreno`:
- formularios web de alta y edicion muestran `Uso`
- backend web guarda `terrain_use_id`
- API de propiedades expone y acepta `terrain_use`
- detalle publico recibe y muestra `terrain_use`
- Estrategia de compatibilidad:
- no se borraron de BD los valores legacy de `type_of_terrain` (`Servicios`, `Industrial`, `Afectado`)
- esos valores legacy dejaron de exponerse en el formulario/catalogo de `Terreno`
- los `Terreno` existentes en produccion usaban solo `Urbano` al momento del cambio

## Production Backup Drill
- Ruta de backup validada para este cambio:
- `/root/kconecta_backups/20260420_2313_pre_terreno`
- Archivos validados:
- `db_production.sql.gz`
- `type_of_terrain.tsv`
- `terrain_properties.tsv`
- Comando funcional para dump productivo:
```bash
docker exec kconecta-crm-b8ejyl.1.uhlwrkdsmasxw6hmpnkio19y3 sh -lc 'mysqldump --no-tablespaces -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' > "$BKP_DIR/db_production.sql"
```
- Comando funcional para snapshot del catalogo `type_of_terrain`:
```bash
docker exec kconecta-crm-b8ejyl.1.uhlwrkdsmasxw6hmpnkio19y3 sh -lc 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" -e "SELECT id,name FROM type_of_terrain ORDER BY id"' > "$BKP_DIR/type_of_terrain.tsv"
```
- Comando funcional para snapshot de propiedades `Terreno`:
```bash
docker exec kconecta-crm-b8ejyl.1.uhlwrkdsmasxw6hmpnkio19y3 sh -lc 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" -e "SELECT id,reference,title,type_id,type_of_terrain_id FROM property WHERE type_id=9 ORDER BY id"' > "$BKP_DIR/terrain_properties.tsv"
```

## Migration Caveat
- La tabla `migrations` de este entorno legacy no coincide con la expectativa estandar de Laravel.
- Columnas observadas:
- `id`, `migration`, `version`, `class`, `group`, `namespace`, `time`, `batch`
- Resultado operativo:
- `php artisan migrate` ejecuta el SQL pero falla al registrar la migracion si no se completan esos campos extra.
- En produccion se resolvio registrando manualmente las migraciones ya ejecutadas mediante `php artisan tinker --execute ... updateOrInsert(...)`.
- Este comportamiento debe tenerse en cuenta antes de futuras migraciones productivas.

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
- Si se agregan mounts/volumenes nuevos en Dokploy para media, poblarlos antes de dar por buena la persistencia.
- Las rutas de media que deben mantenerse persistentes en produccion son:
- `/var/www/html/public/img/uploads`
- `/var/www/html/public/video/uploads`

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
