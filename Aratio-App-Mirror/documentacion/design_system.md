# Sistema de Diseño Unificado - Aratio Pro

Este documento define la guía de estilo, los tokens de diseño y la implementación del sistema de rutas dinámicas para asegurar la consistencia visual y técnica en toda la plataforma.

## 🎨 Paleta de Colores (Oficial)

Se han estandarizado los colores basados en la identidad visual premium (Blue & Gold) observada en el sitio de producción.

| Variable | Hexadecimal | Uso |
| :--- | :--- | :--- |
| `COLOR_PRIMARY` | `#1e3a5f` | Barras de navegación, elementos principales, botones primarios. |
| `COLOR_SECONDARY` | `#d4af37` | Acentos, bordes destacados, estados hover, iconos especiales. |
| `COLOR_ACCENT` | `#2c5282` | Fondos de tarjetas (cards) secundarios, elementos de UI menos pesados. |
| `COLOR_SUCCESS` | `#22c55e` | Mensajes de éxito, confirmaciones, indicadores positivos. |
| `COLOR_DANGER` | `#ef4444` | Errores, alertas críticas, botones de eliminación. |
| `COLOR_WARNING` | `#f59e0b` | Advertencias, estados pendientes. |

---

## 🛤️ Manejo de Rutas Dinámicas (RouteHelper)

Para evitar que los vínculos se pierdan al desplegar o mover la aplicación a subcarpetas (ej: `/aratiopro/`), se ha implementado el `RouteHelper`.

### Cómo usarlo en el código:

En lugar de usar rutas estáticas como `href="/login.php"`, utiliza las funciones globales integradas:

#### 1. Vincular páginas internas
```php
// Antes (Malo - Estático)
<a href="/dashboard.php">Ir al Dashboard</a>

// Ahora (Bueno - Dinámico)
<a href="<?= url('dashboard.php') ?>">Ir al Dashboard</a>
```

#### 2. Cargar Assets (CSS, JS, Imágenes)
```php
// Antes (Malo)
<link rel="stylesheet" href="/assets/css/style.css">

// Ahora (Bueno)
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
```

#### 3. Redirecciones en PHP
```php
// En el backend
header("Location: " . url('login.php'));
exit();
```

---

## 🔡 Tipografía Sugerida

Se recomienda el uso de fuentes modernas para mantener el aspecto premium:
- **Principal:** Inter (Google Fonts)
- **Cuerpo:** Roboto o System UI

```css
body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    color: #1e293b;
    background-color: #f8fafc;
}
```

---

## 🛠️ Próximos Pasos - Unificación Visual
Se recomienda realizar una auditoría de las páginas con "colores diversos" para aplicar estas constantes mediante una hoja de estilos centralizada `assets/css/aratio-theme.css`.

> [!IMPORTANT]
> Se han eliminado todos los archivos relacionados con el despliegue de Edison para evitar confusiones y mantener el workspace centrado exclusivamente en Aratio Pro.
