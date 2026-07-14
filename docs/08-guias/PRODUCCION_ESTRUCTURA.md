# 📄 Documentación de Producción - Sistema Aratio

**Fecha**: 30 de Enero de 2026
**URL**: https://aratio.mrmtech.net
**Hosting**: Hostinger (LiteSpeed Server)

## 🏗️ Estructura del Servidor (public_html)

Basado en la inspección mediante FTP/SSH, la estructura actual en producción es la siguiente:

### 📁 Directorios Core
- `/api/`: Endpoints REST para la gestión de datos (campañas, eventos, etc.)
- `/config/`: Archivos de configuración (`config.php`, `database.php`). *Nota: config.php en producción contiene credenciales reales.*
- `/includes/`: Clases base, autenticación (`Auth.php`) y helpers.
- `/mod_colab/`: Módulo principal de Colaboradores (Arquitectura MVC).
  - `src/`: Controladores, Modelos y Vistas.
  - `public/`: Punto de entrada para el módulo.
  - `routes/`: Definición de rutas amigables.
- `/pages/`: Vistas del Dashboard administrativo tradicional.
- `/database/`: Esquemas SQL y migraciones.
- `/cache/`: Directorio temporal para caché.

### 📄 Archivos Principales
- `index.html`: Landing page (Página de aterrizaje).
- `index.php`: Punto de entrada principal / Router del Dashboard.
- `login.php / logout.php`: Gestión de sesiones.
- `registro_simpatizante.php`: Formulario público de registro.
- `registro_asistencia.php`: Gestión de asistencia a eventos.
- `.htaccess`: Reglas de reescritura, redirecciones y seguridad.

## ⚠️ Archivos de Trabajo Detectados (Basura a Limpiar)
Se han identificado múltiples archivos temporales y de diagnóstico que no deberían estar en un entorno productivo:
- Scripts de diagnóstico: `diagnostico_api.php`, `diagnostico_dropdowns.php`, `explore_server.php`.
- Archivos de prueba: `test_subdomain.php`, `test_departamentos.php`.
- Logs: `php-errors.log`.
- Backups temporales: `config/database.php.backup_update_fix`.
- Scripts de migración: `import_colaboradores.php`, `ejecutar_seed_jaimito.php`.

## 🔄 Estado de Sincronización
- **Landing Page**: Sincronizada con el repositorio (11,507 bytes).
- **Dashboard**: Operativo, requiere autenticación.
- **Base de Datos**: Sincronizada y funcionando correctamente.

---
*Documento generado automáticamente por Antigravity AI.*
