# CLAUDE.md — Constitución del Agente: Padrinos Cali

*Versión: 2.6.0 | Proyecto: padrinoscali.org | Cali, Colombia*

---

## 📝 Principios Core

- **Multi-Agent Orchestration**: Tareas complejas son manejadas por agentes especializados (Estratega, Comunicador, Auditor).
- **Worker-Critic Loop**: Ningún contenido de campaña se publica sin pasar revisión independiente (Quality Gate ≥ 80).
- **Evidence-Based**: Todas las propuestas deben estar respaldadas por datos reales (estadísticas electorales, censos, PDM Cali).
- **Idioma**: Español para comunicación y reportes; inglés para código y especificaciones técnicas.
- **Confidencialidad**: Las credenciales de base de datos y SSH están en `DEPLOY_EDISONGIRALDO.md` — NUNCA exponer en código.

---

## 🎯 Contexto del Proyecto

- **Cliente**: Padrinos Cali — Programa de Liderazgo Social
- **Plataforma**: Sistema de Gestión Social (PHP + MySQL) desplegado en Hostinger
- **URL Producción**: https://padrinoscali.org/aratio/ (migrado 2026-06-01)
- **URL Legacy**: https://edisongiraldo.com/aratio/
- **Stack**: PHP 8+, MySQL, Alpine.js / Vanilla JS, CSS moderno

---

## 📂 Estructura del Proyecto

```
edisongiraldo.com/
├── CLAUDE.md                    ← Esta constitución
├── DEPLOY_EDISONGIRALDO.md      ← Credenciales (NO compartir)
├── .gitignore
├── config/
│   └── config.php              ← Configuración principal
├── *.py                       ← Scripts de diagnóstico/auditoría
├── *_content.txt               ← Contenidos extraídos del remoto
└── quality_reports/          ← Reports auditados

# Desarrollo Local (XAMPP)
F:\xampp2\htdocs\aratio\
├── api/                      ← Endpoints backend
├── pages/                    ← Vistas Alpine.js
├── includes/                 ← Clases núcleo
├── config/
│   └── config.php            ← Config local (apunta a DB remota)
└── mod_*/                   ← Módulos adicionales
```

---

## 🤖 Agentes Disponibles


| Agente          | Rol                                       | Activar con                  |
| --------------- | ----------------------------------------- | ---------------------------- |
| `estratega`     | Análisis territorial y social Cali        | Decisiones estratégicas      |
| `comunicador`   | Mensajes de campaña y contenido           | Generación de contenido      |
| `auditor`       | Critic — validación de contenido          | Revisión de todo output      |
| `desarrollador` | Código PHP/MySQL/JS                       | Tareas técnicas del sistema  |

---

## 🛠️ Comandos del Sistema

```bash
# Ver notebooks NotebookLM
py notebook_agent.py list

# Registrar notebook de campaña
py notebook_agent.py alias edisongiraldo <NOTEBOOK_ID>

# Investigación orquestada
py notebook_agent.py research "Análisis territorial Comuna X Cali"

# Revisión crítica de contenido
py notebook_agent.py review <archivo_o_texto>
```

---

## 🎯 Estado del Proyecto

- **Versión**: v2.5.0 (Portal del Líder — Mejoras Visuales + Perfil Independiente)
- **Git**: ✅ Inicializado
- **Producción**: https://padrinoscali.org/aratio/ ✅ (DNS propagado)
- **Legacy**: https://edisongiraldo.com/aratio/
- **Preview**: https://gold-whale-298635.hostingersite.com/aratio/
- **Login Admin+Líder**: https://padrinoscali.org/aratio/login.php
- **Login Portal Líder**: https://padrinoscali.org/aratio/index.php?page=portal_landing
- **XAMPP local**: F:\xampp2\htdocs\aratio\
- **DB**: u577647812_aratio (única, compartida entre dominios)
- **DB Host local**: `82.197.82.47` (IP directa, DNS no resuelve localmente)
- **Auth**: Admin via email+pass (usuarios), Líder via doc+tel (colaboradores + sesiones_lideres)

---

## 📋 Changelog

### v2.6.0 (2026-06-19) — Fix Edición Colaborador + Geografía

**Problema**: El modal Editar del colaborador no preseleccionaba Departamento, Municipio, Territorio, Barrio ni Puesto de Votación.

**Causa raíz**: La tabla `colaboradores` tiene columna `territorio_id` (FK), pero el JS buscaba `cod_mpio` que no existía en la respuesta de la API.

**Archivos modificados**:

| Archivo | Cambio |
|---------|--------|
| `root_config.php` | `display_errors` de `1` a `0` (evita HTML en salida JSON) |
| `api/colaboradores.php` | JOIN `territorios` para incluir `cod_mpio` + fallback por `departamento+municipio` + PUT acepta `cod_mpio` |
| `pages/colaborador_detalle.php` | `initListasGeograficas()` carga 6 listas en paralelo + preselección correcta |
| `pages/colaboradores.php` | PUT→POST en `guardarAsignacionLider()` |

**Notas técnicas**:
- Hostinger no soporta PUT nativo → usar POST + `_method=PUT`
- `display_errors=1` en PHP rompe JSON → siempre `0` en producción
- `X-Requested-With: XMLHttpRequest` requerido para que `requireAuth()` retorne JSON 401 en vez de redirect HTML

### v2.5.0 — Portal del Líder
