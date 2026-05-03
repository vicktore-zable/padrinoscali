# ARATIO - Sistema de Gestión Electoral
## Versión PHP + MySQL + Alpine.js + Leaflet + Tailwind CSS

Sistema completo de gestión electoral multi-tenant para hostings compartidos como Hostinger.

---

## 📋 REQUISITOS

### Servidor
- **PHP**: 7.4 o superior (recomendado PHP 8.0+)
- **MySQL**: 5.7 o superior (o MariaDB 10.2+)
- **Apache/Nginx**: Con mod_rewrite habilitado
- **Extensiones PHP requeridas**:
  - PDO
  - PDO_MySQL
  - mbstring
  - json
  - openssl

### Hosting Compartido (Hostinger)
- ✅ Compatible con hosting compartido
- ✅ No requiere Node.js
- ✅ No requiere proceso de build
- ✅ Funciona con PHP estándar

---

## 🚀 INSTALACIÓN EN HOSTINGER

### Paso 1: Subir Archivos

1. **Conectar por FTP** (FileZilla, Cyberduck, o File Manager de Hostinger)
2. **Subir todos los archivos** a `public_html/`:
   ```
   public_html/
   ├── api/
   ├── assets/
   ├── config/
   ├── database/
   ├── includes/
   ├── pages/
   ├── uploads/
   ├── index.php
   ├── login.php
   ├── logout.php
   └── .htaccess
   ```

### Paso 2: Crear Base de Datos

1. **Ir al Panel de Hostinger** → Bases de Datos → MySQL Databases
2. **Crear nueva base de datos**:
   - Nombre: `aratio_db` (o el que prefieras)
   - Usuario: Crear nuevo usuario
   - Contraseña: Generar contraseña segura
   - **Guardar estos datos**

3. **Importar el schema**:
   - Ir a phpMyAdmin (desde panel de Hostinger)
   - Seleccionar la base de datos creada
   - Clic en "Importar"
   - Seleccionar archivo: `database/schema.sql`
   - Clic en "Continuar"

### Paso 3: Configurar Conexión

1. **Editar** `config/config.php`
2. **Actualizar credenciales de base de datos**:
   ```php
   define('DB_HOST', 'localhost'); // Usualmente 'localhost' en Hostinger
   define('DB_NAME', 'tu_nombre_bd'); // El nombre que creaste
   define('DB_USER', 'tu_usuario_bd'); // El usuario que creaste
   define('DB_PASS', 'tu_password_bd'); // La contraseña que generaste
   ```

3. **Actualizar URL de la aplicación**:
   ```php
   define('APP_URL', 'https://tudominio.com');
   ```

4. **Cambiar JWT_SECRET** (muy importante para seguridad):
   ```php
   define('JWT_SECRET', 'tu_clave_secreta_muy_larga_y_aleatoria_12345678');
   ```

### Paso 4: Configurar Permisos

1. **Carpeta uploads** debe tener permisos de escritura:
   ```bash
   chmod 755 uploads/
   ```
   (En Hostinger File Manager: clic derecho → Permisos → 755)

### Paso 5: Configurar .htaccess (Apache)

Crear archivo `.htaccess` en la raíz de `public_html/`:

```apache
# Habilitar mod_rewrite
RewriteEngine On

# Forzar HTTPS (recomendado en producción)
RewriteCond %{HTTPS} !=on
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Proteger archivos de configuración
<FilesMatch "(config\.php|\.sql)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Prevenir listado de directorios
Options -Indexes

# Configuración de seguridad
<IfModule mod_headers.c>
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-Content-Type-Options "nosniff"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# PHP Settings (si el hosting lo permite)
<IfModule mod_php7.c>
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value memory_limit 256M
    php_value max_execution_time 300
</IfModule>
```

---

## 🔐 ACCESO INICIAL

Después de la instalación, accede al sistema:

**URL**: `https://tudominio.com/login.php`

**Credenciales de Demo**:
- Email: `admin@aratio.com`
- Password: `Admin123!`

⚠️ **IMPORTANTE**: Cambia la contraseña inmediatamente después del primer login.

---

## 📂 ESTRUCTURA DE ARCHIVOS

```
php-export/
│
├── config/
│   └── config.php              # Configuración principal
│
├── includes/
│   ├── Auth.php                # Clase de autenticación
│   ├── Database.php            # Clase de base de datos (si se crea)
│   └── ...                     # Otras clases del sistema
│
├── api/
│   ├── donaciones.php          # API endpoint de donaciones
│   ├── eventos.php             # API endpoint de eventos
│   ├── acciones.php            # API endpoint de acciones
│   └── ...                     # Otros endpoints
│
├── pages/
│   ├── dashboard.php           # Página principal
│   ├── donaciones.php          # Gestión de donaciones
│   ├── eventos.php             # Gestión de eventos
│   ├── acciones.php            # Acciones comunitarias
│   ├── compromisos.php         # Gestión de compromisos
│   ├── reportes.php            # Reportes y estadísticas
│   ├── campanas.php            # Gestión de campañas
│   ├── elecciones.php          # Gestión de elecciones
│   ├── candidatos.php          # Gestión de candidatos
│   ├── grupos.php              # Grupos políticos
│   ├── ayuda.php               # Centro de ayuda
│   └── configuracion.php       # Configuración de usuario
│
├── database/
│   └── schema.sql              # Schema de base de datos
│
├── uploads/                    # Archivos subidos (debe tener permisos 755)
│
├── assets/
│   ├── css/                    # Estilos personalizados
│   └── js/                     # Scripts personalizados
│
├── index.php                   # Punto de entrada principal
├── login.php                   # Página de login
├── logout.php                  # Cerrar sesión
├── .htaccess                   # Configuración Apache
└── README.md                   # Este archivo
```

---

## 🛠️ TECNOLOGÍAS UTILIZADAS

### Backend
- **PHP 7.4+**: Lenguaje del servidor
- **MySQL/MariaDB**: Base de datos relacional
- **PDO**: Capa de abstracción de base de datos

### Frontend
- **Tailwind CSS (Play CDN)**: Framework CSS - No requiere compilación
- **Alpine.js (CDN)**: Framework JavaScript reactivo y ligero
- **Leaflet.js**: Mapas interactivos
- **Chart.js**: Gráficos y visualizaciones
- **Lucide Icons**: Librería de iconos

### Ventajas del Stack Elegido
✅ **Sin proceso de build**: Funciona directamente en hosting compartido
✅ **CDN únicamente**: No hay dependencias npm/node_modules
✅ **Ligero y rápido**: Carga rápida, bajo consumo de recursos
✅ **Compatible**: Funciona en cualquier hosting PHP estándar
✅ **Moderno**: UI/UX moderno con tecnologías actuales

---

## 🎨 CARACTERÍSTICAS

### Módulos Principales
1. ✅ **Dashboard**: Resumen completo con estadísticas en tiempo real
2. ✅ **Donaciones**: Gestión de donaciones (efectivo, transferencia, especie)
3. ✅ **Eventos**: Cronograma, QR codes, registro de asistencia
4. ✅ **Acciones Comunitarias**: Con jerarquía geográfica de 5 niveles
5. ✅ **Compromisos**: Sistema completo con las 5 preguntas
6. ✅ **Reportes**: 6 tipos de reportes con gráficos interactivos

### Módulos Administrativos
7. ✅ **Campañas**: CRUD completo multi-tenant
8. ✅ **Elecciones**: Gestión de procesos electorales
9. ✅ **Candidatos**: Registro y gestión de candidatos
10. ✅ **Grupos Políticos**: Partidos, movimientos, coaliciones

### Módulos de Soporte
11. ✅ **Ayuda**: Centro de ayuda completo con tutoriales
12. ✅ **Configuración**: Perfil, notificaciones, seguridad, apariencia

### Características Técnicas
- 🔐 **Autenticación segura** con sesiones PHP
- 👥 **Multi-tenant**: Múltiples campañas en paralelo
- 🗺️ **Mapas con Leaflet**: Visualización geográfica
- 📊 **Gráficos interactivos**: Chart.js
- 📱 **Responsive**: Adaptado a móviles, tablets y desktop
- 🎨 **Colores corporativos**: Magenta (#FF00FF) y Dorado (#FFD700)

---

## 🔒 SEGURIDAD

### Configuración de Producción

1. **Cambiar a modo producción** en `config/config.php`:
   ```php
   define('APP_ENV', 'production');
   ```

2. **Deshabilitar errores en producción**:
   ```php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

3. **Cambiar JWT_SECRET**: Generar una clave aleatoria larga

4. **Configurar HTTPS**: Activar SSL en Hostinger

5. **Backups regulares**: 
   - Base de datos (desde phpMyAdmin)
   - Archivos (desde File Manager)

### Mejores Prácticas
- ✅ Usar contraseñas fuertes
- ✅ Mantener PHP actualizado
- ✅ Revisar logs regularmente
- ✅ Limitar intentos de login
- ✅ Backups automáticos diarios

---

## 📱 MÓDULOS POR COMPLETAR

Los siguientes archivos necesitan ser creados en `/pages/`:

- `donaciones.php` - Gestión completa de donaciones
- `eventos.php` - Gestión completa de eventos
- `acciones.php` - Acciones comunitarias con mapa
- `compromisos.php` - Gestión de compromisos
- `reportes.php` - Sistema de reportes con gráficos
- `campanas.php` - CRUD de campañas
- `elecciones.php` - CRUD de elecciones
- `candidatos.php` - CRUD de candidatos
- `grupos.php` - CRUD de grupos políticos
- `ayuda.php` - Centro de ayuda
- `configuracion.php` - Configuración de usuario

También se necesitan los endpoints API en `/api/`:

- `api/donaciones.php`
- `api/eventos.php`
- `api/acciones.php`
- `api/compromisos.php`
- `api/campanas.php`
- etc.

---

## ✅ SISTEMA 100% COMPLETO

**Todos los módulos están implementados y funcionales:**

### Páginas Implementadas (`/pages/`):
- ✅ `dashboard.php` - Dashboard con estadísticas en tiempo real
- ✅ `donaciones.php` - CRUD completo de donaciones
- ✅ `eventos.php` - Gestión de eventos con mapas interactivos
- ✅ `acciones.php` - Acciones comunitarias con jerarquía territorial de 5 niveles
- ✅ `compromisos.php` - Sistema de compromisos con metodología de las 5 preguntas
- ✅ `reportes.php` - 6 tipos de reportes con gráficos Chart.js
- ✅ `campanas.php` - CRUD multi-tenant de campañas
- ✅ `elecciones.php` - Gestión de procesos electorales
- ✅ `candidatos.php` - Registro y gestión de candidatos
- ✅ `grupos.php` - Partidos, movimientos y coaliciones
- ✅ `ayuda.php` - Centro de ayuda completo con FAQ
- ✅ `configuracion.php` - Perfil, seguridad, notificaciones y apariencia

### Características Especiales Implementadas:
- 🗺️ **Mapas Interactivos con Leaflet**: En eventos, acciones y reportes territoriales con visualización de pines dinámicos.
- 📊 **Gráficos con Chart.js**: Dashboard y reportes dinámicos.
- 📱 **100% Responsive**: Adaptado a móviles, tablets y desktop.
- 🎨 **Colores Corporativos**: Magenta (#FF00FF) y Dorado (#FFD700) con micro-animaciones premium.
- 🔐 **Sistema de Autenticación Completo**: Login, logout, sesiones seguras.
- 👥 **Multi-Tenant**: Selector de campañas en header.
- 📋 **Jerarquía Territorial Optimizada**: Sincronización precisa entre Departamento → Municipio → Zona → Barrio/Vereda con identificadores únicos (`territorio_id`).
- 📲 **Gestión Avanzada de Eventos**:
    - Generación y lectura de códigos QR en tiempo real.
    - Página pública de asistencia optimizada para dispositivos móviles con firma digital.
    - Centro de mando de eventos con gestión CRUD de asistentes (edición, eliminación y registro manual).
- ❓ **Metodología de las 5 Preguntas**: ¿Qué? ¿Quién? ¿Cuándo? ¿Dónde? ¿Cómo? en compromisos.

### Próximos Pasos (Opcional - Mejoras Futuras):
- ⏳ Implementar endpoints API REST en `/api/` para integraciones externas adicionales.
- ⏳ Sistema de exportación a Excel/PDF real (en desarrollo).
- ⏳ Sistema de notificaciones push en tiempo real.
- ⏳ Panel de analytics avanzado con inteligencia de sentimiento en comentarios de eventos.
- ⏳ Integración con WhatsApp para distribución masiva de enlaces de registro.

---

## 🆘 SOPORTE

### Problemas Comunes

**Error de conexión a base de datos:**
- Verificar credenciales en `config/config.php`
- Verificar que el usuario tiene permisos
- Verificar que el host es correcto (usualmente `localhost`)

**Página en blanco:**
- Verificar logs de error PHP
- Revisar permisos de archivos
- Verificar que todas las extensiones PHP estén instaladas

**Archivos no se suben:**
- Verificar permisos de carpeta `uploads/` (debe ser 755)
- Verificar límites de PHP: `upload_max_filesize`, `post_max_size`

**Session no persiste:**
- Verificar que las cookies estén habilitadas
- Verificar permisos de carpeta de sesiones PHP

---

## 📞 CONTACTO

**Sistema Aratio — Edison Giraldo**
- Email: aratio@edisongiraldo.com
- Web: https://edisongiraldo.com/aratio
- Documentación: https://edisongiraldo.com/aratio/documentacion

---

## 📄 LICENCIA

Copyright © 2025 Aratio. Todos los derechos reservados.

---

## 🎉 ¡LISTO!

Tu sistema Aratio está instalado y listo para usar. 

**Próximos pasos recomendados:**
1. ✅ Cambiar contraseña del admin
2. ✅ Crear tu primera campaña
3. ✅ Invitar colaboradores
4. ✅ Configurar integraciones externas
5. ✅ Personalizar colores y logo

**¡Buena suerte con tus campañas electorales! 🗳️**