# Arquitectura Social CRM — Guía de Replicación

> **Propósito**: Documentar la arquitectura, patrones y componentes del módulo Social CRM para replicarlo en otros proyectos.
> **Stack**: PHP 8+ / MySQL / Alpine.js / Leaflet / Chart.js

---

## 1. Concepto General

El Social CRM integra interacciones de redes sociales (Facebook + Instagram) con una base de datos de colaboradores/contactos. El flujo central:

1. **Extracción**: APIs sociales → tablas crudas (`fb_posts`, `fb_reactions`, `fb_comments`, `ig_media`, `ig_comments`)
2. **Agregación**: Usuarios únicos → tabla de comentaristas (`fb_commenters`, `social_leads`)
3. **Matching**: Fuzzy matching por nombre de usuario → asignación a colaboradores existentes
4. **Timeline unificado**: Vista consolidada de toda la actividad social de un colaborador

---

## 2. Stack Técnico

| Componente | Tecnología | Propósito |
|-----------|-----------|-----------|
| Backend | PHP 8+ con PDO | APIs REST endpoints |
| Frontend | Alpine.js 3.x | Interactividad SPA-like |
| Mapas | Leaflet + MarkerCluster | Visualización territorial |
| Gráficos | Chart.js | Estadísticas temporales/categorías |
| API Social | Facebook Graph API v19+ | Datos de Facebook e Instagram |
| DI | Container propio (PSR-11-like) | Inyección de dependencias liviana |
| Logger | Logger propio | Logging con rotación y canales |

---

## 3. Base de Datos

### 3.1 Tablas Core

```sql
-- Posts de Facebook
fb_posts (fb_post_id UNIQUE, texto, fecha, url, tipo, likes_count, comments_count, shares_count)

-- Reacciones (likes) por usuario en cada post
fb_reactions (fb_post_id + fb_user_id UNIQUE, user_name, tipo_reaccion, colaborador_id FK)

-- Comentarios por usuario en cada post
fb_comments (fb_comment_id UNIQUE, fb_post_id, fb_user_id, user_name, texto, colaborador_id FK)

-- Comentaristas agregados (un único registro por usuario de FB)
fb_commenters (fb_user_id UNIQUE, user_name, total_reactions, total_comments, colaborador_id FK, estado ENUM)

-- Leads unificados (Facebook + Instagram)
social_leads (red + user_id UNIQUE, user_name, total_interacciones, colaborador_id FK, estado ENUM)

-- Menciones de Instagram desde captions/hashtags
ig_menciones (post_url, fecha, username, texto_contexto, categoria, colaborador_id FK)

-- Media de Instagram (desde Graph API)
ig_media (ig_media_id UNIQUE, caption, media_type, media_url, timestamp, like_count, comments_count)

-- Comentarios de Instagram (desde Graph API)
ig_comments (ig_comment_id UNIQUE, ig_media_id, username, texto, timestamp, colaborador_id FK)

-- Colaboradores (tabla principal del CRM) — columnas añadidas:
ALTER TABLE colaboradores ADD COLUMN facebook_id VARCHAR(100)
ALTER TABLE colaboradores ADD COLUMN instagram_username VARCHAR(100)
ALTER TABLE colaboradores ADD COLUMN instagram_id VARCHAR(100)
```

### 3.2 Patrón de Migraciones

- Cada migración en `database/migrations/` con prefijo de fecha: `20260630_nombre.sql`
- Todas las sentencias usan `IF NOT EXISTS` / `IF NOT EXISTS` → idempotentes
- Hook en Setup Dashboard para ejecutar migraciones pendientes

---

## 4. Capa de Negocio (Clases PHP)

### 4.1 FacebookApi (`includes/FacebookApi.php`)

**Responsabilidad**: Comunicación con Facebook Graph API + persistencia.

| Método | Función |
|--------|---------|
| `fetchPosts(limit, since)` | GET `/{pageId}/posts` con campos: message, created_time, likes.summary, comments.summary, shares |
| `fetchReactions(fbPostId)` | GET `/{postId}/reactions` — id, name, type de cada usuario |
| `fetchComments(fbPostId)` | GET `/{postId}/comments` — from.id, from.name, message |
| `syncAll(maxPosts)` | Orquestador: fetchPosts → por cada post: fetchReactions + fetchComments → updateCommenters → runAutoMatch |
| `upsertPost/reaction/comment` | INSERT ... ON DUPLICATE KEY UPDATE |

**Patrón**: Cada método upsert es privado; el flujo completo se maneja desde `syncAll()`.

### 4.2 InstagramGraphApi (`includes/InstagramGraphApi.php`)

**Responsabilidad**: Comunicación con Instagram Graph API (requiere cuenta Business/Creator).

| Método | Función |
|--------|---------|
| `fetchMedia(limit)` | GET `/{igBusinessId}/media` — id, caption, media_type, timestamp, like_count, comments_count |
| `fetchComments(igMediaId)` | GET `/{mediaId}/comments` — username, text, timestamp |
| `syncComments(maxMedia)` | Orquestador: fetchMedia → upsertMedia → fetchComments → upsertComment |
| `syncLegacyMenciones()` | Carga desde JSON legacy (`maestro_instagram.json`) a `ig_menciones` |

**Nota**: Usa el mismo `FB_PAGE_TOKEN` que FacebookApi (token unificado de Meta).

### 4.3 SocialCRM (`includes/SocialCRM.php`)

**Responsabilidad**: Matching difuso + timeline unificado + estadísticas.

| Método | Función |
|--------|---------|
| `autoMatchCommenters()` | Itera top 100 comentaristas sin match, ejecuta `matchByName()` |
| `matchByName(name)` | Split por espacios → LIKE en colaboradores → `calculateSimilarity()` |
| `calculateSimilarity(a, b)` | Max de: (a) ratio de palabras coincidentes, (b) 1 - levenshtein/maxLen |
| `assignCollaborator(table, id, colId)` | Asigna colaborador y propaga `facebook_id`/`instagram_username` |
| `getTimeline(colaboradorId)` | UNION de fb_reactions + fb_comments + ig_menciones → ORDER BY fecha DESC |
| `getStats()` | Conteos + match_rate |

### 4.4 Patrón de Matching

```
autoMatchCommenters()
  → matchByName("Juan Pérez")
    → split → firstName="Juan", lastName="Pérez"
    → SQL: WHERE nombres LIKE '%Juan%' AND apellidos LIKE '%Pérez%'
    → para cada candidato: calculateSimilarity("Juan Pérez", "Juan Pérez López")
      → wordMatch: cuantas palabras coinciden / total palabras
      → levenshtein: 1 - (levenshtein("juan pérez", "juan pérez lópez") / maxLen)
      → confidence = max(wordMatch, levenshtein) * 100
    → confidence >= 80 → auto-asignar
    → confidence >= 50 → marcar pendiente_revisión
    → confidence < 50 → ignorar
```

---

## 5. API REST (PHP)

### 5.1 Convenciones

- Endpoints en `api/` con `requireAuth()` (sesión PHP)
- Respuesta JSON: `{ "success": true, "data": {...}, "total": N }`
- Header `X-Requested-With: XMLHttpRequest` requerido (diferencia API de requests HTML)
- Nombre de acción vía `$_GET['action']`

### 5.2 Endpoints

| Archivo | Acciones Clave |
|---------|---------------|
| `api/facebook.php` | status, sync, posts, post_detail, commenters, match, suggest, auto_match, stats |
| `api/social_crm.php` | leads, timeline, stats, match_leads, instagram_menciones, instagram_stats, cargar_ig_menciones |
| `api/instagram_graph.php` | status, sync, media, comments, commenters, sync_legacy, stats |
| `api/social_territorial.php` | ig_stats, ig_geo (GeoJSON), ig_categories, ig_timeline, fb_stats, combined |

### 5.3 Paginación

```php
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;
// respuesta incluye: { total, page, pages: ceil(total/$limit) }
```

---

## 6. Frontend (Alpine.js)

### 6.1 Patrón General

Cada página es un componente Alpine.js que:
1. Se inicializa con `x-data="componente()" x-init="init()"`
2. Llama APIs vía `fetch()` con header `X-Requested-With`
3. Renderiza con Alpine directives (`x-for`, `x-show`, `x-text`, `:class`)
4. Lucide icons se refrescan con `$nextTick(() => lucide.createIcons())`

### 6.2 Componentes

| Página | Componente Alpine | Tabs/Funcionalidad |
|--------|------------------|-------------------|
| `pages/social_crm.php` | `socialCRM()` | Feed FB, Comentaristas (3 subfiltros), Leads, Match Automático, Stats |
| `pages/instagram_graph.php` | `igGraph()` | Comentarios IG, Comentaristas, Menciones Históricas |
| `pages/dashboard_territorial_social.php` | `socialTerritorial()` | Mapa Leaflet, Categorías (Chart.js), Temporal (Chart.js), Menciones, Facebook |
| `pages/colaborador_detalle.php` | — | Pestaña "Redes Sociales" con timeline unificado |
| `pages/setup.php` | `setup()` | Health check del sistema |

### 6.3 Modal de Match

```javascript
// Patrón: overlay + backdrop-blur
openMatchModal(target) {
    this.matchTarget = target;
    this.showMatchModal = true;
    this.searchResults = [];
    this.searchQuery = '';
}
searchCollaborator: debounce(async function() { ... }, 300),
async confirmMatch(colaboradorId) {
    await fetch(`api/facebook.php?action=match&commenter_id=${this.matchTarget.id}&colaborador_id=${colaboradorId}`);
}
```

### 6.4 Mapa Leaflet

```javascript
initMap() {
    if (this.map) { this.map.invalidateSize(); return; }
    this.map = L.map('map', { center: [3.45, -76.53], zoom: 12 });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.map);
    this.markers = L.markerClusterGroup().addTo(this.map);
    // markers con L.divIcon de color según categoría, tamaño según likes
}
```

---

## 7. DI Container

### 7.1 Registro de Servicios (`config/di.php`)

```php
$container->singleton(PDO::class, fn() => getDB());
$container->singleton(Logger::class, fn() => Logger::getInstance());
$container->alias('db', PDO::class);
$container->set('facebook.api', fn($c) => new FacebookApi($c->get(PDO::class)));
$container->set('social.crm', fn($c) => new SocialCRM($c->get(PDO::class)));
$container->set('instagram.graph', fn($c) => new InstagramGraphApi($c->get(PDO::class)));
```

### 7.2 Container API

| Método | Función |
|--------|---------|
| `set(id, value)` | Registra servicio o valor directo |
| `singleton(id, factory)` | Factory lazy, ejecutada una sola vez |
| `alias(alias, target)` | Alias a otro servicio registrado |
| `get(id)` | Resuelve: instancia existente → singleton → valor directo → auto-resolve vía ReflectionClass |
| `has(id)` | Verifica existencia |

---

## 8. Logger

### 8.1 API

```php
Logger::getInstance()->info('Mensaje', ['contexto' => 'valor']);
Logger::getInstance()->error('Error', ['exception' => $e->getMessage()]);
// Niveles: debug, info, warning, error, critical
// Canales: facebook.sync, instagram.sync, matching, auth, system
```

### 8.2 Características

- Singleton
- Canales (prefijo en el mensaje)
- Rotación automática cada 30 días (elimina archivos >30 días)
- Directorio: `storage/logs/`
- Nombres de archivo: `facebook.sync-2026-06-30.log`

---

## 9. Configuración

### 9.1 Constantes Requeridas (`root_config.php`)

```php
define('FB_PAGE_ID', 'edisonconcejal');       // Facebook Page username
define('FB_PAGE_TOKEN', '');                   // Page Access Token (Meta Developers)
define('FB_API_VERSION', 'v19.0');             // Versión de Graph API
define('IG_BUSINESS_ID', '');                  // Instagram Business Account ID
```

### 9.2 Obtención de Tokens

1. Meta Developers → Crear App → Producto "Facebook Login"
2. Configurar herramienta "Graph API Explorer"
3. Seleccionar página → generar Page Access Token
4. Permisos requeridos: `pages_read_engagement`, `pages_show_list`
5. Para Instagram: cuenta Business/Creator vinculada al Page

---

## 10. Cron Jobs

### 10.1 `cron/facebook_sync.php`

```
Ejecución: cada 6 horas
Hostinger: php /home/user/domains/dominio.com/public_html/.../cron/facebook_sync.php
Lógica: sincroniza últimos 7 días de posts con reacciones y comentarios
```

---

## 11. Guía de Replicación (Paso a Paso)

### Día 1 — Base
1. Crear tablas (copiar migraciones SQL, renombrar prefijos)
2. Crear `includes/FacebookApi.php` y `includes/SocialCRM.php`
3. Crear endpoint `api/facebook.php` con acción `status`
4. Crear `pages/social_crm.php` con Alpine.js básico (status check + stats)

### Día 2 — Sincronización
5. Obtener `FB_PAGE_TOKEN` desde Meta Developers
6. Implementar `fetchPosts()` + `fetchReactions()` + `fetchComments()`
7. Agregar botón "Sincronizar ahora" en frontend
8. Crear `cron/facebook_sync.php`

### Día 3 — Matching
9. Implementar `matchByName()` con fuzzy matching
10. Agregar modal de match manual en frontend
11. Implementar `autoMatchCommenters()` con umbrales de confianza

### Día 4 — Instagram
12. Migrar cuenta a Business/Creator
13. Crear `includes/InstagramGraphApi.php`
14. Crear `pages/instagram_graph.php`
15. Cargar menciones legacy si existen

### Día 5 — Dashboards
16. Crear `api/social_territorial.php` (GeoJSON para mapa)
17. Crear `pages/dashboard_territorial_social.php` (Leaflet + Chart.js)
18. Agregar pestaña "Redes Sociales" en detalle de colaborador

### Día 6 — Infraestructura
19. Implementar DI Container
20. Implementar Logger
21. Crear `api/setup.php` con health checks
22. Documentar

---

## 12. Principios de Diseño

- **APIs sin framework**: PHP vanilla con PDO, sin ORM
- **Frontend sin build step**: Alpine.js desde CDN, sin npm
- **Migraciones idempotentes**: `IF NOT EXISTS` para ejecución múltiple segura
- **Fuzzy matching sin IA**: Levenshtein + word ratio, suficiente para nombres reales
- **APIs sociales sin sesión**: Las APIs de Meta requieren token permanente, no sesión de usuario
- **Backend agnóstico**: El Container DI permite cambiar implementaciones sin tocar el frontend
- **Logging sin dependencias externas**: Logger custom en vez de Monolog para compatibilidad con hosting compartido

---

## 13. Troubleshooting

| Problema | Causa | Solución |
|----------|-------|----------|
| Graph API 401 | Token expirado | Regenerar en Graph API Explorer |
| Graph API 403 | Permisos insuficientes | Agregar `pages_read_engagement` |
| Instagram no devuelve comments | Cuenta no Business | Migrar a Creator/Business en Configuración de Instagram |
| Matching no encuentra colaboradores | Nombre muy distinto | Asignación manual vía modal |
| JSON legacy no se carga | `storage/maestro_instagram.json` no existe | Ejecutar scraper o crear archivo vacío |
| Alpine.js no renderiza | Script no cargado | Verificar CDN en `index.php` |
