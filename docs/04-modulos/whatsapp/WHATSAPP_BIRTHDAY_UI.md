# Módulo Cumpleaños — Rediseño Visual (v2.8.1)

> **Fecha:** 2026-07-08
> **Archivos modificados:** `api/whatsapp.php`, `pages/whatsapp_log.php`

---

## Cambios realizados

### API (`api/whatsapp.php`)

**Nuevos campos en endpoint `action=cumpleanos`:**

| Campo | Descripción | Ejemplo |
|-------|-------------|---------|
| `fecha_exacta` | Fecha formateada legible | `"15 de marzo"` |
| `dias_faltantes` | Días hasta el próximo cumpleaños (0=hoy, negativo=ya pasó) | `3` |

- `DATEDIFF(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(c.fecha_nacimiento, '%m-%d')), CURDATE())`
- Ordenado por `dias_faltantes ASC`

### Vista (`pages/whatsapp_log.php`)

Rediseño de lista lineal a **tarjetas tipo card** en grid responsive (3 columnas md, 2 sm, 1 xs).

**Estructura de cada tarjeta:**

```
┌─────────────────────────────┐
│ ████████████████████████████│ ← Barra superior (color según urgencia)
│  [AB]  Nombre Apellido      │ ← Avatar con iniciales + badge perfil
│         badge_perfil        │
│ ┌─────────────────────────┐ │
│ │   Cumple    │   Edad    │ │ ← Fondo gris suave
│ │  15 marzo   │   28      │ │
│ └─────────────────────────┘ │
│     🎉  HOY                │ ← Badge countdown (gradiente)
│  o  🔔  Faltan 3 días      │
│  o  📅  Faltan 12 días     │
│ ─────────────────────────── │
│ 📱 3001234567  [Pendiente] ○│ ← Teléfono + estado + botón enviar
└─────────────────────────────┘
```

**Sistema de colores por urgencia:**

| `dias_faltantes` | Barra superior | Avatar | Badge countdown |
|---|---|---|---|
| 0 (hoy) | Rosa → Rojo | Rosa → Rojo | 🎉 "HOY" rosa brillante |
| 1–3 | Ámbar → Naranja | Ámbar → Naranja | 🔔 "Faltan N días" ámbar |
| 4–7 | Azul → Cian | Azul → Cian | 📅 "Faltan N días" gris |
| >7 | Gris oscuro | Gris | 📅 "Faltan N días" gris |
| Negativo | Gris | Gris | ✅ "Ya pasó" gris |

**Botón de enviar:** Circular con icono `send`, loading spinner mientras envía.

---

## Archivos

| Archivo | Líneas | Cambio |
|---------|--------|--------|
| `api/whatsapp.php` | ~248 | +2 columnas SQL (`fecha_exacta`, `dias_faltantes`) + ORDER BY |
| `pages/whatsapp_log.php` | ~375 | Rediseño completo de template + Alpine data |

## Dependencias

- Lucide icons: `cake`, `party-popper`, `bell`, `calendar-check`, `send`, `check-circle`, `x-circle`, `clock`
- Alpine.js `x-for`, `x-show`, `x-transition`
- Tailwind gradients: `bg-gradient-to-r`, `bg-gradient-to-br`