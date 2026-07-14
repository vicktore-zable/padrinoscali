# Diagnóstico de Sincronización — Julio 2026

> **Fecha:** 2026-07-11
> **Estado:** COMPLETADO — 3 repos alineados

---

## 1. Resumen

Tres repositorios fuera de sincronía fueron consolidados:

| Origen | Destino | Estado |
|--------|---------|--------|
| `F:\xampp2\htdocs\aratio\` | `Aratio-App-Mirror\` | ✅ Sincronizado |
| `Aratio-App-Mirror\documentacion\` | `docs\` | ✅ 31 docs útiles copiados, 24 obsoletos archivados |
| `padrinoscali.org/aratio/` | Local | ⏳ No comparado aún |

## 2. Diferencias Encontradas

### 2.1 Archivos faltantes en Mirror (existían en XAMPP)

| Archivo | Tamaño | Fecha | Importancia |
|---------|--------|-------|-------------|
| `pages/whatsapp_log.php` | 23 KB | Jul 8 | **CRÍTICO** — Módulo cumpleaños |
| `pages/whatsapp_messages.php` | — | Jul 8 | Alto — Mensajería WhatsApp |
| `pages/portal_mis_voluntarios.php` | — | Jul 10 | Alto |
| `pages/voluntario_asignacion.php` | — | Jul 10 | Alto |
| `pages/voluntario_registro.php` | — | Jul 10 | Alto |
| `pages/zonas_trabajo.php` | — | Jul 8 | Medio |
| `cron/birthday_check.php` | — | Jun 28 | Alto — Cron de cumpleaños |
| `landing_body.html` | — | — | Bajo |

### 2.2 Archivos con diferencias significativas (XAMPP vs Mirror)

| Archivo | XAMPP | Mirror | Diferencia |
|---------|-------|--------|------------|
| `pages/colaborador_detalle.php` | 142865 B | 117657 B | **25 KB** — contenido diferente |
| `login.php` | 8497 B | 13712 B | Mirror más grande (versión antigua) |
| `registro_simpatizante.php` | 76 B | 56064 B | XAMPP es un stub |
| `registro-lider.php` | 76 B | 72123 B | XAMPP es un stub |
| `index.php` | 34809 B | 32890 B | ~2 KB de diferencia |
| `landing.php` | 35001 B | 34508 B | ~500 B de diferencia |
| `includes/Auth.php` | 10867 B | 14217 B | Mirror tiene versión más grande |
| `api/whatsapp.php` | 8414 B | 8137 B | ~300 B de diferencia |
| `api/colaboradores.php` | 74366 B | 73352 B | ~1 KB de diferencia |
| `api/territorios.php` | 8927 B | 8325 B | ~600 B de diferencia |
| `root_config.php` | 5550 B | 5928 B | ~400 B de diferencia |

### 2.3 Documentación

| Categoría | Cantidad |
|-----------|----------|
| Docs útiles migrados a `docs/` | 31 |
| Docs obsoletos archivados | 24 |
| Docs duplicados | 0 |

## 3. Acciones Tomadas

1. **Sync de código**: Todos los archivos de `pages/`, `api/`, `includes/`, `cron/`, `mod_*/` y raíz copiados de XAMPP a Mirror
2. **Migración de docs**: 31 documentos útiles copiados de `documentacion/` → `docs/` (a sus subdirectorios correspondientes)
3. **Archivo de obsoletos**: 24 documentos de fix/deploy/diagnóstico movidos a `documentacion/archived/`
4. **README en archived/** creado explicando el propósito

## 4. Pendiente

- [ ] Comparar con producción (`padrinoscali.org/aratio/`)
- [ ] Crear script `sync-xampp.sh` para sincronización continua XAMPP → Mirror
- [ ] Actualizar `CLAUDE.md` del workspace raíz con estructura actual de directorios
- [ ] Resolver notas pendientes de `NOTAS_SISTEMA.md` (contraseña a colaboradores)
- [ ] Unificar credenciales: remover DB password de `CLAUDE.md`, dejar solo en `CREDENCIALES_ACCESO.md`

### Sync: 2026-07-11 19:27:32
- Sincronización manual completada
