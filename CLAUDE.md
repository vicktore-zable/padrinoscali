# CLAUDE.md — Constitución del Agente: Padrinos Cali

*Versión: 2.0 | Proyecto: padrinoscali.org | Cali, Colombia*

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

- **Git**: ✅ Inicializado
- **Producción**: https://edisongiraldo.com/aratio/ (temporal)
- **Preview**: https://navajowhite-goose-984880.hostingersite.com/aratio/
- **XAMPP local**: F:\xampp2\htdocs\aratio\ (cambios pendientes sin commit)
- **DB**: 15 tablas en producción (u577647812_aratio)
- **Dominio futuro**: padrinoscali.org
