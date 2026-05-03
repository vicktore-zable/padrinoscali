# Changelog - Aratio v2.0.0

Todas las actualizaciones destacadas del proyecto se documentan en este archivo.

---

## [2.5.0] - 2026-05-02
### 📑 Auditoría de Documentación y Credenciales
- **Corrección de Enlaces Globales**: Actualización masiva de enlaces en `README.md`, `DOCUMENTACION.md` y guías técnicas para apuntar al dominio de producción definitivo (`edisongiraldo.com/aratio`).
- **Actualización de Credenciales**:
    - Reestructuración de `CREDENCIALES_ACCESO.md` para separar credenciales de producción actuales de las de sistemas legados (`aratio.mrmtech.net`).
    - Actualización de accesos SSH/SFTP (Puerto 65002) y credenciales de base de datos (`u577647812_aratio`).
- **Guías de Sincronización**: Actualización de la `GUIA_SINCRONIZAR_PRODUCCION.md` e `INSTRUCCIONES_SUBIR_HOSTINGER.md` con las rutas, IPs y comandos `curl` correctos para el nuevo entorno.
- **Constitución de Agente**: Sincronización de `CLAUDE.md` con los datos de producción reales de la campaña Edison Giraldo.
- **Limpieza de Referencias Obsoletas**: Eliminación de referencias a dominios de prueba (`mrmtech.net`) en toda la documentación técnica principal.

---

## [2.4.0] - 2026-05-01
### 🎓 Sistema de Gestión de Curriculum y Red Avanzada
- **Módulo de Curriculum**: Implementación de una nueva arquitectura de datos para el seguimiento detallado de colaboradores.
    - **Nueva Tabla `curriculum`**: Almacenamiento dinámico en formato JSON para Experiencia Laboral, Formación Académica y Participación Política.
    - **Metadatos Sociales**: Seguimiento de hijos (edades/discapacidad), equipo de fútbol y práctica deportiva.
- **Visualización de Red 2.0**:
    - **Recursividad con CTEs**: Implementadas consultas recursivas (`WITH RECURSIVE`) para cargar jerarquías profundas de forma eficiente.
    - **Focus & Lazy Loading**: Capacidad de hacer zoom en un nodo específico (`root_doc`) para visualizar únicamente su red descendente.
    - **Estadísticas de Red**: Desglose automático por territorio (barrios) y conteo de red total (Directos + Indirectos).
- **Historial y Auditoría**:
    - **Trazabilidad de Líderes**: Nuevo sistema de registro de cambios de liderazgo con motivos y usuario responsable.
    - **Reevaluación de Potencial**: Historial de cambios en datos potenciales e históricos para análisis de crecimiento.
- **UI Premium para Colaboradores**:
    - **Interfaz por Pestañas**: Organización de la vista de detalle en: General, Seguidores, Historial, Curriculum y Acciones.
    - **Formularios Dinámicos**: Uso avanzado de Alpine.js para la gestión de arreglos dinámicos en el curriculum sin recargas de página.

---

## [2.3.0] - 2026-04-25
### 📅 Módulo de Eventos: Carga Inicial de Producción
- **Creación de Eventos en Producción**: Insertados los dos primeros eventos oficiales en la base de datos de producción (`157.173.208.254`) para la campaña de Edison Giraldo.
- **Eventos Registrados**:
    - *Gran Lanzamiento de Campaña - Comuna 2*: Mitin masivo programado para el 10 de mayo de 2026 en el Parque de la Comuna 2.
    - *Diálogos Ciudadanos - Sector Comercio*: Reunión estratégica programada para el 15 de mayo de 2026 en la Cámara de Comercio de Cali.
- **Georreferenciación**: Configuración automática de Departamento (Valle del Cauca) y Municipio (Cali) para asegurar visibilidad en los reportes territoriales y filtros de la UI.
- **Automatización de Admin**: Desarrollo y ejecución de scripts de Python para inserción directa y enriquecimiento de datos en la tabla `eventos`, superando las limitaciones de acceso por UI.

---

## [2.2.0] - 2026-04-19
### 🛡️ Estabilización y Arquitectura Multi-Instancia
- **Refactorización de Configuración**: Implementada carga segura en `config/config.php` (Core) mediante bloques `if (!defined(...))` para permitir sobreescritura desde `root_config.php`.
- **Eliminación de Conflictos**: Limpieza profunda de `root_config.php` para eliminar redeclaraciones de funciones y lógica de sesión redundante, resolviendo el Error 500 crítico.
- **Redirección Dinámica**: Migración de rutas estáticas (`/`) a rutas dinámicas basadas en `url()` en `Auth.php` y `login.php`, garantizando compatibilidad con despliegues en subcarpetas (ej: `/aratio/`).

### 💎 Pulido de Interfaz y UX
- **Correcciones en Dashboard**: Añadido soporte para valores nulos (`?? 0`) en funciones de formato numérico de `dashboard.php` para evitar advertencias de PHP.
- **Enlace Maestro**: Actualizado el botón "Consulta Electoral" para apuntar correctamente al servidor de inteligencia externo.
- **Sincronización Avanzada**: Creación de scripts de espejo (`mirror_local_to_workspace.py`) para mantener el Workspace de Google Drive sincronizado con el desarrollo local.

---

## [2.1.0] - 2026-04-18
### 📊 Reportes BI Mejorados
- **Dashboard KPI Global**: Tarjetas premium con gradientes (colaboradores, líderes, mujeres, hombres, eventos, organizaciones)
- **Navegación Moderna**: Tabs estilo pill con icons
- **Reporte Territorial**: Tablas con barras visuales de progreso, distribución por departamento/municipio/perfil
- **Reporte Geográfico**: Drill-down jerárquico en cascada completo (Departamento → Municipio → Barrio → Colaboradores)
- **Persistencia de Estado**: URLs mantienen el estado del filtro (reporte, depto, mpio, barrio)
- **Mejoras UI**: Card con header gradient, sombras, border-radius

### 🔗 Módulo Consulta Electoral
- **Enlace desde Landing**: Botón "Consulta Elecciones" conecta a heatmap público (?page=consulta_electoral)
- **Enlace desde Dashboard**: Nuevo ítem en sidebar "Consulta Electoral" → abre en nueva pestaña
- **Evita Conflicto**: Naming diferenciado (?page=elecciones vs ?page=consulta_electoral)

### 👥 Colaboradores: Mejoras en Formularios
- **Perfil Profesional**: Sección idéntica a registro-lider (Formación Académica, Experiencia Laboral, Habilidades)
- **Búsqueda Puesto de Votación**: Tipo-ahead con 2+ letras (antes 3+), búsqueda case-insensitive
- **Unificación NIVELES_PARTICIPACION**: Agregado "Contratista"

### 🎨 Identidad Visual AratioPRO
- **Header Gradient**: Azul (#1e3a5f) → Oro (#d4af37)
- **Diseño Mobile-First**: Full-width, responsive
- **Glassmorphism**: Efecto glass en tarjetas
- **Iconos Lucide**: Consistencia visual

---

## [2.0.0] - 2026-04-18
### 📸 Colaboradores: Capa de Identidad Visual (AratioPRO Local)
- **Captura Fotográfica Mobile-First**: Implementada funcionalidad para que líderes y colaboradores puedan tomarse una foto directamente desde la cámara del dispositivo móvil o subirla desde sus archivos.
- **Identidad Visual Premium**:
    - Previsualización en vivo ("En Vivo" badge) de la cámara mediante Alpine.js y la API web de `getUserMedia`.
    - La tabla del módulo principal ahora muestra avatares reales o iniciales elegantes si no hay foto disponible.
- **Backend Optimizado**: Lógica PHP dedicada para interpretar, guardar y asociar imágenes en Base64 de forma eficiente y segura en el servidor.

### 🌟 Portal JAC: Evolución Geo & Branding
- **Rebranding de Entidades**: Migración global de la terminología "JAC" a **"Entidades / Organizaciones"** para una mayor versatilidad del módulo.
- **Dashboard Geo 2.0 (Premium)**:
    - **Burbujas Dinámicas**: Implementación de marcadores estilo burbuja con colores por categoría y contadores de agrupación.
    - **Panel de Información Enriquecido**: Popups rediseñados que incluyen **Presidente, Dirección, Email, Observaciones, Meta Votos y Afiliados**.
    - **Terminología Geográfica**: Actualización de etiquetas a **"Comuna / Corregimiento"** en filtros y visualización.
- **Mejoras en la API de Mapas**: Actualizado el endpoint `api/jac.php?action=mapa` para entregar metadatos completos y valores por defecto robustos.
- **UI/UX Premium**: Implementación de efectos *glassmorphism*, iconos Lucide integrados y transiciones suaves en el mapa.

### 🛠️ Ajustes Técnicos
- **Limpieza de Código**: Eliminación de fragmentos de código huérfanos y optimización del renderizado de capas Leaflet en el Dashboard.
- **Consistencia de Estilos**: Unificación de la estética entre el mapa de administración (`grupos_de_interes.php`) y el dashboard público (`dashboard.php`).

---

## [1.7.0] - 2026-03-18
### 🗺️ Mejoras en Visor Territorial (Mapa de Puestos)
- **Buscador en Tiempo Real**: Implementada búsqueda instantánea en el sidebar para filtrar puestos de votación y barrios por texto (nombre, dirección, comuna).
- **Filtrado Jerárquico por Comuna**: Los puestos de votación ahora se filtran automáticamente al seleccionar un Sector/Comuna en los desplegables.
- **Información Extendida (Popups)**: Las ventanas informativas de los marcadores ahora muestran el Sector/Comuna correspondiente.
- **API v2 de Puestos**: Actualizada la API para soportar parámetros de búsqueda y retornar metadatos geográficos completos.

### 🏢 Módulo Grupos de Interés
- **Heredabilidad de Visor**: Clonada la arquitectura de mapas en `mod_jac/grupos_de_interes.php` como base para el seguimiento de actores estratégicos.
- **Integración JAC**: Vinculado al Dashboard de administración del portal JAC.

---

## [1.6.0] - 2026-03-17
### 🚀 Portal Inteligente JAC (Juntas de Acción Comunal)
- **Acceso Híbrido**: Nueva arquitectura que separa la consulta pública de la administración privada.
- **Dashboard Standalone**: Implementada la vista `/JAC?view=dashboard` como portal público, permitiendo a cualquier ciudadano visualizar las juntas en el mapa sin login.
- **Login Independiente**: Creado un portal de acceso seguro en `mod_jac/login.php` con estética premium, integrado con la autenticación central de Aratio.
- **Gestión Privada**: La sección `?view=gestion` queda protegida, permitiendo la administración de planchas y miembros solo a usuarios autorizados.
- **Manejo de Jaimito**: Configurado usuario de prueba `jaimito@tangamandapio.com` con rol de administrador para la campaña.
- **Generación Demo**: Implementado sistema de carga masiva de datos con 10 JACs y 14 planchas de demostración en barrios reales de Cali.

### 🛠️ Mejoras Técnicas
- **Alpine.js Optimization**: Corregidos errores de llaves duplicadas en bucles `x-for` en los dropdowns territoriales.
- **Sidebar Dinámica**: La barra lateral del portal JAC ahora muestra u oculta secciones según el estado de autenticación.
- **Logout Integrado**: Acción de cierre de sesión añadida directamente al portal JAC.
- **Geolocalización**: Mejorada la jerarquía geográfica del mapa para filtrar por Municipio, Sector y Territorio Valle.

---

## [1.5.0] - 2026-03-16
### ✨ Módulo JAC (Base)
- Implementación inicial del sistema de Juntas de Acción Comunal.
- Creación de API REST CRUD para JACs.
- Primera versión del Visor Cartográfico Basado en Leaflet.
