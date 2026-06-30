# Landing Page — Padrinos Cali

> **Archivo**: `landing.php` (raíz del proyecto)
> **URL**: `https://padrinoscali.org/aratio/` y `https://edisongiraldo.com/aratio/`
> **Última actualización**: 2026-07-01

---

## Estructura

| Sección | ID | Contenido |
|---------|----|-----------|
| Navbar | — | Logo, navegación (Inicio, Perfil, Gestión, Plataforma, Territorio, Mapa Social), botón "Acceso Interno" |
| Hero | `#inicio` | Título, descripción, 3 CTAs (Gestión, Mapa, Sumarme), stats (seguidores, publicaciones, interacciones) |
| Stats Bar | — | 4 íconos: Red de Padrinos, Cobertura Territorial, Gestión Social, Impacto Medible |
| Perfil | `#perfil` | Descripción del programa, lista de logros, imágenes |
| Gestión | `#gestion` | 3 cards: Red de Padrinos, Formación de Líderes, Gestión Territorial |
| **Plataforma** | `#plataforma` | **NUEVO** — 4 cards de features de Aratio con iconos |
| Territorio | `#territorio` | Comunas por bloques, link a mapa interactivo |
| Footer | — | Enlaces, redes sociales, créditos |

## Sección Plataforma (nueva)

Agregada el 2026-07-01. Muestra 4 features del sistema:

| Card | Icono | Color | Features |
|------|-------|-------|----------|
| **CP — Pulso de Campaña** | `activity` | Azul | Keywords gatillo, Captura automática, Embudo medible |
| **Social CRM** | `share-2` | Púrpura | Facebook API, Instagram Graph, Fuzzy matching |
| **Portal del Líder** | `crown` | Ámbar | Rankings, Notificaciones, Feed en vivo |
| **Dashboard Territorial** | `globe` | Esmeralda | Leaflet map, Chart.js, MarkerCluster |

Cada card incluye:
- Badge de estado (Nuevo / Integración / Liderazgo / Territorio)
- Descripción de 2-3 líneas
- 3 tags tecnológicos

Al final de la sección: barra de stack tecnológico (PHP 8+, MySQL, Alpine.js, Facebook Graph API, WhatsApp Cloud API, Leaflet, Chart.js).

## CTAs principales

| Botón | Destino | Descripción |
|-------|---------|-------------|
| "Conoce mi Gestión" | `#gestion` | Scroll a gestión |
| "Ver Mapa Social" | `#territorio` | Scroll a territorio |
| **"Quiero sumarme"** | `?page=cp_captura` | **NUEVO** — Formulario de captura de CP Pulso |
| "Acceso Interno" | `login.php` | Login del sistema |
| "Ver Mapa Interactivo" | `?page=dashboard_organizaciones_publico&campana_id=2` | Mapa público |

## Diseño

- **Colores**: Primary `#1e3a5f` (azul oscuro), Secondary `#d4af37` (dorado)
- **Fuentes**: Outfit (display), Inter (body)
- **Componentes**: glass, glass-dark, card-hover (elevación en hover), gradient-text, hero-shape (clip-path)
- **Iconos**: Lucide vía CDN

## Mantenimiento

Para agregar un nuevo feature a la sección Plataforma:
1. Copiar estructura de card existente (div con clases `bg-gradient-to-br...`)
2. Cambiar icono (buscar en [lucide.dev](https://lucide.dev))
3. Cambiar gradiente de color
4. Actualizar descripción y tags

Los stats del hero son estáticos (no vienen de DB). Actualizar manualmente cuando haya nuevos datos.
