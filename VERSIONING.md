# Versioning

## Goal
Definir una política simple y estable para releases del CRM sin romper el flujo actual con Dokploy.

## Deployment Policy
Para cambios normales del proyecto:
1. Validar localmente.
2. Crear `commit` en `main`.
3. Hacer `push` a `origin/main`.
4. Esperar `autodeploy` en Dokploy.
5. Verificar login, panel y rutas críticas.

Usar redeploy manual solo si el despliegue automático falla o queda algún endpoint inestable.

## Tag Policy
- Tipo de tag: anotado (`git tag -a`)
- Rama base: `main`
- Formato: `vMAJOR.MINOR.PATCH`

## Current Baseline
- `v0.1.0`: baseline operativo inicial con flujo `commit -> push -> autodeploy`
- `v0.1.1`: fix de producción para suprimir warning de Apache `ServerName`

## Version Meaning
- `PATCH`: fixes chicos o ajustes sin cambio funcional amplio
- `MINOR`: cambios funcionales importantes compatibles
- `MAJOR`: cambios grandes o incompatibles

## When To Create A Tag
Crear tag cuando el commit represente uno de estos hitos:
- baseline estable de producción
- cambio sensible en autenticación o base de datos
- entrega funcional importante
- punto seguro de rollback antes de una tanda grande de cambios

## Suggested Commands
```bash
git commit -m "Describe change"
git push origin main
git tag -a v0.1.1 -m "Stable production fix"
git push origin v0.1.1
```
