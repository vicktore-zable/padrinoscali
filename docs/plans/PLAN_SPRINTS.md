# Plan por Sprints — Refactorización y Mejora del Sistema Aratio

> **Versión:** 1.0 | **Fecha:** 2026-07-08 | **Proyecto:** Padrinos Cali
> **Basado en:** Auditoría completa de arquitectura, DB, vistas, integraciones y módulos geográficos

---

## Resumen del Plan

```
Sprint 1: 🔍 Auditoría y Documentación  (3-4 días)
Sprint 2: 🖥️ Reconstrucción de Vistas    (5-7 días)
Sprint 3: ⚙️ Lógica Admin Avanzada       (5-7 días)
Sprint 4: 🔌 Integraciones              (3-5 días)
Sprint 5: 🗺️ Expansión Geográfica         (5-7 días)
```

---

## Sprint 1 — Auditoría y Documentación (Semana 1)

**Objetivo:** Tener todos los diagnósticos para tomar decisiones informadas.

### Tareas

| # | Tarea | Archivo/Componente | Prioridad | Esfuerzo |
|---|---|---|---|---|
| 1.1 | ✅ COMPLETADO — Mapa de arquitectura actual | `docs/02-arquitectura/ARQUITECTURA_ACTUAL.md` | 🔴 | Hecho |
| 1.2 | ✅ COMPLETADO — Listado de vistas faltantes/rotas | `docs/02-arquitectura/VISTAS_PENDIENTES.md` | 🔴 | Hecho |
| 1.3 | ✅ COMPLETADO — Auditoría DB con sugerencias | `docs/02-arquitectura/DB_AUDITORIA.md` | 🔴 | Hecho |
| 1.4 | ✅ COMPLETADO — Análisis módulos admin | `docs/02-arquitectura/MODULOS_ADMIN.md` | 🟡 | Hecho |
| 1.5 | ✅ COMPLETADO — Estado de integraciones | `docs/05-integraciones/ESTADO_INTEGRACIONES.md` | 🟡 | Hecho |
| 1.6 | ✅ COMPLETADO — Estrategia geográfica | `docs/02-arquitectura/MODULOS_GEOGRAFICOS.md` | 🟡 | Hecho |
| 1.7 | ✅ COMPLETADO — Plan por sprints (este doc) | `docs/plans/PLAN_SPRINTS.md` | 🔴 | Hecho |

### Criterios de Aceptación
- ✅ Todos los documentos generados y accesibles desde `docs/00-INDEX.md`
- ✅ Problemas críticos identificados y priorizados

---

## Sprint 2 — Reconstrucción y Mejora de Vistas (Semana 2)

**Objetivo:** Corregir errores críticos que causan fatal errors y mejorar la UX.

### Tareas Técnicas

| # | Tarea | Detalle | Archivo | Prioridad | Esfuerzo |
|---|---|---|---|---|---|
| 2.1 | **Fix fatal error: perfil_lider** | Remover del array `$portalPages` o crear alias → `portal_perfil` | `index.php` | 🔴 | 15 min |
| 2.2 | **Fix fatal error: dashboard_lider** | Remover del array o crear alias → `portal_dashboard` | `index.php` | 🔴 | 15 min |
| 2.3 | **Fix .htaccess rotos** | Eliminar reglas que apuntan a `mapa_territorios.php` inexistente | `.htaccess` | 🔴 | 10 min |
| 2.4 | **Deshabilitar display_errors en páginas públicas** | Cambiar `display_errors=1` a `0` | `registro-lider.php`, `registro_simpatizante.php` | 🟡 | 10 min |
| 2.5 | **Eliminar o archivar páginas de test/debug** | Mover `portal_test*`, `portal_debug*`, `portal_path.php` a `scratch/` | `pages/` (5-7 archivos) | 🟡 | 30 min |
| 2.6 | **Estandarizar session_name() en APIs** | Agregar `session_name(SESSION_NAME)` antes de `session_start()` | `api/*.php` (varios archivos) | 🟡 | 30 min |
| 2.7 | **Evaluar reportes_nuevo.php** | Decidir si reemplaza a `reportes.php` o se elimina | `pages/` | 🟡 | 1 hr |
| 2.8 | **Componentes UI: Selectores geográficos reutilizables** | Crear componente Alpine.js `geo-selectores` | Nuevo archivo en `includes/` o inline | 🟢 | 4 hrs |

### Mejoras de UX

| # | Tarea | Detalle | Prioridad |
|---|---|---|---|
| 2.9 | Barra de búsqueda global en sidebar | Buscar colaboradores, eventos, campañas desde cualquier página | 🟡 |
| 2.10 | Breadcrumbs en páginas de detalle | Navegación: Dashboard > Colaboradores > Detalle > Editar | 🟢 |
| 2.11 | Indicador de carga (loader) en Alpine.js | Spinner en todas las peticiones fetch | 🟢 |
| 2.12 | Tooltips en iconos de acción | Explicación de cada botón de acción en tablas | 🟢 |

### Criterios de Aceptación
- ✅ `?page=perfil_lider` redirige a portal_perfil (sin fatal error)
- ✅ `?page=dashboard_lider` redirige a portal_dashboard
- ✅ `.htaccess` no tiene reglas rotas
- ✅ No hay `display_errors=1` en páginas públicas
- ✅ Páginas de debug no accesibles desde producción

---

## Sprint 3 — Lógica Avanzada de Administración (Semanas 3-4)

**Objetivo:** Robustecer formularios, validaciones y experiencia de administración de datos.

### Tareas

| # | Tarea | Componente | Prioridad | Esfuerzo |
|---|---|---|---|---|
| 3.1 | **Validación backend: email** | `filter_var($email, FILTER_VALIDATE_EMAIL)` en creación/edición | 🔴 | 2 hrs |
| 3.2 | **Validación backend: teléfono Colombia** | Regex `/^3\d{9}$/` con validación de 10 dígitos | 🔴 | 2 hrs |
| 3.3 | **Validación backend: documento** | Longitud según tipo_documento (CC=10, CE=9, TI=8) | 🔴 | 2 hrs |
| 3.4 | **Validación backend: fechas evento** | fecha_inicio < fecha_fin | 🔴 | 1 hr |
| 3.5 | **Validación backend: monto donación** | > 0 y < 100,000,000 COP | 🟡 | 1 hr |
| 3.6 | **Transacciones en APIs críticas** | beginTransaction/commit en creación de colaboradores, eventos | 🟡 | 3 hrs |
| 3.7 | **Filtros avanzados en listados** | Filtros combinados en colaboradores (perfil + estado + territorio + fechas) | 🟡 | 6 hrs |
| 3.8 | **Selector de items por página** | 10/20/50/100 con persistencia | 🟢 | 4 hrs |
| 3.9 | **Ordenamiento por columna** | Click en header de tabla para ordenar ASC/DESC | 🟢 | 4 hrs |
| 3.10 | **Exportación CSV** | Botón exportar datos visibles (filtrados) a CSV UTF-8 | 🟢 | 4 hrs |
| 3.11 | **Agregar usuario_id a actividad_colaborador** | Migración SQL para columna faltante | 🟡 | 30 min |
| 3.12 | **Bitácora visible** | Pestaña "Historial" en detalle de colaborador | 🟢 | 3 hrs |
| 3.13 | **Optimización consultas SELECT *** | Reemplazar SELECT * por columnas específicas en consultas pesadas | 🟡 | 4 hrs |

### Criterios de Aceptación
- ✅ Todos los formularios tienen validación frontend + backend
- ✅ Listados tienen filtros combinados, paginación configurable, ordenamiento
- ✅ Exportación CSV disponible en listados principales
- ✅ Las consultas pesadas no usan `SELECT *`
- ✅ Las transacciones envuelven operaciones críticas

---

## Sprint 4 — Configuración de Integraciones (Semana 5)

**Objetivo:** Activar las conexiones con servicios externos (WhatsApp, Facebook, Instagram, Email).

### Tareas

| # | Tarea | Detalle | Dependencia | Prioridad | Esfuerzo |
|---|---|---|---|---|---|
| 4.1 | **Configurar SMTP Hostinger** | Verificar cuenta email, establecer password en root_config.php | Acceso a Hostinger email admin | 🔴 | 30 min |
| 4.2 | **Configurar WATI WhatsApp** | Obtener API Key, verificar número, activar template cumpleaños | Cuenta WATI | 🔴 | 1 hr |
| 4.3 | **Probar envío de cumpleaños** | Envío manual desde panel `pages/whatsapp_log.php` | 4.2 completado | 🔴 | 30 min |
| 4.4 | **Configurar Facebook App** | Crear app en developers.facebook.com | Cuenta FB de la campaña | 🟡 | 2 hrs |
| 4.5 | **Obtener Page Token** | Página @edisonconcejal | 4.4 completado | 🟡 | 1 hr |
| 4.6 | **Configurar Instagram Business** | Vincular IG a FB Business, obtener IG_BUSINESS_ID | 4.5 completado | 🟡 | 1 hr |
| 4.7 | **Configurar webhook WhatsApp Cloud** | Apuntar callback a `api/whatsapp_webhook.php` | Cuenta Meta Business | 🟡 | 1 hr |
| 4.8 | **Cron email_processor.php** | Programar en Hostinger: `*/5 * * * *` | 4.1 completado | 🟢 | 15 min |
| 4.9 | **Cron whatsapp_broadcast.php** | Programar en Hostinger | 4.2 completado | 🟢 | 15 min |
| 4.10 | **Documentar checklist de activación** | Checklist post-Sprint 4 para verificar cada integración | Todas las anteriores | 🟢 | 1 hr |

### Criterios de Aceptación
- ✅ SMTP configurado → email de prueba enviado desde el sistema
- ✅ WATI configurado → mensaje de cumpleaños enviado manualmente
- ✅ FB Page Token funcional → posts visibles en dashboard Social CRM
- ✅ IG Business vinculado → comentarios apareciendo en `pages/instagram_graph.php`
- ✅ Webhook WhatsApp Cloud recibiendo pings de Meta

---

## Sprint 5 — Expansión Geográfica (Semanas 6-7)

**Objetivo:** Agregar soporte para múltiples municipios del Valle del Cauca aprovechando la estructura actual de datos.

### Tareas

| # | Tarea | Detalle | Prioridad | Esfuerzo |
|---|---|---|---|---|
| 5.1 | **Script ETL: importar Palmira** | Obtener datos DANE, generar SQL de territorios | 🔴 | 2 días |
| 5.2 | **Script ETL: importar Tuluá** | Obtener datos DANE, generar SQL de territorios | 🔴 | 2 días |
| 5.3 | **Script ETL: importar Buenaventura** | Obtener datos DANE, generar SQL de territorios | 🔴 | 2 días |
| 5.4 | **Script ETL: importar Buga** | Obtener datos DANE | 🟡 | 1 día |
| 5.5 | **Script ETL: importar Cartago** | Obtener datos DANE | 🟡 | 1 día |
| 5.6 | **Modificar selector municipio dinámico** | Que cargue todos los municipios disponibles (no solo Cali) | 🔴 | 3 hrs |
| 5.7 | **Actualizar API de colaboradores** | Filtro `municipio` aceptando cualquier valor | 🔴 | 2 hrs |
| 5.8 | **Actualizar API de reportes geográficos** | KPIs agregables por municipio seleccionado | 🟡 | 4 hrs |
| 5.9 | **Mapa Leaflet multi-municipio** | Cargar GeoJSON del municipio seleccionado | 🟡 | 6 hrs |
| 5.10 | **Vista: Reporte Municipal** | Nueva página `pages/reporte_municipal.php` | 🟢 | 4 hrs |
| 5.11 | **Importar puestos de votación** | Puestos de votación para nuevos municipios | 🟡 | 3 hrs |
| 5.12 | **Pruebas de rendimiento** | Verificar tiempos de carga con datos expandidos | 🔴 | 2 hrs |

### Scripts ETL

```python
# scripts/etl/import_municipio_valle.py
# 
# Uso: python scripts/etl/import_municipio_valle.py --cod-mpio 76520 --nombre "Palmira"
#
# 1. Descargar shapefile/geojson de DANE MGN (Marco Geoestadístico)
# 2. Extraer comunas, corregimientos, veredas, barrios
# 3. Asignar códigos DANE jerárquicos
# 4. Generar SQL INSERT para tabla territorios
# 5. Opcional: generar SQL UPDATE para geometrías
```

### Criterios de Aceptación
- ✅ 5 municipios del Valle del Cauca disponibles en selectores geográficos
- ✅ Mapa Leaflet funcional con cada municipio
- ✅ Filtros en listados permiten seleccionar cualquier municipio
- ✅ Nuevos reportes muestran KPIs por municipio
- ✅ Tiempo de carga < 3s con datos expandidos (usar caché)

---

## Resumen de Esfuerzo Estimado

| Sprint | Días | Archivos a Modificar | Archivos Nuevos |
|---|---|---|---|
| Sprint 1 | 3-4 | 0 | 6 (documentación) |
| Sprint 2 | 5-7 | 8-12 | 1 (componente) |
| Sprint 3 | 5-7 | 15-25 | 2-3 (componentes) |
| Sprint 4 | 3-5 | 2 (config) | 0 |
| Sprint 5 | 5-7 | 10-15 | 3 (scripts ETL + vista) |
| **Total** | **21-30** | **35-55** | **12-13** |

---

## Post-Sprint (Mantenimiento Continuo)

| Actividad | Frecuencia | Responsable |
|---|---|---|
| Revisar logs de errores | Semanal | Admin del sistema |
| Refrescar token Instagram (si configurado) | Cada 60 días | Admin del sistema |
| Verificar cookies Instagram scraper | Cada 1-2 semanas | Admin del sistema |
| Actualizar migraciones SQL pendientes | Mensual | Desarrollador |
| Revisar índices y rendimiento DB | Trimestral | Desarrollador |

---

*Plan generado por auditoría del sistema — 2026-07-08*