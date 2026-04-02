# Kconecta CRM - Roadmap

## Baseline (2026-04-02)
- Repository conectado y desplegado en Dokploy.
- Producción operativa con autodeploy sobre `main`.
- Home, login y panel autenticado verificados.
- Flujo de dirección de propiedades migrado a `Places API (New)` y funcionando en producción.

## Phase 1 - Stabilize Production
- Completar prueba end-to-end guardando una propiedad real desde el navegador.
- Extender el mismo endurecimiento del flujo de direcciones al alta de servicios si aplica.
- Añadir checklist simple por deploy:
- build
- env
- smoke test
- rollback pointer

## Phase 2 - Security Hardening
- Rotar credenciales productivas y secrets de aplicación.
- Eliminar fallback legacy de password en texto plano.
- Forzar actualización de passwords por defecto o importados.

## Phase 3 - Data Governance
- Definir fuente de verdad para seed de datos (`seeders` vs snapshots SQL).
- Evitar sobrescrituras local -> producción sin aprobación explícita.
- Formalizar procedimiento de backup y restore drill.

## Phase 4 - Web/Mobile Parity
- Igualar formularios de propiedades por tipo entre CRM web y app móvil.
- Definir pipeline de imágenes WebP compatible para web y móvil.
- Revisar si el contrato del API móvil cubre todos los campos legacy del CRM.

## Phase 5 - Operational Reliability
- Mejorar health checks y observabilidad de app + DB.
- Vigilar drift entre migraciones del repo y runtime productivo.
- Documentar incidente response y rollback operativo.
