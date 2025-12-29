# Solicitudes Internas – Municipalidad de Villarrica

Sistema interno para la gestión de solicitudes de soporte informático.

## Stack
- Laravel 11
- PHP 8.3 (CLI)
- MySQL 8 (Docker)
- Blade
- Bootstrap

## Infraestructura
- MySQL corre en Docker
- Laravel corre en el host
- Datos persistidos en `~/servicios-db/data/mysql`

## Comandos útiles

### Levantar base de datos
```bash
cd ~/servicios-db
sudo docker compose up -d

## Migraciones 
php artisan migrate

## Servidor local
php artisan serve

