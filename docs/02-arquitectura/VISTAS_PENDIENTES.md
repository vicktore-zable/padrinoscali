# Vistas Pendientes y Problemas de Navegación

> **Versión:** 1.0 | **Fecha:** 2026-07-08 | **Proyecto:** Padrinos Cali — Aratio

---

## 1. Vistas que Causan Fatal Error (CRÍTICO)

### 1.1 `perfil_lider` — Página de perfil del líder

| Dato | Valor |
|---|---|
| **Ruta esperada** | `pages/perfil_lider.php` |
| **Referenciada en** | `index.php:7` — `$portalPages` array |
| **Impacto** | **PHP Fatal Error** — `require_once` de archivo inexistente |
| **Causa** | Legacy del portal líder, renombrada a `portal_perfil` sin actualizar array |
| **Solución** | Opción A: Crear wrapper que redirija a `portal_perfil` |
| | Opción B (recomendada): Eliminar del array `$portalPages` |

### 1.2 `dashboard_lider` — Dashboard del líder

| Dato | Valor |
|---|---|
| **Ruta esperada** | `pages/dashboard_lider.php` |
| **Referenciada en** | `index.php:7` — `$portalPages` array |
| **Impacto** | **PHP Fatal Error** |
| **Causa** | Legacy, renombrada a `portal_dashboard` |
| **Solución** | Igual que perfil_lider |

---

## 2. Vistas Sin Enlaces en la Navegación

| Archivo | Propósito | Estado | Acción Propuesta |
|---|---|---|---|
| `dashboard_organizaciones.php` | Dashboard de org. sociales | 🟡 Orfano | Vincular desde sidebar o eliminar |
| `dashboard_asistencia.php` | Dashboard de asistencia | 🟡 Orfano | Vincular desde sidebar de eventos |
| `reportes_nuevo.php` | Reportes versión 2 | 🟡 Orfano | Evaluar si reemplaza a `reportes.php` |
| `portal_debug_files.php` | Debug de archivos | 🔴 Solo debug | Eliminar en producción |
| `portal_path.php` | Debug de rutas | 🔴 Solo debug | Eliminar en producción |
| `portal_setup.php` | Setup de líder | 🟡 Orfano | Integrar en wizard de registro |
| `portal_test.php` / `portal_test_v2.php` | Tests de portal | 🔴 Solo test | Eliminar en producción |
| `portal_login_new.php` | Login alternativo | 🟡 Orfano | Evaluar si reemplaza a `portal_login.php` |
| `portal_recovery.php` | Recuperación de cuenta | 🟡 Orfano | Enlazar desde login |
| `portal_reset.php` | Reset de contraseña | 🟡 Orfano | Enlazar desde recovery |

---

## 3. Archivos .htaccess con Destinos Rotos

| Regla | Destino Actual | Problema |
|---|---|---|
| `^mapa-puestos/?$` → `mapa_territorios.php` | **Archivo no existe` | Causó bucle infinito en Hostinger |

**Solución**: Crear wrapper que redirija a `index.php?page=reportes` con hash o eliminar del .htaccess.

---

## 4. APIs Sin Conexión Frontal Completa

| API | Vista | Estado | Credenciales |
|---|---|---|---|
| `api/whatsapp.php` | `pages/whatsapp_log.php` | ✅ Vista OK, API sin credenciales WATI | `WATI_API_KEY` = vacío |
| `api/whatsapp_messages.php` | `pages/whatsapp_messages.php` | ✅ Vista OK, API sin webhook Cloud | Meta webhook sin configurar |
| `api/whatsapp_webhook.php` | — | ⚠️ Endpoint sin callback configurado | Sin URL de webhook en Meta |
| `api/facebook.php` | — | ⚠️ API sin credenciales | `FB_APP_ID` = vacío |
| `api/instagram_graph.php` | `pages/instagram_graph.php` | ⚠️ Vista OK, API sin token IG | `IG_BUSINESS_ID` = vacío |
| `api/instagram_sync.php` | — | ⚠️ API sin credenciales | Sin token de largo plazo |
| `api/emails.php` | `pages/emails.php` | ⚠️ Vista OK, SMTP sin pass | `SMTP_PASS` = placeholder |
| `api/llamadas.php` | `pages/llamadas.php` | ✅ Vista OK, funcionalidad completa | No requiere API externa |

---

## 5. Vistas de Módulos sin Integración Completa

| Módulo | Vistas Existentes | Funcionalidad Faltante |
|---|---|---|
| **mod_jac** | `dashboard.php`, `grupos_de_interes.php`, `index.php` | CRUD completo de JAC, mapa de juntas, reportes |
| **mod_organizaciones** | `dashboard.php`, `grupos_de_interes.php`, `index.php` | CRUD completo, perfiles de organización |
| **mod_diaD** | `dashboard.php`, `index.php` | Operación logística día de elecciones |
| **mod_elecciones** | `public/index.php`, `src/Views/dashboard.php` | Panel admin de resultados electorales |

---

## 6. Plan de Reconstrucción por Prioridad

| # | Vista | Prioridad | Acción | Esfuerzo |
|---|---|---|---|---|
| 1 | Fix `perfil_lider` / `dashboard_lider` | 🔴 Crítico | Remover del array o crear alias | 15 min |
| 2 | Fix `.htaccess` mapa-puestos | 🔴 Crítico | Eliminar regla rota | 10 min |
| 3 | Deshabilitar `display_errors` en público | 🟡 Alto | Cambiar a 0 en registro-*.php | 10 min |
| 4 | Vincular `portal_recovery` y `portal_reset` | 🟡 Alto | Agregar enlaces en login | 30 min |
| 5 | Eliminar páginas de test/debug | 🟡 Alto | Mover a `scratch/` | 30 min |
| 6 | Limpiar orfandad `reportes_nuevo.php` | 🟢 Medio | Decidir si reemplaza a reportes.php | 1 hr |
| 7 | Completar vistas de módulos (JAC, Org) | 🟢 Medio | CRUD completo para cada módulo | 4 hrs |
| 8 | Vistas integración WhatsApp/FB/IG | 🟢 Bajo | Conectar UI con APIs cuando haya credenciales | 8 hrs |

---

*Documento generado por auditoría de vistas — 2026-07-08*