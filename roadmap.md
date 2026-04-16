# Kconecta CRM - Roadmap

## Baseline (2026-04-15)
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

## Phase 1 - Stabilize Production
- Investigar drift entre referencias en BD y archivos fisicos de media.
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

## Phase 4 - Web/Mobile Parity
- Igualar formularios de propiedades por tipo entre CRM web y app movil.
- Mantener pipeline de imagenes WebP compatible para web y movil.
- Definir estrategia de video compatible para web y movil.
- Revisar si el contrato del API movil cubre todos los campos legacy del CRM.

## Phase 5 - Operational Reliability
- Mejorar health checks y observabilidad de app + DB.
- Vigilar drift entre migraciones del repo y runtime productivo.
- Documentar incident response y rollback operativo.
