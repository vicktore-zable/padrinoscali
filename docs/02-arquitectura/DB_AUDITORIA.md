# Auditoría de Base de Datos — Sugerencias de Optimización

> **Versión:** 1.0 | **Fecha:** 2026-07-08 | **DB:** u577647812_aratio (Hostinger)

---

## 1. Estado Actual

- **Motor:** InnoDB, charset utf8mb4_unicode_ci
- **Tablas:** ~42
- **Vistas SQL:** 3 (v_colaboradores_red, v_colaboradores_completo, v_estadisticas_campana)
- **Triggers:** 2 (cálculo de grupo etario en asistencia_eventos)
- **Índices existentes:** Buenos en tablas core (colaboradores tiene 7 índices compuestos)

---

## 2. Tablas Sin Migración Documentada

Estas tablas existen en producción pero no tienen archivo `CREATE TABLE` en `database/migrations/`:

| Tabla | Migración Recomendada |
|---|---|
| `usuarios` | ✅ Existe también en `mod_colab/database/schema.sql` |
| `donaciones` | ❌ No existe CREATE en ningún migration |
| `sesiones_lideres` | ❌ Solo en `mod_lider/migrations/001_sesiones_lideres.sql` (creación única) |
| `colaboradores` | ✅ Migración en `20260125_colaboradores_completo.sql` |

**Acción sugerida:** Documentar migraciones faltantes en un archivo `database/migrations/faltantes.sql` (solo como referencia, sin ejecutar).

---

## 3. Optimización de Consultas en Código PHP

### 3.1 Consultas sin Índice Aprovechable

| Ubicación | Consulta | Problema | Sugerencia |
|---|---|---|---|
| `api/colaboradores.php` | `SELECT * FROM colaboradores WHERE barrio IN (...)` | Filtro por barrio sin índice específico | Crear índice `idx_barrio_campana (campana_id, barrio)` |
| `pages/reportes.php` | `COUNT(*) FROM colaboradores GROUP BY territorio_id` | Agregación pesada en tabla grande | Usar vista materializada o caché |
| `pages/dashboard.php` | `SELECT COUNT(*) FROM eventos WHERE estado = 'activo'` | Múltiples COUNTs en cada carga | Cachear dashboard (constante CACHE_TTL_DASHBOARD=120s) |
| `api/eventos.php` | JOINs entre eventos y asistencia_eventos sin índice en `evento_id` | JOIN sin índice en FK | Verificar índice `idx_evento_id (evento_id)` en asistencia_eventos |

### 3.2 Mejoras en Consultas Existentes

**Problema:** Uso de `SELECT *` en consultas que solo necesitan columnas específicas.

```sql
-- ❌ Actual (api/colaboradores.php, línea ~150):
SELECT * FROM colaboradores WHERE campana_id = ?

-- ✅ Propuesta:
SELECT id, documento, nombres, apellidos, perfil, telefono, email, 
       departamento, municipio, territorio, barrio, estado
FROM colaboradores WHERE campana_id = ?
```

**Problema**: Consultas sin paginación en reportes agregados.

```sql
-- ❌ Actual (api/reporte_geo_colaboradores.php):
SELECT territorio_id, COUNT(*) as total FROM colaboradores

-- ✅ Propuesta:
SELECT territorio_id, COUNT(*) as total FROM colaboradores 
WHERE campana_id = ? GROUP BY territorio_id
```

### 3.3 Transacciones

| Archivo | Situación | Sugerencia |
|---|---|---|
| `api/colaboradores.php` (PUT/creación) | No usa transacciones | Envolver INSERT/UPDATE en `$db->beginTransaction()` |
| `api/eventos.php` (crear evento + trigger) | INSERT simple + trigger automático | Verificar que trigger no falle ante rollback |
| `api/whatsapp.php` (envío masivo) | Inserts secuenciales en log | Usar transacción por lote de 100 registros |

---

## 4. Sugerencias de Nuevos Índices (Solo Documentación)

La siguiente tabla lista índices sugeridos. **NO crear automáticamente** — revisar primero el plan de ejecución de cada consulta.

| Tabla | Columna(s) | Tipo | Justificación |
|---|---|---|---|
| `colaboradores` | `(campana_id, barrio)` | BTREE | Filtro por barrio en API de colaboradores |
| `colaboradores` | `(campana_id, fecha_nacimiento)` | BTREE | Filtro de cumpleaños (cron + vista) |
| `colaboradores` | `(campana_id, created_at)` | BTREE | Ordenamiento por fecha de registro |
| `asistencia_eventos` | `(evento_id)` | BTREE | JOIN con eventos en reportes |
| `asistencia_eventos` | `(evento_id, asistio)` | BTREE | Conteo de asistencia por evento |
| `eventos` | `(campana_id, estado)` | BTREE | Filtro frecuente en dashboard |
| `eventos` | `(fecha_inicio, estado)` | BTREE | Eventos próximos/activos |
| `whatsapp_log` | `(colaborador_id)` | BTREE | Historial por colaborador |
| `historial_estados` | `(colaborador_id, created_at)` | BTREE | Timeline de cambios |
| `cp_capture_flow` | `(created_at)` | BTREE | Reportes temporales de captura |

---

## 5. Optimización de la Tabla `colaboradores`

### 5.1 Índices Actuales (7 existentes)

| Índice | Columnas | Propósito |
|---|---|---|
| PRIMARY | `id` | PK |
| `idx_jerarquia` | `campana_id, lider_directo, documento` | Búsqueda jerárquica |
| `idx_territorio_campana` | `campana_id, departamento, municipio` | Filtro geográfico |
| `idx_perfil_estado` | `campana_id, perfil, estado` | Filtro por perfil |
| `idx_puesto_mesa` | `campana_id, puesto_votacion, mesa_votacion` | Búsqueda por puesto |
| `idx_facebook_id` | `facebook_id` | Social CRM match |
| `idx_instagram` | `instagram_username` | Social CRM match |

### 5.2 Columnas con alta cardinalidad para `WHERE`

| Columna | Uso Frecuente | ¿Tiene Índice? |
|---|---|---|
| `campana_id` | ✅ Sí (incluida en múltiples compuestos) |
| `lider_directo` | ✅ Sí (idx_jerarquia) |
| `documento` | ✅ Sí (idx_jerarquia) |
| `telefono` | ❌ No (búsqueda en portal líder/registro) |
| `email` | ❌ No (búsqueda ocasional) |
| `estado` | ✅ Sí (idx_perfil_estado) |
| `perfil` | ✅ Sí (idx_perfil_estado) |
| `barrio` | ❌ No (filtro frecuente en reportes geográficos) |

---

## 6. Validaciones de Datos Sugeridas

### 6.1 En PHP (Backend)

| Archivo | Campo | Validación Actual | Sugerencia |
|---|---|---|---|
| `api/colaboradores.php` | `email` | Solamente `!empty()` | Agregar `filter_var($email, FILTER_VALIDATE_EMAIL)` |
| `api/colaboradores.php` | `telefono` | Ninguna | Validar formato cel. Colombia: `/^3\d{9}$/` |
| `api/colaboradores.php` | `documento` | Ninguna | Validar longitud (CC=10d, CE=9d, TI=8d) |
| `api/eventos.php` | `fecha_inicio` > `fecha_fin` | Ninguna | Validar que inicio < fin |
| `api/donaciones.php` | `monto` | Ninguna | Validar > 0 y < 100 millones |

### 6.2 En JavaScript (Frontend)

| Vista | Campo | Validación Actual |
|---|---|---|
| `pages/colaboradores.php` (modal creación) | `telefono` | Solo `required` en HTML |
| `pages/eventos.php` | `fecha_inicio` / `fecha_fin` | Solo `type="date"` |
| `pages/portal_registrar_simpatizante.php` | `documento` | Solo `required` |

---

## 7. Bitácora de Cambios (Auditoría)

El sistema ya cuenta con tablas de auditoría:

| Tabla | Registra |
|---|---|
| `historial_estados` | Cambios en `dato_potencial`, `dato_historico`, `estado` del colaborador |
| `historial_cambios_lider` | Reasignaciones de líder |
| `actividad_colaborador` | Timeline unificado de actividad |
| `workflow_log` | Ejecuciones de reglas de workflow |

### 7.1 Mejora Sugerida

Agregar columna `usuario_id` a la tabla `actividad_colaborador` (ya existe en migración ALAS pero puede faltar en producción) para saber quién realizó cada acción.

---

## 8. Configuración de Entorno Actual

| Parámetro | Local (XAMPP) | Producción (Hostinger) |
|---|---|---|
| DB_HOST | 82.197.82.47 (IP directa) | localhost |
| DB_NAME | u577647812_aratio | u577647812_aratio |
| DB_USER | u577647812_aratio | u577647812_aratio |
| DB_PASS | v6xSHUWhjrxE | v6xSHUWhjrxE |
| DB_CHARSET | utf8mb4 | utf8mb4 |
| display_errors | 0 | 0 |
| SESSION_LIFETIME | 7200s | 7200s |
| CACHE | Filesystem (sí) | Filesystem (sí) |

---

*Documento de solo lectura — 2026-07-08*