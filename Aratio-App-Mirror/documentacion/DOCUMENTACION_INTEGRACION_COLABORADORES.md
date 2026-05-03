# Documentacion Completa: Integracion Modulo Colaboradores en Aratio

**Fecha:** 2026-01-27
**Version:** 2.0.0
**Estado:** Desplegado en produccion

---

## 1. Contexto del Proyecto

### 1.1 Problema Inicial

Existian dos sistemas separados para gestionar colaboradores:

| Sistema | URL | Base de Datos | Estado |
|---------|-----|---------------|--------|
| **Aratio** (principal) | aratio.mrmtech.net | `u156469157_aratio_v1` | Produccion |
| **Colaboradores** (externo) | colaboradores.aratio.mrmtech.net | `u156469157_aratio` | Produccion |

El sistema externo tenia funcionalidades avanzadas (red jerarquica, curriculum, historial, reportes) que Aratio no tenia. El objetivo fue integrar todas estas funcionalidades en Aratio manteniendo la arquitectura **multi-tenant** donde cada campana tiene sus propios datos aislados.

### 1.2 Arquitectura Multi-Tenant

```
SISTEMA ARATIO
  Usuario (super-admin)
     -> Acceso a TODAS las campanas

  Usuario (admin-campana)
     -> usuarios_campanas -> Solo campanas asignadas
            -> colaboradores WHERE campana_id = X
            -> historial WHERE campana_id = X
            -> reportes WHERE campana_id = X

  Colaborador
     -> Pertenece a UNA campana (campana_id)
     -> Tiene UN lider directo (lider_directo -> documento)
     -> Puede tener N seguidores en la misma campana
```

### 1.3 Roles y Permisos

| Rol | Ver Colaboradores | Crear | Editar | Eliminar | Reportes | Red |
|-----|-------------------|-------|--------|----------|----------|-----|
| super-admin | Todas las campanas | Si | Si | Si | Si | Si |
| admin-campana | Solo sus campanas | Si | Si | Si | Si | Si |
| coordinador | Solo sus campanas | Si | Si | No | Si | Si |
| colaborador | Solo sus campanas | Si | Solo propios | No | No | Si |
| veedor | Solo sus campanas | No | No | No | Si | Si |

---

## 2. Infraestructura

### 2.1 Servidor de Produccion

```
Proveedor:   Hostinger
Dominio:     aratio.mrmtech.net
IP:          212.1.208.241
SSH Puerto:  65002
SSH User:    u156469157
SSH Pass:    sthLX6bJPoGh$
```

### 2.2 Metodo de Upload

```
Protocolo:   SFTP via curl
Comando:     curl -k -T archivo "sftp://u156469157:PASSWORD@212.1.208.241:65002/RUTA"
Ruta base:   /home/u156469157/domains/aratio.mrmtech.net/public_html/
```

**Nota:** SCP y SSH interactivo no funcionan desde el entorno de desarrollo. FTP (puerto 21) tambien fallo por credenciales. Solo funciona **curl SFTP** al puerto 65002.

### 2.3 Bases de Datos

| Base de Datos | Host | Usuario | Password | Uso |
|---------------|------|---------|----------|-----|
| `u156469157_aratio_v1` | auth-db690.hstgr.io | u156469157_aratio_v1 | 15zxCeBbvgsR | Aratio principal |
| `u156469157_aratio` | auth-db690.hstgr.io | u156469157_aratio | 15zxCeBbvgsR | Sistema externo colaboradores |

### 2.4 Credenciales de Acceso al Sistema

```
Email:    admin@aratio.mrmtech.net
Password: Admin123!
Rol:      super-admin
```

---

## 3. Proceso de Implementacion

### Fase 1: Migraciones de Base de Datos

**Archivo creado:** `database/migrations/20260125_colaboradores_completo.sql`

Se crearon 3 nuevas tablas y 2 vistas:

#### Tablas Nuevas

```sql
-- 1. curriculum: Hoja de vida profesional
CREATE TABLE curriculum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT NOT NULL,
    experiencia_laboral JSON,      -- [{cargo, empresa, fecha_inicio, fecha_fin, descripcion}]
    formacion_academica JSON,       -- [{titulo, institucion, fecha_inicio, fecha_fin, nivel}]
    participacion_politica JSON,    -- [{cargo, organizacion, fecha_inicio, fecha_fin, descripcion}]
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE,
    UNIQUE KEY uk_colaborador (colaborador_id)
);

-- 2. historial_cambios_lider: Auditoria de cambios de lider
CREATE TABLE historial_cambios_lider (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT NOT NULL,
    colaborador_documento VARCHAR(20) NOT NULL,
    lider_anterior VARCHAR(20),
    lider_nuevo VARCHAR(20),
    motivo TEXT,
    usuario_cambio INT NOT NULL,
    campana_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE
);

-- 3. historial_estados: Trazabilidad de reevaluaciones
CREATE TABLE historial_estados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT NOT NULL,
    campana_id INT NOT NULL,
    dato_potencial_anterior INT,
    dato_potencial_nuevo INT,
    dato_historico_anterior INT,
    dato_historico_nuevo INT,
    estado_anterior VARCHAR(20),
    estado_nuevo VARCHAR(20),
    motivo TEXT,
    tipo_cambio ENUM('creacion','actualizacion','reevaluacion'),
    usuario_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Vistas Creadas

```sql
-- v_colaboradores_red: Colaboradores con metricas de red
-- Incluye total_seguidores, datos del lider directo

-- v_estadisticas_campana: Estadisticas agregadas por campana
-- Incluye totales, estados calculados via CASE sobre dato_potencial/historico
```

#### Indices Adicionales

```sql
ALTER TABLE colaboradores ADD INDEX idx_jerarquia (campana_id, lider_directo, documento);
ALTER TABLE colaboradores ADD INDEX idx_territorio_campana (campana_id, departamento, municipio);
```

**Nota importante:** El campo `estado` NO es una columna real en la tabla `colaboradores`. Se calcula en PHP:
- dato_potencial = 0 -> "Nuevo"
- dato_historico = 0 -> "Desvinculado"
- dato_historico > dato_potencial -> "Crecio"
- dato_historico < dato_potencial -> "Decrece"
- dato_historico = dato_potencial -> "Igual"

**Script de ejecucion:** `run_migration.php` (requiere sesion super-admin)

**Resultado de ejecucion:**
```json
{
    "success": true,
    "message": "Migracion completada exitosamente",
    "results": [
        {"tabla": "curriculum", "status": "OK"},
        {"tabla": "historial_cambios_lider", "status": "OK"},
        {"tabla": "historial_estados", "status": "OK"},
        {"indice": "idx_jerarquia", "status": "Ya existe"},
        {"indice": "idx_territorio_campana", "status": "Ya existe"},
        {"vista": "v_colaboradores_red", "status": "OK"},
        {"vista": "v_estadisticas_campana", "status": "OK"}
    ]
}
```

---

### Fase 2: Extension de la API REST

**Archivo modificado:** `api/colaboradores.php`

Se agregaron 15 nuevos endpoints al archivo existente (~400 lineas nuevas).

#### Endpoints Existentes (ya estaban)

| Metodo | Endpoint | Descripcion |
|--------|----------|-------------|
| GET | `?campana_id=X` | Listar colaboradores |
| GET | `?id=X` | Obtener colaborador |
| GET | `?action=lideres` | Buscar lideres |
| GET | `?action=stats` | Estadisticas basicas |
| POST | (body JSON) | Crear colaborador |
| PUT | (body JSON) | Actualizar colaborador |
| DELETE | `?id=X` | Eliminar colaborador |
| POST | `?action=import` | Importar CSV |

#### Endpoints Nuevos (agregados)

| Metodo | Endpoint | Descripcion |
|--------|----------|-------------|
| GET | `?action=network&campana_id=X` | Red jerarquica completa (nodos + aristas para Vis.js) |
| GET | `?action=network&id=X` | Red centrada en un colaborador |
| GET | `?action=seguidores&id=X` | Seguidores directos |
| GET | `?action=historial&id=X` | Timeline de cambios (lider + estados) |
| GET | `?action=curriculum&id=X` | Obtener curriculum |
| POST | `?action=curriculum` | Guardar/actualizar curriculum |
| POST | `?action=cambiar_lider` | Cambiar lider con auditoria |
| POST | `?action=reevaluar` | Reevaluar dato_potencial con historial |
| GET | `?action=reportes&tipo=general` | Reporte general |
| GET | `?action=reportes&tipo=perfil` | Distribucion por perfil |
| GET | `?action=reportes&tipo=territorio` | Distribucion por territorio |
| GET | `?action=reportes&tipo=lideres` | Top lideres por seguidores |
| GET | `?action=reportes&tipo=crecimiento` | Colaboradores con mejor crecimiento |
| GET | `?action=export&formato=csv` | Exportar a CSV con BOM UTF-8 |
| GET | `?action=plantilla` | Descargar plantilla CSV para importacion |
| GET | `?action=stats_extended` | Estadisticas usando vista SQL |

#### Detalle de Request/Response

**POST cambiar_lider:**
```json
// Request
{
    "colaborador_id": 1,
    "nuevo_lider": "12345678",
    "motivo": "Reorganizacion territorial"
}

// Response
{
    "success": true,
    "message": "Lider actualizado exitosamente",
    "data": {
        "lider_anterior": "87654321",
        "lider_nuevo": "12345678"
    }
}
```

**POST reevaluar:**
```json
// Request
{
    "colaborador_id": 1,
    "dato_potencial": 75,
    "motivo": "Evaluacion trimestral"
}

// Response
{
    "success": true,
    "data": {
        "dato_potencial_anterior": 50,
        "dato_potencial_nuevo": 75,
        "dato_historico": 50,
        "estado_anterior": "Igual",
        "estado_nuevo": "Crecio"
    }
}
```

**GET network (para Vis.js):**
```json
{
    "success": true,
    "data": {
        "nodes": [
            {
                "id": "12345678",
                "label": "Juan P.",
                "title": "Juan Perez\nLider Comunitario\n15 seguidores",
                "color": "#FF00FF",
                "size": 30,
                "perfil": "Lider Comunitario",
                "seguidores": 15,
                "colaborador_id": 1
            }
        ],
        "edges": [
            {"from": "12345678", "to": "87654321", "arrows": "to"}
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

---

### Fase 3: Red Jerarquica (Vis.js)

**Archivo creado:** `pages/colaboradores_red.php`

Pagina completa de visualizacion de red con:

- **Grafo interactivo** usando Vis.js Network (CDN: `vis-network@9.1.6`)
- **Nodos** coloreados por perfil, con tamano proporcional a seguidores
- **Aristas** direccionales (lider -> seguidor)
- **Panel lateral** con:
  - Estadisticas de la red (nodos, conexiones, lideres, sin lider)
  - Leyenda de colores por perfil
  - Filtros (por perfil, minimo de seguidores)
  - Informacion del nodo seleccionado
- **Interacciones**:
  - Click: seleccionar nodo y ver detalles
  - Doble click: navegar al detalle del colaborador
  - Toggle fisica ON/OFF
  - Ajustar vista (fit)

**Colores por perfil:**
| Perfil | Color |
|--------|-------|
| Lider Comunitario | #FF00FF (Magenta) |
| Lider Social | #3B82F6 (Azul) |
| Lider Gremial | #10B981 (Verde) |
| Activista | #F59E0B (Naranja) |
| Simpatizante | #6B7280 (Gris) |

---

### Fase 4: Pagina Detalle Colaborador

**Archivo creado:** `pages/colaborador_detalle.php`

Pagina con 4 tabs usando Alpine.js:

1. **Informacion**: Datos personales, perfil politico, ubicacion, lider directo, barra de progreso
2. **Curriculum**: Experiencia laboral, formacion academica, participacion politica (CRUD dinamico)
3. **Seguidores**: Lista de seguidores directos con link a su detalle
4. **Historial**: Timeline de cambios (lider y estados) con colores por tipo

**Modales incluidos:**
- Cambiar lider (busqueda + motivo)
- Reevaluar dato_potencial (nuevo valor + motivo)
- Agregar item de curriculum (formulario dinamico segun tipo)

---

### Fase 5: Dashboard de Reportes

**Archivo creado:** `pages/colaboradores_reportes.php`

Dashboard con:

- **5 KPI cards**: Total, Nuevos, Crecieron, Igual, Decrecen
- **2 alertas**: Sin lider asignado, Lideres sin seguidores
- **Grafico Doughnut** (Chart.js): Distribucion por perfil
- **Grafico Bar** (Chart.js): Distribucion por estado
- **Top 10 Lideres**: Ranking con barras de progreso
- **Top Municipios**: Distribucion territorial
- **Boton Exportar CSV**: Descarga directa

---

### Fase 6: Actualizacion Pagina Principal

**Archivo modificado:** `pages/colaboradores.php`

Botones agregados al header:
- **Ver Red** (purpura) -> `?page=colaboradores_red`
- **Reportes** (amber) -> `?page=colaboradores_reportes`
- **Exportar** (verde) -> Descarga CSV directa
- Importar y Nuevo (ya existian)

---

### Fase 7: Actualizacion index.php

**Archivo modificado:** `index.php`

Cambios:
- Se guarda `$_SESSION['campana_nombre']` al seleccionar campana
- Se agrega array `$paginasPermitidas` con las nuevas paginas:
  - `colaboradores_red`
  - `colaborador_detalle`
  - `colaboradores_reportes`

---

### Fase 8: Migracion de Datos (en proceso)

**Archivo creado:** `migrate_colaboradores.php`

Script para migrar datos del sistema externo (`u156469157_aratio`) al sistema principal (`u156469157_aratio_v1`).

**Modo diagnostico:**
```
https://aratio.mrmtech.net/migrate_colaboradores.php
```
Muestra: todas las tablas, conteo por tabla, columnas, muestra de datos, campanas destino.

**Modo ejecucion:**
```
https://aratio.mrmtech.net/migrate_colaboradores.php?ejecutar=1&campana_id=X&tabla=NOMBRE
```

**Mapeo flexible de campos:** El script detecta automaticamente variaciones de nombres de columnas (ej: `documento`/`numero_documento`/`cedula`, `email`/`correo`, `telefono`/`celular`, etc.)

---

## 4. Archivos del Proyecto

### Estructura de Archivos Modificados/Creados

```
public_html/
├── index.php                          [MODIFICADO] +campana_nombre, +paginas permitidas
├── run_migration.php                  [CREADO] Script migracion DB (eliminar despues)
├── migrate_colaboradores.php          [CREADO] Script migracion datos (eliminar despues)
├── check_colaboradores.php            [CREADO] Diagnostico (eliminar despues)
├── check_all_databases.php            [CREADO] Diagnostico DBs (eliminar despues)
│
├── api/
│   └── colaboradores.php              [MODIFICADO] +15 endpoints nuevos
│
├── pages/
│   ├── colaboradores.php              [MODIFICADO] +botones Red, Reportes, Exportar
│   ├── colaboradores_red.php          [CREADO] Vis.js red jerarquica
│   ├── colaborador_detalle.php        [CREADO] Detalle con 4 tabs
│   └── colaboradores_reportes.php     [CREADO] Dashboard reportes Chart.js
│
├── database/
│   └── migrations/
│       └── 20260125_colaboradores_completo.sql  [CREADO] SQL migracion
│
└── DOCUMENTACION_INTEGRACION_COLABORADORES.md   [CREADO] Este archivo
```

### Archivos a Eliminar Post-Deployment

```
run_migration.php              # Ya ejecutado
migrate_colaboradores.php      # Despues de migrar datos
check_colaboradores.php        # Diagnostico temporal
check_all_databases.php        # Diagnostico temporal
```

---

## 5. Proceso de Deployment Ejecutado

### Cronologia

```
1. Crear migracion SQL (local)
   -> database/migrations/20260125_colaboradores_completo.sql

2. Crear script de migracion PHP (local)
   -> run_migration.php

3. Subir via curl SFTP:
   curl -k -T archivo "sftp://u156469157:PASS@212.1.208.241:65002/home/u156469157/domains/aratio.mrmtech.net/public_html/RUTA"

   Archivos subidos:
   - api/colaboradores.php        (51,828 bytes)
   - pages/colaboradores.php      (42,552 bytes)
   - pages/colaboradores_red.php  (16,930 bytes)
   - pages/colaborador_detalle.php (51,075 bytes)
   - pages/colaboradores_reportes.php (16,644 bytes)
   - index.php                    (15,304 bytes)
   - run_migration.php            (8,222 bytes)

4. Ejecutar migracion via navegador:
   https://aratio.mrmtech.net/run_migration.php

   Primer intento: Error "Column not found: estado"
   -> Causa: campo 'estado' es calculado en PHP, no existe en DB
   -> Fix: Eliminar referencia a c.estado en vista, usar CASE en v_estadisticas

   Segundo intento: SUCCESS
   -> 3 tablas creadas
   -> 2 vistas creadas
   -> 2 indices ya existian (OK)

5. Verificar datos: 0 colaboradores en tabla
   -> Datos estan en DB externa (u156469157_aratio)
   -> Crear script de migracion de datos (migrate_colaboradores.php)
   -> En proceso de diagnostico
```

### Errores Encontrados y Resueltos

| Error | Causa | Solucion |
|-------|-------|----------|
| FTP login 530 | Password incorrecta para FTP | Usar curl SFTP puerto 65002 |
| SSH Broken pipe | Password interactiva requerida | Usar curl SFTP en lugar de SSH |
| sshpass no disponible | No instalado en entorno | Usar curl SFTP |
| Column 'estado' not found | Campo calculado en PHP, no existe en DB | Eliminar de vista, usar CASE con dato_potencial/historico |
| idx_perfil_estado fallo | Columna estado no existe | Omitir indice |
| 0 colaboradores | Datos en otra DB (u156469157_aratio) | Script migrate_colaboradores.php |

---

## 6. Dependencias Externas (CDN)

| Libreria | Version | Uso |
|----------|---------|-----|
| Tailwind CSS | Play CDN | Estilos |
| Alpine.js | 3.x | Reactividad frontend |
| Chart.js | 4.4.0 | Graficos en reportes |
| Vis.js Network | 9.1.6 | Red jerarquica |
| Leaflet.js | 1.9.4 | Mapas (ya existia) |
| Lucide Icons | latest | Iconos |

---

## 7. Proximos Pasos

1. **Completar migracion de datos** desde DB externa
2. **Eliminar scripts temporales** (run_migration, migrate_colaboradores, check_*)
3. **Probar todas las funcionalidades** con datos reales
4. **Verificar permisos** por rol en cada endpoint

---

## 8. Notas Tecnicas

### Campo 'estado' (calculado)

El campo `estado` NO existe como columna en la tabla `colaboradores`. Se calcula en PHP cada vez que se consultan datos:

```php
function calcularEstado($dato_potencial, $dato_historico) {
    if ($dato_potencial == 0) return 'Nuevo';
    if ($dato_historico == 0) return 'Desvinculado';
    if ($dato_historico > $dato_potencial) return 'Crecio';
    if ($dato_historico < $dato_potencial) return 'Decrece';
    return 'Igual';
}
```

Para las vistas SQL se usa un CASE equivalente:
```sql
SUM(CASE WHEN dato_potencial = 0 THEN 1 ELSE 0 END) as nuevos,
SUM(CASE WHEN dato_historico > dato_potencial AND dato_potencial > 0 THEN 1 ELSE 0 END) as crecieron
```

### Campo 'grupo_etareo' (calculado)

Tambien calculado en PHP:
```php
function calcularGrupoEtareo($fecha_nacimiento) {
    $edad = (new DateTime())->diff(new DateTime($fecha_nacimiento))->y;
    if ($edad <= 17) return 'Joven (0-17)';
    if ($edad <= 28) return 'Juventud (18-28)';
    if ($edad <= 40) return 'Adulto Joven (29-40)';
    if ($edad <= 60) return 'Adulto (41-60)';
    return 'Mayor (61+)';
}
```

### Curriculum (almacenamiento JSON)

Se almacena en 3 campos JSON separados para flexibilidad:
- `experiencia_laboral`: Array de objetos {cargo, empresa, fecha_inicio, fecha_fin, descripcion}
- `formacion_academica`: Array de objetos {titulo, institucion, fecha_inicio, fecha_fin, nivel}
- `participacion_politica`: Array de objetos {cargo, organizacion, fecha_inicio, fecha_fin, descripcion}

### Seguridad

- Todas las consultas usan **prepared statements** (PDO)
- Inputs sanitizados con `htmlspecialchars()` via `sanitize()`
- Autenticacion requerida en todos los endpoints (`requireAuth()`)
- Filtrado por campana_id en todas las consultas
- Scripts de migracion protegidos con verificacion de rol super-admin

---

**Documento generado:** 2026-01-27
**Autor:** Claude Code (Anthropic)
