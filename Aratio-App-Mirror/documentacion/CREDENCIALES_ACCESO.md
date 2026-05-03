# 🔐 CREDENCIALES DE ACCESO - Sistema Aratio (Edison Giraldo)

**Última actualización**: Mayo de 2026
**Sistema**: Aratio v2.4.0
**URL Producción**: https://edisongiraldo.com/aratio/
**URL Preview**: https://navajowhite-goose-984880.hostingersite.com/aratio/
**Estado Acceso**: ✅ Verificado y funcional (SSH/DB)

---

## 🚀 CREDENCIALES ACTUALES (PRODUCCIÓN)

### 👤 Usuario Administrador
- **Email**: aratio@edisongiraldo.com
- **Password**: Admin123!
- **URL Login**: https://edisongiraldo.com/aratio/login.php

### 🗄️ Base de Datos (Hostinger)
- **Host**: localhost (interno) / auth-db690.hstgr.io (externo)
- **Usuario**: u577647812_aratio
- **Password**: v6xSHUWhjrxE
- **Base de datos**: u577647812_aratio

### 🖥️ SSH / SFTP
- **Host**: 157.173.208.254
- **Puerto**: 65002
- **Usuario**: u577647812
- **Password**: E=j$`01yHi^?XfpoM@|CD"5H4

### 📁 Rutas
- **Raíz Web**: `/home/u577647812/domains/edisongiraldo.com/public_html/`
- **Aratio**: `/home/u577647812/domains/edisongiraldo.com/public_html/aratio/`

---

## 📜 CREDENCIALES LEGACY (aratio.mrmtech.net)
*Estas credenciales corresponden al entorno de desarrollo original o campañas anteriores.*

### Login Web (Old)
- URL: https://aratio.mrmtech.net/login.php
- Email: admin@aratio.mrmtech.net
- Password: Admin123!

### Base de Datos (Old)
- Host: auth-db690.hstgr.io
- Usuario: u156469157_aratio_v1
- Password: 15zxCeBbvgsR
- DB: u156469157_aratio_v1

### SSH/FTP (Old)
- Host: 212.1.208.241
- Puerto SSH: 65002
- Puerto FTP: 21
- Usuario: u156469157
- Password: sthLX6bJPoGh$


### Comandos Útiles via SSH
```bash
# Ver logs de errores PHP
tail -f /home/u156469157/domains/aratio.mrmtech.net/public_html/php-errors.log

# Ir al directorio web
cd /home/u156469157/domains/aratio.mrmtech.net/public_html

# Ver estructura de archivos
ls -la

# Crear directorio cache con permisos
mkdir -p cache/rate_limit && chmod 755 cache cache/rate_limit

# Ver uso de disco
du -sh /home/u156469157/domains/aratio.mrmtech.net/public_html/*
```

---

## 🌐 PANEL DE HOSTING

### Hostinger hPanel
```
URL: https://hpanel.hostinger.com
Usuario: (tu email de registro en Hostinger)
Password: (tu password de Hostinger)
```

**Desde aquí puedes acceder a**:
- File Manager
- phpMyAdmin
- Logs de error
- Configuración SSL
- Configuración de dominios
- Backups

---

## 🔌 API REST

### Endpoints Disponibles

#### Territorios (5 niveles geográficos)
```
Base URL: https://aratio.mrmtech.net/api/territorios.php

1. Departamentos:
   GET ?accion=departamentos

2. Municipios:
   GET ?accion=municipios&departamento=Valle+del+Cauca

3. Tipos de Territorio:
   GET ?accion=tipos_territorio&departamento=Valle+del+Cauca&municipio=Cali

4. Territorios (comunas, corregimientos):
   GET ?accion=territorios&departamento=Valle+del+Cauca&municipio=Cali&tipo_territorio=Urbano

5. Barrios:
   GET ?accion=barrios&departamento=Valle+del+Cauca&municipio=Cali&tipo_territorio=Urbano&territorio=Comuna+1

6. Completo (con filtros):
   GET ?accion=completo&departamento=...&municipio=...
```

#### Otros Endpoints API
```
/api/campanas.php          - Campañas (GET, POST, PUT, DELETE)
/api/candidatos.php        - Candidatos (GET, POST, PUT, DELETE)
/api/donaciones.php        - Donaciones (GET, POST, PUT, DELETE)
/api/elecciones.php        - Elecciones (GET, POST, PUT, DELETE)
/api/eventos.php           - Eventos (GET, POST, PUT, DELETE)
/api/grupos.php            - Grupos políticos (GET, POST, PUT, DELETE)
/api/acciones.php          - Acciones comunitarias (GET, POST, PUT, DELETE)
/api/compromisos.php       - Compromisos (GET, POST, PUT, DELETE)
/api/asistencia_eventos.php - Asistencia (GET, POST, PUT, DELETE)
```

**Autenticación**: Todas las APIs requieren sesión activa (cookie ARATIO_SESSION)

---

## 📄 USUARIOS DEL SISTEMA

### Roles Disponibles
```
1. super-admin      - Acceso total al sistema
2. admin-campana    - Administrador de campaña específica
3. coordinador      - Coordinador de área
4. colaborador      - Usuario básico (registro de datos)
5. veedor           - Solo lectura
```

### Usuario Administrador Actual
```sql
-- Query para verificar
SELECT id, nombre, email, rol FROM usuarios WHERE id = 1;

-- Resultado:
id: 1
nombre: Administrador Sistema
email: admin@aratio.mrmtech.net
rol: super-admin
```

---

## 🔧 COMANDOS ÚTILES

### Verificar Conexión MySQL
```bash
mysql -h auth-db690.hstgr.io -u u156469157_aratio_v1 -p15zxCeBbvgsR -e "SELECT VERSION();"
```

### Ver Tablas
```bash
mysql -h auth-db690.hstgr.io -u u156469157_aratio_v1 -p15zxCeBbvgsR u156469157_aratio_v1 -e "SHOW TABLES;"
```

### Contar Territorios
```bash
mysql -h auth-db690.hstgr.io -u u156469157_aratio_v1 -p15zxCeBbvgsR u156469157_aratio_v1 -e "SELECT COUNT(*) FROM territorios;"
```

### Subir Archivo por FTP (curl)
```bash
curl -T "archivo.php" ftp://212.1.208.241/ruta/destino/ --user "u156469157.aratio.mrmtech.net:sthLX6bJPoGh"
```

---

## 🔒 SEGURIDAD

### Headers Activos
```
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
```

### Sesiones
```
Cookie: ARATIO_SESSION
HTTPOnly: true
Secure: true (solo HTTPS)
SameSite: Strict
Duración: 2 horas
```

### Passwords
- Algoritmo: bcrypt (PASSWORD_BCRYPT)
- Cost: 10
- Todas las contraseñas están hasheadas

---

## 📞 SOPORTE

### Documentación del Sistema
```
H:/Mi unidad/2025/5d/app/Multi-Campaign Management System/
├── DEPLOYMENT_SUCCESS_27NOV2025.md      - Reporte de deployment
├── CREDENCIALES_ACCESO.md               - Este archivo
├── src/php-export/
│   ├── DOCUMENTACION.md                 - Documentación técnica (656 líneas)
│   └── README.md                        - Guía de instalación
└── CLAUDE.md                            - Guía completa del proyecto
```

### URLs Útiles
```
Sistema: https://aratio.mrmtech.net
Panel Hostinger: https://hpanel.hostinger.com
Soporte Hostinger: https://support.hostinger.com
```

---

## ⚠️ IMPORTANTE

### Cambiar Password del Admin
1. Login al sistema: https://aratio.mrmtech.net/login.php
2. Ir a: `Configuración` (ícono de engranaje)
3. Seleccionar `Perfil`
4. Click en `Cambiar Contraseña`
5. Ingresar:
   - Password actual: `Admin123!`
   - Nuevo password: (tu nuevo password seguro)
   - Confirmar nuevo password
6. Guardar cambios

### Backup Recomendado
- **Base de datos**: Backup diario desde phpMyAdmin
- **Archivos**: Backup semanal vía FTP
- **Automatización**: Configurar backups automáticos en panel de Hostinger

### Monitoreo
- Revisar logs de error regularmente (panel de Hostinger)
- Verificar funcionamiento de APIs semanalmente
- Monitorear uso de base de datos (espacio disponible)

---

**Fecha de actualización**: 19 de Febrero de 2026
**Versión del sistema**: 1.2.0 - Portal Connection & Redirection Sync
**Estado**: ✅ Credenciales y portal verificados y funcionales
**Última prueba de acceso**: 19/02/2026 05:45 AM
- ✅ SSH: Conexión exitosa (puerto 65002)
- ✅ FTP: Conexión exitosa (puerto 21)
