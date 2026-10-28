# Halcon Outcome Project

## Project Description
Web application system designed for managing and tracking dynamic order processes, warehouse operations, and delivery evidence workflows for Halcon.

## ER Diagram
![Entity Relationship Diagram](./docs/er_diagram.png)

## Database Seeder & Factory Implementation
- **Users Seeder**: Populates default system roles and creates 3 administrative and operational users.
- **Orders Factory**: Uses FakerPHP to generate 50 realistic order entries and their associated customer records.

# Backend RESTful API - Evidencia 2

## Descripción del Proyecto
Este proyecto implementa una API RESTful desarrollada con **Laravel 11** y **SQLite** para la gestión de usuarios y órdenes de compra. La arquitectura implementa validación estricta de datos, relaciones de integridad referencial entre clientes y órdenes, soporte para claves primarias tipo texto (`invoice_number`) y un mecanismo de archivado lógico (`soft deletes`) para las órdenes.

## Requisitos y Configuración
- **PHP**: 8.2+
- **Database**: SQLite
- **Environment**: GitHub Codespaces / Local

## Endpoints de la API

### Recurso: USERS

| Método | Endpoint | Descripción | Status Code |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/users` | Lista todos los usuarios registrados. | `200 OK` |
| `GET` | `/api/users/{id}` | Obtiene los detalles de un usuario por su ID. | `200 OK` |
| `POST` | `/api/users` | Crea un nuevo usuario. | `201 Created` |
| `PUT` | `/api/users/{id}` | Actualiza los datos de un usuario existente. | `200 OK` |

#### Payload para POST /api/users
```json
{
  "name": "Usuario Evidencia",
  "email": "usuario@halcon.com",
  "password": "password123"
}