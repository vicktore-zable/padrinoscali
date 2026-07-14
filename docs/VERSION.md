# Control de Versiones — Aratio

> **Formato:** SemVer (vMAYOR.MENOR.PARCHE)
> **Repo:** `H:\Mi unidad\2026\Cali\Edisongiraldo.com` (git)
> **Producción:** `https://padrinoscali.org/aratio/`

---

## Versión Actual

| Campo | Valor |
|-------|-------|
| **Versión** | v2.18.1 |
| **Fecha** | 2026-07-12 |
| **Estado** | Producción |
| **Commit** | `619ac48` |
| **Feature** | Sidebar colapsable, Dashboard Territorial standalone, fixes BI Hub (loading, notificaciones, nivel_participacion) |

## Siguiente Versión

| Campo | Valor |
|-------|-------|
| **Próxima** | **v2.19.0** |
| **Feature** | *(por definir)* |
| **Estado** | Por definir |

---

## Histórico de Versiones

| Versión | Fecha | Feature Principal | Commit |
|:-------:|:-----:|-------------------|:------:|
| **v3.0.0** | 2026-06-30 | Refactor: Logger, Container DI, PHPUnit, Social CRM completo | *(actual)* |
| **v2.18.1** | **2026-07-12** | **Sidebar colapsable, Dashboard Territorial standalone, fixes BI Hub** | `58490f4` |
| **v2.18.0** | **2026-07-12** | **Panorama BI Hub: distribuciones + top rankings** | `6b00ac3` |
| **v2.17.1** | **2026-07-12** | **Dashboard v2: fix sin_trabajo, charts barrio/comuna, tabbed map 4 capas, remover ALAS/Recientes** | **(deployed)** |
| **v2.17.0** | 2026-07-11 | Dashboard Territorial: Semáforo Zonas de Trabajo + KPIs | *(local)* |
| **v2.16.0** | 2026-07-11 | Zonas Trabajo: Vista Unificada Líder + Botón Recarga GeoJSON | *(local)* |
| **v2.15.0** | 2026-06-30 | Social CRM: Facebook API + match automático + IG mentions | `214d5c5` |
| **v2.14.0** | 2026-06-30 | Líder 2.0 (ranking, feed actividad, notificaciones, API líderes) | `2d77755` |
| **v2.13.0** | 2026-06-29 | Dashboard Territorial (KPIs + gráficos + mapa Leaflet + ALAS) | `6feab7b` |
| **v2.12.0** | 2026-06-29 | Email Campaigns (campañas, segmentación, plantillas, tracking) | `94b03ec` |
| **v2.11.0** | 2026-06-29 | Phone Banking (campañas, cola, panel, historial) | `2f2e568` |
| **v2.10.0** | 2026-06-29 | Interactividad en Vivo (polling, badges, toast) | `8ec9c06` |
| **v2.9.0** | 2026-06-29 | ALAS: Comunicaciones Inteligentes + Workflows + Timeline | `16e447a` |
| **v2.8.0** | 2026-06-28 | Reporte Geográfico Interactivo (Leaflet) | `e4f9b17` |
| **v2.7.0** | 2026-06-19 | Instagram Sync: API v1 + Cookies (686 posts) | `0741e2a` |
| **v2.6.0** | 2026-06-19 | Fix Edición Colaborador + Geografía | `0438683` |
| **v2.5.0** | 2026-06-08 | Portal del Lider + Auth colaboradores | *(en CLAUDE.md)* |
| **v2.3.0** | 2026-06-08 | Colaborador detalle con foto + edición | *(en CLAUDE.md)* |
| **v2.2.0** | 2026-04-19 | Estabilización Multi-Instancia | *(pre-git)* |
| **v2.1.0** | 2026-04-18 | Reportes BI, Consulta Electoral | *(pre-git)* |
| **v2.0.0** | 2026-04-18 | Identidad Visual AratioPRO | *(pre-git)* |
| **v1.x** | 2025-2026 | Versiones legacy (mod_colab) | *(pre-git)* |

---

## Ramas

| Rama | Propósito |
|------|-----------|
| `master` | Producción — estable, desplegada en padrinoscali.org |
| *(feature branches locales)* | Desarrollo de features específicas |

## Política de Versionado

1. **MAYOR** (v3.0.0): Cambios arquitectónicos mayores, breaking changes
2. **MENOR** (v2.9.0): Nuevas features, mejoras significativas
3. **PARCHE** (v2.8.1): Bugfixes, hotfixes, cambios menores

### Flujo de release

```
master (producción)
  ↑ merge
feature/xxx (desarrollo local)
  → pruebas en XAMPP
  → deploy a padrinoscali.org
  → tag + changelog
```
