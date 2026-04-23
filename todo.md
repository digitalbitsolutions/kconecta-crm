# 📝 TODO - Seguimiento del Proyecto

Este archivo resume dónde nos quedamos y cuáles son los siguientes pasos críticos para la orquestación del desarrollo de la App React Native y el Backend Laravel.

---

## 🚩 ESTADO ACTUAL
- **Código Local:** Refactorización UI de 12 formularios completada y subida a GitHub.
- **Git:** Git LFS Saneado; ahora el repositorio es ligero e ignora multimedia. Pushed successfully.
- **Infraestructura:** Backups realizados en VPS (2026-04-16). CI/CD con Dokploy verificado y persistente.

---

## 🛠️ PENDIENTES INMEDIATOS

### 1. Resolución de Git (Prioridad Alta)
- [x] Ejecutar un "Clean Push" (resetear historial de Git) para subir el código al repositorio de la empresa sin errores de LFS. (LOGRADO vía Saneamiento del Índice).
- [x] Confirmar visibilidad del repo en GitHub para conectar con Dokploy. (CONFIRMADO, despliegue operativo).

### 2. Infraestructura & Orquestación
- [ ] Finalizar instalación de **Plane** (ya sea local en Docker o en el VPS vía Dokploy).
- [ ] Conectar Antigravity con Plane (API Key) o seguir usando `managed_docs/kanban.md`.

### 3. Auditoría del Backend (Laravel)
- [ ] Analizar a fondo `app/Http/Controllers/ApiController.php`.
- [ ] **CORS:** Configurar para permitir peticiones desde la App Móvil.
- [ ] **Auth:** Asegurar que Laravel Sanctum esté listo para login desde React Native.
- [ ] **API:** Estandarizar respuestas JSON en los principales endpoints (Propiedades, Servicios, Leads).

### 4. Desarrollo App Móvil (React Native)
- [ ] Inicializar proyecto con **Expo** + TypeScript.
- [ ] Configurar NativeWind (Tailwind CSS) para el diseño premium.
- [ ] Implementar cliente de API (Axios + TanStack Query).

---

## 💡 NOTAS PARA LA PRÓXIMA SESIÓN
Para saltar el error de Git LFS y subir el código limpio, el comando recomendado será:
`rm -rf .git && git init && git add . && git commit -m "Initial commit cleanup" && git remote add origin https://sttildeveloper:TOKEN_AQUI@github.com/digitalbitsolutions/kconecta-ag.git && git push -u origin main --force`

---
*Ultima actualización:* 2026-04-16 22:20 (Refactor UI Global y Cleanup Git finalizado)
