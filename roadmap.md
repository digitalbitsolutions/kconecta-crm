# Kconecta CRM - Roadmap

## Status Update (2026-04-29)
- Calculadora catastral operativa en produccion (`kconecta.com`) con flujo completo:
- validacion de direccion por Google en home
- calculo base por `postal_code` + `m2`
- navegacion a tasacion avanzada con resultado visible
- Backend/API desplegado y verificado:
- `GET /api/cadastral/estimate`
- `POST /api/cadastral/advanced-estimate`
- Migracion `cadastral_prices` aplicada en produccion con registro manual en tabla `migrations` legacy.
- Import de dataset catastral ejecutado en produccion desde CSV (`precios_m2_catalunya_detallado.csv`).
- Resultado: error de conexion eliminado; ahora la UI muestra calculo o mensaje controlado de datos insuficientes.

## Status Update (2026-04-27)
- Provider first-stage business rules (JM) applied in CRM flows:
- provider signup no longer asks for document type/number
- provider address is now optional in registration/profile update for this stage
- provider service publish flow no longer blocks on missing validated address
- Online smoke result reported:
- profile edit OK
- provider services flow OK
- Remaining closure for this stage:
- Gala and JM to run online business validation

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
- Proveedores/servicios (`2026-04-22`):
- formulario de alta de servicios para proveedor ya no duplica datos de usuario
- direccion de servicio se toma desde perfil validado del proveedor
- perfil del proveedor:
- foto/logo se recorta a `350x350` y se guarda en WebP
- `username` queda bloqueado (solo lectura)
- landing de proveedor en `/post/services`:
- slider multi-imagen funcional con controles y dots
- boton de ver publicacion en listado abre en `_blank`
- boton `Ver video` retirado de la cabecera segun decision UX (video queda en bloque inferior)
- detalle publico de servicio:
- fix de carga de video por `service_id`
- mapas de resultados:
- icono legacy reemplazado por icono Kconecta
- Produccion (`2026-04-22`):
- verificacion de email estabilizada con redireccion directa por rol tras confirmacion
- persistencia de media de logos completada con volumen:
- `/var/www/html/public/img/photo_profile`

## Phase 1 - Stabilize Production
- Investigar drift entre referencias en BD y archivos fisicos de media.
- Revisar si en una fase posterior conviene limpiar definitivamente los valores legacy de `type_of_terrain` (`Servicios`, `Industrial`, `Afectado`) tras verificar que ningun flujo dependa de ellos.
- Corregir UX de subida de video:
- mensaje real de limite
- validacion previa
- futura compresion frontend
- Revisar si alta de servicios requiere el mismo hardening que propiedades.
- Ejecutar pasada final de normalizacion UTF-8 en vistas legacy con mojibake.

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
- `Admin -> Usuario detalle` estabilizado:
- fix de `500` en `/users/{id}`.
- desglose de servicios de proveedor validado con tags no clicables.
- recorrido online final validado como correcto.
- `Backoffice -> Mis propiedades` refinado visualmente:
- filtros en dos filas segun referencia objetivo
- placeholders y labels alineados
- acciones `Filtrar/Limpiar` y responsive ajustados
- Proveedor (fase 1 reglas JM):
- registro proveedor adaptado en UI para no solicitar documento en alta
- edicion de perfil proveedor sin bloqueo por direccion
- publicacion/guardado de servicios proveedor sin bloqueo por direccion faltante

## Phase 5 - Operational Reliability
- Mejorar health checks y observabilidad de app + DB.
- Vigilar drift entre migraciones del repo y runtime productivo.
- Documentar incident response y rollback operativo.

- `Detalle publico de propiedades` pulido visual/funcional:
- cards vacias eliminadas por render condicional estricto.
- metadatos superiores con `N/A` para datos faltantes.
- jerarquia de cards ajustada (Fianza/Estado junto a M2 construidos).

## Sub-plan: Backend Calculador Catastral (Precios M2)
*Plan de implementación asíncrona para la importación y cálculo de precios por metro cuadrado.*

- [x] **1. Migration (`cadastral_prices`)**
  - Campos: `id`, `province`, `municipality`, `neighborhood`, `postal_code` (string 10), `price_m2_eur` (decimal 10,2), `import_batch_id` (trazabilidad e histórico de cargas), `created_at`, `updated_at`.
  - Índices simples: `postal_code`, `municipality`.
  - Índice compuesto: `[postal_code, municipality]`.
  - Restricción única (evita duplicados): `unique(postal_code, municipality, neighborhood)`.
- [x] **2. Model (`CadastralPrice`)**
  - Definir guardeds/fillables.
  - Setup para soporte de consultas agregadas.
- [x] **3. Artisan Command (`cadastral:import {path}`)**
  - Lectura por streaming usando `fgetcsv` para máxima eficiencia de RAM.
  - Validaciones: trim de strings, normalización UTF-8, `postal_code` obligatorio, `price_m2_eur` numérico positivo.
  - Gestión de rechazos: logging de filas inválidas.
  - Persistencia vía `upsert`: Insertar si la clave no existe; si el unique key existe, actualizar `price_m2_eur`.
  - Trazabilidad de carga: Guardar y emitir el total de filas procesadas/inválidas y el `import_batch_id`.
- [x] **4. Servicio de Consulta (`CadastralCalculationService`)**
  - Entrada: `postal_code`.
  - Salida: Estructura con `avg_price_m2`, `min`, `max`, `count` para cálculo aproximado rápido y robusto.
- [ ] **5. Testing (PHPUnit/Pest)**
  - Test: importación inicial correcta.
  - Test: upsert (re-importación de datos no debe duplicar, sino actualizar precio).
  - Test: consulta con `postal_code` existente devuelve valores estadísticos.
  - Test: consulta con `postal_code` inexistente controla respuesta.

## Cadastral Production Closure (2026-04-29)
- Backend calculadora catastral operativo en produccion.
- Tabla `cadastral_prices` creada en produccion.
- Registro manual en tabla legacy `migrations` aplicado para la migracion `2026_04_28_000000_create_cadastral_prices_table`.
- CSV `precios_m2_catalunya_detallado.csv` importado en produccion.
- Flujo online validado: home -> calculo base -> tasacion avanzada con valor estimado.
- Pendiente recomendado: ejecutar backup post-deploy y guardar ruta en `tasks.md`.
