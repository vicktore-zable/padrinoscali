# Changelog — Aratio

Todas las versiones notables del proyecto se documentan aquí.

Formato basado en [Keep a Changelog](https://keepachangelog.com/).
Versiones siguen [SemVer](https://semver.org/).

---

## [2.20.0] — 2026-09-23

### ✨ Autocompletado QR por Documento + Seguridad mod_eventos

**Problema**: Asistentes recurrentes (líderes, colaboradores, padrinos) debían digitar
toda su información cada vez que escaneaban el QR. Además, 3 de 4 endpoints del módulo
eventos no tenían autenticación, exponiendo PII (nombres, cédulas, teléfonos, firmas).

**Solución**: Autocompletado inteligente por documento + hardening de seguridad completo.

| Cambio | Archivos | Detalle |
|--------|----------|---------|
| **Autocompletado QR** | `api/colaboradores.php`, `qr_registro.php` | Nuevo action público `buscar_por_documento`. Al digitar documento (≥5 chars) + blur, busca en `colaboradores` → `asistencia_eventos`, autocompleta form y muestra banner "¡Bienvenido(a)!" |
| **Campos readonly** | `qr_registro.php` | Cuando hay autocompletado: nombre, teléfono, email, fecha_nac, depto, municipio → readonly; tipo_doc, género → disabled |
| **Rate-limit POST público** | `asistencia.php` | 20 intentos / 5 min por IP. Validación firma: 512KB max, formato data URI |
| **Auth GET/PUT/DELETE** | `asistencia.php` | `requireAuth()` + `hasAccessToCampana` en todos los endpoints admin |
| **Auth export/reportes** | `exportar_asistencia.php`, `reportes.php` | `requireAuth()` + acceso por campaña |
| **CSRF en mutaciones** | `asistencia.php` | Token `X-CSRF-Token` en PUT/DELETE |
| **Fix handlePut** | `asistencia.php` | `observaciones` → `notas` (columna real). Removidos `sentimiento_*` inexistentes |
| **Validación fechas** | `api/eventos.php` | `fecha_inicio < fecha_fin` |
| **Helper compartido** | `_security.php` (NUEVO) | `eventos_require_admin`, `eventos_csrf_verify`, `eventos_rate_limit`, `eventos_validate_firma`, `eventos_resolve_responsable`, `eventos_validate_fechas` |
| **Sin fuga de errores** | `api/eventos.php` | `$e->getMessage()` removido del response JSON |
| **Deploy script** | `scripts/deploy_autocomplete_qr.py` | Upload SFTP a ambos dominios |

**Archivos modificados**:
- `api/colaboradores.php` — action `buscar_por_documento` público
- `mod_eventos/pages/qr_registro.php` — listener documento + autollenado + banner + readonly
- `mod_eventos/api/asistencia.php` — auth + CSRF + rate-limit + fix notas
- `mod_eventos/api/_security.php` — **NUEVO** helpers de seguridad
- `mod_eventos/api/reportes.php` — auth + acceso campaña
- `mod_eventos/api/exportar_asistencia.php` — auth + acceso campaña
- `mod_eventos/api/eventos.php` — fix validación fechas + sin fuga errores
- `scripts/deploy_autocomplete_qr.py` — **NUEVO** deploy script

**Deploy**: 6 archivos × 2 dominios (padrinoscali.org + edisongiraldo.com). PHP syntax check remoto: ✅ limpio.

**Tests**: Playwright E2E con documento `16832362` en evento 5 — autocompletado + banner + readonly confirmados.

---

## [2.19.0] — 2026-07-30

### ✨ Módulo Eventos v2: Firma Digital, Exportación, Reportes Chart.js

**Problema**: El registro QR no capturaba firma digital. No se podían ver las firmas en la
planilla de asistencia. El botón "Exportar Excel" era un placeholder. No había reportes
consolidados por barrio/comuna.

**Solución**: Rediseño completo del módulo eventos con 7 fases integradas.

| Fase | Archivos | Cambio |
|------|----------|--------|
| 0 | `index.php`, `eventos.php`, `qr_registro.php` | Fix `$campanaId`, firma canvas, logo Padrinos, modal detalle asistente |
| 1 | `database/migration_033_eventos_v2.sql` | Migration: `autorizacion_imagenes` BOOLEAN en `asistencia_eventos` |
| 2 | `qr_registro.php` | Checkboxes: `acepta_comunicaciones` + `autorizacion_imagenes` |
| 3 | `api/asistencia.php` | GET retorna `firma_digital` + `autorizacion_imagenes`. POST acepta `autorizacion_imagenes` |
| 4 | `eventos.php` | Columna "Firma" con thumbnail en tabla. Firma completa en detalle modal |
| 5 | `eventos.php` | Menú ⋮ eliminado → acciones movidas a modalDetalle (QR, WhatsApp, Asistentes, Mapa, Dashboard) |
| 6 | `api/exportar_asistencia.php` | **NUEVO** — Export XLSX (SimpleXLSXGen) + PDF imprimible con logo y firmas |
| 7 | `api/reportes.php`, `reportes.php` | **NUEVO** — Endpoint consolidado + 4 charts Chart.js (barrios, municipios, tipo, comparativo) |

**Detalle por archivo**:

| Archivo | Cambio |
|---------|--------|
| `mod_eventos/index.php` | `$campanaId = $campanaActivaId` agregado para las vistas `asistencia` y `reportes` |
| `mod_eventos/pages/eventos.php` | Menú ⋮ removido, acciones integradas en modalDetalle. Columna firma (thumbnail `<img>`) en tabla asistentes. Detalle asistente muestra firma full + acepta_comunicaciones + autorizacion_imagenes. Botones export Excel y PDF funcionales |
| `mod_eventos/pages/qr_registro.php` | Canvas firma digital con mouse/touch. Logo Padrinos. 3 checkboxes (habeas_data, acepta_comunicaciones, autorizacion_imagenes). Validación firma no vacía |
| `mod_eventos/pages/reportes.php` | Tabs General/Consolidado. Chart.js: barrios (barras H), municipios (doughnut), tipo (pie), comparativo (barras dobles) |
| `mod_eventos/api/asistencia.php` | GET: `firma_digital` + `autorizacion_imagenes` en SELECT. POST: `autorizacion_imagenes` en INSERT + VALUES |
| `mod_eventos/api/exportar_asistencia.php` | **NUEVO**. `formato=xlsx`: 17 columnas con SimpleXLSXGen. `formato=pdf`: HTML `@media print` con logo, QR, firma por asistente. `&asistente_id=Y`: export individual |
| `mod_eventos/api/reportes.php` | **NUEVO**. `action=consolidado`: SQL agrupado por barrio, municipio y tipo, con totales |
| `database/migration_033_eventos_v2.sql` | **NUEVO**. `ALTER TABLE asistencia_eventos ADD COLUMN autorizacion_imagenes` |

**Notas técnicas**:
- `SimpleXLSXGen` (namespace `Shuchkin`) usado para Excel — ya incluido en `includes/`, sin Composer
- PDF es HTML imprimible con `@media print` (no librería PDF externa)
- Chart.js vía CDN `cdn.jsdelivr.net/npm/chart.js@4.4.1`
- `firma_digital` almacenada como LONGTEXT (base64 PNG, hasta 4GB teórico)
- La migración 033 se ejecutó en producción: `ALTER TABLE` con DEFAULT FALSE

## [2.18.6] — 2026-07-21

### 🐛 Fix: mod_eventos congelado por MutationObserver infinito

**Problema**: `init()` en eventos.php creaba un `MutationObserver` que observaba `document.body` y llamaba `lucide.createIcons()` en cada mutación. Como `lucide.createIcons()` reemplaza `<i>` por SVGs (generando nuevas mutaciones), se creaba un loop infinito que congelaba el navegador.

### 🐛 Fix: 30 Alpine Expression Errors (Cannot read properties of null)

**Problema**: El modal de detalle usaba `x-show="detalleEvento"` pero Alpine evalúa `x-text="detalleEvento.nombre"` incluso dentro de elementos ocultos. Al iniciar con `detalleEvento: null`, 28 expresiones lanzaban TypeError. Igual con `eventoSolo.latitud` en el modal de mapa solo.

| Archivo | Cambio |
|---------|--------|
| `mod_eventos/pages/eventos.php` | `x-show="detalleEvento"` → `<template x-if="detalleEvento">` (no evalúa expresiones anidadas cuando es falso) |
| `mod_eventos/pages/eventos.php` | `eventoSolo.latitud` → `eventoSolo?.latitud ?? ''` (optional chaining) |
| `mod_eventos/pages/eventos.php` | MutationObserver removido, reemplazado por `$watch('filtros', ...)` para lucide icons |

## [2.18.5] — 2026-07-13

### 🐛 Fix: Tendencias 500 Internal Server Error

**Problema**: `handleTendencias()` usaba columna `creado_en` que no existe en `colaboradores` (columna real: `created_at`). SQL error 1054 → 500 JSON error.

| Archivo | Cambio |
|---------|--------|
| `api/dashboard.php` | `creado_en` → `created_at` en query de tendencias |

### 🐛 Fix: getGeoJSON crash PHP 8 con rows vacío

**Problema**: `getGeoJSON()` llamaba `max(array_column($rows, $countField))` sin verificar `$rows` vacío. PHP 8 lanza `ValueError`, crasheando endpoint `otros_mapas`.

| Archivo | Cambio |
|---------|--------|
| `api/dashboard.php` | `if (empty($rows)) return ['type'=>'FeatureCollection','features'=>[]]` |

### 🐛 Fix: Spinners de mapa atascados

**Problema**: Spinners usaban `x-show="!semaforo.lideres_total"` — si el API retornaba 0, el spinner quedaba visible para siempre.

| Archivo | Cambio |
|---------|--------|
| `pages/dashboard_territorial.php` | Los 3 spinners ahora usan `x-show="!loaded"` (flag post-carga) |

### 🐛 Fix: Catch individual por fetch

**Problema**: `refreshAll()` usaba `Promise.all` sin try/catch individual — si un endpoint fallaba, todo se rechazaba.

| Archivo | Cambio |
|---------|--------|
| `pages/dashboard_territorial.php` | 7 funciones `load*()` envueltas en try/catch |

### 🐛 Fix: Red Social — definición de líder

**Problema**: Líderes se contaban por `perfil LIKE '%Lider%'` en vez de por `lider_directo`. Profundidad usaba `WITH RECURSIVE` (no soportado en Hostinger). Rotación innecesaria.

| Archivo | Cambio |
|---------|--------|
| `api/bi.php` | Líderes = `COUNT(DISTINCT lider_directo)`; removed profundidad recursiva + rotación |
| `pages/bi.php` | KPI row: grid 5→4, removida card Rotación |

### 🐛 Fix: toggleCapa mapa territorio

**Problema**: `initMapaTerritorio()` llamaba `toggleCapa()` que togglea `capasActivas`, dejando checkboxes invertidos respecto a la capa visible.

| Archivo | Cambio |
|---------|--------|
| `pages/bi.php` | `agregarCapaTerritorio()` separada; `toggleCapa()` usa `capasActivas` post-toggle para decisión show/hide |

---

## [2.18.0] — 2026-07-12

### 🆕 Panorama BI Hub rediseñado: distribuciones + top rankings

**Problema**: Panorama solo mostraba 3 charts básicos (tendencia, composición por perfil, top 5 municipios). Sin visibilidad de distribución por rol/estado/género/nivel de participación, ni rankings de territorios/barrios/líderes.

**Solución**: Rediseño completo del tab Panorama en BI Hub con cards de distribución, top rankings y renombramiento perfil→rol.

| Archivo | Cambio |
|---------|--------|
| `api/bi.php` | `handlePanorama` ahora retorna `distribuciones` (rol, estado, genero, nivel_participacion), `topLideres` (top 10 con seguidores), `topTerritorios` (top 10), `topBarrios` (top 10). Reemplaza `composicion` + `topMuni` |
| `pages/bi.php` | Cards distribución Rol/Estado/Género; badges Nivel Participación; charts Top 10 Territorios, Top 10 Barrios, Top 10 Líderes Directos; labels perfil→rol |

### 🐛 Fix: API ahora retorna `label` en distribuciones (normaliza nombres)

**Problema**: `composicion` usaba `d.perfil` como label; nuevo esquema usa `d.label` genérico.

**Solución**: `distribuciones.rol` usa `perfil AS label`, `distribuciones.estado` usa `COALESCE(NULLIF(estado,''), 'Sin estado') AS label`, etc.

---

### 🐛 Fix: Tab Zonas de Trabajo oculto (HTML estructura)

**Problema**: El tab Zonas de Trabajo y Redes Sociales no se mostraban al hacer clic.

**Causa raíz**: `<div x-show="activeTab === 'actividad'">` (línea 621) no tenía `</div>` de cierre → Social y Zonas quedaban anidados dentro, ocultos por `display: none`.

| Archivo | Cambio |
|---------|--------|
| `pages/colaborador_detalle.php` | `</div>` agregado línea 660 cerrando Actividad; tabs ahora son hermanos correctos |

### 🆕 Tab Zonas de Trabajo — Vista completa

**Feature**: Visualización de zonas propias + de seguidores con tabla, mapa GeoJSON y CRUD completo.

| Archivo | Cambio |
|---------|--------|
| `pages/colaborador_detalle.php` | Tabla Zona/Municipio/Tipo/Responsable/Acciones; empty state personalizado; stats; toast; precarga jerárquica async (dpto→mpio→tipo→territorio→barrio); botones solo para 'propia' |
| `api/zonas_trabajo.php` | `con_seguidores` con filtro lider_directo+campana; PUT con territorio_id; `_method=PUT` via POST; `barrios_v2` |
| `api/territorios.php` | Nueva action `detalle_territorio&id=X` |
| `pages/colaborador_detalle.php` | Mapa Leaflet con responsable en popup; `colaborador_zona_id` |

### 🎨 WhatsApp Cumpleaños — Rediseño tarjetas

**Feature**: Visual redesign del calendario de cumpleaños con countdown y gradiente por urgencia.

| Archivo | Cambio |
|---------|--------|
| `pages/whatsapp_log.php` | Grid 3 columnas; tarjetas con barra gradiente (rojo=hoy, ámbar≤3, azul≤7, gris>7); bloque "Cumple" con fecha+edad; badge countdown; botón circular; toast |
| `api/whatsapp.php` | Endpoint `cumpleanos` con `fecha_exacta` + `dias_faltantes` |

### 🚀 Deploy

Subido 5 archivos a producción (`padrinoscali.org`) y legacy (`edisongiraldo.com`).

---

## [2.14.0] — 2026-06-30

### 🚀 Deploy Completo a Producción + Nuevos Módulos

Subida completa de todos los módulos faltantes a producción en `padrinoscali.org/aratio/`.

### 🆕 CP — Pulso de Campaña

| Archivo | Descripción |
|---------|-------------|
| `includes/MessengerBot.php` | Clase PHP con templates de campaña, envío WhatsApp/Messenger, logging |
| `api/cp_pulso.php` | Endpoint webhook + CRUD triggers |
| `pages/cp_pulso.php` | Panel admin Alpine.js: historial, stats, envío manual |
| `cron/cp_scan.php` | Disparo automático diario de campañas vencidas |
| `cp_captura.php` | Landing pública de captura de leads |
| `database/migrations/20260701_cp_pulso.sql` | Tablas `cp_triggers`, `cp_capture_flow` |
| `root_config.php` | Constante `CP_BASE_URL` |
| `index.php` | Ruta `cp_pulso` + sidebar |

### 🗺️ Dashboard Territorial + Social CRM (deploy a prod)

Migración completa del ecosistema Social CRM a producción:

| Grupo | Archivos |
|-------|----------|
| `includes/` | ActivityLogger, Container, EmailCampaigns, FacebookApi, InstagramGraphApi, Logger, PhoneBanking, SocialCRM, WhatsAppCloudApi, WorkflowEngine |
| `api/` | dashboard, emails, facebook, instagram_graph, lideres, llamadas, setup, social_crm, social_territorial, timeline, whatsapp_messages, whatsapp_webhook, workflows |
| `pages/` | dashboard_territorial, dashboard_territorial_social, emails, instagram_graph, lideres, llamadas, setup, social_crm, whatsapp_messages, workflows |
| `cron/` | email_processor, facebook_sync, whatsapp_broadcast, workflow_processor |
| `config/` | di.php |
| `migrations/` | 8 SQLs (20260628–20260703) |

### 🖥️ Landing Page

- Nueva sección **Plataforma** con 4 targetas de módulos + CTA

### 📊 Monitor Instagram

- Fix sync sin Python: lectura directa desde `maestro_instagram.json`
- Orden descendente por fecha

### 📚 Documentación

- `docs/04-modulos/GUETA_ANALYTICS.md` — Documento maestro del sistema de analytics

---

## [2.13.0] — 2026-06-29

### 🗺️ Dashboard Territorial

Panel de control unificado con KPIs, gráficos de tendencia y mapa interactivo Leaflet.

### 🆕 Archivos nuevos

| Archivo | Descripción |
|---------|-------------|
| `api/dashboard.php` | 5 endpoints: kpis consolidados, geo distribución, tendencias, recientes, ALAS stats |
| `pages/dashboard_territorial.php` | Panel Alpine.js con 3 secciones (KPIs, mapa, gráficos) + ALAS status |

### 📊 KPIs (16 indicadores)
- Colaboradores totales, activos, líderes, perfiles, top municipios
- Donaciones totales y recaudado
- Eventos totales y próximos, asistentes
- Acciones comunitarias y personas contactadas
- Compromisos totales y cumplidos

### 🗺️ Mapa Leaflet
- Carga GeoJSON de territorios desde `api_territorios_geojson.php`
- Tooltips y popups con desglose por perfil (líderes, movilizadores, simpatizantes)
- Fallback a CircleMarker si no hay GeoJSON

### 📈 Gráficos Chart.js
- Colaboradores por mes (barra)
- Donaciones por mes (barra con monto)
- Eventos por mes (barra)
- Selector de período (3/6/12 meses)

### 🔌 ALAS Integración
- Sección "Estado del Sistema" con stats de WhatsApp, Workflows, Phone Banking y Email
- Timeline de actividad reciente con iconos por tipo

### 🧩 Sidebar
- Nuevo enlace "Dashboard Territorial" en navegación principal (icono globe)

## [2.12.0] — 2026-06-29

### 📧 Email Campaigns

Sistema de campañas de correo electrónico con segmentación y tracking de aperturas.

### 🆕 Archivos nuevos

| Archivo | Descripción |
|---------|-------------|
| `includes/EmailCampaigns.php` | Clase core: sendMail (PHP mail()), crear campañas, generar cola, procesar, stats |
| `api/emails.php` | 10 endpoints REST: campañas, crear, procesar cola, plantillas, historial, stats, track pixel |
| `pages/emails.php` | Panel Alpine.js con 5 tabs (Campañas, Nueva, Plantillas, Historial, Stats) |
| `cron/email_processor.php` | Procesador de cola (30 emails por ejecución) |
| `database/migrations/20260701_email_campaigns.sql` | 4 tablas + 3 plantillas predefinidas |

### 📊 Funcionalidades
- **Campañas**: Crear con nombre, asunto, cuerpo HTML y filtros de segmentación
- **Segmentación**: Filtrar por perfil, municipio, territorio, líder
- **Cola**: Generación automática al crear campaña, procesamiento vía cron
- **Plantillas**: 3 predefinidas (Bienvenida, Invitación Evento, Boletín Mensual), reutilizables
- **Tracking**: Pixel de apertura (1x1 GIF) en todos los correos
- **Historial**: Log completo de envíos con estado y errores
- **Stats**: KPIs (hoy, total, pendientes, abiertos, tasa de apertura) + distribución visual
- **Filtros**: SMTP Hostinger ya configurado, usa mail() nativo de PHP

## [2.11.0] — 2026-06-29

### 📞 Phone Banking

Sistema de gestión de llamadas a colaboradores con panel de agente y campañas.

### 🆕 Archivos nuevos

| Archivo | Descripción |
|---------|-------------|
| `includes/PhoneBanking.php` | Clase core: crear campañas, generar cola, asignar, registrar resultados, stats |
| `api/llamadas.php` | 9 endpoints REST: campañas, cola, siguiente, resultado, historial, stats |
| `pages/llamadas.php` | Panel Alpine.js con 4 tabs (Agente, Campañas, Historial, Stats) |
| `database/migrations/20260701_phone_banking.sql` | 3 tablas + 2 reglas de workflow |

### 📊 Panel Agente
- **Campañas**: Crear campaña con nombre, descripción, guión y filtros de segmentación
- **Cola**: Generación automática de cola al crear campaña, filtra por perfil/territorio/líder
- **Llamada**: Botón "Siguiente" toma el primer pendiente, muestra info del colaborador + guión
- **Timer**: Cronómetro automático al iniciar llamada
- **Resultados**: 7 botones rápidos (contestó, no contesta, llamar después, ocupado, no interesado, equivocado, otro)
- **Notas**: Campo opcional para resultados personalizados
- **Mi cola**: Lista de llamadas asignadas al agente actual

### 📈 Historial y Stats
- **Historial**: Tabla paginada con filtro por resultado, muestra colaborador/agente/duración/notas
- **Stats**: KPIs (hoy/semana/total/pendientes/duración prom), breakdown de resultados con barras, top agentes

### 🔌 Integraciones
- `ActivityLogger::log()` en cada resultado de llamada (tipo `llamada_*`)
- `WorkflowEngine::trigger('llamada.finalizada', ...)` con resultado y duración
- 2 reglas predefinidas: seguimiento WhatsApp si contestó, cambio estado si no contesta

## [2.10.0] — 2026-06-29

### ⚡ Interactividad en Vivo

Sistema de polling automático y notificaciones en tiempo real para todos los paneles ALAS.

### 🔴 Badge de mensajes no leídos
- **Nuevo**: Sección "ALAS" en sidebar con Inbox (badge rojo) + Workflows
- **Nuevo**: Polling cada 30s desde `index.php` al endpoint `stats` para actualizar badge
- **Nuevo**: Toast notification cuando llegan mensajes nuevos (ventana 5s auto-hide)
- **Nuevo**: Contador en cabezal de sección ALAS además del badge inline

### 💬 Inbox en vivo
- **Modificado**: `pages/whatsapp_messages.php` — Polling cada 15s refresca lista de conversaciones y stats sin recargar
- **Modificado**: Auto-marca como leído al seleccionar conversación

### ⚙️ Workflows en vivo
- **Modificado**: `pages/workflows.php` — Polling cada 15s refresca KPIs y cola de pendientes

### 📋 Timeline en tiempo real
- **Modificado**: `pages/colaborador_detalle.php` — Polling cada 30s en pestaña Actividad
- **Mejora**: Al cambiar filtros de actividad, se reinicia el polling automáticamente

### 🧩 Infraestructura
- **Modificado**: `index.php` — Toast component Alpine.js global (evento `alas-toat` vía CustomEvent)
- **Modificado**: `index.php` — Páginas `whatsapp_messages` y `workflows` agregadas a `$paginasPermitidas`
- **Modificado**: Todos los componentes Alpine.js con `destroy()` cleanup de intervalos

## [2.9.0] — 2026-06-29

### ✨ ALAS — Automatización de Liderazgo, Acción y Seguimiento

Sistema completo de comunicaciones inteligentes para Aratio. 13 archivos nuevos, 8 modificados, 8 tablas nuevas.

### 💬 WhatsApp Cloud API (Meta)
- **Nuevo**: `includes/WhatsAppCloudApi.php` — Clase para Meta WhatsApp Cloud API (gratis <1,000 convs/mes)
- **Nuevo**: `api/whatsapp_webhook.php` — Webhook público (GET verify + POST mensajes entrantes)
- **Nuevo**: `api/whatsapp_messages.php` — Endpoints: conversaciones, enviar, broadcast, plantillas, stats
- **Nuevo**: `pages/whatsapp_messages.php` — Panel Alpine.js: inbox conversaciones, chat en vivo, broadcast segmentado, gestor de plantillas
- **Nuevo**: `cron/whatsapp_broadcast.php` — Procesador de cola de broadcasts (cada 1 min)
- **Fallback automático** a WATI si Cloud API no está configurado

### ⚙️ Workflow Engine
- **Nuevo**: `includes/WorkflowEngine.php` — Motor de automatización con 6 triggers predefinidos
- **Nuevo**: `api/workflows.php` — Endpoints: reglas, log, stats, toggle, ejecución manual
- **Nuevo**: `pages/workflows.php` — Panel de monitoreo: KPIs, reglas, bitácora, cola de pendientes
- **Nuevo**: `cron/workflow_processor.php` — Procesador de acciones diferidas (cada 5 min)
- **Triggers**: registro, evento próximo, evento finalizado, donación, inactividad 30d, cumpleaños
- **Acciones**: WhatsApp, asignar líder automático, cambio de estado, notificar líder

### 📋 Timeline Unificado
- **Nuevo**: `includes/ActivityLogger.php` — Clase estática para registrar cualquier actividad
- **Nuevo**: `api/timeline.php` — Endpoint paginado con filtros por tipo
- **Nuevo**: Pestaña "Actividad" en perfil del colaborador con timeline cronológico
- **Tipos**: registro, evento, compromiso, donación, WhatsApp, estado, evaluación, líder, cumpleaños

### 🔌 Integración en módulos existentes
- `api/colaboradores.php`: ActivityLogger en crear, cambiar líder, reevaluar + trigger registro
- `api/eventos.php`: Trigger evento próximo al crear evento
- `api/donaciones.php`: ActivityLogger + trigger donación recibida
- `api/compromisos.php`: ActivityLogger
- `api/whatsapp.php`: ActivityLogger en reenvíos

### 🧩 Configuración
- `root_config.php`: Constantes `META_WHATSAPP_TOKEN`, `META_WHATSAPP_PHONE_ID`, `META_WEBHOOK_VERIFY_TOKEN`, `ALAS_VERSION`
- `index.php`: Rutas `whatsapp_messages` y `workflows` + sección ALAS en menú lateral
- 3 migraciones SQL: tablas de conversaciones, workflows y timeline

📖 Plan completo en `docs/01-estrategia/PLAN_FASE1.md`

---

## [2.8.0] — 2026-06-28

### ✨ Sistema de Cumpleaños por WhatsApp
- **Nuevo**: `includes/WhatsAppApi.php` — Clase WATI con 5 templates de cumpleaños (lider, simpatizante, movilizador, familia, default)
- **Nuevo**: `cron/birthday_check.php` — Script diario (8 AM) que envía felicitaciones automáticas
- **Nuevo**: `api/whatsapp.php` — Endpoints: cumpleaños, historial, reenviar, stats
- **Nuevo**: `pages/whatsapp_log.php` — Panel admin Alpine.js: calendario, historial, estadísticas
- **Nuevo**: Tabla `whatsapp_log` para trazabilidad de envíos
- **Modificado**: `root_config.php` — Constantes WATI_API_KEY, WATI_API_URL
- **Modificado**: `config/config.php` — Fallback WhatsApp config
- **Modificado**: `index.php` — Ruta `whatsapp_log` + menú lateral

### 🗺️ Reporte Geográfico Interactivo (Leaflet)
- **Nuevo**: `api/reporte_geo_colaboradores.php` — API de conteos por territorio
- **Nuevo**: Pestaña "Distribución Geográfica" en reportes con mapa CartoDB Positron
- **Modificado**: `api/colaboradores.php` — Filtros municipio y barrio
- **Corregido**: `api_territorios_geojson.php` — ST_Simplify removido (no existe en MySQL hosting)

---

## [2.7.0] — 2026-06-19

### 📸 Instagram Sync: API v1 + Cookies
- **Migración**: GraphQL deprecado → API v1 `/api/v1/feed/user/{id}/` con paginación `next_max_id`
- **Nuevo**: `instagram_login.py` — Login helper con perfil Chrome real
- **Auth**: Selenium headless → Login manual + cookies persistentes
- **Parseo**: Nuevo `_item_to_publicacion()` para estructura API v1
- **Output**: 686 posts extraídos (vs 235 anteriores), desde 2018-02 hasta 2026-06-19
- **Regenerado**: `timeline_concejal.md` con posts completos
- **Subido**: `storage/maestro_instagram.json` (715 KB)

---

## [2.6.0] — 2026-06-19

### 🔧 Fix Edición Colaborador + Geografía
- **Problema**: Modal editar no preseleccionaba Departamento, Municipio, Territorio, Barrio, Puesto de Votación
- **Causa**: Tabla `colaboradores` tiene `territorio_id` pero JS buscaba `cod_mpio` inexistente
- **Fix**: JOIN `territorios` en `api/colaboradores.php` para incluir `cod_mpio`
- **Fix**: PUT→POST en `guardarAsignacionLider()` (Hostinger no soporta PUT nativo)
- **Fix**: `root_config.php` display_errors 1→0 (evita HTML en salida JSON)

---

## [2.5.0] — 2026-06-08

### 👤 Portal del Lider
- Auth 100% contra tabla `colaboradores` (`password_hash`/`verify`)
- Portal landing, dashboard, red jerárquica, perfil, eventos
- Cambio de contraseña y recuperación
- `sesiones_lideres` — sesiones independientes para líderes

### 📸 Colaborador Detalle
- Foto grande en header + grid 4 columnas
- Botón "Editar" con modal completo (cámara, archivo, Ctrl+V paste)
- Iconos w-5 con hover backgrounds, nombre redirige al detalle

---

## [2.2.0] — 2026-04-19

### 🛡️ Estabilización y Arquitectura Multi-Instancia
- Refactorización de configuración: carga segura con `if (!defined(...))`
- Eliminación de conflictos en `root_config.php`
- Redirección dinámica basada en `url()` para subcarpetas

### 💎 UI/UX
- Correcciones en dashboard (valores nulos)
- Sincronización avanzada: scripts de espejo workspace

---

## [2.1.0] — 2026-04-18

### 📊 Reportes BI Mejorados
- Dashboard KPI Global con tarjetas premium
- Reporte territorial con barras visuales
- Reporte geográfico con drill-down jerárquico
- Persistencia de estado en URLs

### 🔗 Consulta Electoral
- Enlace desde landing y dashboard
- Heatmap público (`?page=consulta_electoral`)

---

## [2.0.0] — 2026-04-18

### 📸 AratioPRO
- Captura fotográfica mobile-first (cámara + archivo)
- Identidad visual premium: header gradient azul-oro
- Glassmorphism, Lucide icons, mobile-first

---

## Versiones Anteriores (v1.x)

Documentación de versiones previas (2025-2026) disponible en:
- `Aratio-App-Mirror/documentacion/CHANGELOG.md` (historial completo)
- `Aratio-App-Mirror/mod_colab/` (documentación del módulo original)
