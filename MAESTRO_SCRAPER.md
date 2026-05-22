# Maestro del Scraper de Instagram — Edison Giraldo Campaign

## Propósito
Extraer todas las publicaciones de `@edison_concejal` desde 2022-01-01 con geolocalización, métricas de engagement y clasificación política automatizada.

## Archivos

| Archivo | Descripción |
|---------|-------------|
| `instagram_scraper.py` | Script principal de scraping (Selenium) |
| `merge_instagram_data.py` | Fusiona corridas versionadas y backfillea coordenadas |
| `storage/maestro_instagram.json` | **Archivo maestro** con todos los posts desde 2022 (235 únicos) |
| `storage/instagram_data.json` | Última corrida individual |
| `storage/instagram_data_*.json` | Corridas versionadas históricas |
| `timeline_concejal.md` | Timeline exportado a Markdown |
| `api/instagram_sync.php` | Endpoint PHP que lanza el scraper |
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
    "total_publicaciones": 235,
    "periodos_cubiertos": ["2022-01", ..., "2026-05"],
    "categorias": {"obra": 28, "territorio": 17, ...},
    "acciones_detectadas": {"Seguimiento de obra": 5, ...},
    "publicaciones_relevantes": 38
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
| `fecha` | string | Fecha YYYY-MM-DD | `time[datetime]` del post |
| `fecha_raw` | string | Timestamp ISO8601 original | Atributo datetime |
| `shortcode` | string | **ID único global** | URL del post / página HTML |
| `texto` | string | Caption completo | `span` en página del post |
| `url` | string | URL completa | Elemento `<a>` en grid |
| `tipo` | string | foto/video/carrusel/reel | Selector video/carousel en DOM |
| `likes` | int | Número de likes | Texto "XX likes" en post |
| `comentarios` | int | Número de comentarios | Texto "XX comments" |
| `ubicacion` | string | Nombre del lugar geotagueado | `a[href*='/explore/locations/']` |
| `lat` | float | Latitud GPS | Regex `"lat":` en page source |
| `lng` | float | Longitud GPS | Regex `"lng":` en page source |
| `timestamp_unix` | int | Unix epoch seconds | Convertido de `fecha_raw` ISO |
| `categoria` | string | obra/territorio/reunion/social/... | Análisis NLP de texto |
| `accion_detectada` | string | Seguimiento de obra/Denuncia/... | Matching de keywords |
| `relevancia_politica` | int | 0-10 | Puntaje basado en keywords |
| `hashtags` | string[] | Hashtags extraídos | Regex `#\w+` |
| `menciones` | string[] | @menciones extraídas | Regex `@\w+` |
| `engagement` | dict | `{likes, comentarios}` | Métricas de interacción |

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
# Scrapeo completo (grid + reels)
python instagram_scraper.py --username edison_concejal --since-date 2022-01-01 --monthly

# Fusionar corridas versionadas
python merge_instagram_data.py

# Ver el monitor
# http://localhost/aratio/pages/actividad_instagram.php
```

## Limitaciones conocidas

1. **Instagram Grid inconsistente**: Cada corrida muestra un subset diferente de posts. Por eso se hacen múltiples corridas y se fusionan.
2. **GraphQL API caída**: Los query hashes de Instagram están desactualizados. Solo funciona Selenium directo.
3. **Coordenadas parciales**: Solo 85/235 posts tienen coordenadas — las publicaciones sin geotag no tienen lat/lng.
4. **Cookies expiran**: Las cookies de sesión duran ~1-2 semanas. Re-ejecutar con `--login` si caducan.
5. **Solo local**: El scraper requiere Chrome + Selenium (no funciona en Hostinger).

## Estado actual (última actualización: 2026-05-22)

- **Posts únicos desde 2022**: 235
- **Con coordenadas GPS**: 85
- **Con shortcode**: 235
- **Acciones políticas detectadas**: 55 (lista_acciones)
- **Políticamente relevantes (≥5)**: 38
- **Cobertura**: 2022-01-08 → 2026-05-21
- **Categorías**: obra(28), territorio(17), social(17), proyecto(15), reunion(20), gestion(8), evento(5), denuncia(5), general(120)
- **Acciones**: Seguimiento de obra(5), Denuncia pública(2), Gestión administrativa(3)
