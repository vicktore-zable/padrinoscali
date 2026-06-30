# SETUP ALAS — Guía de Configuración Pendiente

> **Versión:** v2.9.0 | **Actualización:** 2026-06-29

---

## Estado actual

El código está 100% implementado, committeado y las migraciones SQL ejecutadas. Pero el sistema **no puede enviar mensajes reales** hasta completar los pasos abajo.

---

## 1. WhatsApp Business API (Meta)

### 1.1 Crear cuenta

| Paso | Acción | Enlace |
|------|--------|--------|
| 1 | Crear cuenta Facebook Business | https://business.facebook.com |
| 2 | Registrar WhatsApp Business Account | https://business.facebook.com/wa/manage/ |
| 3 | Agregar número de teléfono (verificado, no asociado a WhatsApp personal) | — |
| 4 | Generar token de acceso permanente | Configuración → WhatsApp → Token de acceso |

### 1.2 Llenar `root_config.php`

```php
define('META_WHATSAPP_TOKEN', 'EAAx...');       // Token permanente generado
define('META_WHATSAPP_PHONE_ID', '123456...');   // ID numérico del teléfono
define('META_WEBHOOK_VERIFY_TOKEN', 'aratio_alas_2026');  // Token personalizado
```

### 1.3 Verificar envío

Probar con `WhatsAppCloudApi::sendText()` desde una página o script temporal.

---

## 2. Cron Jobs en Hostinger

Panel Hostinger → Avanzado → Cron Jobs → agregar:

| Frecuencia | Comando | Propósito |
|:----------:|---------|-----------|
| `* * * * *` | `php /home/u577647812/domains/padrinoscali.org/public_html/aratio/cron/whatsapp_broadcast.php` | Procesar cola de broadcasts |
| `*/5 * * * *` | `php /home/u577647812/domains/padrinoscali.org/public_html/aratio/cron/workflow_processor.php` | Ejecutar acciones diferidas |
| `0 8 * * *` | `php /home/u577647812/domains/padrinoscali.org/public_html/aratio/cron/birthday_check.php` | Cumpleaños (existente) |

**Nota:** Asegurar que la ruta PHP sea la correcta en Hostinger (usar `which php` en SSH o contactar soporte).

---

## 3. Webhook Meta

| Paso | Acción |
|------|--------|
| 1 | Ir a https://developers.facebook.com → Apps → (tu app) → WhatsApp → Webhook |
| 2 | Callback URL: `https://padrinoscali.org/aratio/api/whatsapp_webhook.php` |
| 3 | Verify Token: el mismo que `META_WEBHOOK_VERIFY_TOKEN` |
| 4 | Suscribir eventos: `messages`, `message_deliveries`, `message_reads` |
| 5 | Verificar que Meta responda 200 OK (el webhook responde con `hub_challenge`) |

---

## 4. Plantillas Meta

Las plantillas deben ser aprobadas por Meta (revisión humana, 1-3 días hábiles).

| Nombre | Categoría | Variables | Propósito |
|--------|:---------:|-----------|-----------|
| `bienvenida` | UTILITY | `{{nombre}}` | Cuando un colaborador se registra |
| `recordatorio_evento` | UTILITY | `{{nombre}}, {{evento}}, {{fecha}}` | 48h antes de un evento |
| `gracias_asistencia` | UTILITY | `{{nombre}}, {{evento}}` | Después de asistir |
| `gracias_donacion` | UTILITY | `{{nombre}}, {{monto}}` | Al recibir una donación |
| `reactivacion` | MARKETING | `{{nombre}}` | Si no hay actividad en 30 días |

Crear desde: **Meta Business Suite → WhatsApp → Plantillas de mensaje**

---

## 5. Prueba de Integración

```bash
# 1. Probar envío manual
curl -X GET "https://padrinoscali.org/aratio/api/whatsapp_messages.php?action=test"

# 2. Verificar webhook
curl -X GET "https://padrinoscali.org/aratio/api/whatsapp_webhook.php?hub_mode=subscribe&hub_verify_token=aratio_alas_2026&hub_challenge=test123"

# 3. Ver timeline de un colaborador
curl "https://padrinoscali.org/aratio/api/timeline.php?colaborador_id=1"

# 4. Ver KPIs de workflows
curl "https://padrinoscali.org/aratio/api/workflows.php?action=stats"
```

---

## Checklist resumen

- [ ] Cuenta Facebook Business creada
- [ ] WhatsApp Business Account registrada
- [ ] Número verificado en Meta
- [ ] Token permanente generado
- [ ] `root_config.php` con tokens llenos
- [ ] Envío manual probado (sendText)
- [ ] 3 cron jobs configurados en Hostinger
- [ ] Webhook registrado y verificado (200 OK)
- [ ] 5 plantillas creadas y aprobadas
- [ ] Prueba de integración completa
