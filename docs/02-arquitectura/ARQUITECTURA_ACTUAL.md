# Arquitectura Actual — Aratio / Padrinos Cali

> **Versión:** v3.0.0 | **Fecha:** 2026-07-08 | **Proyecto:** Padrinos Cali — Sistema de Gestión Social

---

## 1. Diagrama de Alto Nivel

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        USUARIOS (Frontend)                              │
│  ┌──────────┐  ┌──────────────┐  ┌──────────────────┐  ┌───────────┐  │
│  │ Público  │  │ Líder Social │  │ Admin Campaña    │  │ Candidato │  │
│  │ Visitante│  │ (Portal)     │  │ (Panel Aratio)   │  │ (Consulta)│  │
│  └────┬─────┘  └──────┬───────┘  └────────┬─────────┘  └─────┬─────┘  │
└───────┼───────────────┼───────────────────┼──────────────────┼─────────┘
        │               │                   │                  │
        ▼               ▼                   ▼                  ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                        CAPA DE RUTEO (index.php)                        │
│  ┌──────────┐ ┌──────────────┐ ┌───────────────┐ ┌──────────────────┐  │
│  │ ?page=   │ │ Portal Pages │ │ Mod_* Bridges  │ │ .htaccess Rules │  │
│  │ routing  │ │ (require)    │ │ (include)      │ │ (rewrites)      │  │
│  └──────────┘ └──────────────┘ └───────────────┘ └──────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘
        │               │                   │                  │
        ▼               ▼                   ▼                  ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                     CAPA DE VISTAS (pages/*.php)                        │
│  ┌──────────┐ ┌──────────────┐ ┌───────────────┐ ┌──────────────────┐  │
│  │ Admin    │ │ Portal Líder │ │ Módulos       │ │ Público          │  │
│  │ 50 pages │ │ 17 pages     │ │ mod_* (6)     │ │ registro*.php    │  │
│  └──────────┘ └──────────────┘ └───────────────┘ └──────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘
        │               │                   │                  │
        ▼               ▼                   ▼                  ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                  CAPA API REST (api/*.php — 57 endpoints)                │
│  ┌──────────┐ ┌──────────────┐ ┌───────────────┐ ┌──────────────────┐  │
│  │ CRUD     │ │ Dashboards   │ │ Comunicación  │ │ Social CRM      │  │
│  │ Core     │ │ & Reportes   │ │ WhatsApp/Email│ │ FB/IG           │  │
│  └──────────┘ └──────────────┘ └───────────────┘ └──────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘
        │               │                   │                  │
        ▼               ▼                   ▼                  ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                  CAPA DE NEGOCIO (includes/*.php — 19 clases)            │
│  ┌──────────┐ ┌──────────────┐ ┌───────────────┐ ┌──────────────────┐  │
│  │ Auth     │ │ DatabaseMgr  │ │ WhatsAppApi   │ │ SocialCRM        │  │
│  │ Logger   │ │ CacheManager  │ │ WorkflowEngine │ │ FacebookApi      │  │
│  │ Helpers  │ │ RateLimiter  │ │ PhoneBanking  │ │ InstagramGraph   │  │
│  └──────────┘ └──────────────┘ └───────────────┘ └──────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘
        │               │                   │                  │
        ▼               ▼                   ▼                  ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                       CAPA DE DATOS (MySQL PDO)                         │
│               ┌─────────────────────────────────────┐                   │
│               │  DB: u577647812_aratio (Hostinger)  │                   │
│               │  ~42 tablas, 3 vistas, 2 triggers   │                   │
│               └─────────────────────────────────────┘                   │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Stack Tecnológico

| Componente | Tecnología | Versión | Notas |
|---|---|---|---|
| **Backend** | PHP | 8.x | Sin framework principal, arquitectura propia |
| **Base de Datos** | MySQL (MariaDB) | 10.x | Hostinger — InnoDB, utf8mb4 |
| **Frontend Reactividad** | Alpine.js | 3.x | CDN v3.14.8 |
| **CSS** | Tailwind CSS | 3.x | CDN v3.4.17 |
| **Gráficos** | Chart.js | 4.x | CDN |
| **Mapas** | Leaflet | 1.x | CDN |
| **Iconos** | Lucide Icons | CDN |
| **QR** | QRCode.js | CDN |
| **Autenticación** | Sesiones PHP + JWT | — | Sesión basada en cookies |
| **DI Container** | Container.php | Propietario | Inyección básica de dependencias |
| **Logging** | Logger.php + Monolog (parcial) | Propietario |
| **Caché** | CacheManager.php | Archivos planos | Directorio `/cache/` |
| **Rate Limiter** | RateLimiter.php | Archivos planos | 100 req/min por IP |
| **Módulos MVC** | mod_colab, mod_lider | Propietario | Router + Controller + Model + View |
| **Despliegue** | Scripts Python/PowerShell | — | ~80 scripts de diagnóstico/upload |

---

## 3. Estructura de Directorios (Producción en Hostinger)

```
/home/u577647812/domains/padrinoscali.org/public_html/aratio/
├── index.php                    ← ROUTER PRINCIPAL (574 líneas)
├── root_config.php              ← CONFIG PRINCIPAL (148 líneas)
├── config/
│   ├── config.php               ← Config secundaria
│   └── di.php                   ← DI container setup
├── pages/                       ← 50 vistas del panel admin
│   ├── dashboard.php
│   ├── colaboradores.php
│   ├── colaborador_detalle.php
│   ├── colaboradores_red.php
│   ├── donaciones.php
│   ├── eventos.php
│   ├── reportes.php
│   ├── whatsapp_log.php
│   ├── portal_*.php               ← 17 vistas del portal líder
│   └── ... (50 archivos total)
├── api/                         ← 57 endpoints REST
│   ├── colaboradores.php
│   ├── campanas.php
│   ├── eventos.php
│   ├── whatsapp.php
│   ├── facebook.php
│   ├── instagram_graph.php
│   ├── ... (57 archivos total)
├── includes/                    ← 19 clases núcleo
│   ├── DatabaseManager.php
│   ├── Auth.php
│   ├── CacheManager.php
│   ├── WhatsAppApi.php
│   ├── WorkflowEngine.php
│   ├── SocialCRM.php
│   ├── ... (19 archivos total)
├── mod_colab/                   ← Módulo Colaboradores (MVC completo)
│   ├── src/Controllers/         ← 18 controladores
│   ├── src/Models/             ← 12 modelos
│   ├── src/Views/              ← ~60 vistas
│   ├── src/Middleware/          ← 5 middlewares
│   └── routes/web.php
├── mod_lider/                   ← Módulo Portal Líder (MVC)
│   ├── src/Controllers/         ← 2 controladores
│   ├── src/Models/             ← 13 modelos
│   └── src/Views/portal/       ← ~15 vistas
├── mod_elecciones/              ← Consulta Electoral
├── mod_jac/                     ← Juntas de Acción Comunal
├── mod_organizaciones/          ← Organizaciones Sociales
├── mod_diaD/                    ← Módulo Día D
├── cron/                        ← 6 scripts programados
│   ├── birthday_check.php
│   ├── cp_scan.php
│   ├── facebook_sync.php
│   ├── whatsapp_broadcast.php
│   ├── email_processor.php
│   └── workflow_processor.php
├── database/migrations/         ← 15 archivos SQL
├── TERRITORIOS/                 ← 16+ archivos GeoJSON/SQL
├── storage/                     ← JSON, logs, uploads
├── login.php                    ← Login de administradores
├── logout.php
├── landing.php                  ← Landing page pública
├── registro-lider.php           ← Registro público de líderes
├── registro_simpatizante.php    ← Registro público de simpatizantes
├── cp_captura.php               ← Captura CP Pulso de Campaña
└── .htaccess                    ← Reglas de rewrite
```

---

## 4. Patrón de Ruteo

### 4.1 Flujo de `index.php` (Router Principal)

```
GET /aratio/?page=colaboradores

1. require_once config/config.php              ← Carga configuración
2. require_once root_config.php (si existe)    ← Overrides de instancia
3. $pagina = $_GET['page'] ?? 'dashboard'      ← Determina página
4. Si page está en $portalPages:
     require_once pages/{page}.php             ← SIN layout Aratio
     exit
5. Si page = cp_captura → cp_captura.php       ← Público, sin auth
6. requireAuth() para páginas protegidas        ← Verifica sesión
7. Obtiene campaña activa (GET > session > default)
8. Renderiza layout HTML completo:
   - Header con logo, selector campaña, avatar
   - Sidebar con navegación (15 secciones)
   - Main: include pages/{$pagina}.php
9. Footer con scripts (Alpine, Tailwind, Chart.js, Leaflet)
```

### 4.2 Páginas del Panel Admin (25 páginas whitelisted)

```
dashboard, colaboradores, colaboradores_red, colaborador_detalle,
colaboradores_reportes, donaciones, eventos, acciones, compromisos,
reportes, grupos, elecciones, candidatos, campanas, usuarios,
ayuda, configuracion, registro_asistencia, organizaciones,
whatsapp_log, whatsapp_messages, workflows, llamadas, emails,
dashboard_territorial
```

### 4.3 Páginas del Portal Líder (render SIN layout Aratio)

```
portal_landing, portal_login, portal_auth, portal_red,
perfil_lider (MISSING - CAUSA FATAL ERROR),
dashboard_lider (MISSING - CAUSA FATAL ERROR),
portal_dashboard, portal_registrar_simpatizante,
portal_eventos, portal_change_password, portal_perfil
```

### 4.4 Redirección a Módulos

```
?page=consulta_electoral       → mod_elecciones/public/index.php
?page=public_elecciones*       → mod_elecciones/public/index.php
?page=dashboard_organizaciones_publico → mod_organizaciones/dashboard.php
```

---

## 5. Base de Datos (~42 tablas)

### 5.1 Dominios de Datos

| Dominio | Tablas | Tabla Central |
|---|---|---|
| **Core** | usuarios, campanas, colaboradores, territorios | `colaboradores` (23 columnas, 7 índices) |
| **Elecciones** | elecciones, grupos_politicos, candidatos, puestos_votacion | `candidatos` |
| **Eventos** | eventos, asistencia_eventos | `eventos` |
| **Comunicación** | whatsapp_log, whatsapp_conversaciones, whatsapp_mensajes, whatsapp_plantillas, whatsapp_broadcast_queue, email_campanas, email_cola, email_log, email_plantillas, llamadas_campanas, llamadas_cola, llamadas_log | `whatsapp_conversaciones` |
| **Social CRM** | fb_posts, fb_reactions, fb_comments, fb_commenters, social_leads, ig_media, ig_comments, ig_menciones | `social_leads` |
| **Captura** | cp_triggers, cp_capture_flow | `cp_capture_flow` |
| **Workflow** | workflow_reglas, workflow_log, workflow_acciones_pendientes | `workflow_reglas` |
| **Auditoría** | historial_estados, historial_cambios_lider, actividad_colaborador | `actividad_colaborador` |
| **Perfiles** | curriculum | `curriculum` |

### 5.2 Vistas SQL (3 definidas)

| Vista | Propósito |
|---|---|
| `v_colaboradores_red` | Colaboradores con red jerárquica (seguidores, info del líder) |
| `v_colaboradores_completo` | Colaboradores con nombre del líder y total seguidores |
| `v_estadisticas_campana` | Estadísticas agregadas por campaña (totales, promedios, estados) |

### 5.3 Triggers (2 definidos)

| Trigger | Evento | Propósito |
|---|---|---|
| `calcular_grupo_etareo_asistencia` | BEFORE INSERT en asistencia_eventos | Calcula grupo etario desde fecha_nacimiento |
| `calcular_grupo_etareo_asistencia_update` | BEFORE UPDATE en asistencia_eventos | Recalcula grupo etario |

---

## 6. Módulos Funcionales

### 6.1 Estado por Módulo

| Módulo | Estado | Arquitectura | APIs |
|---|---|---|---|
| **Gestión de Colaboradores** | ✅ Completo | MVC (mod_colab) + flat pages | CRUD completo, geografía, reportes |
| **Portal del Líder** | ✅ Completo | MVC (mod_lider) + bridge pages | Auth, dashboard, red, perfil, eventos |
| **Campañas** | ✅ Completo | Flat pages + API | CRUD, asociación candidatos, asignación usuarios |
| **Eventos** | ✅ Completo | Flat pages + API | CRUD, asistencia, registro QR |
| **Donaciones** | ✅ Completo | Flat pages + API | CRUD, reportes |
| **WhatsApp (Cumpleaños)** | ✅ v2.8.0 | Flat pages + WATI API | Envío manual/cron, histórico, stats |
| **ALAS Comunicaciones** | ✅ v2.9.0 | Clases + APIs + cron | WhatsApp Cloud, Workflows, Timeline |
| **Phone Banking** | ✅ v2.11.0 | API + Clases | Cola de llamadas, resultados, stats |
| **Email Campaigns** | ✅ v2.12.0 | API + PHPMailer | Campañas, plantillas, tracking apertura |
| **Dashboard Territorial** | ✅ v2.13.0 | Leaflet + Chart.js + APIs | 16 KPIs, mapa GeoJSON, gráficos |
| **Social CRM** | ✅ v2.15.0 | APIs + Clases | FB/IG fetch, match automático, leads |
| **CP Pulso de Campaña** | ✅ v2.14.0 | MessengerBot + captura | Triggers, DMs, formulario captura |
| **Interactividad en Vivo** | ✅ v2.10.0 | Polling 15-30s | Toast, badges, timeline en vivo |
| **Consulta Electoral** | ✅ Completo | mod_elecciones standalone | Filtros, dashboard público |
| **JAC** | ⚠️ Parcial | mod_jac standalone | Dashboard, grupos de interés |
| **Día D** | ⚠️ Parcial | mod_diaD standalone | Dashboard de logística electoral |
| **Organizaciones** | ⚠️ Parcial | mod_organizaciones standalone | CRUD básico, dashboard público |

### 6.2 Estado de Conexiones Externas

| Integración | Estado | Credenciales |
|---|---|---|
| **WATI WhatsApp API** | 🟡 Config pendiente | `WATI_API_KEY` = vacío |
| **Facebook Graph API** | 🟡 Config pendiente | `FB_APP_ID` = vacío, `FB_PAGE_TOKEN` = vacío |
| **Instagram Graph API** | 🟡 Config pendiente | `IG_BUSINESS_ID` = vacío |
| **SMTP Hostinger (Email)** | 🟡 Config pendiente | `SMTP_PASS` = placeholder |
| **WhatsApp Cloud API (Meta)** | 🟡 Config pendiente | Tokens y webhook sin configurar |
| **Instagram Scraper** | ✅ Histórico (cookies) | Login manual cada ~2 semanas |

---

## 7. Problemas Identificados

### 7.1 Críticos

| # | Problema | Archivo | Impacto |
|---|---|---|---|
| C1 | `perfil_lider` no existe pero está en `$portalPages` | `index.php:7` | **Fatal Error** al acceder `?page=perfil_lider` |
| C2 | `dashboard_lider` no existe pero está en `$portalPages` | `index.php:7` | **Fatal Error** al acceder `?page=dashboard_lider` |
| C3 | `.htaccess` referencia `mapa_territorios.php` inexistente | `.htaccess` | **Infinite loop** en Hostinger |

### 7.2 Altos

| # | Problema | Archivo | Impacto |
|---|---|---|---|
| A1 | `display_errors=1` en páginas públicas | `registro-lider.php:8`, `registro_simpatizante.php:7` | Exposición de errores PHP/SQL |
| A2 | APIs sin sesión_name() explícito | `api/asistencia_eventos.php:214` | Sesiones con nombre por defecto |
| A3 | WATI/FB/IG APIs cableadas en UI sin credenciales | `pages/whatsapp_log.php`, `pages/instagram_graph.php` etc. | Botones/funcionalidad inactivos |
| A4 | 11 archivos huérfanos en `pages/` | Varios | Código muerto, confusión de mantenimiento |

### 7.3 Medios

| # | Problema | Impacto |
|---|---|---|
| M1 | Sin migraciones para tablas core (usuarios, donaciones, sesiones_lideres) | Difícil replicar esquema desde cero |
| M2 | Sin tests unitarios automatizados (solo 2 tests PHPUnit) | Riesgo de regresiones |
| M3 | Logging híbrido (Logger.php + MonoLog parcial) | Inconsistencia en formato de logs |
| M4 | Caché basada en archivos sin invalidación automática | Datos obsoletos en dashboard |
| M5 | `mod_colab` y `pages/colaboradores.php` duplican funcionalidad | Código redundante |

---

## 8. Recomendaciones Iniciales

| Prioridad | Acción | Sprint |
|---|---|---|
| 🔴 | Fix `perfil_lider` y `dashboard_lider` (crear o remover) | Sprint 2 |
| 🔴 | Remover regla obsoleta `mapa_territorios.php` del `.htaccess` | Sprint 2 |
| 🟡 | Deshabilitar `display_errors` en páginas públicas | Sprint 2 |
| 🟡 | Estandarizar `session_name()` en todas las APIs | Sprint 2 |
| 🟡 | Documentar migraciones faltantes (usuarios, donaciones) | Sprint 1 |
| 🟢 | Limpiar páginas huérfanas en `pages/` | Sprint 2 |
| 🟢 | Completar configuración WATI, FB, IG, SMTP | Sprint 4 |

---

*Documento generado por auditoría del sistema — 2026-07-08*