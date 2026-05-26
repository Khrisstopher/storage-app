# 📦 Storage App

> Plataforma de gestión segura de archivos construida en **PHP puro + JS Vanilla**, sin frameworks ni dependencias externas. Arquitectura MVC personalizada con un motor de seguridad multicapa en el backend.

![Estado](https://img.shields.io/badge/Estado-Terminado-success?style=flat-square)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=flat-square&logo=javascript&logoColor=black)

---

## 🎬 Demo

<!-- Reemplaza esta línea con tu enlace de YouTube, Drive o un GIF animado -->
> 📹 *Video demostrativo próximamente — [Ver demo aquí](#)*

---

## ¿Qué hace esta app?

Los usuarios autenticados pueden subir, listar, descargar y eliminar archivos desde un dashboard personal. El sistema garantiza aislamiento entre usuarios, control de cuotas y validación de contenido antes de persistir cualquier archivo.

Un panel de administración permite gestionar usuarios, grupos y configuraciones globales del sistema.

---

## Stack técnico

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.0+ · PDO · MySQL |
| Frontend | JavaScript ES6+ · Fetch API |
| UI | Bootstrap 5 · SweetAlert2 · Bootstrap Icons |
| Servidor | Apache · XAMPP · `.htaccess` URL Rewriting |

---

## Decisiones técnicas destacadas

**Sin frameworks, sin Composer.** El proyecto implementa su propio mini-framework MVC: router, controlador base, sistema de vistas con layouts, manejo de sesiones y respuestas JSON estandarizadas — todo desde cero.

**Seguridad en capas:**
- Prepared statements reales (`ATTR_EMULATE_PREPARES = false`) — sin inyección SQL posible
- Validación por magic bytes con `finfo` — no solo por extensión de nombre
- Inspección recursiva de ZIPs con `ZipArchive` antes de guardar
- Tokens CSRF en todos los endpoints de mutación, verificados con `hash_equals()`
- Archivos almacenados fuera del webroot (`storage/uploads/` ≠ `public/`)
- Carpetas nombradas con `external_id` criptográfico — sin IDs secuenciales expuestos
- `Content-Disposition` sanitizado para prevenir header injection en descargas

**Sistema de cuotas jerárquico** resuelto con un único `COALESCE` en SQL: usuario → grupo → global. Sin lógica redundante.

**Instalación cero fricciones:** `DatabaseInstaller` detecta automáticamente si la BD existe y ejecuta el esquema SQL en el primer request.

---

## Estructura del proyecto

```
storage-app/
├── app/
│   ├── controllers/
│   │   ├── api/        # Devuelven JSON
│   │   └── web/        # Despachan vistas
│   ├── core/           # Router, Session, View, Controller base
│   ├── helpers/        # FileHelper (MIME, nombres únicos, conversión de bytes)
│   ├── models/         # Queries SQL con PDO
│   └── services/
│       └── handlers/   # StorageHandler — operaciones sobre el filesystem
├── config/             # Entorno (.env), conexión PDO, instalador automático
├── public/             # Único directorio expuesto al servidor web
│   └── js/             # JS Vanilla organizado por módulo
├── sql/                # Esquema y datos iniciales
├── storage/uploads/    # Almacenamiento privado fuera del webroot
└── views/              # Plantillas PHP + layouts
```

---

## Instalación local

```bash
# 1. Clonar en htdocs de XAMPP
git clone https://github.com/Khrisstopher/storage-app.git C:/xampp/htdocs/storage-app

# 2. Copiar el archivo de entorno
cp .env.example .env
# Editar .env si tus credenciales de MySQL difieren de las de XAMPP por defecto

# 3. Iniciar Apache y MySQL desde el panel de XAMPP

# 4. Abrir en el navegador
http://localhost/storage-app/public/
```

> La base de datos se crea e instala automáticamente en el primer acceso.

**Credenciales de acceso inicial:**
| Campo | Valor |
|---|---|
| Email | `admin@test.com` |
| Contraseña | `password` |

---

## Autor

**Khrisstopher** · [LinkedIn](https://www.linkedin.com/in/khrisstopher/)
