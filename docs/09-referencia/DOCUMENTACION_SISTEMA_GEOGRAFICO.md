# 🗺️ Documentación del Sistema Geográfico - Aratio

**Fecha**: 14 de Febrero de 2026
**Versión**: 1.2.0
**Módulo**: Territorios y Mapas Cooperativos

## 1. Descripción General
El Sistema Geográfico de Aratio permite la gestión, visualización y análisis de la infraestructura política mediante el uso de coordenadas y polígonos. Integra la jerarquía territorial (Comunas/Barrios/Veredas) con la ubicación exacta de los Puestos de Votación sobre un mapa interactivo.

---

## 2. Flujo de Datos (Pipeline)

### 2.1 Procesamiento de GeoJSON (`TEERITORIOS/GENERADOR_SQL.py`)
Este script de Python transforma archivos GeoJSON de fuentes oficiales a sentencias SQL compatibles con MySQL Spatial.

- **Entradas**: 
    - `BARRIOS-C-Y-P-B-J.geojson` (Zonas Urbanas)
    - `VEREDAS-C-Y-P-B-J.geojson` (Zonas Rurales)
- **Transformaciones**:
    - Generación de **IDs numéricos consistentes** (basados en códigos DANE).
    - Conversión de coordenadas a formato **WKT (Well-Known Text)**.
    - Normalización de nombres de municipios.
    - Agrupación por Comunas (formato "Comuna X").
- **Salida**: `territorios_con_poligonos.sql` con 1,320 registros.

### 2.2 Base de Datos
Se utiliza el motor **InnoDB** con soporte para tipos de datos espaciales.

**Tabla `territorios`**:
- `id` (bigint): Identificador único numérico.
- `departamento`, `municipio`, `Tipo_territorio`, `Territorio`, `barrio`: Metadatos jerárquicos.
- `geometria` (GEOMETRY NOT NULL): Almacena polígonos (SRID 4326).
- `SPATIAL INDEX (geometria)`: Para consultas de alto rendimiento.

---

## 3. Capa de Servicios (APIs)

### 3.1 API de Geometría (`api_territorios_geojson.php`)
Entrega los polígonos en formato estándar **GeoJSON** para ser consumidos por el mapa.

- **Endpoint**: `https://aratio.mrmtech.net/api_territorios_geojson.php`
- **Parámetros**:
  - `municipio` (Requerido): Ej. "Cali"
  - `tipo` (Opcional): "Urbano" o "Rural"
  - `territorio` (Opcional): Ej. "Comuna 1"
- **Respuesta**: Una `FeatureCollection` de GeoJSON lista para Leaflet.

### 3.2 API de Jerarquía (`api_territorios_verified.php`)
Gestiona los desplegables en cascada (Municipio -> Tipo -> Sector -> Barrio).

### 3.3 API de Puestos (`api_puestos_standalone.php`)
Entrega las coordenadas (lat/long) de los puestos de votación filtrados por municipio.

---

## 4. Visor Territorial (`mapa_territorios.php`)

Interfaz interactiva construida con **Leaflet.js** y **CARTO Dark Matter**.

### Características:
1.  **Exploración en Cascada**: Filtros encadenados que actualizan el mapa en tiempo real.
2.  **Multicapa**:
    - **Capa Territorial**: Polígonos resaltables (Rosa = Urbano, Dorado = Rural).
    - **Capa Electoral**: Marcadores de puestos de votación con información detallada.
3.  **Interacciones**:
    - **Auto-zoom**: El mapa se ajusta automáticamente al área seleccionada.
    - **Hover Info**: Tarjeta con detalles técnicos del sector al pasar el cursor.
    - **FitBounds**: Al seleccionar un barrio específico, el mapa navega directamente a él.

---

## 5. Mantenimiento y Despliegue

### Actualización de Polígonos
1. Colocar nuevos GeoJSON en la carpeta `TEERITORIOS/`.
2. Ejecutar `python GENERADOR_SQL.py`.
3. Importar el SQL resultante mediante el script `import_territorios_final.php` en el servidor.

### Solución de Problemas Comunes
- **Error "setZIndex of null"**: Asegurarse de que `geojsonLayer` esté inicializado como `L.layerGroup()` antes de añadirlo al control de capas.
- **Geometria inválida**: MySQL requiere polígonos cerrados (primer y último punto idénticos). El generador ya maneja este saneamiento.

---
*Documento generado automáticamente por el sistema de gestión de cambios de Aratio.*
