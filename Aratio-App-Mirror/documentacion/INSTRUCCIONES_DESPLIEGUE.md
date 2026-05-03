# 🚀 INSTRUCCIONES DE DESPLIEGUE - ARATIO
## Guía Paso a Paso para Hostinger

---

## 📦 PASO 1: PREPARAR ARCHIVOS PARA SUBIR

### Archivos a Incluir
Debes subir TODOS estos archivos y carpetas a tu hosting:

```
📁 Estructura a Subir:
├── 📁 config/
│   └── config.php
├── 📁 includes/
│   └── Auth.php
├── 📁 pages/
│   ├── dashboard.php
│   ├── donaciones.php
│   ├── eventos.php
│   ├── acciones.php
│   ├── compromisos.php
│   ├── reportes.php
│   ├── campanas.php
│   ├── elecciones.php
│   ├── candidatos.php
│   ├── grupos.php
│   ├── ayuda.php
│   └── configuracion.php
├── 📁 database/
│   └── schema.sql
├── 📁 uploads/ (carpeta vacía)
├── 📄 index.php
├── 📄 login.php
├── 📄 logout.php
├── 📄 install.php (opcional - puedes eliminarlo después)
├── 📄 .htaccess
└── 📄 README.md (opcional)
```

### Archivos que NO debes subir
❌ `node_modules/` (no existe, es PHP puro)  
❌ `.git/` (si tienes control de versiones)  
❌ Archivos de desarrollo local  

---

## 🔧 PASO 2: CONECTAR POR FTP A HOSTINGER

### Opción A: File Manager de Hostinger (Más Fácil)
1. Inicia sesión en tu panel de Hostinger: https://hpanel.hostinger.com
2. Ve a **Archivos** → **File Manager**
3. Navega a la carpeta `public_html/`
4. Usa el botón **Upload** para subir todos los archivos

### Opción B: Cliente FTP (FileZilla)
1. Descarga FileZilla: https://filezilla-project.org/
2. Abre FileZilla y conéctate:
   - **Host**: `ftp.tudominio.com` o `ftp.hostinger.com`
   - **Usuario**: Tu usuario FTP (en panel de Hostinger)
   - **Contraseña**: Tu contraseña FTP
   - **Puerto**: 21
3. Sube todos los archivos a `/public_html/`

### Estructura Final en el Servidor
```
/public_html/
├── config/
├── includes/
├── pages/
├── database/
├── uploads/ ← IMPORTANTE: Permisos 755
├── index.php
├── login.php
├── logout.php
├── install.php
└── .htaccess
```

---

## 🗄️ PASO 3: CREAR BASE DE DATOS

### En Panel de Hostinger

1. **Ir a Bases de Datos**
   - Panel Hostinger → **Bases de Datos** → **MySQL Databases**

2. **Crear Nueva Base de Datos**
   - Nombre: `u123456789_aratio` (Hostinger agrega prefijo automático)
   - Clic en **Crear**

3. **Crear Usuario**
   - Usuario: `u123456789_aratio_user`
   - Contraseña: Genera una segura (guárdala)
   - Clic en **Crear**

4. **Asignar Usuario a Base de Datos**
   - Selecciona el usuario
   - Selecciona la base de datos
   - Permisos: **Todos los privilegios**
   - Clic en **Agregar**

5. **Guardar Credenciales** (las necesitarás):
   ```
   Host: localhost
   Base de Datos: u123456789_aratio
   Usuario: u123456789_aratio_user
   Contraseña: [la que generaste]
   ```

---

## 📊 PASO 4: IMPORTAR SCHEMA SQL

### Método 1: phpMyAdmin (Recomendado)

1. **Acceder a phpMyAdmin**
   - Panel Hostinger → **Bases de Datos** → Clic en **phpMyAdmin**

2. **Seleccionar Base de Datos**
   - En el panel izquierdo, clic en `u123456789_aratio`

3. **Importar SQL**
   - Clic en la pestaña **Importar**
   - Clic en **Seleccionar archivo**
   - Selecciona: `database/schema.sql`
   - Clic en **Continuar** (abajo de la página)

4. **Verificar Importación**
   - Deberías ver las 12 tablas creadas
   - Verifica que existan: `usuarios`, `campanas`, `donaciones`, etc.

### Método 2: Instalador Automático (Alternativa)

Si no quieres usar phpMyAdmin:
1. Ve a: `https://tudominio.com/install.php`
2. Sigue el asistente de instalación
3. Ingresa las credenciales de base de datos
4. El instalador creará todo automáticamente

---

## ⚙️ PASO 5: CONFIGURAR EL SISTEMA

### Editar config/config.php

1. **Abrir el archivo**
   - En File Manager o vía FTP
   - Ruta: `/public_html/config/config.php`

2. **Actualizar Credenciales de Base de Datos**
   ```php
   // ANTES (líneas 23-26)
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'aratio_db');
   define('DB_USER', 'tu_usuario_mysql');
   define('DB_PASS', 'tu_password_mysql');

   // DESPUÉS (tus valores reales)
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'u123456789_aratio');
   define('DB_USER', 'u123456789_aratio_user');
   define('DB_PASS', 'TuPasswordSegura123!');
   ```

3. **Actualizar URL de la Aplicación**
   ```php
   // ANTES (línea 34)
   define('APP_URL', 'http://localhost');

   // DESPUÉS
   define('APP_URL', 'https://tudominio.com');
   ```

4. **Cambiar JWT_SECRET (Seguridad)**
   ```php
   // ANTES (línea 42)
   define('JWT_SECRET', 'tu_clave_secreta_muy_segura_cambiala');

   // DESPUÉS (genera una clave aleatoria larga)
   define('JWT_SECRET', 'A7x9K2mP5nQ8wE4rT6yU3iO0pL1sD9fG2hJ5kN8bV4cX7zM0qW3eR6tY9uI2oP5aS8dF');
   ```

   **Generador de clave**: https://randomkeygen.com/ (usa "CodeIgniter Encryption Keys")

5. **Cambiar a Modo Producción**
   ```php
   // ANTES (línea 35)
   define('APP_ENV', 'development');

   // DESPUÉS
   define('APP_ENV', 'production');
   ```

6. **Deshabilitar Errores en Pantalla** (líneas 10-11)
   ```php
   // ANTES
   error_reporting(E_ALL);
   ini_set('display_errors', 1);

   // DESPUÉS
   error_reporting(E_ALL);
   ini_set('display_errors', 0);
   ini_set('log_errors', 1);
   ini_set('error_log', '/home/u123456789/domains/tudominio.com/public_html/php-errors.log');
   ```

7. **Guardar Cambios**
   - Clic en **Guardar** en File Manager
   - O subir el archivo editado por FTP

---

## 🔒 PASO 6: CONFIGURAR PERMISOS

### Carpeta uploads/

La carpeta `uploads/` debe tener permisos de escritura.

**En File Manager de Hostinger:**
1. Clic derecho en carpeta `uploads/`
2. Seleccionar **Permisos** o **Change Permissions**
3. Establecer: **755** o **drwxr-xr-x**
4. Marcar: **Aplicar a subdirectorios**
5. Clic en **Guardar**

**En FileZilla:**
1. Clic derecho en carpeta `uploads/`
2. **Permisos de archivo...**
3. Valor numérico: **755**
4. Marcar: **Recurse into subdirectories**
5. Clic en **OK**

---

## 🌐 PASO 7: ACTIVAR HTTPS (SSL)

1. **Ir a SSL en Hostinger**
   - Panel → **Seguridad** → **SSL**

2. **Activar SSL Gratuito**
   - Hostinger ofrece SSL gratis con Let's Encrypt
   - Clic en **Instalar SSL**
   - Esperar 10-15 minutos

3. **Forzar HTTPS**
   - El `.htaccess` ya incluye redirección automática a HTTPS
   - Verifica las líneas 103-105:
   ```apache
   RewriteCond %{HTTPS} !=on
   RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

4. **Verificar**
   - Visita: `http://tudominio.com`
   - Debe redirigir automáticamente a: `https://tudominio.com`

---

## 🎉 PASO 8: PRIMERA PRUEBA

### Acceder al Sistema

1. **Abrir Navegador**
   - Ve a: `https://tudominio.com/login.php`

2. **Credenciales Iniciales**
   ```
   Email: admin@aratio.com
   Password: Admin123!
   ```

3. **Verificar Funcionalidad**
   - ✅ ¿Carga la página de login?
   - ✅ ¿Puedes iniciar sesión?
   - ✅ ¿Te redirige al dashboard?
   - ✅ ¿Ves las estadísticas?
   - ✅ ¿Funcionan los menús?

### Si Algo Sale Mal

**Página en Blanco:**
- Activar errores temporalmente en `config/config.php`:
  ```php
  ini_set('display_errors', 1);
  ```
- Revisar logs: `/php-errors.log`

**Error de Conexión a BD:**
- Verificar credenciales en `config/config.php`
- Verificar que el usuario tenga permisos
- Probar conexión desde phpMyAdmin

**Error 500:**
- Revisar `.htaccess`
- Verificar permisos de archivos (644 para PHP, 755 para directorios)
- Revisar logs de Apache en panel de Hostinger

---

## 🔐 PASO 9: CONFIGURACIÓN DE SEGURIDAD

### Cambiar Contraseña del Admin

1. **Login con credenciales default**
   - Email: `admin@aratio.com`
   - Password: `Admin123!`

2. **Ir a Configuración**
   - Clic en tu avatar (arriba derecha)
   - **Configuración** → pestaña **Seguridad**

3. **Cambiar Contraseña**
   - Contraseña actual: `Admin123!`
   - Nueva contraseña: (usa una fuerte)
   - Confirmar
   - Clic en **Guardar**

### Eliminar Instalador (Importante)

**Después de instalar, elimina el archivo install.php:**

1. **Vía File Manager:**
   - Navegar a `/public_html/`
   - Clic derecho en `install.php`
   - **Eliminar**

2. **Vía FTP:**
   - Conectar con FileZilla
   - Navegar a `/public_html/`
   - Eliminar `install.php`

---

## 📱 PASO 10: CONFIGURACIÓN INICIAL

### Crear Tu Primera Campaña

1. **Ir a Campañas**
   - Menú lateral → **Campañas**

2. **Clic en "Nueva Campaña"**

3. **Llenar Formulario**
   - Código: `CAMP-2027-001`
   - Nombre: `Alcaldía de [Tu Ciudad] 2027`
   - Slogan: Tu slogan de campaña
   - Estado: `Activa`
   - Departamento: `[Tu Departamento]`
   - Municipio: `[Tu Municipio]`
   - Fechas: Inicio y fin de campaña
   - Meta de votos: Tu objetivo
   - Presupuesto: (opcional)
   - Colores: Puedes cambiarlos (default: Magenta y Dorado)

4. **Guardar**

5. **Seleccionar Campaña**
   - Usar el selector en el header
   - Ahora todas las funciones estarán asociadas a esta campaña

### Invitar Colaboradores

1. **Crear Usuarios** (implementación futura)
   - Por ahora, crear manualmente en la BD
   - Tabla `usuarios`
   - Luego vincular en `usuarios_campanas`

---

## 🔄 PASO 11: BACKUPS

### Configurar Backups Automáticos

**En Hostinger:**
1. Panel → **Archivos** → **Backups**
2. Activar backups automáticos semanales/diarios
3. Incluir base de datos y archivos

**Manual con phpMyAdmin:**
1. Acceder a phpMyAdmin
2. Seleccionar base de datos
3. Clic en **Exportar**
4. Método: **Rápido**
5. Formato: **SQL**
6. Clic en **Continuar**
7. Guardar archivo `.sql` en tu computadora

**Frecuencia Recomendada:**
- Backups automáticos: Diarios
- Backups manuales: Antes de cambios importantes
- Retención: Mínimo 30 días

---

## 📊 PASO 12: MONITOREO

### Verificar Estado del Sistema

**Diariamente:**
- ✅ Verificar que el sitio cargue
- ✅ Probar login
- ✅ Revisar logs de errores

**Semanalmente:**
- ✅ Verificar espacio en disco
- ✅ Revisar tráfico y rendimiento
- ✅ Actualizar PHP si hay nuevas versiones
- ✅ Backup manual de seguridad

**Mensualmente:**
- ✅ Revisar estadísticas de uso
- ✅ Optimizar base de datos (`OPTIMIZE TABLE`)
- ✅ Limpiar archivos temporales
- ✅ Revisar usuarios activos

---

## 🆘 SOLUCIÓN DE PROBLEMAS

### Problema: Página en Blanco

**Solución:**
1. Activar errores en `config/config.php`:
   ```php
   ini_set('display_errors', 1);
   ```
2. Recargar la página y ver el error
3. Buscar el error en Google o consultar documentación

### Problema: Error de Conexión a BD

**Solución:**
1. Verificar credenciales en `config/config.php`
2. Probar conexión desde phpMyAdmin
3. Verificar que el usuario tenga permisos

### Problema: Archivos No Se Suben

**Solución:**
1. Verificar permisos de `uploads/` (debe ser 755)
2. Verificar límites en `.htaccess`:
   ```apache
   php_value upload_max_filesize 10M
   php_value post_max_size 10M
   ```
3. Crear subcarpetas si no existen

### Problema: Sesión No Persiste

**Solución:**
1. Limpiar cookies del navegador
2. Verificar que `session_start()` funcione
3. Verificar permisos de carpeta de sesiones PHP

---

## 📞 SOPORTE

### Contacto

**Email**: soporte@aratio.com  
**WhatsApp**: +57 300 123 4567  
**Sitio Web**: https://aratio.com  
**Documentación**: https://docs.aratio.com  

### Hostinger Support

**Chat 24/7**: Disponible en panel de Hostinger  
**Email**: support@hostinger.com  
**Base de Conocimiento**: https://support.hostinger.com  

---

## ✅ CHECKLIST FINAL

Antes de considerarlo completo, verifica:

- [ ] Todos los archivos subidos a `/public_html/`
- [ ] Base de datos creada e importada (12 tablas)
- [ ] `config/config.php` configurado con credenciales reales
- [ ] JWT_SECRET cambiado a valor seguro
- [ ] Permisos de `uploads/` establecidos a 755
- [ ] HTTPS activado y funcionando
- [ ] Login funciona con `admin@aratio.com` / `Admin123!`
- [ ] Dashboard carga correctamente
- [ ] Contraseña del admin cambiada
- [ ] `install.php` eliminado
- [ ] Errores deshabilitados en producción
- [ ] Primera campaña creada
- [ ] Backup inicial realizado
- [ ] Sistema probado en móvil y desktop

---

## 🎉 ¡FELICITACIONES!

Tu sistema Aratio está completamente instalado y funcionando en producción.

**Próximos Pasos:**
1. Crear más campañas
2. Invitar colaboradores
3. Registrar donaciones
4. Programar eventos
5. Documentar compromisos
6. Generar reportes

**¡Buena suerte con tus campañas electorales! 🗳️**

---

**Documento**: Instrucciones de Despliegue  
**Versión**: 1.0.0  
**Fecha**: Noviembre 2024  
**Sistema**: Aratio - Gestión Electoral
