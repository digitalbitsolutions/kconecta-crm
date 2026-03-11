# 📋 Kanban de Desarrollo: KConecta CRM Mobile

Este es el centro de orquestación para la nueva App en **React Native** consumiendo el backend **Laravel 12**.

---

## 🏗️ Estado General del Proyecto
- **Backend:** Laravel 12 (Confirmado/Auditando)
- **App Móvil:** Pendiente de inicializar (Expo)
- **Infraestructura AI:** Modelos locales en descarga (DeepSeek/Mistral)

---

## 🗂️ TABLERO KANBAN

### 📥 Backlog (Futuro)
- [ ] Implementar Push Notifications (Firebase/Expo Notifications).
- [ ] Configurar CI/CD para despliegues automáticos en TestFlight/Google Play Store.
- [ ] Añadir Modo Offline (Caché local con SQLite/AsynStorage).
- [ ] Integrar Analytics (Google Analytics o Mixpanel).

### 📋 Por Hacer (To Do)
- [ ] **[Backend]** Verificar configuración de CORS para peticiones desde la App Móvil.
- [ ] **[Backend]** Revisar endpoints de `ApiController.php` y asegurar devolución de JSON estandarizado.
- [ ] **[Backend]** Configurar Laravel Sanctum para autenticación por Token de larga duración.
- [ ] **[Mobile]** Inicializar proyecto Expo con Template de TypeScript + NativeWind (Tailwind).
- [ ] **[Mobile]** Crear estructura de carpetas (components, hooks, services, screens).
- [ ] **[Mobile]** Configurar Axios y TanStack Query para consumo de la API.

### ⏳ En Progreso (In Progress)
- [ ] **[Orquestación]** Auditoría inicial del código Laravel actual.
- [ ] **[Infraestructura]** Descarga y configuración de Modelos locales (Ollama).
- [ ] **[Infraestructura]** Instalación de Docker Desktop y configuración de **Plane**.

### ✅ Finalizado (Done)
- [x] **[Setup]** Clonar repositorio de GitHub.
- [x] **[Setup]** Confirmar versión de Laravel y arquitectura de rutas.
- [x] **[Setup]** Crear sistema de orquestura via Kanban Markdown.

---

## 🛠️ Especificación Técnica (Referencia Rápida)

### Endpoints Clave Identificados:
| Método | Endpoint | Descripción |
| :--- | :--- | :--- |
| GET | `/api/properties` | Búsqueda y listado de propiedades. |
| GET | `/api/services` | Búsqueda y listado de servicios. |
| POST | `/api/visitor/save` | Registro de nuevo visitante/lead. |
| POST | `/api/google/user/verify_token` | Auth vía Google (para el futuro). |

### Stack Tecnológico App Móvil:
- **Framework:** Expo (React Native).
- **Estilos:** NativeWind (Tailwind CSS).
- **Navegación:** Expo Router (basado en carpetas).
- **Estado/API:** TanStack Query + Zustand.
