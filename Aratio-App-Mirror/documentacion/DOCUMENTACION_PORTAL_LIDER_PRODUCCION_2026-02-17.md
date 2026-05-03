# Documentación: Portal de Líderes - Despliegue en Producción

**Versión:** 1.2.0  
**Fecha:** 19 de Febrero de 2026  
**Autor:** Sistema de Debugging Automatizado  
**Estado:** ✅ Completado y Funcional

---

## Resumen Ejecutivo

Se realizó el despliegue exitoso del módulo **Portal de Líderes** en el servidor de producción de Hostinger. El proceso involucró la identificación y resolución de múltiples problemas de configuración del servidor.

### URLs Funcionales

| Página | URL |
|--------|-----|
| Portal Login | https://aratio.mrmtech.net/index.php?page=portal_login |
| Portal Landing | https://aratio.mrmtech.net/index.php?page=portal_landing |
| Portal Dashboard | https://aratio.mrmtech.net/index.php?page=portal_dashboard |

### Credenciales de Prueba

| Tipo | Usuario/Documento | Contraseña/Teléfono |
|------|-------------------|---------------------|
| Líder | 1130665763 | 3178386580 |
| Admin | lider | 123456 |

---

## Problemas Identificados y Resueltos

### 1. Error 500 Interno del Servidor

**Síntoma:** Todas las solicitudes a `index.php?page=portal_login` devolvían error 500.

**Diagnóstico:**
- El servidor web de Hostinger sirve contenido desde el **raíz del FTP**, no desde `domains/aratio.mrmtech.net/public_html/`
- Existía un `.htaccess` en el raíz que redirigía todas las solicitudes a un `index.php` diferente
- Los archivos del portal estaban en la ubicación incorrecta

**Solución:**
1. Eliminar el `.htaccess` del raíz del FTP
2. Subir todos los archivos del portal al raíz del FTP

### 2. Clase `App\Core\Controller` no encontrada

**Síntoma:** Error fatal "Class App\Core\Controller not found"

**Causa:** El directorio `mod_lider/src/Core/` no estaba subido al servidor

**Solución:** Subir los archivos:
- `mod_lider/src/Core/Controller.php`
- `mod_lider/src/Core/Router.php`

### 3. Vista no encontrada: portal.auth.login

**Síntoma:** Exception "Vista no encontrada: portal.auth.login"

**Causa:** El directorio `mod_lider/src/Views/` no estaba subido al servidor

**Solución:** Subir toda la estructura de vistas:
- `mod_lider/src/Views/layouts/portal_public.php`
- `mod_lider/src/Views/layouts/portal.php`
- `mod_lider/src/Views/portal/dashboard.php`
- `mod_lider/src/Views/portal/events.php`
- `mod_lider/src/Views/portal/landing.php`
- `mod_lider/src/Views/portal/network.php`
- `mod_lider/src/Views/portal/auth/login.php`
- `mod_lider/src/Views/portal/auth/register.php`
- `mod_lider/src/Views/portal/auth/forgot-password.php`
- `mod_lider/src/Views/portal/auth/reset-password.php`
- `mod_lider/src/Views/portal/auth/two-factor.php`

---

## Estructura de Archivos en Producción

```
/ (raíz FTP - donde el servidor web sirve contenido)
├── index.php                    # Router principal
├── .htaccess                    # Configuración Apache
├── config/
│   └── config.php              # Configuración de base de datos
├── pages/
│   ├── portal_login.php        # Página de login
│   ├── portal_landing.php      # Página de bienvenida
│   ├── portal_auth.php         # Controlador de autenticación
│   ├── portal_dashboard.php    # Dashboard del líder
│   ├── portal_red.php          # Red de colaboradores
│   └── portal_eventos.php      # Eventos
└── mod_lider/
    ├── src/
    │   ├── bootstrap.php       # Autoloader PSR-4
    │   ├── Controllers/
    │   │   ├── PortalAuthController.php
    │   │   └── LeaderPortalController.php
    │   ├── Models/
    │   │   ├── Colaborador.php
    │   │   ├── Evento.php
    │   │   ├── Usuario.php
    │   │   ├── Campana.php
    │   │   ├── Curriculum.php
    │   │   └── Territorio.php
    │   ├── Core/
    │   │   ├── Controller.php
    │   │   └── Router.php
    │   └── Views/
    │       ├── layouts/
    │       │   ├── portal_public.php
    │       │   └── portal.php
    │       └── portal/
    │           ├── dashboard.php
    │           ├── events.php
    │           ├── landing.php
    │           ├── network.php
    │           └── auth/
    │               ├── login.php
    │               ├── register.php
    │               ├── forgot-password.php
    │               ├── reset-password.php
    │               └── two-factor.php
    └── config/
        ├── config.php
        └── database.php
```

---

## Configuración del Servidor

### Base de Datos

| Parámetro | Valor |
|-----------|-------|
| Host | auth-db690.hstgr.io |
| Database | u156469157_aratio_v1 |
| Usuario | u156469157_aratio_v1 |
| Contraseña | 15zxCeBbvgsR |

### FTP

| Parámetro | Valor |
|-----------|-------|
| Servidor | ftp://212.1.208.241 |
| Usuario | u156469157.aratio.mrmtech.net |
| Contraseña | sthLX6bJPoGh |

### .htaccess (Raíz)

```apache
# Aratio .htaccess - Simplificado para Hostinger
RewriteEngine On
RewriteBase /

# Forzar HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Proteger archivos sensibles
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

# Evitar listado de directorios
Options -Indexes
```

---

## Scripts de Despliegue Creados

Durante el proceso de debugging se crearon los siguientes scripts de PowerShell:

| Script | Propósito |
|--------|-----------|
| `subir_portal_raiz.ps1` | Sube archivos principales del portal |
| `subir_core.ps1` | Sube clases Core (Controller, Router) |
| `subir_views.ps1` | Sube vistas del portal |
| `subir_htaccess_debug.ps1` | Sube .htaccess simplificado |
| `leer_error_log.ps1` | Lee logs de errores PHP |
| `eliminar_htaccess_raiz.ps1` | Elimina .htaccess conflictivo |

---

## Lecciones Aprendidas

1. **Ubicación del servidor web:** Hostinger sirve desde el raíz del FTP, no desde `domains/dominio/public_html/`

2. **OPCache persistente:** El servidor mantiene caché de PHP. Para limpiarlo se necesita:
   - Acceder al panel de control de Hostinger
   - O usar `opcache_reset()` vía PHP

3. **ErrorDocument interfiere:** Las directivas `ErrorDocument` en `.htaccess` pueden ocultar errores reales redirigiendo a páginas personalizadas

4. **Autoloader PSR-4:** El módulo usa namespaces `App\` con autoloader personalizado en `bootstrap.php`

---

## Próximos Pasos Recomendados

1. **Seguridad:**
   - Cambiar credenciales de prueba
   - Implementar rate limiting en login
   - Añadir CSRF tokens

2. **Rendimiento:**
   - Configurar OPcache correctamente
   - Implementar caché de vistas

3. **Monitoreo:**
   - Configurar alertas de errores
   - Implementar logging estructurado

---

## Historial de Cambios

| Fecha | Versión | Cambio |
|-------|---------|--------|
| 2026-02-17 | 1.0.0 | Despliegue inicial del Portal de Líderes |
| 2026-02-19 | 1.2.0 | Sincronización de sesiones, corrección de base de datos y solución de bucle de redirección en el dashboard. |

---

## Contacto y Soporte

Para soporte técnico del portal, revisar:
- Logs de errores: `/php-errors.log` en el raíz del FTP
- Documentación técnica: `DOCUMENTACION_PORTAL_LIDER.md`
