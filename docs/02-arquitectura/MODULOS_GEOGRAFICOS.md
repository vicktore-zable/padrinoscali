# Módulos Geográficos y Estrategia de Expansión

> **Versión:** 1.0 | **Fecha:** 2026-07-08 | **Proyecto:** Padrinos Cali — Aratio

---

## 1. Estructura de Datos Geográficos Actual

### 1.1 Tabla `territorios`

| Columna | Tipo | Ejemplo |
|---|---|---|
| `id` | BIGINT PK | 76001002 (código DANE) |
| `departamento` | VARCHAR(255) | VALLE DEL CAUCA |
| `municipio` | VARCHAR(255) | Cali |
| `Tipo_territorio` | VARCHAR(255) | Comuna, Rural, Corregimiento |
| `cod_mpio` | VARCHAR(255) | 76001 |
| `Codigo` | VARCHAR(255) | 76001002 |
| `Territorio` | VARCHAR(255) | Comuna 1, Corregimiento La Buitrera |
| `barrio` | VARCHAR(255) | Barrio El Calvario |
| `geometria` | GEOMETRY (MULTIPOLYGON) | GeoJSON de polígono |

### 1.2 Índices Espaciales

- `SPATIAL INDEX` en `geometria` para consultas geográficas rápidas

### 1.3 Datos Actuales

- **Cobertura:** Principalmente Cali (código DANE 76001)
- **Archivos fuente:** `Aratio-App-Mirror/TERRITORIOS/` (~19 archivos entre SQL y GeoJSON)
  - `territorios_21022026_completa.sql` — Schema completo de territorios
  - `territorios_con_poligonos.sql` — Territorios con geometría
  - `territorios_valle_add.sql` — Municipios adicionales del Valle
  - `mpio_list.json` — Lista de municipios
  - `veredas_batch_2026.sql` — Veredas rurales

---

## 2. Uso de Geografía en el Sistema

### 2.1 Vistas con Componentes Geográficos

| Vista | Componente Geográfico | Funcionalidad |
|---|---|---|
| `pages/colaboradores.php` | Selectores en cascada | Depto → Municipio → Tipo → Territorio → Barrio |
| `pages/colaborador_detalle.php` | Selectores en cascada + mapa | Edición + location del colaborador |
| `pages/reportes.php` | Mapa Leaflet + filtros | Distribución geográfica con 4 KPIs |
| `pages/dashboard_territorial.php` | Mapa Leaflet + gráficos Chart.js | 16 KPIs territoriales |
| `pages/eventos.php` | Selector de ubicación | Eventos por territorio |
| `pages/portal_registrar_simpatizante.php` | Selectores en cascada | Captura de ubicación |
| `pages/dashboard_territorial_social.php` | Integración ALAS + mapa | CRM social + territorio |

### 2.2 APIs Geográficas

| API | Endpoint | Función |
|---|---|---|
| `api/territorios.php` | GET/POST/PUT/DELETE | CRUD de territorios |
| `api/territorios_simple.php` | GET | Lista plana de territorios |
| `api/territorios_standalone.php` | GET | Territorios standalone para mod_colab |
| `api_territorios_geojson.php` | GET | GeoJSON para mapa Leaflet |
| `api/reporte_geo_colaboradores.php` | GET | Conteos por territorio |
| `api/colaboradores.php` | GET con filtro `municipio`/`barrio` | Colaboradores por ubicación |

---

## 3. Estrategia de Expansión a Municipios del Valle del Cauca

### 3.1 Fuentes de Datos Recomendadas

| Fuente | Tipo | URL / Acceso |
|---|---|---|
| **DANE — Divipola** | Estructura oficial | https://geoportal.dane.gov.co/ |
| **DANE — MGN (Marco Geoestadístico)** | Shapefiles/GeoJSON | https://www.dane.gov.co/index.php/servicios-geoestadisticos |
| **OpenStreetMap** | GeoJSON de límites | https://www.openstreetmap.org/ |
| **Catastro Multipropósito — IGAC** | Shapefiles prediales | https://www.igac.gov.co/ |
| **Misión de Observación Electoral (MOE)** | Datos electorales | https://moe.org.co/ |

### 3.2 Municipios Prioritarios del Valle del Cauca

| Municipio | Código DANE | Población Aprox. | Prioridad |
|---|---|---|---|
| Cali | 76001 | 2.2M | ✅ Actual |
| Palmira | 76520 | 350K | 🔴 Alta |
| Tuluá | 76834 | 220K | 🔴 Alta |
| Buenaventura | 76109 | 400K | 🔴 Alta |
| Buga | 76111 | 120K | 🟡 Media |
| Cartago | 76147 | 135K | 🟡 Media |
| Jamundí | 76364 | 120K | 🟡 Media |
| Yumbo | 76892 | 100K | 🟡 Media |
| Candelaria | 76130 | 85K | 🟢 Baja |
| Florida | 76275 | 55K | 🟢 Baja |

### 3.3 Plan de Ingesta de Datos

**Fase 1 — Datos Básicos (1-2 días):**
1. Obtener códigos DANE de municipios del Valle del Cauca
2. Insertar registros en tabla `territorios` con `Tipo_territorio='Municipio'` y `departamento='VALLE DEL CAUCA'`
3. Agregar `cod_mpio` correcto para cada municipio

**Fase 2 — Datos Territoriales (3-5 días por municipio):**
1. Para cada municipio, obtener sus divisiones: comunas (urbanas) y corregimientos/veredas (rurales)
2. Importar usando script ETL externo (Python) que genere INSERTs
3. Asociar barrios a cada territorio

**Fase 3 — Polígonos GeoJSON (1-2 días por municipio):**
1. Obtener polígonos de DANE MGN o OSM
2. Convertir a MULTIPOLYGON (formato MySQL)
3. Insertar en columna `geometria` usando `ST_GeomFromGeoJSON()`

### 3.4 Script ETL Externo Sugerido

```bash
# Estructura del script Python (fuera del esquema MySQL)
python scripts/etl/import_municipio_valle.py \
  --cod-mpio 76520 \
  --nombre "Palmira" \
  --shapefile "data/palmira.geojson"
```

**Salida del script**: Archivo SQL con INSERTs para tabla `territorios`, ejecutable desde Hostinger.

---

## 4. Ajustes en Vistas para Nuevos Municipios

### 4.1 Selectores en Cascada

**Estado actual:** `departamento (fijo: VALLE DEL CAUCA)` → `municipio (solo: Cali)` → `tipo_territorio (Comuna/Rural)` → `territorio` → `barrio`

**Estado propuesto:**
```
departamento (fijo: VALLE DEL CAUCA)
  └── municipio (dropdown: Cali, Palmira, Tuluá, Buenaventura...)
       ├── tipo_territorio (Urbano/Rural/Corregimiento)
       │    ├── Urbano: Comuna 1, Comuna 2, ...
       │    ├── Rural (Cali): Corregimiento La Buitrera, Pance, ...
       │    └── Municipal (Palmira): Comuna 1, Comuna 2, ...
       │         └── barrio: ...
       └── puesto_votacion (filtrado por municipio)
```

### 4.2 Vistas a Modificar

| Vista | Cambio Necesario |
|---|---|
| `pages/colaboradores.php` | Selector de municipio dinámico (hoy estático) |
| `pages/colaborador_detalle.php` | Selector de municipio dinámico |
| `pages/reportes.php` | Mapa Leaflet cargando múltiples municipios |
| `pages/dashboard_territorial.php` | KPIs y gráficos por municipio seleccionado |
| `pages/eventos.php` | Filtros por todos los municipios disponibles |
| `api/colaboradores.php` | Filtro `municipio` aceptando cualquier valor, no solo Cali |

### 4.3 Módulo de Reporte por Municipio

Crear nueva vista `pages/reporte_municipal.php` con:

- **Selector de municipio** (dropdown con todos los disponibles)
- **KPIs generales**: Total colaboradores, líderes, eventos, donaciones
- **Mapa Leaflet** del municipio seleccionado (cargar GeoJSON por municipio)
- **Gráficos:** Distribución por comuna, perfil, género
- **Tabla de detalle** con colaboradores del municipio

---

## 5. Consideraciones Técnicas

| Aspecto | Consideración |
|---|---|
| **Volumen de datos** | ~300 territorios por municipio grande, ~50 por municipio pequeño |
| **Rendimiento** | Usar CACHE_TTL_TERRITORIOS=86400 (24h) para datos geoestáticos |
| **Geometrías** | No usar ST_Simplify (no existe en MySQL de Hostinger), enviar GeoJSON completo |
| **Colisiones de ID** | IDs de territorios son BIGINT autoincremental, no hay colisión |
| **Capa de abstracción** | La tabla `territorios` ya soporta múltiples municipios con `cod_mpio` |

---

## 6. Roadmap de Expansión Geográfica

| Sprint | Alcance | Entregables |
|---|---|---|
| **Sprint 5.1** | Ingesta de 3 municipios prioritarios (Palmira, Tuluá, Buenaventura) | SQL de territorios, selectores actualizados |
| **Sprint 5.2** | Mapa Leaflet multi-municipio + reporte municipal | Vistas modificadas, reporte por municipio |
| **Sprint 5.3** | Datos electorales por municipio (puestos + mesas) | API de puestos por municipio, integración en vista |
| **Sprint 5.4** | 5 municipios adicionales (Buga, Cartago, Jamundí, Yumbo, Candelaria) | Expansión de datos + validación de rendimiento |

---

*Documento de estrategia geográfica — 2026-07-08*