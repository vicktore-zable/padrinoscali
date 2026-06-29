# Changelog — Aratio

Todas las versiones notables del proyecto se documentan aquí.

Formato basado en [Keep a Changelog](https://keepachangelog.com/).
Versiones siguen [SemVer](https://semver.org/).

---

## [2.9.0] — No publicada

### 🚧 Planeado
- **WhatsApp Cloud API (Meta)**: Reemplazar WATI como provider primario ($0/mes). Mensajería bidireccional, webhook para recibir mensajes, broadcast segmentado, plantillas aprobadas.
- **Workflow Engine**: Sistema de automatización con 5 triggers predefinidos (registro, evento próximo, evento finalizado, donación, inactividad). Acciones: WhatsApp, cambio de estado, asignación de líder.
- **Timeline Unificado**: Tabla `actividad_colaborador` que consolida todas las interacciones (eventos, donaciones, WhatsApp, cambios de estado) en el perfil del colaborador.

📖 Ver plan completo en `docs/01-estrategia/PLAN_FASE1.md`

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
