# Estado de Integraciones Externas

> **Versión:** 1.0 | **Fecha:** 2026-07-08 | **Proyecto:** Padrinos Cali — Aratio

---

## 1. WhatsApp

| Aspecto | Detalle |
|---|---|
| **Proveedor** | WATI (actual) + Meta Cloud API (ALAS) |
| **API actual** | `includes/WhatsAppApi.php` (WATI), `includes/WhatsAppCloudApi.php` (Meta) |
| **Endpoint cumpleaños** | `api/whatsapp.php` — Panel `pages/whatsapp_log.php` |
| **Endpoint conversaciones** | `api/whatsapp_messages.php` — Inbox `pages/whatsapp_messages.php` |
| **Webhook** | `api/whatsapp_webhook.php` — Sin configurar en Meta |
| **Cron** | `cron/birthday_check.php` — 8 AM diario, `cron/whatsapp_broadcast.php` |
| **Credenciales** | `WATI_API_KEY` y `WATI_NUMBER` vacíos en `root_config.php` |

### 1.1 Configuración Pendiente

- [ ] Obtener API Key de WATI (dashboard.wati.io)
- [ ] Configurar número de WhatsApp Business en WATI
- [ ] Configurar webhook de Meta Cloud API apuntando a `https://padrinoscali.org/aratio/api/whatsapp_webhook.php`
- [ ] Verificar plantillas de mensaje aprobadas en Meta Business
- [ ] Configurar cron de cumpleaños en Hostinger: `0 8 * * * php /ruta/cron/birthday_check.php`

---

## 2. Facebook

| Aspecto | Detalle |
|---|---|
| **API** | `includes/FacebookApi.php` — `api/facebook.php` |
| **Clases** | `MessengerBot.php` — Auto-respuesta a comentarios |
| **Endpoint** | Graph API v19.0 |
| **Funcionalidad** | Fetch posts, reactions, comments, auto-DM por keyword match |
| **Credenciales** | `FB_APP_ID`, `FB_PAGE_ID`, `FB_PAGE_TOKEN` vacíos |

### 2.1 Configuración Pendiente

- [ ] Crear App en Facebook Developers
- [ ] Obtener Page Token (página @edisonconcejal)
- [ ] Configurar webhook de Page en Facebook
- [ ] Suscribir Page a `feed` y `comments` campos

---

## 3. Instagram

| Aspecto | Detalle |
|---|---|
| **Sync histórico** | ✅ `instagram_scraper.py` — 686 posts (API v1 + cookies) |
| **Graph API** | `includes/InstagramGraphApi.php` — `api/instagram_graph.php` |
| **Dashboard** | `pages/instagram_graph.php` — Comentarios IG |
| **Monitor** | `pages/actividad_instagram.php` — Monitor Digital |
| **Mapa** | `pages/mapa_instagram.php` — Mapa de Gestión |
| **Match automático** | `includes/SocialCRM.php` — Asocia comentarios a colaboradores |
| **Credenciales** | `IG_BUSINESS_ID` vacío, requiere FB Page Token |

### 3.1 Configuración Pendiente

- [ ] Configurar Instagram Business Account (conversión de perfil personal)
- [ ] Obtener IG Business ID desde FB Graph API
- [ ] Generar token de largo plazo (60 días)
- [ ] Configurar webhook de Instagram en Meta

---

## 4. Email (SMTP)

| Aspecto | Detalle |
|---|---|
| **Proveedor** | Hostinger SMTP |
| **API** | `includes/EmailCampaigns.php` (PHPMailer) |
| **Dashboard** | `pages/emails.php` — Campañas de email |
| **Cron** | `cron/email_processor.php` — Procesa cola de envío |
| **Host** | smtp.hostinger.com:465 (SSL) |
| **Credenciales** | `SMTP_PASS` = `'tu_password_email'` (placeholder) |

### 4.1 Configuración Pendiente

- [ ] Configurar cuenta de email admin@aratio.mrmtech.net en Hostinger
- [ ] Obtener/establecer contraseña SMTP
- [ ] Verificar conexión SMTP (prueba desde `includes/EmailCampaigns.php`)

---

## 5. Instagram Scraper (Histórico)

| Aspecto | Detalle |
|---|---|
| **Script** | `instagram_scraper.py` (en workspace) |
| **Login helper** | `instagram_login.py` — Login manual con Chrome |
| **API** | API v1 (`/api/v1/feed/user/{id}/`) con paginación |
| **Posts extraídos** | 686 (2018-02 a 2026-06-19) |
| **Formato** | JSON en `storage/maestro_instagram.json` |
| **Frecuencia** | Manual (cada ~1-2 semanas cuando cookies expiren) |

---

## 6. Matriz de Prioridades para Configuración

| Integración | Prioridad | Dependencia | Esfuerzo |
|---|---|---|---|
| SMTP (Email) | 🔴 Alta | Hostinger admin | 30 min |
| WhatsApp WATI (cumpleaños) | 🔴 Alta | WATI dashboard + número | 1 hr |
| WhatsApp Cloud (ALAS) | 🟡 Media | Meta Business | 2 hrs |
| Facebook Graph API | 🟡 Media | FB App + Page Token | 2 hrs |
| Instagram Graph API | 🟡 Media | IG Business + FB token | 1 hr |

---

*Documento de estado de integraciones — 2026-07-08*