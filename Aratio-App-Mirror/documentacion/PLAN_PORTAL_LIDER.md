# Plan de Implementación: Portal del Líder

## 1. Visión General
El Portal del Líder es una extensión especializada del sistema `mod_colab` diseñada para empoderar a los líderes de campaña. A diferencia del panel administrativo general, este portal ofrece una vista personalizada y restringida, donde cada líder solo puede gestionar y visualizar su propia estructura descendente.

### Objetivos Clave
- **Autogestión:** Permitir que los líderes vean su progreso en tiempo real.
- **Transparencia:** Mostrar claramente quiénes son sus referidos directos e indirectos.
- **Herramientas de Crecimiento:** Facilitar la captación de nuevos simpatizantes mediante enlaces personalizados.

## 2. Arquitectura Técnica
El portal se integrará dentro de la estructura MVC existente en `mod_colab/src`, aprovechando los modelos y controladores actuales pero con una capa de lógica específica para líderes.

### Componentes Nuevos
- **Controlador:** `LeaderPortalController.php` (Manejará la lógica de negocio exclusiva del líder).
- **Vistas:** Carpeta `views/portal/` con plantillas específicas (Dashboard, Mis Referidos, Mi Red).
- **Middleware:** `LeaderAuthMiddleware` (Verificará que el usuario tenga un `colaborador_id` asociado y esté activo).

### Integración con Base de Datos
- Se utilizará la relación existente en la tabla `usuarios`: `colaborador_id`.
- Las consultas se filtrarán siempre por `jerarquia` o `lider_directo`, asegurando que ningún líder pueda ver datos fuera de su rama.

## 3. Características Principales (Roadmap)

### Fase 1: Fundamentos y Dashboard (Core)
- [ ] **Estructura Base:** Crear controlador y rutas del portal.
- [ ] **Autenticación de Líder:** Verificar vinculación Usuario -> Colaborador al hacer login.
- [ ] **Dashboard Personal:**
    - Tarjetas de resumen (Total Red, Directos, Nivel de Cumplimiento).
    - Gráfico simple de crecimiento personal (últimos 30 días).
    - Acceso rápido a "Mi Enlace de Referido".

### Fase 2: Gestión de Red y Captación
- [ ] **Mi Red Interactiva:**
    - Adaptación del componente de visualización de grafos (Vis.js).
    - Configuración para cargar **solo** la rama descendente del líder logueado.
- [ ] **Herramientas de Captación:**
    - Generador de Enlace Único: `.../registro-simpatizante.php?ref={cedula_lider}`.
    - Generador de Código QR descargable para compartir en WhatsApp/Redes.
    - Script para detectar el parámetro `ref` en el formulario de registro público y pre-asignar el líder.

### Fase 3: Gestión de Equipo (CRM Ligero)
- [ ] **Listado de Mi Equipo:** Tabla con sus líderes directos y simpatizantes.
- [ ] **Detalle de Colaborador:** Vista rápida para ver el contacto y estado de un miembro de su red.
- [ ] **Validación de Referidos:** (Opcional) Permitir al líder confirmar si conoce a un nuevo registro que usó su enlace.

## 4. Lista de Tareas Detallada (Paso a Paso)

### Bloque A: Configuración y Backend
1.  **Crear el Controlador:** `mod_colab/src/Controllers/LeaderPortalController.php`.
    -   Método `index()` para el dashboard.
    -   Método `myNetwork()` para la vista de red.
    -   Método `myTeam()` para el listado.
2.  **Definir Rutas:** Agregar las nuevas rutas en `mod_colab/src/routes.php` (o donde se gestionen).
3.  **Middleware de Seguridad:** Asegurar que solo usuarios con `colaborador_id` válido accedan.

### Bloque B: Desarrollo del Dashboard
4.  **Vista del Dashboard:** Crear `mod_colab/src/Views/portal/dashboard.php`.
5.  **Lógica de Estadísticas:** Implementar métodos en `ColaboradorModel` para obtener stats filtradas por un ID raíz (el líder).
    -   `getNetworkStats($liderId)`: Total red, niveles de profundidad, nuevos hoy.

### Bloque C: Herramientas de Referidos
6.  **Generación de Links:** Implementar lógica para crear URL personalizada en el dashboard.
7.  **Integración con Registro:** Modificar `registro_simpatizante.php` (público) para leer el parámetro `ref` o `lider` de la URL y pre-seleccionar el campo de "Líder que invita".

### Bloque D: Visualización de Red
8.  **Adaptación de API:** Crear/Modificar endpoint en `api/colaboradores.php` para aceptar un `root_node_id` y devolver solo esa sub-red.
9.  **Vista de Red:** Crear `mod_colab/src/Views/portal/network.php` integrando la librería Vis.js con la configuración filtrada.

---
**Nota:** Este plan asume que la base de datos ya tiene la estructura jerárquica lista (campo `lider_directo` y `documento`).
