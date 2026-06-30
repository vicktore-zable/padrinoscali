# FASE 2: Interactividad + Expansión

> **Versión:** v0.1 (borrador) | **Proyecto:** Aratio — Padrinos Cali
> **Depende de:** Fase 1 (ALAS v2.9.0) desplegada y funcional

---

## ⚡ Mejoras de Interactividad (v2.10.0)

Priorizadas por impacto vs esfuerzo:

### Alta prioridad

| # | Mejora | Dónde | Esfuerzo | Impacto |
|:-:|--------|-------|:--------:|:-------:|
| 1 | **Polling automático** — Inbox y Workflows se actualizan solos cada 15s sin recargar página | `pages/whatsapp_messages.php`, `pages/workflows.php` | 🟡 Medio | ✅ Alto |
| 2 | **Notificaciones toast** — Alertas en vivo cuando llega un mensaje nuevo o un workflow se ejecuta | JS global + Alpine `x-init` polling | 🟢 Bajo | ✅ Alto |
| 3 | **Badge en menú lateral** — Contador de mensajes no leídos en el sidebar | `index.php` + endpoint `api/whatsapp_messages.php?action=unread_count` | 🟢 Bajo | ✅ Alto |
| 4 | **Marcar como leído** — Click en conversación la marca como leída con feedback visual | `pages/whatsapp_messages.php` + Alpine | 🟢 Bajo | ✅ Alto |
| 5 | **Timeline en tiempo real** — Las actividades nuevas aparecen sin recargar | `pages/colaborador_detalle.php` + polling suave | 🟡 Medio | ✅ Alto |

### Media prioridad

| # | Mejora | Dónde | Esfuerzo | Impacto |
|:-:|--------|-------|:--------:|:-------:|
| 6 | **Scroll infinito mejorado** — Timeline con lazy loading + skeleton loader | `api/timeline.php` + UI | 🟢 Bajo | 🟡 Medio |
| 7 | **Broadcast selector visual** — Tree checkboxes por territorio en vez de textarea de IDs | `pages/whatsapp_messages.php` | 🟡 Medio | 🟡 Medio |
| 8 | **Previsualización de broadcast** — Modal con conteo + nombres antes de enviar | `pages/whatsapp_messages.php` | 🟡 Medio | 🟡 Medio |
| 9 | **Búsqueda en inbox** — Filtro por nombre de colaborador o número | `pages/whatsapp_messages.php` | 🟡 Medio | 🟡 Medio |
| 10 | **Tooltips y estados** — Indicadores visuales (enviado, entregado, leído, fallido) | `pages/whatsapp_messages.php` | 🟡 Medio | 🟡 Medio |

### Baja prioridad (post-Fase 2)

| # | Mejora | Dónde | Esfuerzo | Impacto |
|:-:|--------|-------|:--------:|:-------:|
| 11 | **Dark mode** — Toggle en sidebar, persistencia en localStorage | Global CSS + Alpine | 🟢 Bajo | 🟢 Bajo |
| 12 | **Exportar timeline a PDF/CSV** — Botón de descarga por colaborador | `pages/colaborador_detalle.php` | 🟡 Medio | 🟢 Bajo |
| 13 | **WebSockets (Pusher)** — Tiempo real real, sin polling | Infraestructura | 🔴 Alto | 🟡 Medio |

---

## 🚀 Nuevas Capacidades (v2.11.0+)

### Phone Banking (v2.11.0)

| Componente | Descripción |
|------------|-------------|
| **API** | `api/llamadas.php` — CRUD campañas de llamadas, logs, resultados |
| **Cola** | Tabla `llamadas_cola` con prioridad, estado, agente |
| **UI** | Panel de agente: script + botón "Llamar" + resultado rápida (contestó/no/llamar después) |
| **Integración** | ActivityLogger + WorkflowEngine (trigger `llamada.finalizada`) |

### Email Campaigns (v2.12.0)

| Componente | Descripción |
|------------|-------------|
| **API** | `api/emails.php` — PHPMailer, templates, adjuntos |
| **Cola** | Tabla `email_cola` con logs de envío, rebotes, aperturas |
| **Templates** | Editor simple con variables `{{nombre}}`, `{{territorio}}` |
| **Segmentación** | Filtrar destinatarios por territorio, lider, estado |

### Dashboard Territorial (v2.13.0)

| Componente | Descripción |
|------------|-------------|
| **KPIs** | Colaboradores activos, eventos del mes, donaciones, conversión |
| **Gráficos** | Chart.js o Highcharts: tendencias, distribución, embudo |
| **Exportación** | PDF semanal automático (cron + Dompdf/TCPDF) |
| **Mapa** | Leaflet con calor de colaboradores por barrio |

### Líder 2.0 (v2.14.0)

| Componente | Descripción |
|------------|-------------|
| **Feed propio** | El líder ve su actividad (colaboradores que registró, eventos, cumpleaños) |
| **Ranking** | Tabla de líderes por colaboradores activos |
| **Notificaciones** | El líder recibe notificación cuando un colaborador suyo cumple años o asiste a evento |

### Instagram → CRM (v2.15.0)

| Componente | Descripción |
|------------|-------------|
| **Match automático** | Relacionar menciones en comentarios con colaboradores existentes |
| **Lead gen** | Capturar nuevos leads de comentarios y seguidores |
| **Timeline cruzado** | Mostrar actividad de Instagram en el timeline del colaborador |

---

## 📊 Interactividad: Detalle Técnico

### Polling con Alpine.js (recomendado sobre WebSockets)

```html
<div x-data="{ 
    mensajes: [], 
    noLeidos: 0,
    init() {
        this.cargar();
        setInterval(() => this.cargar(), 15000);
    },
    cargar() {
        fetch('api/whatsapp_messages.php?action=list')
            .then(r => r.json())
            .then(d => { 
                this.mensajes = d.data;
                this.noLeidos = d.data.filter(m => !m.leido).length;
            });
    }
}">
```

**Razones:**
- Hostinger no soporta WebSockets persistentes
- PHP + polling cada 15s es ~5,760 requests/día — despreciable
- Se puede migrar a Pusher (gratis 200k msgs/día) en v3.0.0 si el tráfico crece

### Notificaciones Toast

```html
<!-- Ejemplo con Alpine -->
<div x-data="{ toast: { show: false, message: '' } }"
     x-init="setInterval(() => {
         fetch('api/check_new.php').then(r => r.json()).then(d => {
             if (d.new) { toast.show = true; toast.message = d.message; }
         });
     }, 30000)">
    <div x-show="toast.show" 
         x-transition
         class="toast">
        <span x-text="toast.message"></span>
        <button @click="toast.show = false">×</button>
    </div>
</div>
```

---

## 📐 Refactor Técnico Pendiente

| Tarea | Prioridad | Notas |
|-------|:---------:|-------|
| `WhatsAppCloudApi` sin `static` (inyección de dependencias) | 🟡 Media | Para test unitario viable |
| Tests unitarios para `WorkflowEngine` | 🟡 Media | PHPUnit, mock DB |
| `config.php` sin headers en CLI (las migraciones lanzan warnings) | 🟢 Baja | Ya no molesta |
| Rate limiting en endpoints de API | 🟡 Media | Evitar abuso en broadcast |
| Logging centralizado (Monolog o similar) | 🔴 Alta | Hoy cada clase loguea aparte |
| Manejo de errores consistente (try/catch con JSON response) | 🔴 Alta | Algunos endpoints no tienen catch |

---

## Prioridad Sugerida

```
v2.10.0 ──── Interactividad (polling, badges, toast, marcar leído, timeline real-time)
    │
v2.11.0 ──── Phone Banking (panel agente + cola + resultados)
    │
v2.12.0 ──── Email Campaigns (PHPMailer + templates + segmentación)
    │
v2.13.0 ──── Dashboard Territorial (KPIs + gráficos + mapa)
    │
v2.14.0 ──── Líder 2.0 (feed propio + ranking + notificaciones)
    │
v2.15.0 ──── Instagram → CRM (match automático + lead gen)
    │
v3.0.0  ──── Refactor mayor (inyección dependencias, logging centralizado, tests)
```

---

*Borrador — 2026-06-29 — Pendiente de revisión con el equipo*
