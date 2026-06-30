# v2.15.0 — Social CRM: Facebook → CRM + Instagram Territorial

## Arquitectura

```
Facebook Graph API ──→ fb_scraper.php ──→ DB ──→ CRM Match ──→ Timeline
                          (PHP cURL, cron)       │                   
Instagram API v1 ──→ JSON (existente) ────────→ Análisis territorial
```

## APIs por red

| Red | API | Datos obtenidos | Limitación |
|-----|-----|----------------|------------|
| **Facebook** | Graph API v19+ | Posts, reacciones CON usuario, comentarios CON usuario, shares | Token Page con `pages_read_engagement` |
| Instagram | API v1 (actual) | Captions, menciones, likes count, comentarios count, ubicación | **No** da quién like ni quién comenta |

Facebook es la fuente primaria de interacciones sociales con usuarios reales.

## Tablas nuevas

```sql
fb_posts        — Posts de Facebook (id, fb_post_id, texto, fecha, url, tipo, likes_count, comments_count, shares_count, metadata)
fb_reactions    — Reacciones con usuario (fb_post_id, fb_user_id, user_name, tipo_reaccion, fecha, colaborador_id)
fb_comments     — Comentarios con usuario (fb_post_id, fb_comment_id, fb_user_id, user_name, texto, fecha, colaborador_id)
fb_commenters   — Perfiles de usuarios FB no identificados (fb_user_id, user_name, total_interacciones, ultima_interaccion, colaborador_id, estado)
social_leads    — Leads de redes sociales (red, user_id, user_name, email, telefono, total_interacciones, colaborador_id, estado)
```

## Componentes

| Archivo | Tipo | Propósito |
|---------|------|-----------|
| `database/migrations/20260630_social_crm.sql` | NUEVO | Crear 5 tablas + índices |
| `includes/FacebookApi.php` | NUEVO | Clase: fetchPosts, fetchReactions, fetchComments, refreshToken, isConfigured |
| `api/facebook.php` | NUEVO | Endpoints: sync, stats, posts, commenters, leads |
| `api/social_crm.php` | NUEVO | MatchByName, matchLeads, getTimeline, getStats |
| `pages/social_crm.php` | NUEVO | Panel admin Alpine.js: 5 tabs (FB Feed, Comentaristas, Leads, Match, Stats) |
| `cron/facebook_sync.php` | NUEVO | Sincronización diaria (cada 6h) |
| `root_config.php` | MOD | Constantes FB_APP_ID, FB_PAGE_ID, FB_PAGE_TOKEN |
| `index.php` | MOD | Ruta `social_crm` + sidebar |

## Flujo de datos

1. **Facebook Graph API** → `FacebookApi::fetchRecentPosts()` → `fb_posts`
2. Por cada post → `fetchReactions(fb_post_id)` → `fb_reactions` (upsert por fb_user_id + fb_post_id)
3. Por cada post → `fetchComments(fb_post_id)` → `fb_comments` (upsert por fb_comment_id)
4. **Match**: `SocialCRM::matchByName(user_name)` → busca en colaboradores por:
   - Coincidencia exacta de nombres
   - Levenshtein < 30%
   - Email match
5. Match ≥ 80% → asigna `colaborador_id` automáticamente
6. Match 50-79% → `social_leads` estado `pendiente_revision`
7. Sin match → `social_leads` estado `nuevo`
8. **ActivityLogger**: Cuando se asigna match → `social_fb_reaction` / `social_fb_comment`
9. **Instagram**: Ya existente en JSON, se consume para territorial

## Panel Admin (social_crm.php)

- **Feed FB**: Timeline de posts de Facebook con reacciones y comentarios expandibles
- **Comentaristas**: Usuarios FB que interactúan, ranking por frecuencia, filtro sin match
- **Leads**: Lista de usuarios no identificados, botón "Asignar a colaborador" → modal búsqueda
- **Match**: Sugerencias automáticas con slider de confianza
- **Stats**: Totales: posts, reacciones, comentarios, match rate, leads pendientes

## Instagram Territorial

Instagram permanece sin cambios en scraping. Los datos JSON (686 posts, categorías, ubicaciones) se integran como fuente secundaria para:
- Mapa de gestión (ya existe `pages/mapa_instagram.php`)
- Monitor Digital (ya existe `pages/actividad_instagram.php`)
- Dashboard Territorial social (sección combinada IG+FB)

## Token Setup

Admin crea FB App en developers.facebook.com → Page Token → configura en root_config.php:
```php
define('FB_APP_ID', '');
define('FB_PAGE_ID', 'edisonconcejal');
define('FB_PAGE_TOKEN', '');
```
Panel muestra instrucciones si token está vacío.
