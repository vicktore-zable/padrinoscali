# Control de Versiones — Aratio

> **Formato:** SemVer (vMAYOR.MENOR.PARCHE)
> **Repo:** `H:\Mi unidad\2026\Cali\Edisongiraldo.com` (git)
> **Producción:** `https://padrinoscali.org/aratio/`

---

## Versión Actual

| Campo | Valor |
|-------|-------|
| **Versión** | v2.11.0 |
| **Fecha** | 2026-06-29 |
| **Estado** | Producción |
| **Commit** | *(actual)* |
| **Feature** | Phone Banking: campañas, cola, panel agente, historial, stats |

## Siguiente Versión

| Campo | Valor |
|-------|-------|
| **Próxima** | **v2.12.0** |
| **Feature** | Email Campaigns |
| **Estado** | En planificación (ver `01-estrategia/FASE2_ROADMAP.md`) |

---

## Histórico de Versiones

| Versión | Fecha | Feature Principal | Commit |
|:-------:|:-----:|-------------------|:------:|
| **v2.11.0** | 2026-06-29 | Phone Banking (campañas, cola, panel, historial) | *(actual)* |
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
