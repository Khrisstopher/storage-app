# 📦 Storage App

Aplicación web de almacenamiento de archivos desarrollada en PHP puro y JavaScript Vanilla, con arquitectura MVC personalizada, sistema de autenticación, control de roles y validaciones de seguridad en el backend.

---

## 🚀 Estado del proyecto

⚠️ En desarrollo activo.

**Módulos completados:**

- Sistema de autenticación (registro, login, logout)
- Dashboard de usuario con gestión de archivos
- Subida, listado, descarga y eliminación de archivos
- Validación de extensiones prohibidas (incluyendo contenido de archivos ZIP)
- Panel de administración global con restricción de extensiones
- Gestión de cuota dinámica desde el panel de administración global
- Límite de cuota establecido en gerarquía de usuario --> grupo --> global

**En desarrollo:**
- Modificación de grupo del usuario
- Modificación de cuota de usuario
- Mostrar Grupo y cuota de usuario en lavista de Administración de usuarios
- Eliminar usuario desde Administración de ususarios

---

## 📌 Descripción

Storage App es una aplicación web para gestionar archivos de forma segura. Los usuarios pueden subir, listar, descargar y eliminar sus archivos. El sistema aplica validaciones desde el backend: extensiones bloqueadas, inspección del contenido de ZIPs, y límite de almacenamiento por usuario.

El proyecto fue desarrollado siguiendo una arquitectura MVC personalizada en PHP orientado a objetos, con JavaScript Vanilla (ES6+) y Fetch API para el manejo asíncrono de peticiones y renderizado dinámico de la interfaz, sin dependencia de frameworks externos.

---

## ⚙️ Tecnologías utilizadas

### Backend
- PHP 8+ (POO Avanzada con Namespaces y Autoloading lógico)
- MySQL
- PDO
- Arquitectura MVC personalizada

### Frontend
- HTML5 / CSS3
- JavaScript Vanilla (ES6+)
- Fetch API

### UI
- Bootstrap 5
- SweetAlert2
- Bootstrap Icons

### Entorno de desarrollo
- XAMPP
- Apache con `.htaccess` para URL rewriting

---

## ✨ Funcionalidades implementadas

- Registro e inicio de sesión con validaciones en backend
- Regeneración de ID de sesión en login (protección contra session fixation)
- Cierre de sesión con destrucción completa de cookie y sesión
- Dashboard protegido por sesión
- Subida de archivos con validación de extensiones bloqueadas
- Inspección del contenido de archivos ZIP para detectar extensiones prohibidas dentro
- Control de cuota de almacenamiento por usuario (límite de 10 MB)
- Resolución automática de nombres duplicados (`archivo (1).pdf`, `archivo (2).pdf`, etc.)
- Listado de archivos del usuario con iconos por tipo
- Descarga segura de archivos (acceso verificado por sesión y propiedad)
- Eliminación de archivos con confirmación y transacción BD + filesystem
- Panel de administración protegido por rol
- Configuración de extensiones bloqueadas desde el panel admin
- Router personalizado con rutas web (vistas) y rutas API (JSON)
- Respuestas JSON estandarizadas para todas las peticiones asíncronas
- Separación de carpetas de almacenamiento por usuario usando `external_id` (sin exponer IDs internos)
- Crear grupos con sus cuotas específicas y modificar los existentes

---

## 🔐 Seguridad implementada

- Contraseñas hasheadas con `password_hash()` / `password_verify()`
- Queries con PDO y prepared statements reales (protección contra SQL injection)
- `session_regenerate_id(true)` en cada login
- Destrucción completa de sesión y cookie en logout
- `external_id` aleatorio (`bin2hex(random_bytes(16))`) para aislar carpetas de usuario
- Rutas de almacenamiento fuera del directorio `public/`
- Validación de extensiones en el servidor (no solo en cliente)
- Inspección recursiva del contenido de ZIPs
- Rutas admin protegidas por autenticación + verificación de `role_id`
- Mensajes de error genéricos al usuario (sin exponer trazas ni SQLSTATE)
- Gestión de Sesiones Centralizada: Implementación de clase Session para mitigar errores de manipulación directa de $_SESSION.

---

## 📁 Estructura del proyecto

```
storage-app/
│
├── app/
│   │   .htaccess                             # Bloquea acceso directo a /app
│   │
│   ├── controllers/
│   │   ├── api/                              # Controladores API (JSON)
│   │   │   ├── AdminSettingController.php
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── GroupController.php
│   │   │   └── UserController.php
│   │   │
│   │   └── web/                              # Controladores para vistas
│   │       ├── AdminSettingController.php
│   │       ├── AuthController.php
│   │       └── DashboardController.php
│   │
│   ├── core/                                 # Núcleo del mini framework MVC
│   │   ├── Controller.php                    # Clase base de controladores
│   │   ├── Router.php                        # Sistema de rutas y dispatcher
│   │   ├── Session.php                       # Manejo centralizado de sesiones
│   │   └── View.php                          # Renderizado de vistas y layouts
│   │
│   ├── helpers/                              # Utilidades reutilizables
│   │   ├── FileHelper.php
│   │   └── ResponseHelper.php                # Respuestas JSON estandarizadas
│   │
│   ├── models/                               # Acceso a base de datos
│   │   ├── AdminSettingModel.php
│   │   ├── AuthModel.php
│   │   ├── DashboardModel.php
│   │   ├── GroupModel.php
│   │   └── UserModel.php
│   │
│   └── services/                             # Lógica de negocio
│       ├── AdminSettingService.php
│       ├── AuthService.php
│       ├── DashboardService.php
│       ├── GroupService.php
│       ├── UserService.php
│       │
│       └── handlers/
│           └── StorageHandler.php            # Gestión física del filesystem
│
├── config/
│   ├── app.php                               # Configuración global y constantes
│   └── db_connection.php                     # Clase Database (PDO)
│
├── docs/
│   └── screenshots/                          # Capturas de pantalla del proyecto
│       ├── adminSettings.png
│       ├── dashboard.png
│       ├── home.png
│       ├── login.png
│       └── register.png
│
├── logs/
│   └── debug.log                             # Registro de errores internos
│
├── postman/                                  # Recursos de pruebas API
│   ├── collections/
│   ├── environments/
│   ├── flows/
│   ├── globals/
│   │   └── workspace.globals.yaml
│   ├── mocks/
│   └── specs/
│
├── public/                                   # Único directorio público
│   │   .htaccess                             # Rewrite rules
│   │   index.php                             # Front controller
│   │
│   ├── css/
│   │   ├── admin/
│   │   │   └── settings.css
│   │   │
│   │   ├── auth/
│   │   │   ├── home.css
│   │   │   ├── login.css
│   │   │   └── register.css
│   │   │
│   │   └── files/
│   │       └── dashboard.css
│   │
│   ├── img/
│   │   ├── KHRISM.ico
│   │   └── KHRISM.png
│   │
│   └── js/
│       │   main.js                           # Configuración global JS
│       │
│       ├── admin/
│       │   ├── global_settings.js
│       │   ├── groups.js
│       │   └── users.js
│       │
│       ├── auth/
│       │   ├── login.js
│       │   └── register.js
│       │
│       └── files/
│           └── dashboard.js
│
├── sql/
│   └── consultas.sql                         # Scripts SQL iniciales
│
├── storage/
│   └── uploads/                              # Archivos subidos fuera del webroot
│       ├── .gitkeep
│       └── {external_id}/                    # Directorio aislado por usuario
│
├── views/
│   │   404.php
│   │   dashboard.php
│   │   home.php
│   │   login.php
│   │   register.php
│   │
│   ├── admin/
│   │   ├── global_settings.html
│   │   ├── groups.html
│   │   └── users.html
│   │
│   └── layouts/
│       ├── admin.php                         # Layout administrativo
│       └── main.php                          # Layout principal
│
├── .gitignore
└── README.md
```

---

## 🧠 Arquitectura

Arquitectura MVC personalizada implementada desde cero en PHP puro, sin frameworks externos. Inspirada en patrones de frameworks modernos como Laravel pero sin sus dependencias.

### Flujo de una petición

```
index.php → Router → Controller → Service → Model → DB
                                          ↓
                                     StorageHandler (filesystem)
```

### Capas y responsabilidades

| Capa | Responsabilidad |
|---|---|
| **Router** | Recibe la URL, valida el verbo HTTP y despacha al controller correcto |
| **Controllers/web** | Verifican autenticación y renderizan vistas |
| **Controllers/api** | Verifican autenticación/rol y retornan JSON, además de un log de error interno en logs/debug.log |
| **Services** | Contienen toda la lógica de negocio (validaciones, reglas, orquestación) |
| **Models** | Ejecutan las queries a la BD y retornan datos crudos |
| **Helpers** | Funciones utilitarias estáticas reutilizables entre servicios |
| **StorageHandler** | Gestiona las operaciones físicas sobre el filesystem |
| **View / Layout** | Renderizan las vistas PHP con datos inyectados |

### Separación de rutas

El router diferencia dos tipos de rutas:

- **Rutas web** → responden con vistas HTML renderizadas en el servidor
- **Rutas API** → responden con JSON para las peticiones asíncronas del frontend

---

## 🛠️ Instalación y configuración

### Requisitos

- PHP 8.0+
- MySQL 5.7+
- Apache con `mod_rewrite` habilitado
- XAMPP (u otro entorno local equivalente)

### Pasos

1. Clonar o descomprimir el proyecto dentro del directorio de tu servidor local:
   ```
   /xampp/htdocs/storage-app/
   ```

2. Importar la base de datos. Crear primero la BD en tu gestor MySQL:
   ```sql
   CREATE DATABASE storage_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
   Luego ejecutar el script `sql/consultas.sql`.

3. Configurar la conexión a la base de datos en `config/app.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'storage_app');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

4. Verificar que `BASE_URL` en `config/app.php` coincide con tu entorno:
   ```php
   define('BASE_URL', '/storage-app/public/');
   ```

5. Asegurarse de que `mod_rewrite` está activo en Apache y que el `.htaccess` de `public/` tiene permisos de lectura.

6. Acceder desde el navegador:
   ```
   http://localhost/storage-app/public/
   ```

### Usuario administrador de prueba

```
Email:    admin@test.com
Password: admin123
```

---

## 🌐 Rutas Web

| Método | Ruta | Descripción | Auth requerida |
|---|---|---|---|
| `GET` | `/` | Página principal | No |
| `GET` | `/home` | Página de inicio | No |
| `GET` | `/login` | Vista de inicio de sesión | No |
| `GET` | `/register` | Vista de registro | No |
| `GET` | `/dashboard` | Panel principal del usuario | Sí |
| `GET` | `/admin/settings` | Configuración global del sistema | Admin |
| `GET` | `/admin/groups` | Gestión de grupos | Admin |
| `GET` | `/admin/users` | Gestión de usuarios | Admin |

---

## 📡 Endpoints de la API

| Método | Ruta | Descripción | Auth requerida |
|---|---|---|---|
| `POST` | `/auth/register` | Registro de nuevo usuario | No |
| `POST` | `/auth/login` | Inicio de sesión | No |
| `POST` | `/auth/logout` | Cierre de sesión | Sí |
| `GET` | `/files/list` | Listar archivos del usuario | Sí |
| `POST` | `/files/upload` | Subir archivo | Sí |
| `POST` | `/files/delete` | Eliminar archivo | Sí |
| `GET` | `/files/download?id={id}` | Descargar archivo | Sí |
| `GET` | `/global/listFileRestrictions` | Obtener extensiones bloqueadas | Admin |
| `POST` | `/global/saveFileRestrictions` | Actualizar extensiones bloqueadas | Admin |
| `GET` | `/global/listQuotaGlobalLimit` | Obtener el límite de cuota global | Admin |
| `POST` | `/global/saveQuotaGlobalLimit` | Actualizar el límite de cuota global | Admin |
| `GET` | `/groups/list` | Listar grupos | Admin |
| `POST` | `/groups/create` | Crear grupo | Admin |
| `PUT` | `/groups/update` | Actualizar grupo | Admin |
| `DELETE` | `/groups/delete` | Eliminar grupo | Admin |
| `GET` | `/users/list` | Listar usuarios | Admin |
| `PUT` | `/users/update` | Actualizar usuario | Admin |
| `DELETE` | `/users/delete` | Eliminar usuario | Admin |

Todas las respuestas tienen la estructura:

```json
{
  "status": true,
  "message": "Descripción del resultado",
  "data": null
}
```

---

## 📸 Capturas de la aplicación

### 🏠 Home
Vista principal de bienvenida.

![Home](docs/screenshots/home.png)

---

### 🔐 Login
Pantalla de inicio de sesión.

![Login](docs/screenshots/login.png)

---

### 📝 Registro
Formulario de registro de nuevos usuarios.

![Register](docs/screenshots/register.png)

---

### 📂 Dashboard
Panel del usuario para visualizar y gestionar sus archivos.

![Dashboard](docs/screenshots/dashboard.png)

---

### ⚙️ Panel de administración
Configuración de extensiones de archivo restringidas. (en desarrollo)

![Admin Settings](docs/screenshots/adminSettings.png)
