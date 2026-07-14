# Índice Maestro de Documentación

> **Proyecto:** Aratio — Padrinos Cali
> **Última actualización:** 2026-07-11

---

## 01 — Estrategia

| Documento | Descripción |
|-----------|-------------|
| `PLAN_FASE1.md` | Plan Fase 1: ALAS — Comunicaciones Inteligentes + Workflows (WhatsApp bidireccional, automatización, timeline unificado) |
| `FASE2_ROADMAP.md` | **NUEVO** — Fase 2: Interactividad + Phone Banking + Email + Dashboard + Líder 2.0 |

## 02 — Arquitectura

| Documento | Descripción |
|-----------|-------------|
| `ARQUITECTURA_ACTUAL.md` | **NUEVO** — Mapa completo de arquitectura, stack, ruteo, estructura de directorios, ~42 tablas DB, 6 módulos, problemas identificados y recomendaciones |
| `VISTAS_PENDIENTES.md` | **NUEVO** — Listado de vistas que causan fatal error, huérfanas, sin enlaces, APIs sin configuración y plan de reconstrucción |
| `DB_AUDITORIA.md` | **NUEVO** — Auditoría de base de datos: optimización de consultas, sugerencias de índices, validaciones, transacciones y bitácoras |
| `MODULOS_ADMIN.md` | **NUEVO** — Análisis de lógica de administración por entidad, propuesta de componentes UI reutilizables y jerarquía de permisos |
| `MODULOS_GEOGRAFICOS.md` | **NUEVO** — Estrategia de expansión a municipios del Valle del Cauca: estructura actual, fuentes DANE, script ETL, ajustes en vistas |

## 03 — Despliegue

| Documento | Descripción |
|-----------|-------------|
| `MANTENIMIENTO.md` | Guía de mantenimiento del servidor y la aplicación |
| `SETUP_ALAS.md` | **NUEVO** — Guía paso a paso para configurar WhatsApp Cloud API, cron jobs, webhook y plantillas Meta |

**Documentación relacionada en `DEPLOY_EDISONGIRALDO.md` (raíz del proyecto):**
Credenciales SSH, SFTP, DB, SMTP y rutas de producción.

## 04 — Módulos

### Colaboradores

| Documento | Estado |
|-----------|--------|
| DOCUMENTACION_INTEGRACION_COLABORADORES.md | ✅ `04-modulos/colaboradores/` |
| CAMBIOS_PUESTOS_Y_CURRICULUM.md | ✅ `04-modulos/colaboradores/` |
| DOC_CURRICULUM_2026-02-09.md | ✅ `04-modulos/colaboradores/` |

### Portal Líder

| Documento | Estado |
|-----------|--------|
| PLAN_PORTAL_LIDER.md | ✅ `04-modulos/portal-lider/` |
| DOCUMENTACION_PORTAL_LIDER.md | ✅ `04-modulos/portal-lider/` |
| DOCUMENTACION_PORTAL_LIDER_PRODUCCION_2026-02-17.md | ✅ `04-modulos/portal-lider/` |
| DOCUMENTACION_SETUP_LIDER_2026-02-17.md | ✅ `04-modulos/portal-lider/` |
| INVENTARIO_CAMBIOS_PORTAL_LIDER.md | ✅ `04-modulos/portal-lider/` |
| ESTADO_ACTUAL_PORTAL_LIDER.md | ✅ `04-modulos/portal-lider/` |
| DOC_AJUSTES_LIDERES_2026-02-10.md | ✅ `04-modulos/portal-lider/` |
| DOC_AJUSTES_INSCRIPCION_2026-02-06.md | ✅ `04-modulos/portal-lider/` |
| DOCUMENTACION_EVENTOS_2026_02_15.md | ✅ `04-modulos/portal-lider/` |

### WhatsApp

| Documento | Descripción |
|-----------|-------------|
| `ORIGEN_BIRTHDAY_WATI.md` | Documentación original del sistema de cumpleaños v2.8.0 |
| `WHATSAPP_BIRTHDAY_UI.md` | **NUEVO** — Rediseño visual de tarjetas de cumpleaños: fecha exacta + días faltantes + sistema de colores por urgencia (v2.8.1) |

### JAC

| Documento | Estado |
|-----------|--------|
| DOCUMENTACION_MOD_JAC.md | ✅ `04-modulos/jac/` |

### Dia D

| Documento | Ubicación original |
|-----------|-------------------|
| TECHNICAL_DOC.md | `diaD_dist/` en XAMPP |
| FINAL_PROJECT_SUMMARY.md | `diaD_dist/` en XAMPP |

## 05 — Integraciones

### Estado General

| Documento | Descripción |
|-----------|-------------|
| `ESTADO_INTEGRACIONES.md` | **NUEVO** — Estado actual de todas las integraciones externas (WhatsApp, Facebook, Instagram, Email) con checklist de configuración pendiente |

### Instagram

| Documento | Descripción |
|-----------|-------------|
| `MAESTRO_SCRAPER.md` | Documentación técnica del scraper de Instagram (@edison_concejal) |
| `TIMELINE_CONCEJAL.md` | Timeline completo de 686 posts (2018-2026) — 278 KB |

### WhatsApp

| Documento | Descripción |
|-----------|-------------|
| `ORIGEN_BIRTHDAY_WATI.md` | Documentación del sistema de cumpleaños v2.8.0 con WATI |

## 06 — Reportes

| Documento | Descripción |
|-----------|-------------|
| `REPORTE_CREACION_EVENTOS.md` | Reporte de creación de eventos |

**Documentos archivados (movidos a `documentacion/archived/`):**
- REPORTE_REVISION_SISTEMA.md — Obsoleto, ver `09-referencia/RESUMEN_EJECUTIVO.md`
- ESTADO_SISTEMA.md — Obsoleto (v1.4.0), reemplazado por `02-arquitectura/ARQUITECTURA_ACTUAL.md`
- DIAGNOSTICO_LOCAL_2026-02-12.md — Diagnóstico único, sin valor residual
- Otros 21 documentos de fix/deploy archivados

## 07 — Sesiones

| Documento | Fecha |
|-----------|-------|
| `sesion_2026-05-03.md` | 2026-05-03 |
| `sesion_2026-05-21.md` | 2026-05-21 |

## 08 — Guías

| Documento | Estado |
|-----------|--------|
| `SETUP_ALAS.md` | ✅ Presente |
| GUIA_INICIO_LOCAL.md | ✅ `08-guias/` |
| GUIA_SINCRONIZAR_PRODUCCION.md | ✅ `08-guias/` |
| INSTRUCCIONES_DESPLIEGUE.md | ✅ `08-guias/` |
| REGISTRO_WEB_Y_SOLUCION_GOLD.md | ✅ `08-guias/` |
| PRODUCCION_ESTRUCTURA.md | ✅ `08-guias/` |
| REDIRECCION_COLABORADORES_A_ARATIO.md | ✅ `08-guias/` |
| README_SEED_JAIMITO_CARTERO.md | ✅ `08-guias/` |
| DOCUMENTACION_MIGRACION_GOLD.md | ✅ `08-guias/` |
| ~~SYNC_XAMPP.md~~ | 🔴 Archivado (obsoleto) |
| ~~INSTRUCCIONES_SUBIR_HOSTINGER.md~~ | 🔴 Archivado (obsoleto) |

## 09 — Referencia

| Documento | Estado |
|-----------|--------|
| design_system.md | ✅ `09-referencia/` |
| DOCUMENTACION_TECNICA_C4.md | ✅ `09-referencia/` |
| DOCUMENTACION.md | ✅ `09-referencia/` |
| DOCUMENTACION_SINCRONIZACION.md | ✅ `09-referencia/` |
| DOCUMENTACION_SISTEMA_GEOGRAFICO.md | ✅ `09-referencia/` |
| DOCUMENTACION_FINAL_CORRECCION_INSCRIPCION.md | ✅ `09-referencia/` |
| CREDENCIALES_ACCESO.md | ✅ `09-referencia/` |
| NOTAS_SISTEMA.md | ✅ `09-referencia/` |
| RESUMEN_EJECUTIVO.md | ✅ `09-referencia/` |
| SELECTORES_GEOGRAFICOS_IMPLEMENTADOS.md | ✅ `09-referencia/` |
| LANDING_PAGE_CREADA.md | ✅ `09-referencia/` |
| RESUMEN_CAMBIOS_MAPA_DASHBOARD_EVENTOS.md | ✅ `09-referencia/` |

---

## Documentación externa referenciada

> **Consolidación completada 2026-07-11:** Los 31 documentos útiles de `documentacion/` fueron copiados a `docs/`. Los 24 documentos obsoletos (fix/deploy/diagnóstico) están en `documentacion/archived/`.

## Planes

| Documento | Descripción |
|-----------|-------------|
| `PLAN_SPRINTS.md` | **NUEVO** — Plan detallado por 5 sprints: tareas, prioridades, esfuerzo estimado, criterios de aceptación para cada sprint |

Para buscar documentación histórica archivada, usar `grep -r "tema" Aratio-App-Mirror/documentacion/archived/` en la terminal.
