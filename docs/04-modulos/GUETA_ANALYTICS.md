# Guetá Analytics — Documento Maestro

> *Guetá* (muysccubun) = casa / territorio
> *Guetá Analytics* = "lo que piensa el territorio"
> **Social Policy Intelligence** — De la tendencia social al proyecto de gobierno

---

## 1. Concepto

**¿Qué es?** Un sistema que monitorea redes sociales de un gobernante/candidato, clasifica sus publicaciones por tema de gestión pública, calcula cuáles generan más engagement ciudadano, y traduce las tendencias en proyectos de acuerdo / planes de gobierno.

**¿Qué resuelve?** Hoy las redes se usan para comunicar, no para gobernar. Guetá Analytics cierra el círculo: escuchar lo que la gente aplaude o reclama → priorizar agenda → proponer proyectos con evidencia.

**¿Quién lo usa?**
- El propio concejal/alcalde (priorizar gestión)
- El equipo de comunicaciones (qué contenido funciona)
- El equipo legislativo (qué proyectos presentar)
- La ciudadanía (transparencia, rendición de cuentas)

---

## 2. Arquitectura de Información

### Pipeline de datos

```
REDES SOCIALES
    │
    ▼
EXTRACCIÓN (ETL)
    ├── Instagram (maestro_instagram.json legacy + Graph API)
    ├── Facebook (Graph API — posts, reactions, comments)
    └── Twitter/X (opcional futuro)
    │
    ▼
MOTOR DE CLASIFICACIÓN
    ├── Por tema de gestión pública
    │   (infraestructura, educación, salud, seguridad, movilidad,
    │    espacio público, desarrollo económico, cultura, medio ambiente,
    │    bienestar social)
    ├── Por engagement score
    │   (likes + comments + shares / seguidores * 100)
    └── Por sentimiento (positivo, negativo, neutro — NLP básico)
    │
    ▼
RANKING DE TENDENCIAS
    ├── Top temas con más engagement
    ├── Evolución temporal (sube/baja)
    ├── Distribución territorial (qué comuna menciona qué)
    └── Brecha: lo que más publico vs lo que más engagea
    │
    ▼
BANCO DE PROYECTOS
    ├── Cada tendencia → propuesta de proyecto
    ├── Estado (propuesta, en estudio, radicado, aprobado)
    └── Evidencia: publicaciones que lo respaldan
```

### Modelo de datos

```
Tema
  id, nombre, slug, descripcion, activo

Publicacion (importada de redes)
  id, red (ig/fb), post_id, texto, fecha, url, tipo,
  likes, comentarios, shares, ubicacion, lat, lng, raw_json

Clasificacion
  id, publicacion_id, tema_id, subtema, relevancia (0-10),
  sentimiento, confianza

Tendencia
  id, tema_id, periodo, total_posts, total_likes,
  total_comentarios, engagement_rate, variacion_porcentual

Proyecto
  id, tema_id, titulo, descripcion, estado,
  fecha_creacion, evidencias (json con urls de posts)

Ranking
  id, fecha, tema_id, posicion, engagement_score,
  tendencia (sube/baja/estable)
```

### Datos que ya existen de Aratio (reutilizables)

| Dato | Origen | Ubicación |
|------|--------|-----------|
| 686 posts de Instagram | Scraper legacy | `storage/maestro_instagram.json` |
| Posts + reacciones FB | Graph API (cuando haya token) | Tablas `fb_posts`, `fb_reactions` |
| IG comments con username | Graph API | Tabla `ig_comments` |
| Menciones geolocalizadas | Scraper legacy | Tabla `ig_menciones` |
| Categorías (obra, evento, reunion, etc.) | Clasificación scraper | Dentro del JSON |
| Relevancia política (0-10) | Clasificación scraper | Dentro del JSON |

### Lo que hay que construir nuevo

| Componente | Descripción |
|-----------|-------------|
| Clasificador por tema de gestión pública | NLP + reglas para mapear posts a temas |
| Engagement score normalizado | Fórmula que pondera likes, comments, shares por alcance |
| Trend ranking automático | Cálculo periódico de qué sube y baja |
| Banco de proyectos | CRUD de propuestas vinculadas a tendencias |
| Dashboard público | Next.js con ranking, charts, mapa |
| Reportes exportables | PDF mensual de tendencias |

---

## 3. Tecnología

### Stack recomendado

| Capa | Tecnología | Por qué |
|------|-----------|---------|
| **Backend** | Python (FastAPI) | NLP nativo, pandas, ecosistema data science |
| **Frontend** | Next.js + Tailwind + Chart.js | Dashboard moderno, SSR, exportable a PDF |
| **Base de Datos** | PostgreSQL | Analytics queries, JSONB para datos flexibles |
| **Despliegue** | Vercel (frontend) + Railway/Render (backend) | Sin server management |
| **Workers** | Celery / cron | Clasificación batch de posts |
| **APIs externas** | Facebook Graph API, Instagram Basic Display API | Mismos tokens que Aratio |

### ¿Por qué no PHP?

| Aratio (PHP) | Guetá Analytics (Python) |
|-------------|--------------------------|
| CRM transaccional | Analytics + ML |
| CRUD de colaboradores | Clasificación NLP + ranking |
| ERP de campaña | Business Intelligence público |
| Legacy (funciona) | Greenfield (moderno, independiente) |

### Componentes reutilizables de Aratio

- `includes/InstagramGraphApi.php` → lógica de Graph API (se porta a Python)
- `includes/FacebookApi.php` → lógica FB (se porta a Python)
- `storage/maestro_instagram.json` → 686 posts listos para alimentar motor
- Clasificación scraper existente (obra, evento, territorio) → seed para taxonomía
- `includes/SocialCRM.php` fuzzy matching → opcional si se integra con Aratio

---

## 4. Funcionalidades por Fase

### Fase 1 — Dashboard de Tendencias (MVP)

```
┌─────────────────────────────────────────────────┐
│  Guetá Analytics — Dashboard Público            │
├─────────────────────────────────────────────────┤
│  [7 días] [30 días] [12 meses] [Todo]           │
├──────────┬──────────┬──────────┬───────────────┤
│  Top tema │ Engagement│ Posts    │ Proyectos     │
│  Infra    │  12.4%    │   86     │    3          │
├──────────┴──────────┴──────────┴───────────────┤
│  Ranking de Temas (barras horizontales)          │
│  ████████ Infraestructura  86 posts  12.4%      │
│  ██████   Educación        54 posts  9.8%       │
│  ████     Seguridad        32 posts  7.2%       │
│  ███      Salud            21 posts  5.1%       │
├─────────────────────────────────────────────────┤
│  Evolución Temporal (línea: engagement x mes)    │
├─────────────────────────────────────────────────┤
│  Mapa de Temas por Comuna                        │
└─────────────────────────────────────────────────┘
```

### Fase 2 — Banco de Proyectos

```
┌─────────────────────────────────────────────────┐
│  Tendencia → Proyecto                            │
├─────────────────────────────────────────────────┤
│  🏗️ Infraestructura    Engagement: 12.4%        │
│  ├── Propuesta: "Programa de mantenimiento      │
│  │    de vías terciarias en Comunas 1-6"        │
│  ├── Evidencia: 23 posts                        │
│  ├── Estado: 📝 En estudio                      │
│  └── Ver proyectos relacionados →              │
├─────────────────────────────────────────────────┤
│  📚 Educación          Engagement: 9.8%         │
│  ├── Propuesta: "Bibliotecas digitales móviles  │
│  │    para zonas rurales"                       │
│  ├── Evidencia: 15 posts                        │
│  └── ...                                        │
└─────────────────────────────────────────────────┘
```

### Fase 3 — Reportes Exportables

- Informe mensual de tendencias (PDF)
- Ranking de temas vs engagement
- Comparativa entre periodos
- Propuestas generadas desde tendencias top

---

## 5. Logo Prompt

Para DALL·E / Midjourney / generador de imágenes:

```
A minimalist logo for an analytics platform called "Guetá Analytics". The design combines:
1. A stylized waveform / heartbeat line that transitions into a topographical map contour
2. A geometric "G" that doubles as a data dashboard grid
3. Colors: deep indigo (#1a1a3e) and warm gold (#d4a843)
4. Clean, modern, sans-serif typography
Style: Flat vector, minimal, tech-forward, suitable for dark and light mode.
Overall feel: analytical yet organic, indigenous roots meets data science.
```

---

## 6. Roadmap

| Semana | Fase | Entregable |
|--------|------|-----------|
| 1-2 | Fundación | Repositorio, modelo datos, ETL JSON legacy → PostgreSQL |
| 3-4 | Clasificación | Motor de temas + engagement score + ranking |
| 5-6 | Frontend | Dashboard público (ranking + evolución temporal) |
| 7-8 | Proyectos | Banco de proyectos + relación tendencia→propuesta |
| 9-10 | Integración | Live sync Graph API + reportes exportables |
| 11-12 | Launch | Documentación, deploy, demo |

---

## 7. Preguntas Pendientes

1. ¿Dashboard público o privado?
2. ¿Arrancar solo con Instagram (686 posts listos) o esperar Facebook?
3. ¿Proyectos manuales o generación automática vía NLP?
4. ¿Solo Edison o SaaS para otros políticos/entidades?
