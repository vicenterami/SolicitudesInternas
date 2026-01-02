# Solicitudes Internas – Municipalidad de Villarrica 🏢

Sistema de gestión de tickets y soporte informático desarrollado a medida para optimizar el flujo de trabajo entre funcionarios municipales y el departamento de TI.

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Docker-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind-CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)

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

## 🛠 Stack Tecnológico

* **Backend:** Laravel 11 Framework.
* **Lenguaje:** PHP 8.3 (CLI).
* **Base de Datos:** MySQL 8.0 (Ejecutándose en contenedor Docker).
* **Frontend:** Blade Templates + Tailwind CSS (Laravel Breeze).
* **Gráficos:** Chart.js.

---

## 🔐 Arquitectura y Seguridad

Este proyecto implementa prácticas de desarrollo profesional y seguro, evitando lógica "hardcoded".

### 1. Políticas de Seguridad (Policies & Gates)
La autorización no se maneja dentro de los controladores, sino a través de **Laravel Policies**:
* **Centralización:** Las reglas de negocio (`SolicitudPolicy`) determinan quién puede ver o editar un recurso.
* **Implementación:** Se utiliza `Gate::authorize('update', $solicitud)` para proteger tanto las vistas como las acciones de base de datos contra accesos no autorizados.

### 2. Acceso a Datos (Eloquent ORM)
Interacción con la base de datos mediante **Eloquent**, garantizando:
* Protección nativa contra **SQL Injection**.
* Manejo eficiente de relaciones (`BelongsTo`, `HasMany`).
* Código limpio y mantenible sin SQL puro.

---

## 📂 Esquema de Base de Datos

Principales entidades del sistema:

| Tabla | Descripción | Relaciones Clave |
| :--- | :--- | :--- |
| **users** | Funcionarios y personal TI. | `belongsTo` Role. |
| **roles** | Definición de permisos (1:Usuario, 2:Técnico, 3:Admin). | `hasMany` Users. |
| **solicitudes** | Ticket de soporte (Núcleo del sistema). | `belongsTo` Creador, `belongsTo` Técnico. |
| **comentarios** | Historial de conversación. | `belongsTo` Solicitud. |
| **adjuntos** | Evidencias (Imágenes/PDF). | `belongsTo` Solicitud. |

---

## 🚀 Guía de Instalación y Despliegue

### 1. Infraestructura de Base de Datos
El proyecto requiere una instancia de MySQL corriendo (configurada vía Docker).

```bash
cd ~/servicios-db
# Levantar el contenedor de MySQL en segundo plano
sudo docker compose up -d