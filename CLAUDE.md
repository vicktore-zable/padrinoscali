# CLAUDE.md — Constitución del Agente: Padrinos Cali

*Versión: 2.18.0 | Proyecto: padrinoscali.org | Cali, Colombia*

---

## 📝 Principios Core

- **Multi-Agent Orchestration**: Tareas complejas son manejadas por agentes especializados (Estratega, Comunicador, Auditor).
- **Worker-Critic Loop**: Ningún contenido de campaña se publica sin pasar revisión independiente (Quality Gate ≥ 80).
- **Evidence-Based**: Todas las propuestas deben estar respaldadas por datos reales (estadísticas electorales, censos, PDM Cali).
- **Idioma**: Español para comunicación y reportes; inglés para código y especificaciones técnicas.
- **Confidencialidad**: Las credenciales de base de datos y SSH están en `DEPLOY_EDISONGIRALDO.md` — NUNCA exponer en código.

---

## 🎯 Contexto del Proyecto

- **Cliente**: Padrinos Cali — Programa de Liderazgo Social
- **Plataforma**: Sistema de Gestión Social (PHP + MySQL) desplegado en Hostinger
- **URL Producción**: https://padrinoscali.org/aratio/ (migrado 2026-06-01)
- **URL Legacy**: https://edisongiraldo.com/aratio/
- **Stack**: PHP 8+, MySQL, Alpine.js / Vanilla JS, CSS moderno

---

## 📂 Estructura del Proyecto

```
edisongiraldo.com/
├── CLAUDE.md                    ← Esta constitución
├── DEPLOY_EDISONGIRALDO.md      ← Credenciales (NO compartir)
├── Aratio-App-Mirror/           ← Espejo sincronizado del código XAMPP
│   ├── DIAGNOSTICO_SINCRONIZACION.md
│   ├── pages/api/includes/cron/config/
│   ├── mod_*/                   ← 7 módulos activos
│   ├── documentacion/
│   │   ├── *.md                 ← 34 docs útiles (31 migrados a docs/)
│   │   └── archived/            ← 24 docs obsoletos
│   └── sync-xampp-to-mirror.sh  ← Script de sincronización
├── config/
│   └── config.php               ← Configuración principal
├── docs/                        ← Documentación organizada por temas
│   ├── 00-INDEX.md             ← Índice maestro (actualizado 2026-07-11)
│   ├── CHANGELOG.md            ← Changelog unificado
│   ├── VERSION.md              ← Control de versiones
│   ├── 01-estrategia/          ← Planes y roadmaps
│   ├── 02-arquitectura/        ← Diagramas, DB, APIs
│   ├── 03-despliegue/          ← Deploy, credenciales, mantenimiento
│   ├── 04-modulos/             ← Docs por módulo (migrados de documentacion/)
│   ├── 05-integraciones/       ← Instagram, WhatsApp
│   ├── 06-reportes/            ← Auditorías, diagnósticos
│   ├── 07-sesiones/            ← Notas de sesiones
│   ├── 08-guias/              ← How-to guides (8 docs)
│   ├── 09-referencia/          ← Design system, specs (12 docs)
│   └── versiones/              ← Snapshots por versión
├── *.py                       ← Scripts de diagnóstico/auditoría/deploy
└── production_index.html      ← Homepage estática de producción

# Desarrollo Local (XAMPP)
F:\xampp2\htdocs\aratio\
├── pages/                    ← Vistas Alpine.js
├── api/                      ← Endpoints backend
├── includes/                 ← Clases núcleo
├── cron/                     ← Tareas programadas (birthday_check)
├── config/config.php         ← Config local (apunta a DB remota)
└── mod_*/                   ← 7 módulos activos

# Producción
https://padrinoscali.org/aratio/
├── index.html                ← Página estática (landing)
├── index.php                 ← Router de la app (sincronizado con XAMPP)
├── login.php / landing.php   ← App activa
├── pages/whatsapp_log.php    ← Módulo cumpleaños (idéntico a XAMPP)
└── api/*.php                 ← Endpoints funcionales
```

---

## 🤖 Agentes Disponibles


| Agente          | Rol                                       | Activar con                  |
| --------------- | ----------------------------------------- | ---------------------------- |
| `estratega`     | Análisis territorial y social Cali        | Decisiones estratégicas      |
| `comunicador`   | Mensajes de campaña y contenido           | Generación de contenido      |
| `auditor`       | Critic — validación de contenido          | Revisión de todo output      |
| `desarrollador` | Código PHP/MySQL/JS                       | Tareas técnicas del sistema  |

---

## 🛠️ Comandos del Sistema

```bash
# Ver notebooks NotebookLM
py notebook_agent.py list

# Registrar notebook de campaña
py notebook_agent.py alias edisongiraldo <NOTEBOOK_ID>

# Investigación orquestada
py notebook_agent.py research "Análisis territorial Comuna X Cali"

# Revisión crítica de contenido
py notebook_agent.py review <archivo_o_texto>

# === Sincronización XAMPP → Mirror ===
bash Aratio-App-Mirror/sync-xampp-to-mirror.sh

# === Instagram Sync ===
# Login manual (cuando cookies expiren — ~1-2 semanas)
python instagram_login.py

# Scraping completo (@edison_concejal)
python instagram_scraper.py --username edison_concejal --max-posts 5000 --monthly
```

---

## 🎯 Estado del Proyecto

- **Versión**: v2.15.0 (WhatsApp Birthday + Zonas Trabajo + CRUD)
- **Git**: ✅ Inicializado (3 repos: XAMPP, workspace root, Aratio-App-Mirror)
- **Producción**: https://padrinoscali.org/aratio/ ✅ (DNS propagado)
- **Legacy**: https://edisongiraldo.com/aratio/
- **Preview**: https://gold-whale-298635.hostingersite.com/aratio/
- **Login Admin+Líder**: https://padrinoscali.org/aratio/login.php
- **Login Portal Líder**: https://padrinoscali.org/aratio/index.php?page=portal_landing
- **XAMPP local**: F:\xampp2\htdocs\aratio\
- **DB**: u577647812_aratio (única, compartida entre dominios)
- **DB Host local**: `82.197.82.47` (IP directa, DNS no resuelve localmente)
- **Auth**: Admin via email+pass (usuarios), Líder via doc+tel (colaboradores + sesiones_lideres)

---

## 📋 Changelog

### v2.18.0 (2026-07-12) — Panorama BI Hub: distribuciones + top rankings

**Problema**: Panorama solo mostraba 3 charts básicos. Sin distribución por rol/estado/género/nivel_participación, ni top líderes/territorios/barrios.

**Solución**: Rediseño del tab Panorama con cards de distribución, charts de top rankings y renombramiento perfil→rol.

| Archivo | Cambio |
|---------|--------|
| `api/bi.php` | `handlePanorama`: retorna `distribuciones` (rol, estado, genero, nivel_participacion), `topLideres` (top 10), `topTerritorios` (top 10), `topBarrios` (top 10) |
| `pages/bi.php` | Cards Rol/Estado/Género, badges Nivel Participación, charts Top 10 Territorios/Barrios/Líderes Directos |

### v2.17.0 (2026-07-11) — Dashboard Territorial: Semáforo Zonas de Trabajo

**Problema**: El mapa del Dashboard Territorial solo mostraba polígonos de municipios. No había visibilidad de cobertura de zonas de trabajo ni indicadores de líderes con/sin trabajo social.

**Solución**: Nuevo endpoint `zonas_semaforo` que retorna GeoJSON de zonas de trabajo con colores semáforo (verde≥5, amarillo 3-4, rojo 1-2 responsables) + KPIs de líderes con/sin trabajo social. Capa de zonas superpuesta en el mapa con leyenda, tooltip y popup. Botón de recarga solo para la capa de zonas.

| Archivo | Cambio |
|---------|--------|
| `api/dashboard.php` | Nuevo `action=zonas_semaforo`: GeoJSON agrupado por territorio+barrio con `n_responsables`, colores semáforo, KPIs `lideres_total/con_trabajo/sin_trabajo` |
| `pages/dashboard_territorial.php` | 3 KPI cards (Líderes Totales, Con Trabajo Social, Sin Trabajo Social), capa polígonos semáforo, leyenda inline, botón recarga zonas, `agregarCapaZonas()`, `recargarZonasSemaforo()` |

**Deploy**: 2 archivos a padrinoscali.org y edisongiraldo.com.

**Notas técnicas**:
- Semáforo: ≥5 responsables = verde, 3-4 = amarillo, 1-2 = rojo
- Capa zonas se superpone a capa municipios (municipios en púrpura claro, zonas en semáforo)
- Botón recarga elimina y recrea solo la capa de zonas (no la de municipios)
- `fillOpacity: 0.35` para ver polígonos de municipios debajo

### v2.17.1 (2026-07-12) — Dashboard v2: Filtros + fix conteos + tabbed map

**Problema**: "Sin Trabajo Social" mostraba número incorrecto. ALAS y Actividad Reciente ocupaban espacio sin datos relevantes. Sin visibilidad de compromisos, eventos, acciones ni Instagram en el mapa.

**Solución**: Fix conteos (sin trabajo social = total - con trabajo), remover ALAS/Recientes, nuevo tabbed map con 4 capas temáticas (Compromisos, Eventos, Acciones, Instagram) lazy-load.

| Archivo | Cambio |
|---------|--------|
| `api/dashboard.php` | `handleZonasSemaforo`: `sin_trabajo = total - conTrabajo` (sin restar admin); nueva `handleOtrosMapas`: 4 GeoJSON (compromisos, eventos, acciones, instagram) con paletas de colores distintas y `$getGeoJSON()` helper |
| `pages/dashboard_territorial.php` | Removidas ALAS + Actividad Reciente (HTML + Alpine `alas`/`recientes`/`iconoActividad`/`tiempoRelativo`); nueva sección tabbed map con 4 tabs y mapa Leaflet compartido; Alpine: `tabOtros`, `otrosMapa`, `otrosLayer`, `otrosData`, `otrosCargados`, `loadOtrosMapas()`, `initMapaOtros()`, `cargarOtrosMapa()`, `mostrarCapaOtros()`, `cambiarTab()` |

**Deploy**: 2 archivos a padrinoscali.org.

### v2.16.0 (2026-07-11) — Zonas de Trabajo: Vista Unificada Líder + Botón Recarga GeoJSON

**Problema**: Un líder no veía las zonas de trabajo de sus colaboradores en una vista unificada. El mapa Leaflet no tenía botón de recarga de capa geoespacial. Los datos se duplicaban cuando múltiples personas cubrían el mismo barrio.

**Solución**: API `con_seguidores` ahora desduplica por `territorio_id|barrio`, agrega array `responsables[]` por zona y retorna `tipo_display` (propia/seguidor/mixta). Mapa Leaflet con control personalizado de recarga solo para capa GeoJSON (no tabla). Botón "Recargar" general en el header de la pestaña.

| Archivo | Cambio |
|---------|--------|
| `api/zonas_trabajo.php` | `con_seguidores`: desduplicación, `responsables[]`, `geometry_json`, `tipo_display` |
| `pages/colaborador_detalle.php` | Template: badges separados, tabla responsables, mapa con control recarga. JS: `inicializarMapaZonas()`, `recargarZonas()`, zoom 11 (Cali), solo cargar en tab click |
| `index.php` (raíz + Aratio-App-Mirror) | CSS `.leaflet-control-custom.rotating` animación spin |

**Deploy**: 3 archivos a padrinoscali.org y edisongiraldo.com.

**Notas técnicas**:
- Desduplicación: `key = territorio_id + '|' + barrio`. Primera zona define `tipo_display`
- Contadores `propias`/`seguidores` usan datos SIN desduplicar (cuentan asignaciones reales)
- Botón Leaflet recarga SOLO capa GeoJSON (no tabla/stats)
- Botón "Recargar" UI recarga TODO (tabla + mapa + stats)
- `loadZonasTrabajo()` solo se ejecuta al clickear tab (no en `loadColaborador()`)
- Mapa centrado en Cali `[3.4516, -76.5320]` zoom 11
- `fitBounds(pad(0.1))` ajusta vista a todas las zonas

### v2.15.0 (2026-07-08) — Fix Tab Zonas + CRUD Completo + WhatsApp Redesign

**Problema**: Tab Zonas de Trabajo y Redes Sociales no se mostraban — `<div>` de Actividad nunca se cerraba.

**Solución**: `</div>` agregado en línea 660. Además, Zonas de Trabajo ahora tiene vista completa con tabla, stats, mapa, empty state, modal CRUD y precarga jerárquica async al editar.

| Archivo | Cambio |
|---------|--------|
| `pages/colaborador_detalle.php` | Fix HTML (Actividad tab cerrado); tabla+stats+mapa+empty+toast+precarga+barrios_v2 |
| `api/zonas_trabajo.php` | `con_seguidores`, PUT con territorio_id, `_method=PUT`, barrios_v2 |
| `api/territorios.php` | Nueva action `detalle_territorio` |
| `pages/whatsapp_log.php` | Rediseño tarjetas cumpleaños (grid, countdown, gradiente) |
| `api/whatsapp.php` | fecha_exacta + dias_faltantes |

**Deploy**: 5 archivos subidos a padrinoscali.org y edisongiraldo.com via SSH key.

**Notas técnicas**:
- Hostinger no soporta PUT nativo → usar POST con `_method=PUT`
- `barrios_v2` retorna `{id, barrio}` en vez de strings planos
- `zonaPrecargarEdicion()` carga 5 niveles jerárquicos en secuencia async
- La precarga evita que Alpine sobrescriba valores al cargar selectores hijos
- SSH key auth funciona con `~/.ssh/id_ed25519` en puerto 65002
- PQ key exchange warnings son informativos, ignorar

### v2.7.0 (2026-06-19) — Instagram Sync: API v1 + Cookies

**Problema**: El scraper de Instagram (`instagram_scraper.py`) usaba GraphQL `query_hash` (API deprecada que ya no devuelve JSON) y Selenium scrolling. Instagram ahora redirige a login incluso para perfiles públicos. Datos congelados desde mayo 2026 (235 posts).

**Solución**: Migración a API v1 oficial de Instagram + login manual con cookies.

| Cambio | Detalle |
|--------|---------|
| API | GraphQL deprecado → `/api/v1/feed/user/{id}/` con paginación `next_max_id` |
| Auth | Selenium headless (bloqueado) → Login manual + cookies persistentes |
| Parseo | Nuevo `_item_to_publicacion()` para estructura de API v1 |
| Script | Nuevo `instagram_login.py` — helper de login con perfil Chrome |
| Output | 686 posts extraídos (vs 235 anteriores), desde 2018-02 hasta 2026-06-19 |

**Archivos modificados**:

| Archivo | Cambio |
|---------|--------|
| `instagram_scraper.py` | API v1 pagination + cookie loading + `_item_to_publicacion()` |
| `instagram_login.py` | **NUEVO** — Login helper con perfil Chrome real |
| `timeline_concejal.md` | Regenerado con 686 posts |
| `storage/maestro_instagram.json` | Subido a producción (715 KB) |
| `storage/instagram_data.json` | Subido a producción (715 KB) |

**Flujo actual**:
```
1. python instagram_login.py   ← Abre Chrome con perfil real, guarda cookies
2. python instagram_scraper.py  ← Carga cookies, extrae user_id via Selenium,
                                   pagina API v1 con requests (~58 páginas)
3. Output: storage/instagram_data.json + timeline_concejal.md
4. Subir a producción: maestro_instagram.json
```

**Notas técnicas**:
- Instagram ahora bloquea scraping sin sesión activa (incluso perfiles públicos)
- Las cookies de Instagram duran ~1-2 semanas antes de expirar
- API v1 `/feed/user/` devuelve 12 items por página, paginación con `next_max_id`
- Perfil de Chrome se usa para login manual (evita detección de bot)
- `--monthly` ya no es necesario (API v1 obtiene todos los posts de una sola pasada)
- `--visible` opcional para debuggear; por defecto headless

### v2.6.0 (2026-06-19) — Fix Edición Colaborador + Geografía

**Problema**: El modal Editar del colaborador no preseleccionaba Departamento, Municipio, Territorio, Barrio ni Puesto de Votación.

**Causa raíz**: La tabla `colaboradores` tiene columna `territorio_id` (FK), pero el JS buscaba `cod_mpio` que no existía en la respuesta de la API.

**Archivos modificados**:

| Archivo | Cambio |
|---------|--------|
| `root_config.php` | `display_errors` de `1` a `0` (evita HTML en salida JSON) |
| `api/colaboradores.php` | JOIN `territorios` para incluir `cod_mpio` + fallback por `departamento+municipio` + PUT acepta `cod_mpio` |
| `pages/colaborador_detalle.php` | `initListasGeograficas()` carga 6 listas en paralelo + preselección correcta |
| `pages/colaboradores.php` | PUT→POST en `guardarAsignacionLider()` |

**Notas técnicas**:
- Hostinger no soporta PUT nativo → usar POST + `_method=PUT`
- `display_errors=1` en PHP rompe JSON → siempre `0` en producción
- `X-Requested-With: XMLHttpRequest` requerido para que `requireAuth()` retorne JSON 401 en vez de redirect HTML

### v2.8.0 (2026-06-28) — Sistema de Cumpleaños por WhatsApp

**Feature**: Automatización de mensajes de felicitación por WhatsApp a colaboradores en su cumpleaños.

| Archivo | Tipo | Descripción |
|---------|------|-------------|
| `database/migrations/20260628_whatsapp_birthday.sql` | NUEVO | Migración tabla `whatsapp_log` |
| `includes/WhatsAppApi.php` | NUEVO | Clase con templates, envío WATI y logging |
| `cron/birthday_check.php` | NUEVO | Script diario (ejecutar 8 AM en Hostinger) |
| `api/whatsapp.php` | NUEVO | Endpoints: cumpleaños, historial, reenviar, stats |
| `pages/whatsapp_log.php` | NUEVO | Panel admin Alpine.js con 3 secciones |
| `root_config.php` | MODIFICADO | Constantes WATI_API_KEY, WATI_API_URL |
| `config/config.php` | MODIFICADO | Fallback whatsapp config |
| `index.php` | MODIFICADO | Ruta `whatsapp_log` + menú lateral |

**Cron Hostinger** (configurar en Panel Hostinger → Cron Jobs):
```
0 8 * * * php /home/u577647812/domains/padrinoscali.org/public_html/aratio/cron/birthday_check.php
```

**Panel Admin**: `https://padrinoscali.org/aratio/index.php?page=whatsapp_log`
- Sección 1: Calendario de cumpleaños (hoy/semana/mes/personalizado) con envío manual
- Sección 2: Historial de envíos (paginado, filtros por estado/fecha)
- Sección 3: Estadísticas (enviados hoy, mes, tasa de éxito, próximos 7 días)

**WATI**: Configurar `WATI_API_KEY` y `WATI_NUMBER` en `root_config.php` antes de usar.

### v2.5.0 — Portal del Líder
