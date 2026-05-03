# 🚀 DEPLOYMENT CHECKLIST - ARATIO

**Versión**: 1.0.0
**Sistema**: Multi-Campaign Management System

---

## ⚠️ IMPORTANTE: NUNCA SALTAR STAGING

**No subas directamente a producción**. Siempre prueba en staging primero.

---

## 📋 FASE 1: STAGING (OBLIGATORIO)

### 1.1 Configuración Inicial ⏳

- [ ] Crear subdominio en Hostinger: `staging.aratio.mrmtech.net`
- [ ] Crear base de datos MySQL separada: `u156469157_aratio_staging`
- [ ] Anotar credenciales de la BD staging

### 1.2 Subir Archivos ⏳

- [ ] Conectar vía FTP/File Manager a Hostinger
- [ ] Navegar al directorio del subdominio staging
- [ ] Subir TODOS los archivos de `src/php-export/`:
  - [ ] Carpeta `api/` (6 archivos)
  - [ ] Carpeta `config/` (config.php)
  - [ ] Carpeta `includes/` (Auth.php)
  - [ ] Carpeta `pages/` (12 archivos)
  - [ ] Carpeta `database/` (schema.sql)
  - [ ] Carpeta `uploads/` (vacía)
  - [ ] `index.php`
  - [ ] `login.php`
  - [ ] `logout.php`
  - [ ] `install.php`
  - [ ] `.htaccess`

### 1.3 Configurar Base de Datos ⏳

- [ ] Abrir phpMyAdmin en Hostinger
- [ ] Seleccionar base de datos staging: `u156469157_aratio_staging`
- [ ] Ir a pestaña **Importar**
- [ ] Seleccionar archivo `database/schema.sql`
- [ ] Hacer clic en **Continuar**
- [ ] Verificar que se crearon las **12 tablas**:
  - [ ] usuarios
  - [ ] sesiones
  - [ ] elecciones
  - [ ] grupos_politicos
  - [ ] candidatos
  - [ ] campanas
  - [ ] usuarios_campanas
  - [ ] donaciones
  - [ ] eventos
  - [ ] asistencia_eventos
  - [ ] acciones_comunitarias
  - [ ] compromisos

### 1.4 Ajustar config.php (Solo si staging no se detecta auto) ⏳

**Nota**: Si el hostname `staging.aratio.mrmtech.net` NO se detecta como staging:

- [ ] Abrir `config/config.php`
- [ ] Modificar líneas 20-35 para agregar detección de staging:
```php
$isStaging = strpos($_SERVER['HTTP_HOST'] ?? '', 'staging') !== false;
$isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', 'aratio.localhost', '127.0.0.1'])
           || php_sapi_name() === 'cli';

if ($isStaging) {
    define('DB_HOST', 'localhost');  // O auth-db690.hstgr.io si BD remota
    define('DB_NAME', 'u156469157_aratio_staging');
    define('DB_USER', 'u156469157_aratio_staging');  // Usuario de la BD staging
    define('DB_PASS', 'PASSWORD_STAGING');
} elseif ($isLocal) {
    // ... local config
} else {
    // ... production config
}
```

### 1.5 Configurar Permisos ⏳

- [ ] Carpeta `uploads/`: Permisos **755** (rwxr-xr-x)
- [ ] Verificar que `.htaccess` esté activo

### 1.6 Verificación Inicial ⏳

- [ ] Abrir en navegador: `https://staging.aratio.mrmtech.net`
- [ ] Verificar que cargue la página de login
- [ ] **NO** aparecen errores PHP visibles

---

## 🧪 FASE 2: TESTING EN STAGING (CRÍTICO)

### 2.1 Testing de Autenticación ⏳

- [ ] Login con credenciales del admin:
  - Email: `admin@aratio.mrmtech.net`
  - Password: `Admin123!`
- [ ] Login exitoso, redirige al dashboard
- [ ] Logout funciona correctamente
- [ ] Intentar acceder a `/pages/dashboard.php` sin login → redirige a login
- [ ] Sesión expira después de 2 horas (opcional: probar o confiar)

### 2.2 Testing de Módulos (12/12) ⏳

**Dashboard**
- [ ] Carga sin errores
- [ ] Muestra 4 tarjetas de KPIs
- [ ] Gráfico de Chart.js se renderiza
- [ ] Estadísticas muestran datos (aunque sea 0)

**Campañas**
- [ ] Lista de campañas carga
- [ ] Selector de campaña en header funciona
- [ ] Botón "Nueva Campaña" abre modal
- [ ] Crear campaña con datos de prueba → exitoso
- [ ] Editar campaña → cambios se guardan
- [ ] Eliminar campaña → se elimina correctamente
- [ ] API `/api/campanas.php` responde (probar con Postman/curl)

**Donaciones**
- [ ] Lista carga sin errores
- [ ] Crear donación → exitoso
- [ ] Filtros funcionan (por estado, método)
- [ ] Editar donación → cambios se guardan
- [ ] Eliminar donación → se elimina
- [ ] API `/api/donaciones.php` responde

**Eventos**
- [ ] Lista carga
- [ ] **Mapa de Leaflet carga** con tiles de OpenStreetMap
- [ ] Crear evento con ubicación → marcador aparece en mapa
- [ ] Editar evento → funciona
- [ ] Eliminar evento → funciona
- [ ] API `/api/eventos.php` responde

**Acciones Comunitarias**
- [ ] Lista carga
- [ ] Jerarquía territorial de 5 niveles funciona
- [ ] Crear acción → exitoso
- [ ] Mapa muestra marcadores
- [ ] Contador de contactos funciona

**Compromisos**
- [ ] Lista carga
- [ ] Formulario de 5 preguntas completo
- [ ] Crear compromiso → exitoso
- [ ] Estados y prioridades funcionan
- [ ] Porcentaje de avance se actualiza

**Reportes**
- [ ] Selector de tipo de reporte funciona
- [ ] **Gráficos de Chart.js se renderizan**
- [ ] 6 tipos de reportes cargan:
  - [ ] Donaciones
  - [ ] Eventos
  - [ ] Acciones
  - [ ] Compromisos
  - [ ] Territorial (con mapa)
  - [ ] General
- [ ] Botones de exportación presentes (aunque no funcionen)

**Elecciones**
- [ ] Lista carga
- [ ] CRUD funciona
- [ ] API `/api/elecciones.php` responde

**Candidatos**
- [ ] Lista/cards cargan
- [ ] Avatares con iniciales se generan
- [ ] CRUD funciona
- [ ] API `/api/candidatos.php` responde

**Grupos Políticos**
- [ ] Lista/cards cargan
- [ ] Colores del partido se aplican
- [ ] CRUD funciona
- [ ] API `/api/grupos.php` responde

**Ayuda**
- [ ] Tabs de navegación funcionan
- [ ] Contenido de ayuda visible
- [ ] Información de contacto presente

**Configuración**
- [ ] Tabs funcionan (Perfil, Seguridad, Notificaciones, Apariencia)
- [ ] Cambio de contraseña funciona
- [ ] Toggles de notificaciones interactivos

### 2.3 Testing de Seguridad ⏳

**HTTPS**
- [ ] Candado verde en navegador
- [ ] URL comienza con `https://`
- [ ] Certificado SSL válido

**Archivos Protegidos**
- [ ] `https://staging.../config/config.php` → **403 Forbidden** o **404 Not Found**
- [ ] `https://staging.../database/schema.sql` → **403** o **404**
- [ ] `https://staging.../includes/Auth.php` → **403** o **404**

**SQL Injection** (prueba básica)
- [ ] Intentar login con email: `admin@aratio.com' OR '1'='1`
- [ ] Login NO funciona → protegido con prepared statements ✅

**XSS** (prueba básica)
- [ ] Crear campaña con nombre: `<script>alert('XSS')</script>`
- [ ] Al ver la campaña, script NO se ejecuta → protegido con htmlspecialchars ✅

**Sesiones**
- [ ] Cerrar sesión y luego navegar a `/pages/dashboard.php` → redirige a login
- [ ] Cookies tienen flags: `HttpOnly`, `Secure`, `SameSite=Strict` (verificar en DevTools)

### 2.4 Testing de Performance ⏳

- [ ] Dashboard carga en < 2 segundos
- [ ] Consultas de DB no tardan > 1 segundo
- [ ] CDNs cargan correctamente:
  - [ ] Tailwind CSS (estilos se aplican)
  - [ ] Alpine.js (modales funcionan)
  - [ ] Leaflet.js (mapas cargan)
  - [ ] Chart.js (gráficos se renderizan)
  - [ ] Lucide Icons (iconos visibles)

### 2.5 Testing Responsive ⏳

- [ ] Abrir en móvil (o DevTools responsive mode)
- [ ] Menú lateral se adapta/colapsa
- [ ] Cards se apilan verticalmente
- [ ] Tablas tienen scroll horizontal
- [ ] Formularios se adaptan a pantalla pequeña
- [ ] Botones y enlaces son clickeables en móvil

### 2.6 Documentar Issues ⏳

- [ ] Anotar TODOS los errores encontrados en un archivo:
  - Descripción del error
  - Pasos para reproducir
  - Módulo afectado
  - Severidad (crítico, medio, bajo)
- [ ] Corregir errores críticos ANTES de producción
- [ ] Errores medios/bajos pueden esperar (anotar en roadmap)

---

## 🏁 FASE 3: PRODUCCIÓN (Solo si Staging OK)

### 3.1 Pre-Deployment ⏳

- [ ] **TODOS** los tests en staging pasaron
- [ ] Issues críticos corregidos
- [ ] Backup de producción (si existe instalación previa):
  ```bash
  mysqldump -u USER -p DB_NAME > backup_prod_$(date +%Y%m%d).sql
  ```

### 3.2 Subir a Producción ⏳

- [ ] Conectar vía FTP a Hostinger
- [ ] Navegar a `public_html/` del dominio principal
- [ ] Subir TODOS los archivos (igual que en staging)
- [ ] Verificar estructura de carpetas correcta

### 3.3 Configurar Base de Datos ⏳

- [ ] Abrir phpMyAdmin
- [ ] Seleccionar base de datos: `u156469157_aratio_v1`
- [ ] **Si BD vacía**: Importar `schema.sql`
- [ ] **Si BD existe**: Verificar que tenga las 12 tablas

**Nota**: El sistema auto-detecta producción por hostname, no requiere cambios en config.php

### 3.4 Configurar Permisos ⏳

- [ ] Carpeta `uploads/`: **755**
- [ ] `.htaccess` activo

### 3.5 SSL/HTTPS ⏳

- [ ] Panel de Hostinger → **SSL/TLS**
- [ ] Activar certificado (Let's Encrypt gratis)
- [ ] Forzar HTTPS en `.htaccess` (ya configurado)
- [ ] Verificar candado verde en navegador

### 3.6 Post-Deployment Inmediato ⏳

- [ ] Abrir `https://aratio.mrmtech.net`
- [ ] Login con admin funciona
- [ ] Dashboard carga correctamente
- [ ] NO aparecen errores PHP

### 3.7 Cambiar Credenciales Sensibles ⏳

**Password del Admin**
- [ ] Login como admin
- [ ] Ir a Configuración → Seguridad
- [ ] Cambiar password a uno nuevo y seguro
- [ ] Anotar nuevo password en lugar seguro

**JWT_SECRET (opcional pero recomendado)**
- [ ] Editar `config/config.php` línea 55
- [ ] Generar nuevo secret:
  ```php
  echo bin2hex(random_bytes(32));
  ```
- [ ] Reemplazar el valor actual

**Passwords SMTP (si usas email)**
- [ ] Configurar `SMTP_PASS` en `config.php` líneas 84

### 3.8 Eliminar Archivos Sensibles ⏳

- [ ] Eliminar `install.php` del servidor
- [ ] Verificar que `config.php` esté protegido por `.htaccess`

### 3.9 Configurar Backups Automáticos ⏳

**En Panel de Hostinger**
- [ ] Ir a **Backups**
- [ ] Configurar backups automáticos:
  - Base de datos: **Diarios**
  - Archivos: **Semanales**
- [ ] Verificar que backups se ejecuten correctamente

### 3.10 Monitoreo ⏳

**Logs de Errores**
- [ ] Verificar ubicación del log: `/home/u156469157/.../php-errors.log`
- [ ] Revisar log para errores iniciales
- [ ] Configurar alerta de errores críticos (opcional)

**Uptime Monitoring (opcional)**
- [ ] Configurar servicio de monitoreo (UptimeRobot, StatusCake, etc.)
- [ ] Alertas si el sitio cae

### 3.11 Testing Final en Producción ⏳

**Repetir tests críticos**:
- [ ] Login/logout funciona
- [ ] Selector de campaña funciona
- [ ] CRUD en al menos 3 módulos (Campañas, Donaciones, Eventos)
- [ ] Mapas de Leaflet cargan
- [ ] Gráficos de Chart.js se renderizan
- [ ] HTTPS activo (candado verde)
- [ ] Archivos sensibles NO accesibles

### 3.12 Documentación Final ⏳

- [ ] Anotar URL de producción: `https://aratio.mrmtech.net`
- [ ] Anotar credenciales del admin (nuevo password)
- [ ] Documentar fecha de deployment
- [ ] Crear documento con instrucciones para usuarios finales (opcional)

---

## ✅ CHECKLIST DE COMPLETITUD

### Staging
- [ ] Configuración completada
- [ ] Archivos subidos
- [ ] Base de datos importada
- [ ] Testing exhaustivo realizado
- [ ] Issues documentados y corregidos

### Producción
- [ ] Archivos subidos
- [ ] Base de datos configurada
- [ ] SSL/HTTPS activo
- [ ] Credenciales cambiadas
- [ ] Archivos sensibles eliminados
- [ ] Backups configurados
- [ ] Monitoreo configurado
- [ ] Testing final realizado

---

## 🚨 SI ALGO SALE MAL

### En Staging
✅ **Puedes experimentar libremente**. Staging existe para romper cosas sin consecuencias.

1. Revisa logs de PHP: `php-errors.log`
2. Revisa consola de navegador (F12 → Console)
3. Verifica conexión a BD en phpMyAdmin
4. Revisa permisos de archivos/carpetas
5. Si es irreparable: elimina todo y vuelve a subir desde cero

### En Producción
⚠️ **Más cuidado, pero tampoco pánico**.

1. **No elimines la base de datos** a menos que tengas backup
2. Revisa logs de errores
3. Si el sitio no carga: verifica `.htaccess`
4. Si BD no conecta: verifica credenciales en `config.php`
5. Si es grave: restaura desde backup más reciente
6. Si no tienes backup: contacta soporte de Hostinger

---

## 📞 CONTACTO

**Proyecto**: Aratio - Multi-Campaign Management System
**Versión**: 1.0.0
**Fecha**: 25 de Noviembre de 2025

**Documentación Adicional**:
- `RESUMEN_EJECUTIVO.md` - Visión general
- `REPORTE_REVISION_SISTEMA.md` - Análisis técnico completo
- `CLAUDE.md` - Guía rápida de referencia
- `src/php-export/INSTRUCCIONES_DESPLIEGUE.md` - Guía detallada de deployment

---

**🎯 ORDEN DE EJECUCIÓN**:
1. ✅ FASE 1: Staging (configuración)
2. ✅ FASE 2: Testing en Staging (crítico, no saltar)
3. ✅ FASE 3: Producción (solo si staging 100% OK)

**Tiempo Total Estimado**: 4-6 horas (incluye staging + testing + producción)

---

**¡Éxito en el Deployment!** 🚀
