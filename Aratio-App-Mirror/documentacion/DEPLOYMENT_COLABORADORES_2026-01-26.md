# Deployment: Integración Completa Módulo Colaboradores

**Fecha:** 2026-01-26
**Versión:** 2.0.0 - Integración Colaboradores

---

## Resumen de Cambios

### Nuevos Archivos Creados

| Archivo | Descripción |
|---------|-------------|
| `pages/colaboradores_red.php` | Visualización red jerárquica con Vis.js |
| `pages/colaborador_detalle.php` | Detalle de colaborador con tabs |
| `pages/colaboradores_reportes.php` | Dashboard de reportes con Chart.js |
| `database/migrations/20260125_colaboradores_completo.sql` | Migración de base de datos |
| `run_migration.php` | Script para ejecutar migración |

### Archivos Modificados

| Archivo | Cambios |
|---------|---------|
| `api/colaboradores.php` | +15 nuevos endpoints (network, historial, curriculum, etc.) |
| `pages/colaboradores.php` | +Botones navegación (Red, Reportes, Exportar) |
| `index.php` | +Soporte para nuevas páginas |

---

## Paso 1: Subir Archivos al Servidor

### Método 1: Panel de Control (File Manager)

1. Acceder a Hostinger Panel → Files → File Manager
2. Navegar a `domains/aratio.mrmtech.net/public_html/`
3. Subir los siguientes archivos:

**Archivos nuevos:**
```
pages/colaboradores_red.php
pages/colaborador_detalle.php
pages/colaboradores_reportes.php
database/migrations/20260125_colaboradores_completo.sql
run_migration.php
```

**Archivos modificados:**
```
api/colaboradores.php
pages/colaboradores.php
index.php
```

### Método 2: FTP (FileZilla)

```
Host: 212.1.208.241
Usuario: u156469157@aratio.mrmtech.net
Password: [Solicitar al administrador]
Puerto: 21
Directorio remoto: /domains/aratio.mrmtech.net/public_html/
```

---

## Paso 2: Ejecutar Migración de Base de Datos

### Opción A: Via Navegador (Recomendado)

1. Iniciar sesión en Aratio como **super-admin**
2. Acceder a: `https://aratio.mrmtech.net/run_migration.php`
3. Verificar que el resultado sea:
   ```json
   {
       "success": true,
       "message": "Migracion completada exitosamente",
       "verificacion": {
           "curriculum": "Existe",
           "historial_cambios_lider": "Existe",
           "historial_estados": "Existe"
       }
   }
   ```
4. **IMPORTANTE**: Eliminar `run_migration.php` después de ejecutar

### Opción B: Via phpMyAdmin

1. Acceder a phpMyAdmin desde panel Hostinger
2. Seleccionar base de datos: `u156469157_aratio_v1`
3. Ir a pestaña SQL
4. Copiar y pegar el contenido de:
   `database/migrations/20260125_colaboradores_completo.sql`
5. Ejecutar

---

## Paso 3: Verificación Post-Deployment

### 3.1 Verificar Tablas Creadas

En phpMyAdmin ejecutar:
```sql
SHOW TABLES LIKE 'curriculum';
SHOW TABLES LIKE 'historial_cambios_lider';
SHOW TABLES LIKE 'historial_estados';
```

### 3.2 Verificar Vistas

```sql
SELECT * FROM v_colaboradores_red LIMIT 1;
SELECT * FROM v_estadisticas_campana;
```

### 3.3 Probar Funcionalidades

| Test | URL | Resultado Esperado |
|------|-----|-------------------|
| Lista colaboradores | `/index.php?page=colaboradores` | Lista con nuevos botones |
| Red jerárquica | `/index.php?page=colaboradores_red` | Grafo Vis.js |
| Reportes | `/index.php?page=colaboradores_reportes` | Dashboard con gráficos |
| Detalle | `/index.php?page=colaborador_detalle&id=1` | Página con tabs |
| API Network | `/api/colaboradores.php?action=network&campana_id=1` | JSON con nodes/edges |
| Exportar CSV | `/api/colaboradores.php?action=export&formato=csv&campana_id=1` | Descarga CSV |
| Plantilla | `/api/colaboradores.php?action=plantilla` | Descarga plantilla CSV |

---

## Nuevos Endpoints API

### Endpoints GET

| Endpoint | Descripción |
|----------|-------------|
| `?action=network&campana_id=X` | Red jerárquica completa |
| `?action=seguidores&id=X` | Seguidores de un colaborador |
| `?action=historial&id=X` | Historial de cambios |
| `?action=curriculum&id=X` | Curriculum del colaborador |
| `?action=reportes&tipo=X&campana_id=X` | Reportes (general/perfil/territorio/lideres/crecimiento) |
| `?action=export&formato=csv&campana_id=X` | Exportar a CSV |
| `?action=plantilla` | Descargar plantilla importación |
| `?action=stats_extended&campana_id=X` | Estadísticas usando vista |

### Endpoints POST

| Endpoint | Body JSON | Descripción |
|----------|-----------|-------------|
| `?action=curriculum` | `{colaborador_id, experiencia_laboral[], formacion_academica[], participacion_politica[]}` | Guardar curriculum |
| `?action=cambiar_lider` | `{colaborador_id, nuevo_lider, motivo}` | Cambiar líder con historial |
| `?action=reevaluar` | `{colaborador_id, dato_potencial, motivo}` | Reevaluar con historial |

---

## Estructura de Datos

### Red Jerárquica (response)

```json
{
    "success": true,
    "data": {
        "nodes": [
            {
                "id": "12345678",
                "label": "Juan P.",
                "color": "#FF00FF",
                "size": 30,
                "perfil": "Lider Comunitario",
                "seguidores": 15
            }
        ],
        "edges": [
            {
                "from": "12345678",
                "to": "87654321",
                "arrows": "to"
            }
        ],
        "stats": {
            "total_nodos": 150,
            "total_aristas": 120,
            "sin_lider": 8,
            "lideres_activos": 12
        }
    }
}
```

### Curriculum (estructura)

```json
{
    "experiencia_laboral": [
        {
            "cargo": "Coordinador",
            "empresa": "Alcaldía",
            "fecha_inicio": "2020-01-01",
            "fecha_fin": "2023-12-31",
            "descripcion": "Coordinación de programas"
        }
    ],
    "formacion_academica": [
        {
            "titulo": "Administración",
            "institucion": "Universidad",
            "nivel": "Pregrado",
            "fecha_inicio": "2015-01-01",
            "fecha_fin": "2019-12-01"
        }
    ],
    "participacion_politica": [
        {
            "cargo": "Edil",
            "organizacion": "JAL",
            "fecha_inicio": "2020-01-01",
            "fecha_fin": null,
            "descripcion": "Representante"
        }
    ]
}
```

---

## Colores por Perfil (Vis.js)

| Perfil | Color |
|--------|-------|
| Líder Comunitario | `#FF00FF` (Magenta) |
| Líder Social | `#3B82F6` (Azul) |
| Líder Gremial | `#10B981` (Verde) |
| Activista | `#F59E0B` (Naranja) |
| Simpatizante | `#6B7280` (Gris) |

---

## Seguridad

### Permisos por Rol

| Acción | super-admin | admin-campana | coordinador | colaborador | veedor |
|--------|-------------|---------------|-------------|-------------|--------|
| Ver red | ✅ | ✅ (su campaña) | ✅ | ✅ | ✅ |
| Ver historial | ✅ | ✅ | ✅ | ✅ | ✅ |
| Cambiar líder | ✅ | ✅ | ✅ | ❌ | ❌ |
| Reevaluar | ✅ | ✅ | ✅ | ❌ | ❌ |
| Editar curriculum | ✅ | ✅ | ✅ | ❌ | ❌ |
| Exportar | ✅ | ✅ | ✅ | ❌ | ✅ |

### Multi-Tenant

Todas las consultas están filtradas por `campana_id`. Un usuario solo puede:
- Ver colaboradores de campañas a las que tiene acceso
- Modificar datos dentro de su campaña asignada

---

## Troubleshooting

### Error: "Tabla curriculum no existe"

```sql
-- Ejecutar en phpMyAdmin:
CREATE TABLE IF NOT EXISTS curriculum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT NOT NULL,
    experiencia_laboral JSON,
    formacion_academica JSON,
    participacion_politica JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE,
    UNIQUE KEY uk_colaborador (colaborador_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Error: "Vista no existe"

```sql
-- Ejecutar completo el archivo:
-- database/migrations/20260125_colaboradores_completo.sql
```

### Red jerárquica no carga

1. Verificar que la campaña tiene colaboradores
2. Verificar consola del navegador (F12) para errores JS
3. Probar endpoint directamente: `/api/colaboradores.php?action=network&campana_id=1`

### Gráficos no aparecen

1. Verificar que Chart.js carga correctamente
2. Verificar datos del API de reportes
3. Probar: `/api/colaboradores.php?action=reportes&tipo=general&campana_id=1`

---

## Limpieza Post-Deployment

Después de verificar que todo funciona:

1. **Eliminar scripts de migración:**
   - `run_migration.php`

2. **Verificar logs:**
   - Revisar `error_log` en panel Hostinger
   - Verificar que no hay errores 500

---

## Contacto

Para soporte técnico o problemas de deployment, contactar al equipo de desarrollo.

**Documentación generada:** 2026-01-26
