# Análisis de Lógica de Administración de Información

> **Versión:** 1.0 | **Fecha:** 2026-07-08 | **Proyecto:** Padrinos Cali — Aratio

---

## 1. Entidades Clave y su Administración Actual

### 1.1 Colaboradores (Padrinos)

| Aspecto | Estado Actual | Mejora Propuesta |
|---|---|---|
| **Creación** | Modal con formulario, selectores geográficos en cascada | Validación completa de campos (documento, teléfono, email) |
| **Edición** | Modal con preselección geográfica (fix v2.6.0) | Agregar historial de cambios visible |
| **Listado** | Tabla con búsqueda, paginación incluida (ITEMS_PER_PAGE=20) | Agregar filtros avanzados (perfil, estado, territorio, barrio, rango de fechas) |
| **Detalle** | Página dedicada con grid 4-columnas, foto | Agregar timeline de actividad, curriculum, red directa |
| **Jerarquía** | Columna `lider_directo` (self-ref por documento) | Visualización de árbol jerárquico (collapsable) |
| **Red** | `pages/colaboradores_red.php` — vista de red | Matriz de seguidores por líder con KPIs |

### 1.2 Campañas

| Aspecto | Estado Actual | Mejora Propuesta |
|---|---|---|
| **CRUD completo** | ✅ Sí (api/campanas.php + pages/campanas.php) | |
| **Asociación candidatos** | ✅ Sí | Auto-completar búsqueda de candidatos |
| **Selector en sidebar** | ✅ Sí (campaña activa en sesión) | |
| **Reportes por campaña** | ✅ Dashboard + vistas filtradas | |
| **Duplicar campaña** | ❌ No | Clonar configuración de campaña anterior |

### 1.3 Eventos

| Aspecto | Estado Actual | Mejora Propuesta |
|---|---|---|
| **CRUD** | ✅ API completa | |
| **Asistencia QR** | ✅ Registro con firma digital | Validación de datos duplicados (documento + evento) |
| **Dashboard** | `pages/dashboard_asistencia.php` | Integrar en vista de eventos |
| **Recordatorio automático** | 🟡 Workflow regla (ALAS) | Configurar cron + notificación WhatsApp |
| **Exportar asistencia** | ✅ `api/asistencia_export_excel.php` | |

### 1.4 Donaciones

| Aspecto | Estado Actual | Mejora Propuesta |
|---|---|---|
| **CRUD** | ✅ API (donaciones.php + pages/donaciones.php) | |
| **Reportes** | Dashboard suma total por campaña | Histórico por donante, top donantes |
| **Comprobante** | ✅ URL de archivo | Agregar link de visualización directa |
| **Validación montos** | ❌ No | Agregar validación frontend y backend |

### 1.5 Usuarios del Sistema

| Aspecto | Estado Actual | Mejora Propuesta |
|---|---|---|
| **CRUD** | ✅ `api/usuarios.php` + `pages/usuarios.php` | |
| **Roles** | `admin`, `superadmin` (hardcoded) | Roles configurables con permisos granulares |
| **Auth** | Email + password (bcrypt) | 2FA opcional, historial de login |
| **Session** | Cookie-based, 2h TTL | Recordar sesión (checkbox "mantener sesión") |

### 1.6 Candidatos y Elecciones

| Aspecto | Estado Actual | Mejora Propuesta |
|---|---|---|
| **Candidatos** | ✅ CRUD completo | Historial de candidaturas |
| **Grupos políticos** | ✅ CRUD completo | Biografía del partido, integrantes |
| **Elecciones** | ✅ CRUD completo | Historial de resultados electorales |
| **Consulta electoral** | ✅ `mod_elecciones/public/index.php` | Integrar en panel admin |

---

## 2. Funcionalidades Transversales a Mejorar

### 2.1 Filtros y Búsqueda

**Estado actual:** Búsqueda básica por texto en algunas tablas.

**Propuesta:** Sistema unificado de filtros con:
- Búsqueda por texto libre
- Filtros por rango de fechas (created_at, fecha_nacimiento)
- Filtros por estado, perfil, territorio, barrio
- Filtros combinados (AND lógico)
- Persistencia de filtros en URL (compartible)

### 2.2 Paginación

**Estado actual:** 20 items por página (constante `ITEMS_PER_PAGE`).

**Propuesta:**
- Selector de items por página (10, 20, 50, 100)
- Paginación con saltos (primera, última, numérica)
- Total de resultados visible
- Ordenamiento por columnas (clic en header)

### 2.3 Exportación de Datos

**Estado actual:** Solo exportación de asistencia a Excel.

**Propuesta:**
- Botón "Exportar CSV" en listados principales
- Exportación de datos filtrados (lo mismo que ve en pantalla)
- Formato: compatibilidad Excel UTF-8

### 2.4 Validación Unificada

**Estado actual:** Validación inconsistente entre páginas.

**Propuesta:**
- **Frontend**: Alpine.js con validación en tiempo real
  - Formato teléfono Colombia: `^3\d{9}$`
  - Formato documento: según tipo (CC=10d, CE=9d, TI=8d, NIT=9-15d)
  - Email: formato estándar
  - Fechas: inicio < fin
- **Backend**: PHP + PDO prepared statements (ya implementado)
  - Mayor uso de `filter_var()` y expresiones regulares
  - Mensajes de error específicos por campo

---

## 3. Propuesta de Componentes UI Reutilizables

### 3.1 Componente: `DataTable` (Alpine.js)

```
<x-datatable 
  :api="api/colaboradores.php"
  :columns="['nombre', 'documento', 'perfil', 'territorio', 'estado']"
  :filters="['perfil', 'estado', 'municipio']"
  :per-page="[10, 20, 50, 100]"
  :sortable="true"
  :exportable="true"
/>
```

**Funcionalidad:**
- Carga AJAX con JSON
- Filtros combinados
- Paginación
- Ordenamiento
- Exportación CSV
- Búsqueda en vivo

### 3.2 Componente: `GeoSelectores`

```
<geo-selectores 
  :departamento="VALLE DEL CAUCA"
  :municipio="model.municipio"
  :territorio="model.territorio" 
  :barrio="model.barrio"
/>
```

**Funcionalidad:**
- Carga en cascada (departamento → municipio → tipo_territorio → territorio → barrio)
- Preselección al editar
- Soporte para múltiples municipios
- Cacheo de listas en cliente (24h)

### 3.3 Componente: `ActivityTimeline`

```
<activity-timeline
  :colaborador-id="colaborador.id"
  :limit="50"
  :types="['evento', 'llamada', 'whatsapp', 'donacion']"
/>
```

**Funcionalidad:**
- Timeline scroll infinito
- Filtro por tipo de actividad
- Badge de actividad reciente
- Link directo al registro fuente

---

## 4. Jerarquía de Perfiles y Niveles de Acceso

### 4.1 Perfiles de Colaborador (actual)

| Perfil | Descripción |
|---|---|
| `Lider Comunitario` | Líder con red de seguidores |
| `Lider / Coordinador` | Líder con responsabilidad territorial |
| `Simpatizante` | Colaborador base sin red |
| *(otros dinámicos)* | Configurable desde la UI |

### 4.2 Niveles de Colaborador (dato_potencial)

| Nivel | Rango | Color |
|---|---|---|
| Nuevo | 1-25 | Gris |
| Regular | 26-50 | Azul |
| Activo | 51-75 | Verde |
| Estrella | 76-100 | Dorado |

### 4.3 Roles de Usuario del Sistema (propuesta)

| Rol | Permisos | 
|---|---|
| `superadmin` | Todo el sistema, gestión de usuarios |
| `admin` | CRUD completo de todas las entidades |
| `operador` | CRUD de colaboradores, eventos, asistencia |
| `capturista` | Solo crear/editar colaboradores y registrar asistencia |
| `lector` | Solo visualización de reportes y dashboards |

---

*Documento de análisis de administración — 2026-07-08*