# 🚀 PUNTO DE RECUPERACIÓN (Mensaje de Orquestación para Mí Mismo)

**Atención (Futuro Yo):** Cuando leas esto, significa que el usuario ha regresado a la sesión o se reinició el contexto. Nuestro objetivo inmediato es construir la **Calculadora Avanzada (AVM)** en Laravel.

## 🎯 Estado Actual (Recién Finalizado)
1. Ya importamos los precios base de M2 en la tabla `cadastral_prices` usando `cadastral:import`.
2. El servicio `CadastralCalculationService` ya calcula medias y tiene fallback dinámico por municipio.
3. En la página de inicio (`index.blade.php`) ya funciona el estimador rápido (JS) y ahora tiene un botón "Obtener estimación exacta" que redirige a `/calculadora-avanzada`.

## 🛠️ Próximo Paso A Ejecutar Inmediatamente

Debemos construir el motor de Tasación Avanzada. Sigue estrictamente este orden:

### 1. La Vista Frontend (Blade)
- Crear archivo `resources/views/page/calculadora_avanzada.blade.php`.
- Construir un formulario moderno y atractivo (preferiblemente tipo wizard paso a paso) que pregunte:
  - Dirección (Autocomplete de Google) y M² construidos.
  - Tipología (Piso, Ático, Bajo, Chalet independiente).
  - Estado de conservación (A reformar, Buen estado, Obra nueva).
  - Distribución (Habitaciones y Baños).
  - Extras clave (Ascensor, Terraza, Garaje, Piscina).
- Al final del formulario, debe haber un Call To Action (CTA) pidiendo Email o Teléfono para entregar la "Tasación Detallada" (generación de Leads).

### 2. El Backend y Algoritmo
- En `routes/web.php` registrar la ruta GET `/calculadora-avanzada` que retorne la vista.
- En `app/Services/CadastralCalculationService.php`, agregar el método `advancedEstimate(array $data)` que aplique los siguientes multiplicadores teóricos al valor base (el valor obtenido de `estimatePropertyValue`):
  - Ático (+15%), Bajo interior (-10%), Chalet independiente (+20%).
  - Estado a reformar (-20%), Obra nueva (+15%).
  - Sin ascensor en piso (-15%).
  - Garaje (+15.000€ a +25.000€ directos al total estimado).
- En `app/Http/Controllers/Api/CadastralController.php`, agregar el endpoint `advancedEstimate` (o similar) que reciba los datos del formulario, valide y consuma el nuevo servicio.

**Instrucción Final para mí mismo:** Lee esta información al reintegrarte y pregunta al usuario si desea que comiences inyectando el código de la vista Blade (`calculadora_avanzada.blade.php`) o el algoritmo del Backend.
