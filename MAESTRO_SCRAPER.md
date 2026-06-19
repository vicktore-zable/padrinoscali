# Maestro del Scraper de Instagram — Edison Giraldo Campaign

## Propósito
Extraer todas las publicaciones de `@edison_concejal` desde 2018 con geolocalización, métricas de engagement y clasificación política automatizada.

## Archivos

| Archivo | Descripción |
|---------|-------------|
| `instagram_scraper.py` | Script principal de scraping — API v1 + Selenium + cookies |
| `instagram_login.py` | **NUEVO** Helper de login manual para renovar cookies |
| `merge_instagram_data.py` | Fusiona corridas versionadas y backfillea coordenadas |
| `storage/maestro_instagram.json` | **Archivo maestro** con todos los posts (686 únicos) |
| `storage/instagram_data.json` | Última corrida individual |
| `storage/.cookies/instagram_cookies.json` | Cookies de sesión de Instagram (excluir de git) |
| `timeline_concejal.md` | Timeline exportado a Markdown |
| `api/instagram_sync.php` | Endpoint PHP que lanza el scraper (solo local) |
| `pages/actividad_instagram.php` | Monitor visual de campaña |

## Estructura del JSON Maestro

```json
{
  "concejal": {
    "username": "edison_concejal",
    "nombre": "Edison Alberto Giraldo",
    "ciudad": "Cali, Valle del Cauca"
  },
  "estadisticas": {
    "total_publicaciones": 686,
    "periodos_cubiertos": ["2018-02", ..., "2026-06"],
    "categorias": {"general": 420, "obra": 79, "territorio": 32, ...},
    "acciones_detectadas": {"Seguimiento de obra": 12, ...},
    "publicaciones_relevantes": 97
  },
  "timeline": {
    "2022-01": [ {post}, {post}, ... ],
    ...
  },
  "lista_acciones": [
    {
      "fecha": "2022-04-07",
      "shortcode": "CcnLA6IueII",
      "accion": "Seguimiento de obra",
      "descripcion": "...",
      "categoria": "obra",
      "relevancia": 8,
      "url": "https://www.instagram.com/p/CcnLA6IueII/",
      "ubicacion": "Siloé, Cali, Colombia",
      "lat": 3.43722,
      "lng": -76.5225,
      "timestamp_unix": 1650541868,
      "engagement": {
        "likes": 42,
        "comentarios": 3
      }
    }
  ]
}
```

## Campos de cada publicación

| Campo | Tipo | Descripción | Origen |
|-------|------|-------------|--------|
| `fecha` | string | Fecha YYYY-MM-DD | `taken_at` Unix timestamp |
| `fecha_raw` | string | Unix timestamp original | `item.taken_at` |
| `shortcode` | string | **ID único global** | `item.code` o `item.pk` |
| `texto` | string | Caption completo | `item.caption.text` |
| `url` | string | URL completa | Construida desde shortcode |
| `tipo` | string | foto/video/carrusel/reel | `item.media_type` (1/2/8) |
| `likes` | int | Número de likes | `item.like_count` |
| `comentarios` | int | Número de comentarios | `item.comment_count` |
| `categoria` | string | obra/territorio/reunion/social/... | Análisis NLP de texto |
| `accion_detectada` | string | Seguimiento de obra/Denuncia/... | Matching de keywords |
| `relevancia_politica` | int | 0-10 | Puntaje basado en keywords |
| `hashtags` | string[] | Hashtags extraídos | Regex `#\w+` |
| `menciones` | string[] | @menciones extraídas | Regex `@\w+` |

## Cómo usar `shortcode` como ID único

El `shortcode` es el identificador universal de Instagram para cada publicación. Ejemplo:

```
URL:  https://www.instagram.com/reel/DYnmjG1hZgC/
                     shortcode → DYnmjG1hZgC
```

Para almacenar en base de datos sin duplicados:
```sql
CREATE TABLE publicaciones (
    shortcode VARCHAR(20) PRIMARY KEY,
    fecha DATE,
    texto TEXT,
    lat DECIMAL(10,7),
    lng DECIMAL(10,7),
    ...
);
INSERT INTO publicaciones (...) VALUES (...)
ON DUPLICATE KEY UPDATE likes=VALUES(likes), comentarios=VALUES(comentarios);
```

## Dependencias

- Python 3.8+
- `pip install requests beautifulsoup4 selenium webdriver-manager`
- Chrome/Chromium (para Selenium)
- XAMPP local para el monitor PHP

## Cómo ejecutar

```bash
# 1. Login manual (solo si cookies expiraron — ~1-2 semanas)
python instagram_login.py
# Se abre Chrome, inicias sesión manualmente, cookies se guardan solas

# 2. Scraping completo (API v1 + Selenium para obtener user_id)
python instagram_scraper.py --username edison_concejal --max-posts 5000 --monthly

# 3. Ver en el monitor
# http://localhost/aratio/pages/actividad_instagram.php
```

## Flujo interno del scraper

```
instagram_scraper.py
  1. Carga cookies guardadas de storage/.cookies/instagram_cookies.json
  2. Abre Selenium headless → navega a Instagram (con cookies) → obtiene user_id
  3. Cierra Selenium
  4. Pagina API v1 /api/v1/feed/user/{user_id}/?count=12 usando requests
     con las cookies → ~58 páginas, 12 items cada una → 686 posts
  5. Analiza texto → categoriza (obra/territorio/reunión/…) 
  6. Calcula relevancia política (0-10)
  7. Exporta JSON + Markdown
```

## Limitaciones conocidas

1. **Login requerido**: Instagram ahora requiere sesión activa incluso para perfiles públicos. Sin cookies frescas → no hay datos.
2. **Cookies expiran**: Las cookies de sesión duran ~1-2 semanas. Ejecutar `python instagram_login.py` para renovar.
3. **Coordenadas**: La API v1 no devuelve coordenadas GPS de los posts. El merge script intenta backfill via Selenium.
4. **Solo local**: El scraper requiere Chrome + Selenium (no funciona en Hostinger).
5. **Rate limiting**: Instagram no limita la API v1 `/feed/user/` pero es recomendable esperar ~500ms entre páginas.

## Estado actual (última actualización: 2026-06-19)

- **Posts únicos**: 686
- **Con shortcode**: 686
- **Con coordenadas GPS**: 85 (backfill via merge script)
- **Acciones políticas detectadas**: 97 (lista_acciones)
- **Políticamente relevantes (≥5)**: 97
- **Cobertura**: 2018-02 → 2026-06-19
- **Meses cubiertos**: 101 meses
- **Rango**: 8+ años de gestión documentada
