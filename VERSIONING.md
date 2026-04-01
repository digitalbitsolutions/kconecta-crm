# Versioning

## Goal
Definir una politica simple y estable para releases del CRM sin romper el flujo actual con Dokploy.

## Deployment Policy
Para cambios normales del proyecto:
1. Validar localmente.
2. Crear `commit` en `main`.
3. Hacer `push` a `origin/main`.
4. Esperar `autodeploy` en Dokploy.
5. Verificar login, panel y rutas criticas.

Usar redeploy manual solo si el despliegue automatico falla o queda algun endpoint inestable.

## Tag Policy
- Tipo de tag: anotado (`git tag -a`)
- Rama base: `main`
- Formato: `vMAJOR.MINOR.PATCH`

## Initial Convention
- `v0.1.0`: primer baseline operativo con flujo `commit -> push -> autodeploy`
- `PATCH`: fixes chicos o ajustes sin cambio funcional amplio
- `MINOR`: cambios funcionales importantes compatibles
- `MAJOR`: cambios grandes o incompatibles

## When To Create A Tag
Crear tag cuando el commit represente uno de estos hitos:
- baseline estable de produccion
- cambio sensible en autenticacion o base de datos
- entrega funcional importante
- punto seguro de rollback antes de una tanda grande de cambios

## Suggested Commands
```bash
git commit -m "Describe change"
git push origin main
git tag -a v0.1.0 -m "Initial operational baseline"
git push origin v0.1.0
```
