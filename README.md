# 📦 Storage App

Aplicación web avanzada de almacenamiento y gestión segura de archivos desarrollada en PHP estructurado bajo el patrón arquitectónico MVC (Model-View-Controller) personalizado y JavaScript Vanilla en el cliente. Diseñada como un proyecto de ingeniería personal para demostrar la viabilidad, optimización y control total sobre la lógica de negocio y seguridad en el backend sin depender de frameworks monolíticos pesados.

---

## 🚀 Estado del proyecto

<p align="center">
  <img src="https://img.shields.io/badge/Estado-Terminado-success?style=for-the-badge&logo=github" alt="Estado del proyecto: Terminado">
</p>

<div align="center">

# ✅ Proyecto Finalizado

Este sistema ha sido completado, auditado y se encuentra totalmente funcional para entornos de desarrollo local.

</div>

---

## 📌 Descripción

Storage App es una plataforma para gestionar archivos de forma eficiente y segura. Los usuarios autenticados disponen de un aislamiento completo para subir, listar, descargar y eliminar sus documentos en directorios protegidos.

El núcleo de la aplicación reside en su motor de validaciones en el backend, el cual intercepta las cargas para garantizar el uso justo del almacenamiento del servidor mediante políticas dinámicas de cuotas, restricciones estrictas de tipos de archivo y un analizador de inspección binaria que audita de forma recursiva los archivos comprimidos (`.zip`) antes de conceder su persistencia.

---

## ⚙️ Tecnologías utilizadas

### Backend
- **PHP 8.0+** (Programación Orientada a Objetos Avanzada con espacios de nombres y Autoloading nativo)
- **MySQL** (Esquema relacional optimizado con restricciones de integridad referencial en cascada)
- **PDO (PHP Data Objects)** (Conexión segura configurada con emulación de Prepared Statements desactivada)

### Frontend
- **JavaScript Vanilla (ES6+)** (Arquitectura basada en eventos y manipulación asíncrona del DOM)
- **Fetch API** (Comunicación asíncrona cliente-servidor)

### UI & Componentes
- **Bootstrap 5** (Layout responsive y estructuración visual limpia)
- **SweetAlert2** (Feedback UX inmediato para notificaciones dinámicas del sistema)
- **Bootstrap Icons** (Soporte iconográfico para tipos de archivo y acciones del panel)

### Servidor y Entorno
- **Apache** (Uso de directivas `.htaccess` para el enmascaramiento de rutas mediante URL Rewriting)
- **XAMPP / Local PHP Environment**

---

## ✨ Características y Funcionalidades Principales

### Gestión y Reglas de Almacenamiento (Backend-Driven)
- **Control Jerárquico de Cuotas (Límite de Almacenamiento):** Sistema inteligente de resolución de cuotas con prioridades escalonadas:
  1. *Prioridad Máxima:* Límite específico asignado de forma personalizada a un usuario.
  2. *Prioridad Media:* Límite heredado a través del Grupo al que pertenece el usuario.
  3. *Prioridad por Defecto:* Límite del Sistema Global Inicial (configurado por defecto en 50 MB).
- **Filtro Global de Extensiones Prohibidas:** Bloqueo perimetral en el servidor contra la subida de scripts ejecutables o potencialmente peligrosos (ej. `.exe`, `.bat`, `.js`, `.php`).
- **Auditoría e Inspección de Archivos ZIP:** Si un usuario intenta subir un contenedor comprimido `.zip`, el backend abre temporalmente el archivo en memoria usando `ZipArchive`, itera sobre su árbol de contenidos y rechaza de forma inmediata la subida completa si detecta tan solo un archivo interno con extensión restringida.
- **Resolución de Nombres Colisionados:** Algoritmo que evita la sobreescritura en el filesystem renombrando automáticamente archivos duplicados con sufijos incrementales (ej. `documento (1).pdf`, `documento (2).pdf`).

### Panel Administrativo de Control (Roles y Grupos)
- **Módulo de Usuarios:** Permite administrar las cuentas del sistema, asignarles cuotas dedicadas o vincularlos a equipos específicos.
- **Módulo de Grupos:** Creación y modificación de agrupaciones organizacionales (ej. "SENA", "Premium", "Desarrolladores") asignándoles cuotas de disco compartidas para todos sus miembros.
- **Configuración Global:** Panel unificado para cambiar el almacenamiento por defecto del sistema y añadir/remover extensiones a la lista negra del servidor.

### Interfaz y Experiencia de Usuario (UX/UI)
- **Operaciones Single Page Application (SPA) Parciales:** La subida, listado, actualización de configuraciones y eliminaciones se realizan de forma asíncrona mediante Fetch API. La interfaz procesa las respuestas del servidor en tiempo real sin recargar el navegador.
- **Descargas Seguras Interceptadas:** Los archivos de los usuarios no se exponen con enlaces directos en la web; son servidos dinámicamente por un controlador que verifica los permisos de sesión antes de realizar el streaming de bytes.

---

## 🔐 Capa de Seguridad Avanzada

- **Hasheo de Contraseñas:** Uso de las funciones nativas de PHP `password_hash()` y `password_verify()` para el almacenamiento y validación segura de las contraseñas de los usuarios.
- **Prevención de Inyecciones SQL:** Erradicación total de ataques de inyección mediante la parametrización estricta de todas las consultas SQL utilizando Prepared Statements reales en PDO.
- **Protección de Sesiones:** Mitigación de vulnerabilidades de Fijación de Sesión mediante la regeneración y destrucción forzada del ID de sesión (`session_regenerate_id(true)`) en los flujos de login y logout.
- **Aislamiento Físico de Recursos:** El directorio raíz de almacenamiento (`storage/uploads/`) se encuentra ubicado **fuera** del directorio público web (`public/`), bloqueando cualquier intento de ejecución directa de archivos subidos desde el navegador.
- **Seguridad por Ofuscación de Identificadores:** Las carpetas del almacenamiento físico se nombran utilizando cadenas aleatorias robustas (`external_id` autogenerado mediante bytes pseudoaleatorios criptográficamente seguros), protegiendo la identidad secuencial interna de la base de datos.

---

## 📁 Estructura del proyecto

```
storage-app/
│
├── app/
│   ├── .htaccess                         # Bloquea el acceso HTTP directo a la lógica de la app
│   ├── controllers/
│   │   ├── api/                          # Controladores API que procesan y retornan JSON
│   │   └── web/                          # Controladores Web encargados de despachar vistas HTML
│   ├── core/                             # Componentes del mini-framework custom (Router, Session, View)
│   ├── helpers/                          # Clases de utilidades (FileHelper, ResponseHelper)
│   ├── models/                           # Capa de datos y abstracción de queries SQL
│   └── services/                         # Capa de Servicios: Lógica de negocio pura y reglas del sistema
│       └── handlers/                     # Operaciones de bajo nivel sobre el Filesystem (StorageHandler)
│
├── config/
│   ├── app.php                           # Constantes de entorno y credenciales globales
│   ├── DatabaseInstaller.php             # Script de instalación automática de base de datos y esquema
│   └── db_connection.php                 # Clase Database para el control de la conexión PDO
│
├── docs/                                 # Documentación de soporte del sistema
├── logs/
│   └── debug.log                         # Registro y trazabilidad aislada de excepciones internas
├── postman/                              # Colecciones de pruebas para auditoría de endpoints API
│
├── public/                               # ÚNICO directorio expuesto al servidor web Apache
│   ├── .htaccess                         # Directivas de reescritura de URLs hacia el Front Controller
│   ├── index.php                         # Front Controller: Punto de entrada único de la aplicación
│   ├── css/                              # Estilos modulares organizados por secciones
│   ├── img/                              # Recursos gráficos estáticos y logotipos
│   └── js/                               # Controladores y scripts en Javascript Vanilla (ES6+)
│
├── sql/
│   └── consultas.sql                     # Esquema de la Base de Datos e inicialización maestra
├── storage/
│   └── uploads/                          # Almacenamiento físico privado aislado del webroot
├── views/                                # Plantillas de las páginas y layouts modulares
├── .gitignore
└── README.md
```

---
## 🧠 Arquitectura de Flujo Interno

El sistema implementa un desacoplamiento estricto de responsabilidades bajo principios arquitectónicos limpios:

```text
[Petición del Cliente]
        │
        ▼
public/index.php
(Front Controller)
        │
        ▼
DatabaseInstaller
(Verificación de salud e instalación automática en frío)
        │
        ▼
Router
(Despacho analítico de verbos HTTP y URI)
        │
        ▼
Controller
(Validación de sesión, extracción de inputs y encapsulado)
        │
        ▼
Service
(Orquestación de reglas de negocio:
 cuotas, ZIPs y extensiones)
        │
        ▼
Model ─────────────────────▶ [Base de Datos MySQL]
        │
        ▼
StorageHandler ───────────▶ [Filesystem Físico]
```

---

## 🛠️ Instalación y Configuración Automática  
### *(Despliegue Cero Fricciones)*

El proyecto cuenta con un **Módulo de Instalación Automatizada** que autodetecta la ausencia del esquema de datos y levanta el sistema completo la primera vez que se visita desde el navegador, eliminando la necesidad de importar archivos SQL manualmente en phpMyAdmin.

### Pasos para iniciar el sistema

#### 1️⃣ Clonar o mover la carpeta del proyecto

Mover el proyecto completo directamente al directorio público de XAMPP:

```text
C:\xampp\htdocs\storage-app\
```

---

#### 2️⃣ Configuración Inicial del Entorno *(Opcional)*

Abrir el archivo:

```text
config/app.php
```

Por defecto viene configurado con las credenciales estándar de XAMPP, por lo que si utilizas la configuración nativa de fábrica no necesitas modificar nada.

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'storage_app');
define('DB_USER', 'root');
define('DB_PASS', ''); // Vacío por defecto en XAMPP

// URL base apuntando a la carpeta pública
define('BASE_URL', '/storage-app/public/');
```

---

#### 3️⃣ Ejecutar el sistema en el navegador

Abrir el panel de control de XAMPP e iniciar:

- Apache
- MySQL

Luego acceder desde el navegador a:

```text
http://localhost/storage-app/public/
```