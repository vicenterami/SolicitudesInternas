# Solicitudes Internas – Municipalidad de Villarrica 🏢

Sistema integral de gestión de tickets y soporte informático (Help Desk) con capacidades de **tiempo real** y procesamiento asíncrono. Diseñado para optimizar el flujo de trabajo TI garantizando alta disponibilidad y una experiencia de usuario fluida (SPA-like).

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Reverb](https://img.shields.io/badge/Laravel_Reverb-WebSockets-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind-CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Axios](https://img.shields.io/badge/Axios-AJAX-5A29E4?style=for-the-badge&logo=axios&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Docker-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

---

## 📋 Características Principales

* **📊 Dashboard Ejecutivo en Vivo:** Métricas, contadores y gráficos de KPI (Tickets por Prioridad) que se actualizan automáticamente vía WebSockets.
* **🎫 Gestión de Tickets:** Ciclo de vida completo con estados (Pendiente 🔴, Asignada 🟡, Resuelta 🟢).
* **💬 Chat en Tiempo Real:** Sistema de comentarios con actualización instantánea (sin recargar la página), indicadores de edición y eliminación.
* **📎 Adjuntos:** Soporte para evidencias y archivos con almacenamiento seguro.
* **🛡️ Control de Acceso (RBAC):**
    * **Usuario:** Vista limitada a sus propias solicitudes.
    * **Técnico:** Vista global, capacidad de gestión y reasignación.
    * **Admin:** Vista global, capacidad de gestión completa y usuarios.
* **🔒 Seguridad:** Validación de datos server-side y protección de rutas con Policies.
* **⚡ Arquitectura Reactiva:** Interfaz optimizada con Alpine.js y Axios para una sensación de aplicación nativa.

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
| **Broadcasting** | **Laravel Reverb** | Servidor de WebSockets first-party. |
| **Frontend** | Blade + Tailwind + **Alpine.js** | Stack TALL modificado para velocidad. |
| **Cliente HTTP** | **Axios** | Peticiones asíncronas (AJAX). |
| **Cliente WS** | **Laravel Echo** | Cliente de WebSockets en JS. |
| **Base de Datos** | MySQL 8.0 (Docker) | Persistencia de datos. |
| **Colas** | Database Driver | Procesamiento asíncrono de eventos. |

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

## ⚙️ Configuración del Entorno (.env)

Para desplegar el proyecto, duplica el archivo .env.example a .env y configura las siguientes variables

```ini
APP_NAME="Solicitudes Internas"
APP_ENV=local
APP_KEY=base64:GENERA_TU_CLAVE_AQUI  # Ejecuta: php artisan key:generate
APP_DEBUG=true
APP_TIMEZONE=America/Santiago        # Configurado para Chile

# ⚠️ Cambiar 'localhost' por tu IP de red (ej: 192.168.1.50) si accedes desde otros PC
APP_URL=http://localhost

# Configuración Base de Datos (Docker Default)
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

# Colas y Archivos
BROADCAST_CONNECTION=reverb
FILESYSTEM_DISK=public       # CRÍTICO: Para ver imágenes adjuntas
QUEUE_CONNECTION=database    # CRÍTICO: Para procesar el chat

# Configuración de Reverb (WebSockets)
REVERB_APP_ID=100001
REVERB_APP_KEY=reverb_app_key_dev
REVERB_APP_SECRET=reverb_app_secret_dev
REVERB_HOST="0.0.0.0"
REVERB_PORT=8080
REVERB_SCHEME=http

# Configuración Frontend (Vite)
VITE_APP_NAME="${APP_NAME}"
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="localhost" # ⚠️ Debe coincidir con la IP de APP_URL
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```
---

## 🚀 Guía de Instalación Rápida (Docker Sail)

Este proyecto utiliza Laravel Sail. No necesitas instalar PHP ni MySQL en tu sistema, solo Docker Desktop.

### 1. Clonar y Configurar

```bash
git clone <url-del-repo>
cd SolicitudesInternas
cp .env.example .env
# (Edita el .env con los valores de arriba)
```

### 2. Instalar Dependencias

Usamos un contenedor temporal para instalar las librerías de PHP:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

### 3. Iniciar el Sistema

```bash
./vendor/bin/sail up -d       # Levantar contenedores
./vendor/bin/sail npm install # Instalar dependencias JS
./vendor/bin/sail npm run build # Compilar assets iniciales
```

### 4. Configuración Final (Base de Datos & Storage)

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed # Crea tablas y usuarios de prueba
./vendor/bin/sail artisan storage:link         # Vincula la carpeta pública
```
---

## 🔐 Usuarios de Prueba (Seeders)

El comando migrate:fresh --seed crea los siguientes accesos por defecto:

| Rol | Email | Contraseña |
| :--- | :--- | :--- |
| Admin | admin@example.com | password|
| Técnico | tecnico@example.com | password|
| Usuario | usuario@example.com | password|

---

## 🛠 Solución de Problemas Comunes

Las imágenes dan error 403: Asegúrate de que en tu .env tengas FILESYSTEM_DISK=public y hayas ejecutado ./vendor/bin/sail artisan storage:link.

El chat no se actualiza solo: Verifica que tengas corriendo queue:work y reverb:start. Si hiciste cambios en el código backend, reinicia la cola con ./vendor/bin/sail artisan queue:restart.

La hora de los tickets es incorrecta: Verifica que en tu .env tengas APP_TIMEZONE=America/Santiago y limpia la caché con ./vendor/bin/sail artisan config:clear.

---

## 💻 Guía de Desarrollo (Comandos Diarios)

Para que el sistema funcione al 100% (incluyendo chat en tiempo real y estilos), necesitas correr estos procesos. Se recomienda usar pestañas separadas en la terminal:

### 1. Servidor Principal

El sitio estará disponible en http://localhost (o tu IP de red).

```bash
./vendor/bin/sail up
```

### 2. Compilación de Assets (Vite)

Para cargar estilos y JS (Hot Reload).

```bash
./vendor/bin/sail npm run dev
```

### 3. WebSockets (Reverb)

Para que funcione el Chat en tiempo real.

```bash
./vendor/bin/sail artisan reverb:start
```

### 4. Cola de Trabajo (Worker)

Para procesar envíos de notificaciones en segundo plano.

```bash
./vendor/bin/sail artisan queue:work
```

---

### 4. El "Script Mágico" (Bonus)

Forma de no tener que escribir 4 comandos cada vez.
Crea un archivo llamado `dev.sh` en la raíz de tu proyecto:

```bash
touch dev.sh
chmod +x dev.sh
nano dev.sh
```

Y pega este contenido dentro:

```bash
#!/bin/bash
echo "🚀 Iniciando Entorno de Solicitudes Internas..."

# 1. Levantar Docker
./vendor/bin/sail up -d

# 2. Instalar dependencias de front si no existen
if [ ! -d "node_modules" ]; then
    ./vendor/bin/sail npm install
fi

# 3. Abrir pestañas o procesos en segundo plano es complejo en script simple,
# pero podemos usar un gestor de procesos ligero o instrucciones:

echo "✅ Contenedores Arriba."
echo "⚠️  Ahora ejecuta en pestañas separadas:"
echo "   1. ./vendor/bin/sail npm run dev"
echo "   2. ./vendor/bin/sail artisan reverb:start"
echo "   3. ./vendor/bin/sail artisan queue:work"
```

---

## 🐳 Manual de Operaciones: Docker y Base de Datos

Guía técnica para la gestión de contenedores y acceso a datos del sistema Solicitudes Internas.

### 1. Estado del Sistema

El proyecto corre sobre Laravel Sail (Docker Compose). Para verificar que todo esté funcionando:

```bash
docker ps
```

Deberías ver activos los siguientes servicios:

    solicitudesinternas-laravel.test-1: Aplicación Web (Puertos 80, 5173, 8080).

    solicitudesinternas-mysql-1: Base de Datos (Puerto 3306).


### 2. Acceso a Base de Datos (Clientes Externos)

Puedes conectar cualquier gestor de base de datos (DBeaver, HeidiSQL, TablePlus, Workbench) usando estas credenciales. Los datos persisten en el volumen sail-mysql aunque apagues el PC.

Parámetro,Valor
Host,127.0.0.1
Port,3306
User,sail
Password,password
Database,laravel

## 3. Gestión vía Terminal (CLI)

No es necesario instalar clientes gráficos. Sail incluye herramientas de línea de comandos.

## 🔌 Conectarse a MySQL

Entra a la consola SQL directamente dentro del contenedor:

```bash
./vendor/bin/sail mysql
```

Comandos útiles dentro de MySQL:

```bash
SHOW TABLES;       -- Ver todas las tablas
SELECT * FROM users; -- Ver usuarios registrados
EXIT;              -- Salir
```

## 🛠 Comandos de Mantenimiento

Reiniciar la Base de Datos (Borrado Completo): ⚠️ Advertencia: Esto borra todos los tickets y comentarios.

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

Ver Logs del Sistema: Si algo falla, revisa qué está pasando en los contenedores:

```bash
./vendor/bin/sail logs -f
```

## 4. Ciclo de Vida de los Contenedores

Iniciar el sistema (Segundo plano):

```bash
./vendor/bin/sail up -d
```

Detener el sistema:

```bash
./vendor/bin/sail stop
```

Destruir contenedores (Apagado total): Nota: No borra los datos de la BD, solo los contenedores.
```bash
./vendor/bin/sail down
```
