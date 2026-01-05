# Solicitudes Internas – Municipalidad de Villarrica 🏢

Sistema integral de gestión de tickets y soporte informático (Help Desk) con capacidades de **tiempo real** y procesamiento asíncrono. Diseñado para optimizar el flujo de trabajo TI garantizando alta disponibilidad y una experiencia de usuario fluida (SPA-like).

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Reverb](https://img.shields.io/badge/Laravel_Reverb-WebSockets-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind-CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Axios](https://img.shields.io/badge/Axios-AJAX-5A29E4?style=for-the-badge&logo=axios&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Docker-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

---

## 📋 Características Principales

* **📊 Dashboard Ejecutivo:** Visualización gráfica de métricas, KPIs y contadores de estado en tiempo real.
* **🎫 Gestión de Tickets:** Ciclo de vida completo (Creación, Asignación, Resolución).
* **🛡️ Control de Acceso (RBAC):**
    * **Usuario:** Vista limitada a sus propias solicitudes.
    * **Técnico/Admin:** Vista global, capacidad de gestión y reasignación.
* **💬 Interacción:** Hilo de comentarios por solicitud y subida de archivos adjuntos.
* **🔒 Seguridad:** Validación de datos server-side y protección de rutas.

---

## 🚀 Evolución y Capacidades

Este proyecto va más allá de un CRUD tradicional, implementando una arquitectura moderna para resolver problemas de escalabilidad y latencia:

### ⚡ Interactividad en Tiempo Real (Real-Time)
* **WebSockets con Laravel Reverb:** Actualización instantánea de interfaces sin recarga manual.
* **Chat en Vivo:** Los comentarios en las solicitudes aparecen instantáneamente para todos los participantes activos.
* **Notificaciones Push:** El dashboard administrativo se actualiza automáticamente al recibir nuevas solicitudes (cambio de color y alertas visuales).

### 🔄 Procesamiento Asíncrono (Queues)
* **Optimización de Rendimiento:** Las tareas pesadas (como el envío de notificaciones y broadcasting) se delegan a una **Cola de Trabajo (Queue)** en base de datos.
* **Experiencia de Usuario:** El servidor responde inmediatamente a las peticiones HTTP, mientras un *Worker* procesa la lógica de difusión en segundo plano, evitando tiempos de carga ("freezing").

### 🌐 AJAX & SPA Experience
* **Navegación Fluida:** Implementación de **Axios** para el envío de formularios y comentarios, eliminando el parpadeo de recarga de página completo.
* **Prevención de Duplicados:** Lógica inteligente en el Frontend para gestionar la concurrencia entre la respuesta AJAX local y el evento WebSocket entrante.

---

## 🛠 Stack Tecnológico

| Capa | Tecnología | Descripción |
| :--- | :--- | :--- |
| **Backend** | Laravel 11 (PHP 8.3) | Framework principal. |
| **WebSockets** | **Laravel Reverb** | Servidor de sockets first-party para broadcasting. |
| **Frontend** | Blade + Tailwind + **Alpine.js** | Renderizado híbrido con reactividad ligera. |
| **Cliente HTTP** | **Axios** | Peticiones asíncronas para comentarios sin reload. |
| **Cliente WS** | **Laravel Echo** | Escucha de canales privados y públicos en JS. |
| **Base de Datos** | MySQL 8.0 (Docker) | Persistencia relacional. |
| **Colas** | Database Driver | Gestión de Jobs (`jobs` table) y eventos fallidos. |

---

## 🔐 Arquitectura y Seguridad

Este proyecto implementa prácticas de desarrollo profesional y seguro, evitando lógica "hardcoded".

### 1. Políticas de Seguridad (Policies & Gates)
La autorización no se maneja dentro de los controladores, sino a través de **Laravel Policies**:
* **Centralización:** Las reglas de negocio (`SolicitudPolicy`) determinan quién puede ver o editar un recurso.
* **Implementación:** Se utiliza `Gate::authorize('update', $solicitud)` para proteger tanto las vistas como las acciones de base de datos contra accesos no autorizados.
* **Validación de Roles:** Middleware y Policies estrictas para asegurar que solo Admins/Técnicos gestionen tickets.

### 2. Acceso a Datos (Eloquent ORM)
Interacción con la base de datos mediante **Eloquent**, garantizando:
* Protección nativa contra **SQL Injection**.
* Manejo eficiente de relaciones (`BelongsTo`, `HasMany`).
* Código limpio y mantenible sin SQL puro.

### 3. Modelo de Eventos (Event-Driven)
El sistema sigue el patrón de "Observador" mediante Eventos y Listeners:
1.  **Acción:** Usuario crea comentario.
2.  **Controlador:** Guarda en BD y despacha evento `NuevoComentarioCreado`.
3.  **Cola:** El evento se serializa y se guarda en la tabla `jobs`.
4.  **Worker:** Procesa el job y envía el mensaje al servidor Reverb.
5.  **Reverb:** Distribuye el mensaje a los clientes conectados (Browsers).

---

## 📂 Esquema de Base de Datos

La persistencia de datos se gestiona mediante **MySQL 8.0**. El esquema se divide en dos grupos lógicos: Entidades de Negocio y Tablas de Infraestructura.

### 1. Entidades de Negocio (Core)

Tablas principales que soportan la lógica de la aplicación.

| Tabla | Descripción | Atributos Clave / Restricciones | Relaciones (FK) |
| :--- | :--- | :--- | :--- |
| **users** | Usuarios del sistema (Funcionarios y TI). | `email` (Unique). | `belongsTo(roles)` |
| **roles** | Catálogo de permisos. | `1: Usuario`, `2: Técnico`, `3: Admin`. | `hasMany(users)` |
| **solicitudes** | **(Tabla Principal)** Tickets de soporte. | `prioridad`: ENUM('baja', 'media', 'alta')<br>`estado`: ENUM('pendiente', 'asignada', 'resuelta') | `belongsTo(users, 'user_id')`<br>`belongsTo(users, 'tecnico_id')` *(Nullable)* |
| **comentarios** | Hilo de chat en tiempo real. | `comentario` (Text). | `belongsTo(solicitud)`<br>`belongsTo(user)` |
| **adjuntos** | Archivos y evidencias subidas. | `ruta_archivo` (String). | `belongsTo(solicitud)` |

### 2. Infraestructura y Sistema (Async & Cache)

Tablas gestionadas automáticamente por Laravel para soportar la arquitectura asíncrona y de alto rendimiento.

| Tabla | Función Técnica | Detalle Técnico |
| :--- | :--- | :--- |
| **jobs** | **Cola de Trabajo.** Almacena eventos serializados (como `NuevoComentarioCreado`) esperando ser procesados por el Worker. | `payload` (LongText): Contiene el objeto serializado.<br>`available_at`: Timestamp de ejecución. |
| **failed_jobs** | **Auditoría de Errores.** Almacena jobs que fallaron tras múltiples intentos (`attempts`) para depuración posterior. | `exception`: Stack trace del error.<br>`payload`: Datos que causaron el fallo. |
| **cache** | Almacenamiento temporal para acelerar consultas frecuentes. | Driver de caché configurado en base de datos. |
| **sessions** | Gestión de sesiones de usuario activas. | Permite invalidar sesiones desde el backend. |

---

## 🚀 Guía de Instalación y Despliegue

### 1. Infraestructura de Base de Datos
El proyecto requiere una instancia de MySQL corriendo (configurada vía Docker).

```bash
cd ~/servicios-db
# Levantar el contenedor de MySQL en segundo plano
sudo docker compose up -d
```

---

## 💻 Guía de Ejecución (Entorno Local)

Debido a la arquitectura desacoplada, el entorno de desarrollo requiere **4 procesos simultáneos**. Se recomienda usar terminales divididas o pestañas.

### 1. Servidor Web (Laravel)
Maneja las peticiones HTTP estándar (vistas, API).
```bash
php artisan serve
# Corre en: [http://127.0.0.1:8000](http://127.0.0.1:8000)
```

### 2. Compilación de Assets (Vite)

Maneja el Hot Module Replacement (HMR) para CSS y JS.

```bash
npm run dev
# Corre en: http://localhost:5173
```

### 3. Servidor de WebSockets (Reverb)

El "Walkie-Talkie" del sistema. Mantiene las conexiones persistentes.

```bash
php artisan reverb:start
# Corre en: localhost:8080
```

### 4. Procesador de Colas (Worker) 👷

El trabajador incansable. Procesa eventos y notificaciones en segundo plano.

**Nota Importante:** Si este proceso no corre, los mensajes de chat no se enviarán a los otros usuarios.

```bash
php artisan queue:work
```

## 📋 Comandos Útiles

Si realizas cambios en el código backend (Eventos/Jobs) mientras el worker está corriendo, recuerda reiniciar la cola:

```bash
php artisan queue:restart
```
Limpiar caché de configuración (útil si cambias .env):

```bash
php artisan config:clear
```

Desarrollado con ❤️ para la Municipalidad de Villarrica.