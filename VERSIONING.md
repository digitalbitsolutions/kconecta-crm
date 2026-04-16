# Versioning

## Goal
Definir una politica simple y estable para releases del CRM sin romper el flujo actual con Dokploy.

## Deployment Policy
Para cambios normales del proyecto:
1. Validar localmente.
2. Revisar el estado del repo local antes de commitear.
3. Crear `commit` en `main`.
4. Hacer `push` a `origin/main`.
5. Esperar `autodeploy` en Dokploy.
6. Verificar login, panel y el flujo funcional afectado.

Usar redeploy manual solo si el despliegue automatico falla o queda algun endpoint inestable.

## Tag Policy
- Tipo de tag: anotado (`git tag -a`)
- Rama base: `main`
- Formato: `vMAJOR.MINOR.PATCH`

## Current Baseline
- `v0.1.0`: baseline operativo inicial con flujo `commit -> push -> autodeploy`
- `v0.1.1`: fix de produccion para suprimir warning de Apache `ServerName`

## Current State
- Desde `v0.1.1` se han desplegado varios fixes productivos sin nuevo tag:
- alta robusta de propiedades
- migracion a `Places API (New)`
- conversion de imagenes a WebP
- hardening de placeholders `Seleccione`
- limpieza de multimedia en edicion
- fallback para propiedades sin portada
- fix de render del simbolo `EUR`
- fix de layout en edicion de `Terreno`
- correccion de persistencia de media via volumenes en Dokploy

- El proximo tag recomendable deberia reservarse para un hito mas redondo, por ejemplo:
- implementacion robusta de `createService()`
- compresion o validacion consistente de video
- endurecimiento de autenticacion legacy
- baseline estable tras auditoria operativa completa

## Version Meaning
- `PATCH`: fixes chicos o ajustes sin cambio funcional amplio
- `MINOR`: cambios funcionales importantes compatibles
- `MAJOR`: cambios grandes o incompatibles

## When To Create A Tag
Crear tag cuando el commit represente uno de estos hitos:
- baseline estable de produccion
- cambio sensible en autenticacion o base de datos
- entrega funcional importante
- punto seguro de rollback antes de una tanda grande de cambios

Antes de crear un tag:
- verificar que el arbol local este limpio o con cambios intencionales claros
- verificar que el commit de `main` sea exactamente el que se quiere desplegar o marcar

## Suggested Commands
```bash
git commit -m "Describe change"
git push origin main
git tag -a v0.1.2 -m "Stable production milestone"
git push origin v0.1.2
```
