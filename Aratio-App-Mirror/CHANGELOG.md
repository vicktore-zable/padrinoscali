# Changelog - Aratio v2.0.0

Todas las actualizaciones destacadas del proyecto se documentan en este archivo.

---

## [2.15.0] - 2026-06-30
### 🌐 Social CRM: Facebook + Instagram → CRM
- **Facebook Graph API** (`includes/FacebookApi.php`): Fetch posts, reacciones (con quién), comentarios (con quién), shares desde `facebook.com/edisonconcejal`
- **Match Automático** (`includes/SocialCRM.php`): Algoritmo fuzzy matching por nombre (Levenshtein + word match) entre usuarios FB y colaboradores CRM
- **5 tablas nuevas**: `fb_posts`, `fb_reactions`, `fb_comments`, `fb_commenters`, `social_leads`, `ig_menciones`
- **API endpoints**: `api/facebook.php` (8 endpoints: status, sync, posts, post_detail, commenters, match, suggest, stats), `api/social_crm.php` (leads, timeline, IG mentions, Instagram carga masiva)
- **Panel Admin** (`pages/social_crm.php`): 5 tabs Alpine.js (Feed FB, Comentaristas, Leads, Match, Stats) + modal asignación a colaborador + sincronización manual
- **Instagram Mentions**: 452 menciones históricas cargadas desde JSON a DB (`ig_menciones`)
- **Cron**: `cron/facebook_sync.php` para sincronización cada 6h
- **Sidebar**: Nueva sección "Social CRM" con acceso a Redes Sociales, Monitor Digital, Mapa de Gestión, Líderes
- **Constantes**: `FB_PAGE_ID`, `FB_PAGE_TOKEN`, `FB_API_VERSION` en `root_config.php`

---

## [2.14.0] - 2026-06-30
### 👤 Líder 2.0: Ranking, Feed y API Dedicada
- **API de Líderes** (`api/lideres.php`): 5 endpoints (ranking, detalle, feed, notificaciones, global stats)
- **Ranking de Líderes**: Tabla ordenable por 4 métricas (seguidores, eventos, contactadas, donaciones) en `pages/lideres.php`
- **Modal Detalle**: Carga en paralelo seguidores + perfiles + feed de actividad + notificaciones del líder
- **Feed en Portal del Líder**: Sección "Actividad Reciente" en dashboard con polling 30s, carga más, iconos por tipo
- **Notificaciones**: Cumpleaños próximos, nuevos seguidores, actividad del equipo en últimos 7 días
- **Sincronización**: Integración con el portal líder existente sin modificar routing

---

## [2.2.0] - 2026-04-19
### 🛡️ Estabilización y Arquitectura Multi-Instancia
- **Refactorización de Configuración**: Implementada carga segura en `config/config.php` (Core) mediante bloques `if (!defined(...))` para permitir sobreescritura desde `root_config.php`.
- **Eliminación de Conflictos**: Limpieza profunda de `root_config.php` para eliminar redeclaraciones de funciones y lógica de sesión redundante, resolviendo el Error 500 crítico.
- **Redirección Dinámica**: Migración de rutas estáticas (`/`) a rutas dinámicas basadas en `url()` en `Auth.php` y `login.php`, garantizando compatibilidad con despliegues en subcarpetas (ej: `/aratio/`).

### 💎 Pulido de Interfaz y UX
- **Correcciones en Dashboard**: Añadido soporte para valores nulos (`?? 0`) en funciones de formato numérico de `dashboard.php` para evitar advertencias de PHP.
- **Enlace Maestro**: Actualizado el botón "Consulta Electoral" para apuntar correctamente al servidor de inteligencia externo.
- **Sincronización Avanzada**: Creación de scripts de espejo (`mirror_local_to_workspace.py`) para mantener el Workspace de Google Drive sincronizado con el desarrollo local.

---

## [2.1.0] - 2026-04-18
### 📊 Reportes BI Mejorados
- **Dashboard KPI Global**: Tarjetas premium con gradientes (colaboradores, líderes, mujeres, hombres, eventos, organizaciones)
- **Navegación Moderna**: Tabs estilo pill con icons
- **Reporte Territorial**: Tablas con barras visuales de progreso, distribución por departamento/municipio/perfil
- **Reporte Geográfico**: Drill-down jerárquico en cascada completo (Departamento → Municipio → Barrio → Colaboradores)
- **Persistencia de Estado**: URLs mantienen el estado del filtro (reporte, depto, mpio, barrio)
- **Mejoras UI**: Card con header gradient, sombras, border-radius

### 🔗 Módulo Consulta Electoral
- **Enlace desde Landing**: Botón "Consulta Elecciones" conecta a heatmap público (?page=consulta_electoral)
- **Enlace desde Dashboard**: Nuevo ítem en sidebar "Consulta Electoral" → abre en nueva pestaña
- **Evita Conflicto**: Naming diferenciado (?page=elecciones vs ?page=consulta_electoral)

### 👥 Colaboradores: Mejoras en Formularios
- **Perfil Profesional**: Sección idéntica a registro-lider (Formación Académica, Experiencia Laboral, Habilidades)
- **Búsqueda Puesto de Votación**: Tipo-ahead con 2+ letras (antes 3+), búsqueda case-insensitive
- **Unificación NIVELES_PARTICIPACION**: Agregado "Contratista"

### 🎨 Identidad Visual AratioPRO
- **Header Gradient**: Azul (#1e3a5f) → Oro (#d4af37)
- **Diseño Mobile-First**: Full-width, responsive
- **Glassmorphism**: Efecto glass en tarjetas
- **Iconos Lucide**: Consistencia visual

---

## [2.0.0] - 2026-04-18
### 📸 Colaboradores: Capa de Identidad Visual (AratioPRO Local)
- **Captura Fotográfica Mobile-First**: Implementada funcionalidad para que líderes y colaboradores puedan tomarse una foto directamente desde la cámara del dispositivo móvil o subirla desde sus archivos.
- **Identidad Visual Premium**:
    - Previsualización en vivo ("En Vivo" badge) de la cámara mediante Alpine.js y la API web de `getUserMedia`.
    - La tabla del módulo principal ahora muestra avatares reales o iniciales elegantes si no hay foto disponible.
- **Backend Optimizado**: Lógica PHP dedicada para interpretar, guardar y asociar imágenes en Base64 de forma eficiente y segura en el servidor.

### 🌟 Portal JAC: Evolución Geo & Branding
- **Rebranding de Entidades**: Migración global de la terminología "JAC" a **"Entidades / Organizaciones"** para una mayor versatilidad del módulo.
- **Dashboard Geo 2.0 (Premium)**:
    - **Burbujas Dinámicas**: Implementación de marcadores estilo burbuja con colores por categoría y contadores de agrupación.
    - **Panel de Información Enriquecido**: Popups rediseñados que incluyen **Presidente, Dirección, Email, Observaciones, Meta Votos y Afiliados**.
    - **Terminología Geográfica**: Actualización de etiquetas a **"Comuna / Corregimiento"** en filtros y visualización.
- **Mejoras en la API de Mapas**: Actualizado el endpoint `api/jac.php?action=mapa` para entregar metadatos completos y valores por defecto robustos.
- **UI/UX Premium**: Implementación de efectos *glassmorphism*, iconos Lucide integrados y transiciones suaves en el mapa.

### 🛠️ Ajustes Técnicos
- **Limpieza de Código**: Eliminación de fragmentos de código huérfanos y optimización del renderizado de capas Leaflet en el Dashboard.
- **Consistencia de Estilos**: Unificación de la estética entre el mapa de administración (`grupos_de_interes.php`) y el dashboard público (`dashboard.php`).

---

## [1.7.0] - 2026-03-18
### 🗺️ Mejoras en Visor Territorial (Mapa de Puestos)
- **Buscador en Tiempo Real**: Implementada búsqueda instantánea en el sidebar para filtrar puestos de votación y barrios por texto (nombre, dirección, comuna).
- **Filtrado Jerárquico por Comuna**: Los puestos de votación ahora se filtran automáticamente al seleccionar un Sector/Comuna en los desplegables.
- **Información Extendida (Popups)**: Las ventanas informativas de los marcadores ahora muestran el Sector/Comuna correspondiente.
- **API v2 de Puestos**: Actualizada la API para soportar parámetros de búsqueda y retornar metadatos geográficos completos.

### 🏢 Módulo Grupos de Interés
- **Heredabilidad de Visor**: Clonada la arquitectura de mapas en `mod_jac/grupos_de_interes.php` como base para el seguimiento de actores estratégicos.
- **Integración JAC**: Vinculado al Dashboard de administración del portal JAC.

---

## [1.6.0] - 2026-03-17
### 🚀 Portal Inteligente JAC (Juntas de Acción Comunal)
- **Acceso Híbrido**: Nueva arquitectura que separa la consulta pública de la administración privada.
- **Dashboard Standalone**: Implementada la vista `/JAC?view=dashboard` como portal público, permitiendo a cualquier ciudadano visualizar las juntas en el mapa sin login.
- **Login Independiente**: Creado un portal de acceso seguro en `mod_jac/login.php` con estética premium, integrado con la autenticación central de Aratio.
- **Gestión Privada**: La sección `?view=gestion` queda protegida, permitiendo la administración de planchas y miembros solo a usuarios autorizados.
- **Manejo de Jaimito**: Configurado usuario de prueba `jaimito@tangamandapio.com` con rol de administrador para la campaña.
- **Generación Demo**: Implementado sistema de carga masiva de datos con 10 JACs y 14 planchas de demostración en barrios reales de Cali.

### 🛠️ Mejoras Técnicas
- **Alpine.js Optimization**: Corregidos errores de llaves duplicadas en bucles `x-for` en los dropdowns territoriales.
- **Sidebar Dinámica**: La barra lateral del portal JAC ahora muestra u oculta secciones según el estado de autenticación.
- **Logout Integrado**: Acción de cierre de sesión añadida directamente al portal JAC.
- **Geolocalización**: Mejorada la jerarquía geográfica del mapa para filtrar por Municipio, Sector y Territorio Valle.

---

## [1.5.0] - 2026-03-16
### ✨ Módulo JAC (Base)
- Implementación inicial del sistema de Juntas de Acción Comunal.
- Creación de API REST CRUD para JACs.
- Primera versión del Visor Cartográfico Basado en Leaflet.
