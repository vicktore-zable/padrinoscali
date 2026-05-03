# Documentación de Implementación: Portal del Líder (Actualizado)

## Resumen Ejecutivo
Se ha implementado el "Portal del Líder", un entorno dedicado para que los líderes gestionen su red y eventos. La autenticación se ha separado del flujo principal, permitiendo el ingreso con documento/documento y la creación automática de usuarios. Además, se ha configurado la landing page como punto de entrada principal.

## Componentes Principales

### 1. Autenticación Dedicada (`PortalAuthController`)
Ubicación: `mod_colab/src/Controllers/PortalAuthController.php`
- **Ruta**: `/portal/login`
- **Lógica**:
    - Permite ingreso con Documento y Contraseña (inicialmente el mismo documento).
    - Si el usuario no existe en tabla `usuarios` pero sí en `colaboradores`:
        - Se crea automáticamente el usuario (`usuario` = documento, `password` = documento).
        - Se asigna rol `lider`.
        - Se vincula con el `colaborador_id`.
    - Redirección automática al Dashboard del Líder (`/portal/dashboard`).

### 2. Punto de Entrada (Landing Page)
Ubicación: `mod_colab/src/Views/home/index.php`
- Se ha modificado la landing page para incluir un botón destacado **"Portal Líderes"**.
- El archivo raíz `index.php` ha sido actualizado para servir la landing page (`mod_colab`) por defecto en la ruta `/`, reemplazando la redirección forzada al login antiguo.

### 3. Portal del Líder (`LeaderPortalController`)
Ubicación: `mod_colab/src/Controllers/LeaderPortalController.php`
- **Dashboard**: Muestra estadísticas clave (Total Red, directos), enlace de referido para captación, y lista de equipo directo.
- **Mi Red**: Visualización jerárquica de la estructura.

### 4. Gestión de Eventos y Permisos
Ubicación: `mod_colab/src/Controllers/EventoController.php`
- **Creación**: Los líderes pueden crear eventos. El responsable se asigna automáticamente al líder actual.
- **Edición**: Restringida. Un líder solo puede editar eventos donde él es el **responsable**.
- **Eliminación**: Prohibida para líderes (botón oculto y validación en backend).
- **Visibilidad**: Pueden ver todos los eventos de la campaña.

## Cambios en Archivos del Sistema

### Rutas (`mod_colab/routes/web.php`)
- Agregadas rutas para `/portal/login` (GET/POST).
- Agregadas rutas para el dashboard y red (`/portal/dashboard`, `/portal/mi-red`).

### Vistas
- `mod_colab/src/Views/portal/auth/login.php`: Nueva vista de login específica para líderes.
- `mod_colab/src/Views/portal/dashboard.php`: Dashboard con métricas y enlace de referido.
- `mod_colab/src/Views/home/index.php`: Landing page con accesos diferenciados.

### Base de Datos
- Tabla `usuarios`: Se requieren columnas `usuario`, `tipo_usuario`, `colaborador_id`, `activo`, `password`, `email`.
- Script de migración: `fix_database_schema.php` (ejecutar si no se ha hecho).

## Instrucciones de Despliegue
1. **Sincronizar Archivos**: Subir `mod_colab/src`, `mod_colab/routes` e `index.php` (raíz).
2. **Base de Datos**: Verificar que la tabla `usuarios` tenga las columnas nuevas.
3. **Prueba de Acceso**:
   - Ir a la raíz del dominio.
   - Clic en "Portal Líderes".
   - Ingresar con un documento válido de colaborador (si es primera vez, usar documento como password).
   - Verificar acceso al dashboard y estadísticas.
