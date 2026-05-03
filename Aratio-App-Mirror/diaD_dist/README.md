# Módulo Día D - Guía de Implementación

Este paquete contiene el módulo **Día D** optimizado para seguimiento electoral en tiempo real, listo para ser desplegado para nuevos clientes.

## Estructura del Paquete
- `api/`: Endpoints de datos y reportes.
- `mod_diaD/`: Interfaces de usuario (Formulario y Dashboard).
- `config/`: Archivos de configuración y temas.
- `includes/`: Lógica de conexión a base de datos.
- `sql/`: Esquema de base de datos necesario.

## Pasos para la Instalación

### 1. Preparación de la Base de Datos
- Crea una nueva base de datos en tu hosting.
- Importa el archivo `sql/schema.sql` usando phpMyAdmin o similar.

### 2. Configuración de Conexión
- Edita el archivo `config/config.php`.
- Actualiza las constantes `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASS` con las credenciales de tu servidor.

### 3. Personalización de Marca
- Edita el archivo `config/theme.php`.
- Cambia `THEME_PRIMARY`, `THEME_SECONDARY` y `THEME_ACCENT` con los códigos hexadecimales del cliente.
- Actualiza `CLIENT_NAME` y `APP_TITLE`.

### 4. Despliegue
- Sube el contenido de la carpeta `diaD_dist` al directorio raíz (o una subcarpeta) de tu hosting vía FTP.
- Asegúrate de que las rutas en el archivo `.htaccess` apunten correctamente a `mod_diaD/index.php`.

## Requisitos Técnicos
- PHP 7.4 o superior.
- MySQL 5.7 o superior con soporte para JSON (opcional).
- Conexión a internet (carga librerías como Tailwind, Alpine.js y Leaflet vía CDN).

---
© 2026 - Módulo Día D redistribuible.
