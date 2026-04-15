# CLAUDE.md — Constitución del Agente: Edison Giraldo Campaign System
*Versión: 1.0 | Proyecto: edisongiraldo.com | Cali, Colombia 2027*

---

## 📝 Principios Core

- **Multi-Agent Orchestration**: Tareas complejas son manejadas por agentes especializados (Estratega, Comunicador, Auditor).
- **Worker-Critic Loop**: Ningún contenido de campaña se publica sin pasar revisión independiente (Quality Gate ≥ 80).
- **Evidence-Based**: Todas las propuestas deben estar respaldadas por datos reales (estadísticas electorales, censos, PDM Cali).
- **Idioma**: Español para comunicación y reportes; inglés para código y especificaciones técnicas.
- **Confidencialidad**: Las credenciales de base de datos y SSH están en `DEPLOY_EDISONGIRALDO.md` — NUNCA exponer en código.

---

## 🎯 Contexto del Proyecto

- **Cliente**: Edison Giraldo — Candidato político, Cali 2027
- **Plataforma**: Sistema Multi-Campaña (PHP + MySQL) desplegado en Hostinger
- **URL Producción**: https://edisongiraldo.com/
- **URL Preview**: https://navajowhite-goose-984880.hostingersite.com/
- **Stack**: PHP 8+, MySQL, Alpine.js / Vanilla JS, CSS moderno

---

## 📂 Estructura del Proyecto

```
edisongiraldo.com/
├── CLAUDE.md                    ← Esta constitución
├── DEPLOY_EDISONGIRALDO.md      ← Credenciales y rutas (NO compartir)
├── .claude/
│   ├── agents/                  ← Agentes especializados de campaña
│   ├── references/              ← Perfiles de dominio y contexto
│   └── rules/                   ← Reglas de calidad
└── quality_reports/             ← Outputs auditados de IA
```

---

## 🤖 Agentes Disponibles

| Agente | Rol | Activar con |
|--------|-----|-------------|
| `estratega` | Análisis territorial y electoral Cali | Decisiones estratégicas |
| `comunicador` | Mensajes de campaña y contenido | Generación de contenido |
| `auditor` | Critic — validación política y factual | Revisión de todo output |
| `desarrollador` | Código PHP/MySQL/JS | Tareas técnicas del sistema |

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

## 🎯 Estado Actual del Proyecto

- **Sistema en Producción**: ✅ Activo en Hostinger
- **Base de Datos Migrada**: ✅ 15 tablas (76 usuarios, 1231 puestos de votación)
- **NotebookLM**: 🔄 Pendiente configuración
- **Agentes IA**: 🔄 Pendiente activación
- **Frontend Skills**: 🔄 Pendiente aplicación
