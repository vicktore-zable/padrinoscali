# Changelog - Sistema Aratio

Todos los cambios notables en este proyecto serán documentados en este archivo.

## [2.0.0] - 2026-03-19
### 🌟 Portal JAC: Evolución Geográfica & Rebranding
#### Módulo Entidades (mod_jac)
- **Rebranding Completo**: Migración de la terminología "JAC" a **"Entidades / Organizaciones"** en toda la interfaz (filtros, tablas, popups).
- **Dashboard Geo 2.0**:
    - **Visualización Premium**: Implementación de marcadores tipo "Burbuja" con códigos de colores por categoría (JAC, Deporte, Cultura, Ambiente, Social).
    - **Clusters Inteligentes**: Los marcadores agrupados muestran ahora el conteo de entidades en la ubicación.
    - **Popups de Alta Gama**: Ventanas de información enriquecidas que muestran **Presidente, Dirección, Email, Observaciones, Meta Votos y Afiliados**.
    - **Estandarización Geográfica**: Ajuste de etiquetas de "Territorio" a **"Comuna / Corregimiento"** para mayor precisión local.
- **Optimización de API** (`api/jac.php`): Inclusión de campos de contacto y metadatos extendidos en el endpoint de mapas con manejo de valores nulos.
- **UI/UX Avanzada**: Integración de efectos *glassmorphism*, micro-animaciones en marcadores y soporte completo para iconos de Lucide.

#### Mejoras Técnicas
- **Refactorización de JS**: Limpieza de lógica redundante en `renderMapItems` y optimización del flujo de datos asíncronos en Alpine.js.
- **Despliegue Automatizado**: Sincronización verificada de todos los componentes en el entorno de producción (`aratio.mrmtech.net`).

---


## [1.5.0] - 2026-03-17
### ✨ Nuevas Funcionalidades
#### Módulo JAC (mod_jac) — Juntas de Acción Comunal
- **Tabla `jac_registros`**: Almacena las JACs asociadas a una campaña (`id_campana`) con campos: nombre, presidente, teléfono, email, dirección, `territorio_valle`, municipio, sector, comuna, votos comprometidos, afiliados, estado y observaciones.
- **API REST** (`/api/jac.php`): Endpoints CRUD completos — list, get, stats, create, update, delete. Filtros por municipio, estado, territorio, búsqueda de texto.
- **CRUD Administrativo** (`?page=jac`): Vista integrada en Aratio con tabla interactiva, filtros, modal Crear/Editar (Alpine.js), 4 KPIs en cabecera.
- **Dashboard Público** (`/mod_jac/dashboard.php` / `/dashboard-jac`): Visible sin autenticación, con 6 KPIs, Chart.js (barras por municipio + dona de estado), ranking de territorios, listado de JACs activas.
- **Integración Dashboard Aratio** (`pages/dashboard.php`): Banner "Módulo JAC" con contador de activas y botones de acceso rápido.
- **Sidebar**: Enlace "JAC" bajo la sección Administración con ícono `building-2`.
- **Routing**: Regla en `.htaccess` → `/dashboard-jac` apunta a `mod_jac/dashboard.php`.

### 🔧 Mejoras
- **`index.php`**: `jac` añadido a `$paginasPermitidas`; routing para `dashboard_jac_publico`.

---

## [1.4.1] - 2026-03-10
### ✨ Mejoras
#### Gestión de Usuarios
- **Aislamiento de Datos por Campaña**: Filtrado visual para que los supervisores solo vean colaboradores de su campaña.
- **Botones y Acciones CRUD**: Habilitación de acciones (Crear/Editar/Eliminar) para roles `supervisor`, limitadas a colaboradores de su misma jerarquía.
- **Roles Clarificados**: Nuevo diseño de modal con diferenciación entre Roles de Plataforma y Niveles de Campo.
- **Tabla Inteligente**: Ordenación asíncrona por todas las columnas (Usuario, Rol, Estado, Último Acceso), con prioridad jerárquica para Roles (Super Admin primero).

#### Colaboradores
- **Edición Rápida en Tabla**: Añadido modal de edición directa desde la tabla (`Nivel`, `Perfil` y `Estado`) en la nueva vista y en la vista legacy. El estado se sincroniza correctamente recalculando el progreso.

### 🐛 Correcciones
#### Navegación
- **Ruta de Elecciones**: Se ha corregido el interceptor en `index.php` que redirigía erróneamente el botón administrativo de "Elecciones" hacia el submódulo público, impidiendo acceder a la configuración de la jerarquía electoral.

---

## [1.4.0] - 2026-03-10
### ✨ Nuevas Funcionalidades
#### Módulo Día D (mod_diaD + diaD_dist)
- **Captura de Votos en Tiempo Real**: Interfaz optimizada para móviles para reporte de votos por mesa.
- **Dashboard Territorial**: Mapa Leaflet, heatmap, matriz de mesas, semáforo de cumplimiento y ranking de líderes.
- **Cálculo Incremental**: Suma automática de votos nuevos con totales calculados por el servidor.
- **Exportación XLSX**: Exportación a Excel del detalle por mesa.
- **Búsqueda Contextual**: Búsqueda de puestos filtrada por municipio (3+ caracteres).
- **Acceso Público**: Dashboard visible sin autenticación.
- **Datos de Prueba**: Script seeder validado con 10 registros en Yumbo, Cali, Vijes y Palmira.

#### Registro de Simpatizantes
- **Fecha de Nacimiento Opcional**: El campo ya no es obligatorio.
- **Eliminación del Botón "Soy Líder"**: Removido del header para simplificar la UX.

#### Registro de Líderes
- **Protección con Autenticación**: Solo usuarios con rol `admin-campana` o `super-admin` pueden acceder.

#### Landing Page
- **Rediseño Premium**: Gradientes intensificados, glassmorphism mejorado, tipografía editorial.
- **Animaciones Sutiles**: Micro-animaciones para mejorar el engagement.
- **Branding Día D**: Sección dedicada con gradientes y acentos de campaña.

#### Dashboard
- **Navegación por Municipio**: Botones/tabs dedicados para Cali y Yumbo con filtro global.

### 🔧 Mejoras
- **Documentación**: Sincronización de VERSION.txt, CHANGELOG.md y ESTADO_SISTEMA.md.
- **Config**: APP_VERSION actualizado a 1.4.0.

---

## [1.3.3] - 2026-03-01
### ✨ Nuevas Funcionalidades
#### Gestión de Red en Perfil de Colaboradores
- **Dashboard Interactivo**: Integración del panel de analítica ("Gestión de Red") dentro del detalle individual del colaborador, homologando la vista de métricas del portal principal de líderes.
- **Navegación Cruzada**: 
  - Enlaces directos desde la tabla general de colaboradores al perfil individual de cada persona.
  - El campo "Líder Directo" ahora funciona como un hipervínculo navegable hacia el perfil superior.
- **Impacto Territorial y KPIs**: Cálculo en tiempo real de la estructura de referidos (Equipo Directo Nivel 1 y Red Consolidada), con desglose geográfico (barrios) y tabla de seguimiento avanzada.
- **API Optimizada**: El endpoint de colaboradores cuenta ahora con lógica recursiva para calcular y exportar volúmenes exactos de crecimiento en la base de seguidores.

---

## [1.3.2] - 2026-02-18
### ✨ Nuevas Funcionalidades
#### Portal del Líder y Sincronización
- **Unificación de Sesiones**: Se unificó el nombre de la sesión a `ARATIO_SESSION` en todos los módulos (Núcleo, mod_colab, mod_lider) para permitir la navegación sin pérdida de estado.
- **Seguridad de Sesiones**: Corrección de nombres de columnas en la tabla `sesiones` (`token`, `expires_at`) para cumplir con el esquema real de la base de datos.
- **Modelo de Eventos**: Actualización integral del modelo `Evento.php` para usar los campos reales de la BD (`fecha_inicio`, `lugar`, `tipo`), resolviendo errores de visualización y ordenamiento.
- **Entorno Local**: Sincronización del `router.php` local para emular el comportamiento del `.htaccess` de producción, facilitando pruebas de registro e inscripción.

### 🔧 Mejoras
- **Logs**: Implementación de mensajes de debug más detallados en el proceso de autenticación y verificación de sesiones.
- **Configuración**: Sincronización de credenciales de base de datos en archivos de configuración locales y archivos `.env`.

### 🐛 Correcciones
- **Mi Red**: Corrección de la ruta de la API en la visualización de Vis.js para usar rutas relativas compatibles con el enrutamiento unificado.
- **Agenda de Eventos**: Reparación de la vista `events.php` para mostrar correctamente la fecha y el lugar usando los campos actualizados.

---

## [1.3.1] - 2026-02-15
### ✨ Nuevas Funcionalidades
#### Sistema de Eventos y Asistencia (Mejoras Estratégicas)
- **Reportes Avanzados**:
  - Exportación a Excel (CSV) de asistentes por evento (`api/asistencia_export_excel.php`).
  - Reporte de Impresión PDF profesional con estilos @media print, ficha técnica completa y mapa.
- **Análisis Post-Evento**:
  - Nuevo módulo de **Reevaluación Estratégica** en el editor de eventos para seguimiento de impacto.
  - Campo `reevaluacion_estrategica` en BD para análisis de logros territoriales.
- **Registro Manual Potenciado**:
  - Formulario de inscripción manual extendido: fecha nacimiento, tipo documento, documento, teléfono, email, género, firma digital y observaciones.
  - Gestión integrada de Habeas Data y consentimiento de comunicaciones en registros administrativos.

### 🔧 Mejoras
#### Base de Datos
- **Tabla `asistencia_eventos`**: Agregada columna `territorio_id` para vinculación geopolítica precisa.
- **Tabla `eventos`**: Agregada columna `reevaluacion_estrategica` (TEXT).

### 🐛 Correcciones
- **Asistencia**: Reparado error de visualización de asistentes en el modal (causado por columna faltante). Sincronización verificada de 80 registros existentes.

## [1.3.0] - 2026-02-12
### Solucionado
- **Formulario de Inscripción**: Corrección total de los selectores geográficos (Departamentos, Municipios, Territorios, Barrios).
- **Asignación de Líderes**: Reparación del filtro dinámico de "Líder Referente" basado en la campaña seleccionada.
- **Puestos de Votación**: Restauración de la carga automática de puestos de votación según el municipio.
- **Limpieza de Código**: Eliminación de duplicidad de código y scripts corruptos en la vista de inscripción pública.
- **Estética**: Ajustes en el layout público para mayor consistencia visual (Aratio Magenta/Dorado).

## [1.2.0] - 2026-01-23

### ✨ Nuevas Funcionalidades

#### Integración de Colaboradores (Aratio Core)
- **Módulo Interno de Colaboradores** (`pages/colaboradores.php`)
  - Consumo de API externa `https://colaboradores.aratio.mrmtech.net/api/v1/colaboradores`
  - Autenticación vía `X-API-Key: aratio_prod_secure_token_5d_2025`
  - Filtrado automático por campaña activa para usuarios normales.
  - **Vista Global de Súper Administrador**: permite ver colaboradores de todas las campañas con su respectiva asociación.
  - Columna de campaña dinámica en el listado para administradores.
  - Diseño premium unificado con Aratio (Tailwind + Alpine.js).
  - Micro-avatares dinámicos y estados con badges de colores.
  - Barras de progreso de cumplimiento de metas de votos.

- **Acceso Directo desde Campañas**
  - Añadido botón "Colabs" en cada tarjeta de la vista de campañas para filtrado rápido.
  - Integración en la barra lateral reemplazando el enlace externo.

### 🚀 Deployment
- **Entorno**: Producción (https://aratio.mrmtech.net)
- **Archivos Desplegados**:
  - `/index.php` (layout y sidebar)
  - `/pages/colaboradores.php` (módulo nuevo)
  - `/pages/campanas.php` (mejoras en UI)
- **Estado**: ✅ Completado y verificado en Hostinger.


## [1.1.0] - 2025-11-29

### ✨ Nuevas Funcionalidades

#### Sistema de Asistencia a Eventos
- **Formulario Público de Registro** (`registro_asistencia.php`)
  - Acceso vía código QR con parámetro `?evento=ID`
  - Jerarquía geográfica en cascada de 5 niveles (Departamento → Municipio → Tipo Territorio → Territorio → Barrio)
  - Integración con API `/api/territorios.php` para carga dinámica de ubicaciones
  - 9 áreas de interés con multi-selección
  - Firma digital con canvas HTML5
  - Validación de habeas data obligatoria
  - Cierre automático de registro 24 horas después del evento
  - Campos adicionales: tipo documento, fecha nacimiento, género, teléfono, email

- **Dashboard de Asistencia** (`pages/dashboard_asistencia.php`)
  - 4 KPIs en tiempo real: Total asistentes, Promedio edad, Género predominante, Área más popular
  - 8 filtros dinámicos: evento, rango fechas, género, grupo etario, departamento, municipio, tipo territorio, área de interés
  - 4 gráficos interactivos (Chart.js):
    - Distribución por género (dona)
    - Distribución por grupo etario (barras)
    - Top 5 áreas de interés (barras horizontales)
    - Tendencia de registros por fecha (líneas)
  - Tabla de datos con búsqueda en tiempo real
  - Exportación preparada (Excel/PDF)

- **Gestión de Asistencias en Eventos** (`pages/eventos.php`)
  - Botón desplegable "Gestionar Asistencias" con 3 opciones:
    - Generar Código QR para registro público
    - Ver lista completa de asistentes (modal con 8 columnas)
    - Acceso directo a Dashboard de Análisis
  - Modal de asistentes actualizado con nuevos campos:
    - Tipo documento, documento, género, grupo etario
    - Ubicación completa (depto/municipio/territorio/barrio)
    - Teléfono, áreas de interés, habeas data

### 🔧 Mejoras

#### Base de Datos
- **Tabla `asistencia_eventos`** - 13 nuevos campos:
  - `tipo_documento` VARCHAR(10) - Tipo de documento de identidad
  - `fecha_nacimiento` DATE - Fecha de nacimiento del asistente
  - `genero` VARCHAR(20) - Género del asistente
  - `grupo_etareo` VARCHAR(20) - Calculado automáticamente por trigger
  - `departamento` VARCHAR(100) - Nivel 1 de jerarquía geográfica
  - `municipio` VARCHAR(100) - Nivel 2 de jerarquía geográfica
  - `tipo_territorio` VARCHAR(100) - Nivel 3 (Localidad/Comuna/Corregimiento/Zona)
  - `territorio` VARCHAR(100) - Nivel 4 de jerarquía geográfica
  - `barrio` VARCHAR(100) - Nivel 5 de jerarquía geográfica
  - `areas_interes` JSON - Array de áreas de interés seleccionadas
  - `firma_digital` LONGTEXT - Firma en formato base64
  - `habeas_data` BOOLEAN - Aceptación de política de datos
  - `acepta_comunicaciones` BOOLEAN - Aceptación de comunicaciones

- **Triggers Creados**:
  - `calcular_grupo_etareo_asistencia` (BEFORE INSERT) - Calcula grupo etario automáticamente
  - `calcular_grupo_etareo_asistencia_update` (BEFORE UPDATE) - Actualiza grupo etario en modificaciones

- **Índices Agregados**:
  - `idx_grupo_etareo` - Optimiza consultas por grupo etario
  - `idx_tipo_territorio` - Optimiza filtros territoriales

#### APIs REST
- **Nueva API**: `/api/asistencia_eventos.php`
  - GET: Consultar asistentes de un evento
  - POST: Registrar nueva asistencia
  - Validaciones: evento activo, no duplicados, campos obligatorios

- **Nueva API**: `/api/dashboard_asistencia.php`
  - GET con 8 parámetros de filtrado
  - Retorna KPIs, datos para gráficos y tabla de asistentes
  - Optimizada con índices de base de datos

#### Interfaz de Usuario
- **Selects en Cascada**: Implementación consistente en:
  - Formulario de registro público
  - Formulario de creación/edición de eventos
  - Página de acciones comunitarias

- **Componentes Reutilizables**:
  - Sistema de carga dinámica de territorios vía Alpine.js
  - Funciones JavaScript estandarizadas (cargarDepartamentos, cargarMunicipios, etc.)

### 📝 Documentación
- `AJUSTES_ASISTENCIA.md` - Documentación técnica completa de los 4 ajustes realizados
- `staging_upload/` - Paquete completo de deployment con:
  - 5 archivos PHP (112 KB)
  - 1 migración SQL (7 KB)
  - 8 documentos de deployment (82 KB)
  - 2 scripts de automatización (6 KB)
  - Total: 16 archivos (~207 KB)

### 🚀 Deployment
- **Entorno**: Producción (https://aratio.mrmtech.net)
- **Método**: FTP con curl
- **Archivos Desplegados**:
  - `/registro_asistencia.php` (28 KB)
  - `/pages/eventos.php` (46 KB)
  - `/pages/dashboard_asistencia.php` (19 KB)
  - `/api/asistencia_eventos.php` (10 KB)
  - `/api/dashboard_asistencia.php` (9 KB)
- **Base de Datos**: Migración aplicada exitosamente en producción
- **Fecha**: 29 de Noviembre de 2025
- **Estado**: ✅ Completado y verificado

### 🔗 Enlaces Importantes
- Formulario público: `https://aratio.mrmtech.net/registro_asistencia.php?evento=ID`
- Dashboard asistencia: `https://aratio.mrmtech.net/index.php?page=dashboard_asistencia`
- Módulo eventos: `https://aratio.mrmtech.net/index.php?page=eventos`

### 🐛 Correcciones
- Resuelto error 404 en registro_asistencia.php (ubicación incorrecta /pages/ → root)
- Corregida migración SQL con sintaxis `ADD COLUMN IF NOT EXISTS`
- Actualizada URL de registro en eventos.php para apuntar a root

---

## [1.0.0] - 2025-11-23

### Lanzamiento Inicial
- Sistema multi-tenant de gestión electoral
- 12 módulos completos
- 12 tablas de base de datos
- 6 APIs REST
- Autenticación con 5 niveles de roles
- Jerarquía territorial de 5 niveles
- Metodología de compromisos (5 preguntas)
- Mapas interactivos con Leaflet.js
- Gráficos dinámicos con Chart.js
- Detección automática de entorno (local/producción)

---

## Versionado

Este proyecto usa [Versionado Semántico](https://semver.org/):
- **MAJOR**: Cambios incompatibles en API
- **MINOR**: Nueva funcionalidad compatible con versiones anteriores
- **PATCH**: Correcciones de bugs compatibles con versiones anteriores

**Versión actual:** 1.4.0
