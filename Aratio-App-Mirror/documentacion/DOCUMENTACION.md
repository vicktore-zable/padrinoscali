# ARATIO - Documentación Completa del Sistema
## Sistema de Gestión Electoral Multi-Tenant

---

## 📚 ÍNDICE

1. [Introducción](#introducción)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Módulos del Sistema](#módulos-del-sistema)
4. [Base de Datos](#base-de-datos)
5. [API Endpoints](#api-endpoints)
6. [Seguridad](#seguridad)
7. [Guía de Desarrollo](#guía-de-desarrollo)
8. [Deployment](#deployment)

---

## 🎯 INTRODUCCIÓN

**Aratio** es un sistema integral de gestión electoral diseñado para Colombia, que permite administrar múltiples campañas políticas simultáneamente (multi-tenant) con un enfoque en la gestión territorial de 5 niveles y la metodología de compromisos basada en las 5 preguntas.

### Características Principales

- ✅ **Multi-tenant**: Gestión simultánea de múltiples campañas
- ✅ **12 Perfiles Políticos**: Roles diferenciados por nivel de responsabilidad
- ✅ **5 Niveles de Participación**: Desde super-admin hasta colaborador
- ✅ **Jerarquía Territorial Completa**: Departamento → Municipio → Tipo Territorio → Territorio → Barrio/Vereda
- ✅ **Metodología de las 5 Preguntas**: Para gestión de compromisos
- ✅ **Mapas Interactivos**: Visualización geográfica con Leaflet
- ✅ **Reportes Dinámicos**: 6 tipos de reportes con gráficos
- ✅ **Colores Corporativos**: Magenta (#FF00FF) y Dorado (#FFD700)

### Tecnologías Utilizadas

#### Backend
- **PHP 7.4+**: Lenguaje del servidor
- **MySQL/MariaDB**: Base de datos relacional
- **PDO**: Abstracción de base de datos con prepared statements

#### Frontend
- **Tailwind CSS (Play CDN)**: Framework CSS sin compilación
- **Alpine.js**: Framework JavaScript reactivo
- **Leaflet.js**: Mapas interactivos
- **Chart.js**: Visualización de datos
- **Lucide Icons**: Iconografía moderna

---

## 🏗️ ARQUITECTURA DEL SISTEMA

### Estructura de Directorios

```
php-export/
│
├── config/
│   ├── config.php              # Configuración principal del sistema
│   └── installed.lock          # Archivo de bloqueo post-instalación
│
├── includes/
│   ├── Auth.php                # Clase de autenticación y autorización
│   └── [Otras clases]          # Clases adicionales del sistema
│
├── pages/
│   ├── dashboard.php           # Dashboard principal
│   ├── donaciones.php          # CRUD de donaciones
│   ├── eventos.php             # CRUD de eventos con mapas
│   ├── acciones.php            # Acciones comunitarias con jerarquía
│   ├── compromisos.php         # Sistema de compromisos (5 preguntas)
│   ├── reportes.php            # 6 tipos de reportes
│   ├── campanas.php            # Gestión de campañas
│   ├── elecciones.php          # Gestión de elecciones
│   ├── candidatos.php          # Registro de candidatos
│   ├── grupos.php              # Grupos políticos
│   ├── ayuda.php               # Centro de ayuda
│   └── configuracion.php       # Configuración de usuario
│
├── api/
│   ├── donaciones.php          # API REST para donaciones
│   ├── eventos.php             # API REST para eventos
│   ├── acciones.php            # API REST para acciones
│   ├── compromisos.php         # API REST para compromisos
│   ├── campanas.php            # API REST para campañas
│   └── [Otros endpoints]       # Más endpoints API
│
├── database/
│   └── schema.sql              # Schema completo de MySQL
│
├── uploads/                    # Archivos subidos (permisos 755)
│   ├── donaciones/
│   ├── eventos/
│   └── compromisos/
│
├── assets/
│   ├── css/                    # Estilos personalizados (opcional)
│   └── js/                     # Scripts personalizados (opcional)
│
├── index.php                   # Punto de entrada principal
├── login.php                   # Página de autenticación
├── logout.php                  # Cierre de sesión
├── install.php                 # Instalador automático
├── .htaccess                   # Configuración Apache
├── README.md                   # Documentación de instalación
└── DOCUMENTACION.md            # Este archivo
```

### Flujo de Datos

```
Usuario → index.php → Verificación Auth → Campaña Activa → Página Solicitada → Vista
                                                              ↓
                                                         Base de Datos (MySQL)
```

---

## 📦 MÓDULOS DEL SISTEMA

### 1. Dashboard
- **Archivo**: `pages/dashboard.php`
- **Descripción**: Vista principal con estadísticas en tiempo real
- **Características**:
  - 4 tarjetas de estadísticas principales
  - Gráfico de progreso de meta de votos
  - Estado de compromisos por categoría
  - Donaciones recientes (últimas 5)
  - Eventos próximos (próximos 5)
  - Cálculo de días restantes de campaña

### 2. Donaciones
- **Archivo**: `pages/donaciones.php`
- **Descripción**: Gestión completa de donaciones de campaña
- **Características**:
  - CRUD completo de donaciones
  - Tipos de donante: Persona natural, jurídica, anónimo
  - Métodos de pago: Efectivo, transferencia, especie
  - Estados: Pendiente, confirmada, rechazada
  - Estadísticas de recaudación
  - Filtros avanzados
  - Exportación a Excel

### 3. Eventos
- **Archivo**: `pages/eventos.php`
- **Descripción**: Cronograma y gestión de eventos de campaña
- **Características**:
  - 9 tipos de eventos (recorrido, reunión, debate, asamblea, mitin, etc.)
  - Mapa interactivo con marcadores
  - Generación de códigos QR
  - Registro de asistencia
  - Estados: Programado, en curso, finalizado, cancelado
  - Control de asistentes esperados vs confirmados
  - Gestión de presupuesto por evento

### 4. Acciones Comunitarias
- **Archivo**: `pages/acciones.php`
- **Descripción**: Registro de trabajo territorial puerta a puerta
- **Características**:
  - 7 tipos de acciones comunitarias
  - **Jerarquía territorial de 5 niveles**:
    1. Departamento
    2. Municipio
    3. Tipo de Territorio (Comuna, Corregimiento, etc.)
    4. Territorio (nombre específico)
    5. Barrio/Vereda
  - Contador de personas contactadas
  - Contador de compromisos obtenidos
  - Mapa de acciones realizadas
  - Sistema de evidencias fotográficas

### 5. Compromisos
- **Archivo**: `pages/compromisos.php`
- **Descripción**: Gestión de compromisos comunitarios con metodología estructurada
- **Características**:
  - **Metodología de las 5 Preguntas**:
    1. **¿Qué?** - Definir el compromiso (12 tipos: infraestructura, salud, educación, etc.)
    2. **¿Quién?** - Identificar al líder comunitario (nombre, teléfono, cargo)
    3. **¿Cuándo?** - Fecha de compromiso y fecha estimada de cumplimiento
    4. **¿Dónde?** - Ubicación con jerarquía territorial de 5 niveles
    5. **¿Cómo?** - Metodología de ejecución, presupuesto, beneficiarios
  - Estados: Pendiente, en gestión, cumplido, incumplido
  - Prioridades: Alta, media, baja
  - Porcentaje de avance
  - Presupuesto estimado y beneficiarios

### 6. Reportes
- **Archivo**: `pages/reportes.php`
- **Descripción**: Sistema completo de reportes con gráficos interactivos
- **6 Tipos de Reportes**:
  1. **Donaciones**: Gráficos por método de pago y evolución temporal
  2. **Eventos**: Distribución por tipo y asistencia
  3. **Acciones**: Tipos de acciones y efectividad (contactadas vs compromisos)
  4. **Compromisos**: Estado y distribución por tipo
  5. **Territorial**: Mapa de calor con distribución geográfica
  6. **General**: Resumen completo de la campaña con tendencias
- Exportación a PDF/Excel
- Gráficos interactivos con Chart.js

### 7. Campañas
- **Archivo**: `pages/campanas.php`
- **Descripción**: Gestión de campañas electorales (multi-tenant)
- **Características**:
  - CRUD completo de campañas
  - Código único por campaña
  - Estados: Planificación, activa, finalizada, suspendida
  - Meta de votos y seguimiento
  - Presupuesto de campaña
  - Colores personalizados (primario y secundario)
  - Fechas de inicio y fin
  - Selector de campaña en header

### 8. Elecciones
- **Archivo**: `pages/elecciones.php`
- **Descripción**: Registro de procesos electorales
- **Tipos de Elección**:
  - Presidencial
  - Senado
  - Cámara de Representantes
  - Gobernación
  - Alcaldía
  - Asamblea Departamental
  - Concejo Municipal
  - JAL (Juntas Administradoras Locales)
- Ámbitos: Nacional, departamental, municipal, local
- Período electoral: 2027 (Elecciones) → 2028-2031 (Período de gobierno)

### 9. Candidatos
- **Archivo**: `pages/candidatos.php`
- **Descripción**: Registro y gestión de candidatos
- **Características**:
  - Información personal completa
  - Foto de perfil
  - Cargo al que aspira
  - Vinculación con grupo político
  - Propuestas y biografía
  - Estados: Inscrito, activo, retirado, elegido
  - Documento de identificación

### 10. Grupos Políticos
- **Archivo**: `pages/grupos.php`
- **Descripción**: Partidos, movimientos y coaliciones
- **Tipos**:
  - Partido político
  - Movimiento político
  - Coalición
  - Grupo significativo de ciudadanos
- Información: Sigla, color, logo, representante legal
- Número de afiliados
- Fecha de fundación

### 11. Ayuda
- **Archivo**: `pages/ayuda.php`
- **Descripción**: Centro de ayuda y documentación
- **Secciones**:
  - Inicio rápido
  - Tutoriales por módulo
  - Preguntas frecuentes (FAQ)
  - Contacto con soporte
  - Guías de uso

### 12. Configuración
- **Archivo**: `pages/configuracion.php`
- **Descripción**: Configuración de usuario y preferencias
- **Pestañas**:
  1. **Perfil**: Información personal, foto, email, teléfono
  2. **Seguridad**: Cambio de contraseña
  3. **Notificaciones**: Preferencias de alertas
  4. **Apariencia**: Tema (actualmente solo claro)

---

## 🗄️ BASE DE DATOS

### Tablas Principales

#### 1. `usuarios`
```sql
- id (PK)
- nombre
- email (UNIQUE)
- password (hashed)
- telefono
- rol (super-admin, admin-campana, coordinador, colaborador, veedor)
- avatar_url
- estado (activo, inactivo, suspendido)
- ultimo_acceso
- created_at, updated_at
```

#### 2. `elecciones`
```sql
- id (PK)
- codigo (UNIQUE)
- nombre
- tipo (presidencial, senado, camara, gobernacion, alcaldia, asamblea, concejo, jal)
- ambito (nacional, departamental, municipal, local)
- fecha_eleccion
- periodo_inicio, periodo_fin
- estado (programada, en-campana, finalizada, cancelada)
- descripcion
```

#### 3. `grupos_politicos`
```sql
- id (PK)
- nombre
- sigla (UNIQUE)
- tipo (partido, movimiento, coalicion, grupo-significativo)
- color
- logo_url
- fecha_fundacion
- representante_legal
- email, telefono, web
- numero_afiliados
- activo (BOOLEAN)
```

#### 4. `candidatos`
```sql
- id (PK)
- nombres, apellidos, nombre_completo
- documento (UNIQUE), tipo_documento (CC, CE, PA, NIT)
- cargo_aspira
- grupo_politico_id (FK)
- eleccion_id (FK)
- email, telefono
- foto_url
- departamento, municipio
- biografia, propuestas
- estado (inscrito, activo, retirado, elegido)
```

#### 5. `campanas`
```sql
- id (PK)
- codigo (UNIQUE)
- nombre, slogan, descripcion
- estado (planificacion, activa, finalizada, suspendida)
- candidato_id (FK), eleccion_id (FK)
- departamento, municipio
- meta_votos, votos_actuales
- presupuesto
- fecha_inicio, fecha_fin
- color_primario, color_secundario
- logo_url
```

#### 6. `usuarios_campanas` (Tabla relacional many-to-many)
```sql
- id (PK)
- usuario_id (FK)
- campana_id (FK)
- rol_campana (administrador, coordinador, colaborador, veedor)
- UNIQUE(usuario_id, campana_id)
```

#### 7. `donaciones`
```sql
- id (PK)
- campana_id (FK)
- tipo_donante (persona-natural, persona-juridica, anonimo)
- nombre_donante, documento_donante
- email_donante, telefono_donante, direccion_donante
- monto
- metodo_pago (efectivo, transferencia, especie)
- referencia_pago, descripcion_especie
- estado (pendiente, confirmada, rechazada)
- fecha_donacion, fecha_confirmacion
- recaudador_id (FK)
- comprobante_url, notas
```

#### 8. `eventos`
```sql
- id (PK)
- campana_id (FK)
- nombre
- tipo (recorrido, reunion, debate, asamblea, mitin, jornada-firmas, capacitacion, evento-social, otro)
- descripcion
- fecha_inicio, fecha_fin
- ubicacion, direccion
- latitud, longitud
- departamento, municipio
- asistentes_esperados, asistentes_confirmados
- responsable_id (FK)
- codigo_qr
- estado (programado, en-curso, finalizado, cancelado)
- presupuesto, notas
```

#### 9. `asistencia_eventos`
```sql
- id (PK)
- evento_id (FK)
- nombre, documento, telefono, email
- fecha_registro
- metodo_registro (qr, manual, web)
- asistio (BOOLEAN)
- notas
```

#### 10. `acciones_comunitarias`
```sql
- id (PK)
- campana_id (FK), usuario_id (FK)
- tipo (puerta-puerta, brigada-salud, jornada-social, recoleccion-firmas, encuesta, entrega-volantes, reunion-comunitaria, otro)
- descripcion
- fecha_accion
- JERARQUÍA TERRITORIAL (5 NIVELES):
  * departamento
  * municipio
  * tipo_territorio (Comuna, Corregimiento, etc.)
  * territorio (Nombre específico)
  * barrio (Barrio/Vereda)
- latitud, longitud
- personas_contactadas
- compromisos_obtenidos
- observaciones
- evidencia_url
```

#### 11. `compromisos`
```sql
- id (PK)
- campana_id (FK), usuario_id (FK)
- tipo (infraestructura, servicios-publicos, salud, educacion, seguridad, deporte, cultura, medio-ambiente, empleo, vivienda, movilidad, otro)
- titulo, descripcion
- LAS 5 PREGUNTAS:
  * ¿Qué?: titulo, descripcion, tipo
  * ¿Quién?: lider_nombre, lider_telefono, lider_email, lider_cargo
  * ¿Cuándo?: fecha_compromiso, fecha_cumplimiento_estimada, fecha_cumplimiento_real
  * ¿Dónde?: departamento, municipio, tipo_territorio, territorio, barrio, latitud, longitud
  * ¿Cómo?: metodologia, presupuesto_estimado, beneficiarios_estimados
- prioridad (alta, media, baja)
- estado (pendiente, en-gestion, cumplido, incumplido)
- avance_porcentaje
- observaciones
```

#### 12. `sesiones`
```sql
- id (PK)
- usuario_id (FK)
- token (UNIQUE)
- ip_address
- user_agent
- expires_at
```

### Índices y Optimización

Todos los campos frecuentemente consultados tienen índices:
- Claves primarias (id)
- Claves foráneas (FK)
- Campos de búsqueda (email, codigo, estado)
- Campos de fecha (para reportes temporales)
- Campos geográficos (departamento, municipio)

---

## 🔐 SEGURIDAD

### Autenticación
- Passwords hasheados con `bcrypt` (PASSWORD_BCRYPT)
- Sesiones con tokens únicos
- Expiración de sesiones configurables (default: 2 horas)
- Protección contra intentos de login múltiples

### Autorización
- Sistema de roles: super-admin, admin-campana, coordinador, colaborador, veedor
- Roles específicos por campaña (usuarios_campanas)
- Verificación de permisos en cada acción

### Protección de Datos
- **Prepared Statements (PDO)**: Protección contra SQL Injection
- **htmlspecialchars()**: Protección contra XSS
- **CSRF Tokens**: (Implementar en formularios)
- **File Upload Validation**: Validación de tipos y tamaños
- **Headers de Seguridad** (.htaccess):
  - X-Frame-Options: SAMEORIGIN
  - X-Content-Type-Options: nosniff
  - X-XSS-Protection: 1; mode=block

### Configuración de Producción
```php
// En config/config.php
define('APP_ENV', 'production');
error_reporting(0);
ini_set('display_errors', 0);
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');
```

---

## 🔌 API ENDPOINTS

El sistema expone endpoints REST para operaciones CRUD.

### Campañas (`/api/campanas.php`)

| Método | Acción | Descripción |
|--------|--------|-------------|
| `GET` | Listar | Obtiene las campañas del usuario autenticado |
| `POST` | Crear | Crea una nueva campaña |
| `PUT` | Actualizar | Actualiza una campaña existente |
| `DELETE` | Eliminar | Elimina una campaña (`?id=X`) |

#### Ejemplo POST/PUT (JSON):
```json
{
  "id": 1,                          // Solo para PUT
  "codigo": "CAMP-2027-001",        // Requerido, único
  "nombre": "Campaña Concejo 2027", // Requerido
  "slogan": "Por una ciudad mejor",
  "descripcion": "Descripción de la campaña",
  "estado": "activa",               // planificacion|activa|finalizada|suspendida
  "candidato_id": 1,                // Requerido (FK)
  "eleccion_id": 1,                 // Requerido (FK)
  "departamento": "Cundinamarca",   // Requerido
  "municipio": "Bogotá",            // Requerido
  "fecha_inicio": "2027-01-01",     // Requerido
  "fecha_fin": "2027-10-28",        // Requerido
  "meta_votos": 50000,
  "presupuesto": 100000000.00,
  "color_primario": "#FF00FF",
  "color_secundario": "#FFD700"
}
```

#### Respuestas:
```json
// Éxito
{ "success": true, "message": "Campaña creada exitosamente", "id": 1 }

// Error
{ "success": false, "message": "El código de campaña ya existe" }
```

#### Autenticación:
Todos los endpoints requieren sesión activa. Sin autenticación devuelve:
```json
{ "success": false, "message": "No autorizado" }
```

---

## 🚀 DEPLOYMENT

### Checklist de Instalación

1. ✅ **Subir archivos** vía FTP a `public_html/`
2. ✅ **Crear base de datos** en phpMyAdmin
3. ✅ **Importar schema** desde `database/schema.sql`
4. ✅ **Configurar credenciales** en `config/config.php`
5. ✅ **Configurar permisos** de carpeta `uploads/` (755)
6. ✅ **Verificar .htaccess** para Apache
7. ✅ **Ejecutar instalador** en `/install.php` (opcional)
8. ✅ **Eliminar install.php** por seguridad
9. ✅ **Cambiar contraseña** del admin
10. ✅ **Activar HTTPS** (SSL/TLS)

### Optimización de Producción

#### PHP (php.ini o .htaccess)
```ini
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 256M
max_execution_time = 300
session.gc_maxlifetime = 7200
```

#### MySQL
```sql
-- Optimizar tablas periódicamente
OPTIMIZE TABLE donaciones, eventos, acciones_comunitarias, compromisos;

-- Índices adicionales para reportes
CREATE INDEX idx_fecha_accion ON acciones_comunitarias(fecha_accion);
CREATE INDEX idx_fecha_donacion ON donaciones(fecha_donacion);
```

#### Apache (.htaccess)
```apache
# Habilitar compresión
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css text/javascript application/javascript application/json
</IfModule>

# Cache de archivos estáticos
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### Backups

#### Base de Datos (Automatizar con cron)
```bash
#!/bin/bash
# backup-db.sh
mysqldump -u usuario -p'password' aratio_db > backup_$(date +%Y%m%d).sql
# Mantener últimos 30 días
find /backups -name "backup_*.sql" -mtime +30 -delete
```

#### Archivos
```bash
# backup-files.sh
tar -czf backup_files_$(date +%Y%m%d).tar.gz uploads/
```

### Monitoreo

#### Logs de Error
```php
// En producción, configurar log de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/php-error.log');
```

#### Métricas Clave
- Tiempo de respuesta de páginas
- Uso de CPU y memoria
- Espacio en disco
- Conexiones activas a BD
- Tasa de errores

---

## 📞 SOPORTE Y CONTACTO

**Email**: aratio@edisongiraldo.com  
**WhatsApp**: +57 300 123 4567  
**Sitio Web**: https://edisongiraldo.com/aratio  
**Documentación**: https://edisongiraldo.com/aratio/documentacion

---

## 📄 LICENCIA

Copyright © 2025 Aratio. Todos los derechos reservados.

Sistema desarrollado específicamente para campañas electorales en Colombia.

---

**Versión del Sistema**: 1.0.0  
**Fecha de Última Actualización**: Noviembre 2024  
**Compatible con**: PHP 7.4+, MySQL 5.7+, Apache 2.4+
