# CP — Pulso de Campaña

> **Inspiración**: Embudo redes → captura → organización → poder
> **Proyecto**: Aratio — Padrinos Cali
> **Versión**: v1.0 | 2026-07-01

---

## Concepto

Cada post en redes sociales es una máquina de captura de datos. Una palabra clave en los comentarios activa un bot que recoge nombre, comuna y celular, y lo convierte en colaborador del CRM — sin fricción, sin formularios externos, sin plataformas de pago.

Tres capas de métricas:

| Capa | Métrica clave | Lo que mide |
|------|--------------|-------------|
| **Comunicación** | Alcance + Engagement views/comments | Qué tan lejos llega el mensaje |
| **Organización** | Capturas → Voluntarios → Líderes | Cuánta gente pasa de espectador a militante |
| **Poder** | Activación territorial | Cuántos responden a una convocatoria |

---

## Fase 1 — Automation Social (HIGH)

**Meta**: Cada comentario con palabra clave activa un DM automático que captura datos y crea un colaborador.

### Componentes

| # | Componente | Archivo | Descripción |
|---|-----------|---------|-------------|
| 1.1 | Migración SQL | `database/migrations/20260701_cp_pulso.sql` | Tablas: `cp_triggers`, `cp_capture_flow` |
| 1.2 | MessengerBot | `includes/MessengerBot.php` | Detección de keywords + auto-respuesta + flujo de captura |
| 1.3 | API endpoints | `api/cp_pulso.php` | triggers CRUD, comment monitoring, funnel stats |
| 1.4 | Dashboard | `pages/cp_pulso.php` | Embudo de conversión + filtro territorial |

### Flujo Técnico

```
1. Cron cada 5min → api/cp_pulso.php?action=scan
2. Fetch comments recientes desde FB Graph API
3. Match contra keywords configuradas en cp_triggers
4. Si hay match y no se ha respondido aún:
   a. Registrar en cp_capture_flow (evita duplicados)
   b. Enviar DM vía Messenger API con template configurado
   c. Iniciar flujo de captura: ¿Comuna? ¿Celular?
5. Cuando completa datos → crear/actualizar colaborador
6. Registrar en cp_capture_flow con origen: red + comuna + fecha
```

### Keyword Triggers (ejemplos)

| Palabra | Intención | Auto-respuesta |
|---------|-----------|---------------|
| `"quiero"` | Voluntariado | "Gracias por querer sumarte ¿en qué comuna vivís?" |
| `"comuna"` | Identificación territorial | "¿Eres de esa comuna? Déjame tu celular para sumarte" |
| `"evento"` | Asistencia | "Te enviamos info del próximo evento, ¿tu celular?" |
| `"Cali"` | General | "Qué bueno que te interesa Cali, ¿de qué comuna eres?" |

---

## Fase 2 — Embudo Único de Conversión (MEDIUM)

**Meta**: Tablero que conecta comunicación con organización.

| # | Componente | Descripción |
|---|-----------|-------------|
| 2.1 | Pipeline visual | Cards: Alcance → Comments → Capturas → Voluntarios → Líderes |
| 2.2 | Filtro territorial | Todo segmentable por comuna/barrio con datos existentes |
| 2.3 | Costo por contacto | Views / capturas = costo. Basado en inversión en ads |
| 2.4 | Alertas | Dónde se pierde más conversión en el embudo |

**Métricas**: 
- Comments con keyword / total comments (tasa de activación)
- DM completados / DM iniciados (tasa de captura)
- Capturas / comentarios con keyword (conversión neta)
- Voluntarios nuevos / capturas (activación)
- Costo por captura (inversión en ads / capturas)

---

## Fase 3 — Rapid Response Territorial (MEDIUM)

**Meta**: Activar colaboradores/líderes por comuna en minutos.

| # | Componente | Descripción |
|---|-----------|-------------|
| 3.1 | Selector territorial | Elegir comuna(s) + mensaje + canal (WA/email/llamada) |
| 3.2 | Broadcast inteligente | Usar infraestructura existente (WhatsAppApi, EmailCampaigns) |
| 3.3 | Tracking en vivo | Polling 30s: enviados, abiertos, respondidos, asistentes |
| 3.4 | Post-Election Mode | La base se mantiene para incidencia ciudadana post-elección |

---

## Stack

| Componente | Tecnología |
|-----------|-----------|
| Backend | PHP 8+, PDO, cURL |
| Frontend | Alpine.js 3.x, Chart.js |
| DB | MySQL (Hostinger) |
| APIs externas | Facebook Graph API (+ Messenger), WhatsApp Cloud API |
| Automatización | Cron (Hostinger) cada 5min |
| Costo adicional | $0 (APIs gratuitas, stack existente) |

---

## Dependencias

- `FB_PAGE_TOKEN` configurado (necesario para Messenger API)
- Permiso `pages_messaging` en el token de Meta
- `WHATSAPP_PROVIDER` configurado (para flujo de captura vía WA)
- Tablas de migración ejecutadas

---

## Roadmap

| Semana | Entregable |
|--------|-----------|
| 1 | Migración + MessengerBot + scan cron |
| 2 | API endpoints + dashboard embudo |
| 3 | Rapid Response + Post-Election toggle |
