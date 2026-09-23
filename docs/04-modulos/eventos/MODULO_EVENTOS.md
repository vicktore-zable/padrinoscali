# Módulo Eventos (mod_eventos)

*Versión: 2.20.0 | Última actualización: 2026-09-23*

---

## Descripción

Sistema de gestión de eventos con registro de asistencia vía QR, firma digital,
autocompletado por documento, exportación Excel/PDF y reportes consolidados con Chart.js.

---

## Arquitectura

```
mod_eventos/
├── index.php                 ← Entry point, sidebar, routing de vistas
├── pages/
│   ├── eventos.php           ← CRUD eventos + modales QR/asistencia/mapa
│   ├── asistencia.php        ↑ Planilla de asistencia por evento (dedicada)
│   ├── qr_registro.php       ← Formulario público de registro (sin auth)
│   └── reportes.php          ← Estadísticas + Chart.js consolidado
└── api/
    ├── eventos.php           ← CRUD JSON eventos
    ├── asistencia.php        ← CRUD JSON asistencias (GET/POST/PUT/DELETE)
    ├── exportar_asistencia.php ← Export XLSX + PDF imprimible
    └── reportes.php          ← Endpoint datos consolidados (barrio/municipio/tipo)
```

---

## Flujo de Registro

```
1. Admin crea evento → POST /api/eventos.php
2. Admin genera QR → QRCode.js con URL → qr_registro.php?evento=X
3. Asistente escanea QR → llena formulario + firma digital canvas
4. Submit → POST /api/asistencia.php → INSERT firma_digital (base64)
5. Admin ve asistentes → GET /api/asistencia.php?evento_id=X → firma_digital en columna
6. Admin exporta → GET /api/exportar_asistencia.php?evento_id=X&formato=xlsx|pdf
```

---

## Bases de Datos

### Tabla: `eventos`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT PK | Auto-increment |
| `campana_id` | INT FK | Campaña a la que pertenece |
| `nombre` | VARCHAR(255) | Nombre del evento |
| `tipo` | ENUM | recorrido, reunion, debate, asamblea, mitin, etc. |
| `fecha_inicio` | DATETIME | Inicio del evento |
| `fecha_fin` | DATETIME | Fin del evento |
| `ubicacion` | VARCHAR(500) | Nombre del lugar |
| `latitud` / `longitud` | DECIMAL | Coordenadas geográficas |
| `asistentes_esperados` / `asistentes_confirmados` | INT | Conteo de asistentes |
| `estado` | ENUM | programado, en-curso, finalizado, cancelado |

### Tabla: `asistencia_eventos`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT PK | Auto-increment |
| `evento_id` | INT FK | Evento al que asiste |
| `nombre` | VARCHAR(255) | Nombre completo |
| `documento` / `tipo_documento` | VARCHAR + ENUM | Identificación |
| `telefono` / `email` | VARCHAR | Contacto |
| `genero` | ENUM | masculino, femenino, otro |
| `grupo_etareo` | VARCHAR | Calculado por trigger |
| `departamento` / `municipio` / `barrio` | VARCHAR | Ubicación |
| `firma_digital` | LONGTEXT | Base64 PNG de la firma |
| `habeas_data` | BOOLEAN | Aceptación Ley 1581 |
| `acepta_comunicaciones` | BOOLEAN | Acepta recibir información |
| `autorizacion_imagenes` | BOOLEAN | Autoriza uso de imagen |
| `metodo_registro` | ENUM | qr, manual, web |
| `asistio` | BOOLEAN | Confirmó asistencia |

---

## Endpoints API

### `GET /api/asistencia.php?evento_id=X`
Retorna lista de asistentes + stats (total, género, edad, método).

### `POST /api/asistencia.php`
Registra un asistente. Body JSON con `nombre`, `documento`, `genero`, `departamento`, `municipio`, `firma` (base64), `habeas_data`, `acepta_comunicaciones`, `autorizacion_imagenes`.

### `GET /api/exportar_asistencia.php?evento_id=X&formato=xlsx|pdf`
- `formato=xlsx`: Descarga Excel real con `SimpleXLSXGen` (logo Padrinos, datos evento, tabla asistentes, firmas)
- `formato=pdf`: HTML optimizado para imprimir/guardar como PDF con logo, QR, tabla y firmas
- `&asistente_id=Y`: Exporta un solo asistente

### `GET /api/reportes.php?action=consolidado&campana_id=X`
Retorna JSON con `por_barrio`, `por_municipio`, `por_tipo`, `totales`.

---

## QR Registration Page

**URL pública:** `/aratio/mod_eventos/pages/qr_registro.php?evento=X`

Campos del formulario:
- Nombre *, Tipo Doc *, Documento *
- Teléfono, Email
- Género *
- Fecha de Nacimiento
- Departamento *, Municipio *
- **Firma Digital** (canvas con mouse/touch)
- **Habeas Data** * (check)
- **Acepta Comunicaciones** (check)
- **Autorización de Imagen** (check)

Validaciones:
- Todos los campos * son requeridos
- Firma no puede estar vacía (compara con canvas vacío)
- Documento único por evento
- Ventana de registro: 24h después de finalización del evento

---

## Exportaciones

### Excel (XLSX)
- Librería: `SimpleXLSXGen` (Shuchkin, incluida, sin Composer)
- Columnas: # · Nombre · Documento · Tipo Doc · Teléfono · Email · Género · Grupo Etario · Departamento · Municipio · Barrio · Habeas Data · Comunicaciones · Autorización Imagen · Método · Fecha Registro · Notas
- 17 columnas

### PDF (HTML imprimible)
- CSS `@media print` optimizado para A4
- Logo Padrinos (base64 inline)
- Datos del evento (nombre, tipo, fecha, ubicación, dirección, asistentes)
- Tabla con firmas visibles por cada asistente
- Botón "Imprimir / PDF" abre diálogo de impresión del navegador

---

## Reportes Consolidados

**Tab "Consolidado"** en `?page=mod_eventos&view=reportes`:

| Chart | Tipo | Descripción |
|-------|------|-------------|
| Top 10 Barrios | Barras horizontales | Asistentes agrupados por barrio |
| Asistentes por Municipio | Doughnut | Distribución por municipio |
| Eventos por Tipo | Pie | Torta por tipo de evento |
| Eventos vs Asistentes | Barras dobles | Comparativo por municipio |

KPIs adicionales: Barrios distintos, Municipios distintos, Eventos con asistencia, Total de asistencias.

---

## Autocompletado por Documento (v2.20.0)

Cuando un asistente recurrente (líder, colaborador, padrino) digita su documento
en el formulario QR y sale del campo (`blur` o `Enter`):

1. Se busca en `colaboradores` (misma campaña → global) y luego en `asistencia_eventos`
2. Si se encuentra → se autocompletan todos los campos del formulario
3. Los campos quedan **readonly/deshabilitados** (solo el documento es editable)
4. Aparece banner verde: *"¡Tus datos están en la plataforma! Bienvenido(a) [Nombre]. Solo firma para confirmar tu asistencia."*
5. El usuario solo firma en el canvas y envía

**Endpoint**: `GET /api/colaboradores.php?action=buscar_por_documento&documento=X&evento_id=Y`
- **Público** (sin autenticación)
- Retorna: `encontrado`, `fuente` (`colaborador`|`asistencia_previa`), `mensaje_bienvenida`, `data`

---

## Seguridad (v2.20.0)

| Medida | Endpoint | Detalle |
|--------|----------|---------|
| `requireAuth()` + `hasAccessToCampana` | GET/PUT/DELETE asistencia | Solo admin con acceso a la campaña |
| `requireAuth()` + `hasAccessToCampana` | exportar_asistencia, reportes | PII + firmas protegidas |
| CSRF (`X-CSRF-Token`) | PUT/DELETE asistencia | Token en `$_SESSION['csrf_token']` |
| Rate-limit (20/5min por IP) | POST asistencia (público) | Previene spam |
| Validación firma | POST asistencia | 512KB max, formato `data:image/*;base64,` |
| Validación fechas | POST/PUT eventos | `fecha_inicio < fecha_fin` |
| Sin fuga de errores | todos los endpoints | `$e->getMessage()` no se expone al cliente |

**Helper compartido**: `mod_eventos/api/_security.php` — funciones `eventos_*` reutilizables.

---

## Mejoras UX v2.19.0

1. **Menú ⋮ eliminado**: Las acciones QR, WhatsApp, Asistentes, Mapa y Dashboard
   se movieron al modal de Detalles del Evento.
2. **Firma visible**: Columna en tabla de asistentes con thumbnail.
   Clic → modal con firma tamaño completo.
3. **3 checkboxes**: Habeas Data, Acepta Comunicaciones y Autorización de Imagen.
4. **Dos botones de exportación**: Excel (XLSX real) y PDF (HTML imprimible).
5. **Chart.js**: 4 gráficos interactivos en reportes consolidados.

---

## Archivos Modificados v2.20.0

| Archivo | Cambio |
|---------|--------|
| `api/colaboradores.php` | Action público `buscar_por_documento` |
| `mod_eventos/pages/qr_registro.php` | Listener documento + autollenado + banner bienvenida + readonly |
| `mod_eventos/api/asistencia.php` | Auth + CSRF + rate-limit + fix `notas` |
| `mod_eventos/api/_security.php` | **NUEVO** — Helpers compartidos de seguridad |
| `mod_eventos/api/reportes.php` | Auth + acceso campaña |
| `mod_eventos/api/exportar_asistencia.php` | Auth + acceso campaña |
| `mod_eventos/api/eventos.php` | Fix validación fechas + sin fuga errores |
| `scripts/deploy_autocomplete_qr.py` | **NUEVO** — Deploy script SFTP |

## Archivos Modificados v2.19.0

| Archivo | Cambio |
|---------|--------|
| `mod_eventos/index.php` | Fix `$campanaId` en vistas asistencia + reportes |
| `mod_eventos/pages/eventos.php` | Eliminado ⋮ menu. Acciones en modalDetalle. Columna firma en tabla. Detail modal con firma + autorización. Export Excel/PDF funcional |
| `mod_eventos/pages/qr_registro.php` | Canvas firma digital + 2 nuevos checkboxes + logo Padrinos |
| `mod_eventos/pages/reportes.php` | Reescribir con tabs General/Consolidado + Chart.js |
| `mod_eventos/api/asistencia.php` | GET retorna `firma_digital` + `autorizacion_imagenes`. POST acepta `autorizacion_imagenes` |
| `mod_eventos/api/exportar_asistencia.php` | **NUEVO** — Export XLSX con `SimpleXLSXGen` + PDF |
| `mod_eventos/api/reportes.php` | **NUEVO** — Endpoint consolidado barrio/municipio/tipo |
| `database/migration_033_eventos_v2.sql` | **NUEVO** — `ALTER TABLE ADD COLUMN autorizacion_imagenes` |

---

## Librerías Externas

| Librería | CDN | Uso |
|----------|-----|-----|
| Alpine.js | `cdn.jsdelivr.net/npm/alpinejs@3` | Reactividad frontend |
| Tailwind CSS | `cdn.tailwindcss.com` | Estilos utilitarios |
| Lucide | `unpkg.com/lucide@latest` | Iconos SVG |
| Leaflet | `unpkg.com/leaflet@1.9.4` | Mapas interactivos |
| QRCode.js | `cdn.jsdelivr.net/npm/qrcodejs` | Generación QR |
| Chart.js | `cdn.jsdelivr.net/npm/chart.js@4.4.1` | Gráficos reportes |
| SimpleXLSXGen | Incluido en `includes/` | Export Excel nativo |
