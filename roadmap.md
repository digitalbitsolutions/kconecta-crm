# Kconecta CRM - Roadmap

## Baseline (2026-04-21)
- Repository conectado y desplegado en Dokploy.
- Produccion operativa con autodeploy sobre `main`.
- Home, login, panel y listados principales verificados.
- Flujo de direccion de propiedades migrado a `Places API (New)` y funcionando en produccion.
- Flujos de alta por tipo de propiedad validados online.
- Edicion de propiedades validada online con reemplazo de multimedia.
- Flujo de `Garaje` validado online en alta y edicion para venta y alquiler.
- Gala valido online el flujo de `Garaje` sin incidencias.
- Flujo de `Terreno` validado localmente en alta para venta y alquiler.
- Layout de edicion de `Terreno` alineado con el fix aplicado antes en `Garaje`.
- Vistas protegidas ante propiedades sin portada.
- Backup productivo previo a cambios creado y validado en host.
- Persistencia de media corregida en Dokploy con volumenes para imagenes y videos.
- Restauracion desde backup validada.
- Media historica y nueva persistieron correctamente tras redeploy.
- Seleccion multiple de imagenes en galeria alineada en formularios web de edicion de propiedades.
- Validacion local completada para `Piso` y `Local o nave`.
- Validacion online completada tras deploy para el fix de seleccion multiple en galeria.
- `Terreno` ya separa `Tipo de terreno` y `Uso` en modelo, formularios, API y detalle publico.
- Produccion ya tiene:
- tabla `terrain_use`
- columna `property.terrain_use_id`
- `Urbanizable` agregado a `type_of_terrain`
- Backup previo al cambio de `Terreno` creado en:
- `/root/kconecta_backups/20260420_2313_pre_terreno`
- Procedimiento real de backup validado contra el contenedor MySQL:
- `kconecta-crm-b8ejyl.1.uhlwrkdsmasxw6hmpnkio19y3`
- Procedimiento real de migracion/cache clear validado contra el contenedor app:
- `kconecta-kconectacrm-5oikfs.1.8j4e7feeo9l3yxw5hap9vhw8k`
- `Terreno` ya soporta `Tipo de calificación` en formularios web, API y detalle publico.
- Produccion ya tiene:
- tabla `terrain_qualification`
- tabla pivot `terrain_qualifications`
- validacion online final confirmada en detalle publico de terreno (con `Tipo de calificación` visible)
- `Terreno` ya soporta visualizacion condicional de campos de superficie por `Tipo de terreno` en formularios web:
- para `Urbano` y `Urbanizable` muestra `Superficie edificable` y `Superficie minima vende/alquila`
- para otros tipos los oculta y limpia en frontend

## Phase 1 - Stabilize Production
- Investigar drift entre referencias en BD y archivos fisicos de media.
- Revisar si en una fase posterior conviene limpiar definitivamente los valores legacy de `type_of_terrain` (`Servicios`, `Industrial`, `Afectado`) tras verificar que ningun flujo dependa de ellos.
- Corregir UX de subida de video:
- mensaje real de limite
- validacion previa
- futura compresion frontend
- Revisar si alta de servicios requiere el mismo hardening que propiedades.

## Phase 2 - Security Hardening
- Rotar credenciales productivas y secrets de aplicacion.
- Eliminar fallback legacy de password en texto plano.
- Forzar actualizacion de passwords por defecto o importados.

## Phase 3 - Data Governance
- Definir fuente de verdad para seed de datos (`seeders` vs snapshots SQL).
- Evitar sobrescrituras local -> produccion sin aprobacion explicita.
- Formalizar procedimiento de backup y restore drill para DB y volumenes de media.
- Documentar y, si es viable, corregir la incompatibilidad de la tabla legacy `migrations` con el registrador estandar de Laravel.

## Phase 4 - Web/Mobile Parity
- Igualar formularios de propiedades por tipo entre CRM web y app movil.
- Mantener alineados los formularios web de alta y edicion para que no reaparezcan drift como el de galeria multiple.
- Mantener pipeline de imagenes WebP compatible para web y movil.
- Definir estrategia de video compatible para web y movil.
- Revisar si el contrato del API movil cubre todos los campos legacy del CRM.

## Closed In This Round
- `Terreno` quedo alineado en datos, formularios y detalle publico.

## Phase 5 - Operational Reliability
- Mejorar health checks y observabilidad de app + DB.
- Vigilar drift entre migraciones del repo y runtime productivo.
- Documentar incident response y rollback operativo.
