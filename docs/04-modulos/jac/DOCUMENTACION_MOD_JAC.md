# DOCUMENTACIÓN: Módulo mod_jac
## Juntas de Acción Comunal — Sistema Aratio v1.5.0
**Fecha**: 2026-03-17

---

## Descripción

El módulo **mod_jac** gestiona las Juntas de Acción Comunal (JAC) del Valle del Cauca. Cada registro está asociado a una campaña específica (`id_campana`) y utiliza el campo geográfico `territorio_valle` para organizar la información territorialmente.

Sigue el mismo patrón arquitectónico que `mod_diaD`: carpeta de módulo propia, API REST en `/api/`, página admin integrada en el panel Aratio, y dashboard público standalone sin autenticación.

---

## Archivos Creados / Modificados

| Archivo | Tipo | Descripción |
|---|---|---|
| `database/mod_jac_migration.sql` | **NEW** | Migración SQL — tabla `jac_registros` |
| `api/jac.php` | **NEW** | API REST CRUD completa |
| `pages/jac.php` | **NEW** | Vista admin (CRUD) integrada en Aratio |
| `mod_jac/dashboard.php` | **NEW** | Dashboard público standalone |
| `pages/dashboard.php` | **MODIFIED** | Banner JAC añadido |
| `index.php` | **MODIFIED** | Sidebar link, `$paginasPermitidas`, routing |
| `.htaccess` | **MODIFIED** | Regla `/dashboard-jac` |
| `documentacion/CHANGELOG.md` | **MODIFIED** | Entrada v1.5.0 |

---

## Base de Datos

### Tabla `jac_registros`

```sql
CREATE TABLE jac_registros (
  id                  INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  id_campana          INT UNSIGNED NOT NULL,       -- FK → campanas.id
  nombre_jac          VARCHAR(200) NOT NULL,
  presidente          VARCHAR(200),
  telefono            VARCHAR(20),
  email               VARCHAR(150),
  direccion           VARCHAR(250),
  territorio_valle    VARCHAR(150) NOT NULL,       -- Ej: "Norte de Cali", "Yumbo Centro"
  municipio           VARCHAR(100) NOT NULL,
  sector              VARCHAR(100),               -- Barrio / vereda
  comuna              VARCHAR(60),
  votos_comprometidos INT DEFAULT 0,
  afiliados_count     INT DEFAULT 0,
  estado              ENUM('activa','inactiva','en_proceso') DEFAULT 'activa',
  observaciones       TEXT,
  usuario_id          INT UNSIGNED,               -- Usuario que registró
  created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at          TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Para ejecutar la migración:**
```bash
# Via phpMyAdmin → importar database/mod_jac_migration.sql
# O via CLI:
mysql -u user -p aratio_db < database/mod_jac_migration.sql
```

---

## API REST `/api/jac.php`

| Método | Acción | Descripción |
|---|---|---|
| `GET ?action=list&campana_id=X` | Listar | Lista JACs (filtros: municipio, estado, territorio_valle, search) |
| `GET ?action=get&id=X` | Obtener | Un registro por ID |
| `GET ?action=stats&campana_id=X` | Stats | KPIs globales + desglose por municipio y territorio |
| `GET ?action=municipios&campana_id=X` | Municipios | Listado de municipios únicos |
| `GET ?action=territorios&campana_id=X` | Territorios | Listado de territorios únicos |
| `POST` | Crear | Crear nueva JAC (requiere auth) |
| `PUT` | Actualizar | Actualizar JAC por ID (requiere auth) |
| `DELETE ?id=X` | Eliminar | Solo admin/super-admin |

**Respuesta estándar:**
```json
{ "success": true, "data": [...], "total": 5 }
```

---

## URLs del Módulo

| URL | Acceso | Descripción |
|---|---|---|
| `/JAC?view=dashboard` | **PÚBLICO** | Dashboard Geométrico / Mapa de JACs |
| `/JAC?view=gestion` | **PRIVADO** | Gestión integral de JACs, Planchas y Miembros |
| `/mod_jac/login.php` | **N/A** | Login independiente del Portal JAC |
| `/?page=jac` | **PRIVADO** | Administración administrativa centralizada |
| `/api/jac.php` | **API** | Endpoints de datos JAC |

---

## Roles y Permisos (Portal Híbrido)

| Rol | Dashboard Público | Gestión / Planchas | Admin Central |
|---|---|---|---|
| Público (sin login) | ✅ | ❌ | ❌ |
| Colaborador | ✅ | ✅ (Ver) | ❌ |
| Admin-Campana | ✅ | ✅ (Full) | ✅ |
| Super-Admin | ✅ | ✅ (Full) | ✅ |

> [!IMPORTANT]
> El acceso a la **Gestión** requiere autenticación. Si un usuario no autenticado intenta acceder a `?view=gestion`, es redirigido automáticamente a `/mod_jac/login.php`.

---

## Campo `territorio_valle`

El campo `territorio_valle` es un **varchar libre** que representa la subdivisión geográfica del Valle del Cauca relevante para la campaña. Ejemplos:

- `Norte de Cali`, `Cali Centro`, `Cali Oriente`
- `Yumbo Norte`, `Yumbo Centro`
- `Palmira Sur`
- `Buenaventura Puerto`

> Este campo puede actualizarse para ser FK a una tabla `territorio_valle` dedicada cuando se requiera estructura normalizada.

---

## Integración con Dashboard Aratio

El banner del módulo JAC aparece en `pages/dashboard.php` justo debajo del banner Día D. Muestra:
- Ícono y nombre del módulo
- Contador de JACs activas de la campaña activa
- Botón "Gestionar JACs" → `?page=jac`
- Botón "Dashboard Público" → `/JAC?view=dashboard`

---

## Flujo de Autenticación del Portal (v1.6.0)

```
Usuario sin login visita /JAC?view=dashboard
    → ✅ Acceso permitido (público)

Usuario sin login visita /JAC?view=gestion
    → 🔄 Redirige a /mod_jac/login.php
    → Usuario ingresa credenciales
    → ✅ Si OK → redirige a /JAC?view=gestion
    → ❌ Si error → muestra mensaje de error inline

Usuario logueado visita /JAC?view=gestion
    → ✅ Acceso directo permitido
    → Sidebar muestra nombre + botón "Cerrar Sesión"
```

---

## Usuario de Prueba — Jaimito el Cartero

| Campo | Valor |
|---|---|
| Email | `jaimito@tangamandapio.com` |
| Password | `Jaimito2026!` |
| Rol | `admin-campana` |
| Campaña | `4 — Jaimito el Cartero` |
| Rol en Campaña | `administrador` |

---

## Datos de Demostración

Se generaron automáticamente mediante el script `mod_jac/generator_demo.php` (eliminado tras ejecución):

- **10 JACs** en barrios reales de Cali (Terrón Colorado, El Ingenio, Vipasa, etc.)
- **14 Planchas** distribuidas (4 JACs con 2 planchas, 6 JACs con 1 plancha)
- **7 Miembros por Plancha** con los cargos del Bloque Directivo, Vigilancia y Convivencia
- Todos los datos nominados con prefijo `user_demo_N` o `DEMO`
- Asociadas a la **Campaña ID: 4**

---

## Git Commit

```bash
# v1.6.0 - 2026-03-17
git add .
git commit -m "feat: v1.6.0 - Portal JAC Standalone con Acceso Híbrido y Login Independiente"
# Commit: e252d63
```

---

## Historial de Versiones del Módulo

| Versión | Fecha | Cambios Principales |
|---|---|---|
| **v1.6.0** | 2026-03-17 | Portal Standalone, Login Independiente, Auth Híbrido, Demo Data Generator |
| v1.5.0 | 2026-03-16 | Módulo JAC inicial, API REST, Visor Leaflet, CRUD Admin |
