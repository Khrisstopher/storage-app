# 📦 Storage App

Aplicación web de almacenamiento seguro desarrollada en PHP y JavaScript Vanilla, enfocada en la gestión de archivos con validaciones de seguridad, control de almacenamiento y sistema de roles.

---

## 🚀 Estado del proyecto

⚠️ En desarrollo avanzado.

Actualmente el proyecto ya cuenta con:

- Sistema de autenticación
- Inicio de sesión y registro
- Logout
- Dashboard de usuario
- Listado de archivos
- Eliminación de archivos
- Router MVC personalizado
- Arquitectura basada en POO
- Renderizado dinámico usando JavaScript Vanilla (Fetch API)
- Validación de archivos ZIP
- Panel de administración
- Descarga de Archivos subidos

Actualmente se está desarrollando:

- Restricción de extensiones peligrosas
- Gestión de cuotas de almacenamiento
- Sistema de grupos y roles avanzados
- Lógica de panel de administración

---

## 📌 Descripción

Storage App es una aplicación web diseñada para gestionar archivos de forma segura, permitiendo a los usuarios subir y administrar documentos mientras el sistema aplica reglas de validación desde el backend.

El proyecto fue desarrollado siguiendo una arquitectura MVC personalizada y utilizando PHP orientado a objetos junto con JavaScript Vanilla (ES6+) para el manejo asíncrono de peticiones y renderizado dinámico de la interfaz.

---

## ⚙️ Tecnologías utilizadas

### Backend
- PHP (POO)
- MySQL
- Arquitectura MVC personalizada

### Frontend
- HTML5
- CSS3
- JavaScript Vanilla (ES6+)
- Fetch API

### UI / UX
- Bootstrap 5
- SweetAlert 2

### Entorno
- XAMPP
- Apache

---

## ✨ Funcionalidades implementadas

- Registro de usuarios
- Inicio de sesión
- Cierre de sesión
- Dashboard protegido por sesión
- Listado de archivos del usuario
- Eliminación de archivos
- Sistema de rutas personalizado
- Controladores separados para vistas y API
- Respuestas JSON para peticiones asíncronas
- Validación de archivos con extensiones prohibidas en archivos zip

---

## 📁 Estructura del proyecto

```bash
storage-app/
│
├── app/
│   ├── controllers/
│   │   ├── api/                # Controladores para lógica de negocio y respuestas JSON
│   │   └── web/                # Controladores para renderizado de vistas
│   │
│   ├── core/                   # Núcleo del framework MVC personalizado
│   │   ├── Controller.php
│   │   ├── Router.php
│   │   └── View.php
│   │
│   ├── models/                 # Estructura destinada a entidades y acceso a datos
│   │
│   └── services/               # Servicios con lógica de negocio
│
├── config/                     # Configuración general y conexión a base de datos
│
├── public/                     # Punto de entrada público de la aplicación
│   ├── css/
│   ├── js/
│   ├── img/
│   └── index.php
│
├── storage/                    # Archivos subidos por los usuarios
│
├── sql/                        # Scripts SQL e inserciones iniciales
│
├── views/                      # Vistas renderizadas por el sistema
│
└── README.md
```

---

## 🧠 Arquitectura

El proyecto fue desarrollado utilizando una arquitectura MVC personalizada inspirada en frameworks modernos como Laravel, pero implementada completamente desde cero en PHP puro y Programación Orientada a Objetos (POO).

La aplicación separa responsabilidades en:

- **Controllers:** manejan el flujo de la aplicación.
- **Services:** encapsulan la lógica de negocio.
- **Views:** renderizan la interfaz de usuario.
- **Core:** contiene el núcleo del framework MVC personalizado.

Además, se implementó un sistema de rutas personalizado que diferencia:

- Rutas web (renderizado de vistas)
- Rutas API (respuestas JSON y lógica asíncrona)

---

## 📸 Capturas de la aplicación

### 🏠 Home

Vista principal de bienvenida de la aplicación.

![Home](docs/screenshots/home.png)

---

### 🔐 Login

Pantalla de inicio de sesión para usuarios registrados.

![Login](docs/screenshots/login.png)

---

### 📝 Registro

Formulario de registro de nuevos usuarios.

![Register](docs/screenshots/register.png)

---

### 📂 Dashboard

Panel principal del usuario donde puede visualizar y gestionar sus archivos.

![Dashboard](docs/screenshots/dashboard.png)

---

### Panel de administración

Restricción de tipos de archivos
![admin/settings](docs/screenshots/adminSettings.png)