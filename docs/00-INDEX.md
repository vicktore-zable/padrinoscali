# Índice Maestro de Documentación

> **Proyecto:** Aratio — Padrinos Cali
> **Última actualización:** 2026-07-08

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

| Documento | Ubicación original |
|-----------|-------------------|
| DOCUMENTACION_INTEGRACION_COLABORADORES.md | `documentacion/` en XAMPP |
| CAMBIOS_PUESTOS_Y_CURRICULUM.md | `documentacion/` en XAMPP |
| DOC_CURRICULUM_2026-02-09.md | `documentacion/` en XAMPP |

### Portal Líder

| Documento | Ubicación original |
|-----------|-------------------|
| PLAN_PORTAL_LIDER.md | `documentacion/` en XAMPP |
| DOCUMENTACION_PORTAL_LIDER.md | `documentacion/` en XAMPP |
| DOCUMENTACION_PORTAL_LIDER_PRODUCCION_2026-02-17.md | `documentacion/` en XAMPP |
| DOCUMENTACION_SETUP_LIDER_2026-02-17.md | `documentacion/` en XAMPP |
| INVENTARIO_CAMBIOS_PORTAL_LIDER.md | `documentacion/` en XAMPP |
| ESTADO_ACTUAL_PORTAL_LIDER.md | `documentacion/` en XAMPP |
| DOC_AJUSTES_LIDERES_2026-02-10.md | `documentacion/` en XAMPP |

### WhatsApp

| Documento | Descripción |
|-----------|-------------|
| `ORIGEN_BIRTHDAY_WATI.md` | Documentación original del sistema de cumpleaños v2.8.0 |
| `WHATSAPP_BIRTHDAY_UI.md` | **NUEVO** — Rediseño visual de tarjetas de cumpleaños: fecha exacta + días faltantes + sistema de colores por urgencia (v2.8.1) |

### JAC

| Documento | Ubicación original |
|-----------|-------------------|
| DOCUMENTACION_MOD_JAC.md | `documentacion/` en XAMPP |

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

**Documentación relacionada en `documentacion/` (XAMPP):**
- REPORTE_REVISION_SISTEMA.md (57 KB)
- ESTADO_SISTEMA.md
- DIAGNOSTICO_LOCAL_2026-02-12.md
- RESUMEN_EJECUTIVO.md

## 07 — Sesiones

| Documento | Fecha |
|-----------|-------|
| `sesion_2026-05-03.md` | 2026-05-03 |
| `sesion_2026-05-21.md` | 2026-05-21 |

## 08 — Guías

| Documento | Ubicación original |
|-----------|-------------------|
| GUIA_INICIO_LOCAL.md | `documentacion/` en XAMPP |
| GUIA_SINCRONIZAR_PRODUCCION.md | `documentacion/` en XAMPP |
| SYNC_XAMPP.md | `documentacion/` en XAMPP |
| INSTRUCCIONES_DESPLIEGUE.md | `documentacion/` en XAMPP |
| INSTRUCCIONES_SUBIR_HOSTINGER.md | `documentacion/` en XAMPP |

## 09 — Referencia

| Documento | Ubicación original |
|-----------|-------------------|
| design_system.md | `documentacion/` en XAMPP |
| DOCUMENTACION_TECNICA_C4.md | `documentacion/` en XAMPP |

---

## Documentación externa referenciada

> La mayoría de los documentos históricos (2025-2026) residen en:
> **`F:\xampp2\htdocs\aratio\documentacion\`** (57 archivos)
> **`F:\xampp2\htdocs\aratio\docs\`** (2 archivos)
> **`Aratio-App-Mirror\documentacion\`** (espejo en workspace)

## Planes

| Documento | Descripción |
|-----------|-------------|
| `PLAN_SPRINTS.md` | **NUEVO** — Plan detallado por 5 sprints: tareas, prioridades, esfuerzo estimado, criterios de aceptación para cada sprint |

Para buscar documentación histórica, usar `grep -r "tema" documentacion/` en la terminal.
