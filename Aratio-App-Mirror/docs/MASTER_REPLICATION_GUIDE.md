# 🏆 GUÍA MAESTRA DE REPLICACIÓN - Aratio MCMS

Esta guía consolida todo el conocimiento necesario para replicar, instalar y desplegar el sistema **Aratio - Multi-Campaign Management System**.

## 📍 Mapa de Documentación

Toda la documentación relevante ha sido consolidada en el directorio `/docs`.

### 1. Fundamentos y Arquitectura (`docs/core/`)
- **[Reporte de Revisión del Sistema](file:///h:/Mi%20unidad/2025/5d/app/Multi-Campaign%20Management%20System/docs/core/REPORTE_REVISION_SISTEMA.md)**: El "plano" completo de la base v1.0.
- **[Sistema Geográfico](file:///h:/Mi%20unidad/2025/5d/app/Multi-Campaign%20Management%20System/docs/core/DOCUMENTACION_SISTEMA_GEOGRAFICO.md)**: Cómo funciona la jerarquía territorial de 5 niveles.
- **[Integración de Colaboradores](file:///h:/Mi%20unidad/2025/5d/app/Multi-Campaign%20Management%20System/docs/core/DOCUMENTACION_INTEGRACION_COLABORADORES.md)**: Lógica del motor `mod_colab`.
- **[Portal del Líder](file:///h:/Mi%20unidad/2025/5d/app/Multi-Campaign%20Management%20System/docs/core/PLAN_PORTAL_LIDER.md)**: Roadmap y diseño del portal privado.
- **[Arquitectura C4](file:///h:/Mi%20unidad/2025/5d/app/Multi-Campaign%20Management%20System/docs/core/DOCUMENTACION_TECNICA_C4.md)**: Diagramas de alto nivel.

### 2. Guías de Configuración y Despliegue (`docs/guides/`)
- **[Credenciales de Acceso](file:///h:/Mi%20unidad/2025/5d/app/Multi-Campaign%20Management%20System/docs/guides/CREDENCIALES_ACCESO.md)**: 🔑 SSH, FTP, DB y GitHub (Actualizado).
- **[Guía de Inicio Local](file:///h:/Mi%20unidad/2025/5d/app/Multi-Campaign%20Management%20System/docs/guides/GUIA_INICIO_LOCAL.md)**: Paso a paso para correr en XAMPP o Laragon.
- **[Manual de Despliegue](file:///h:/Mi%20unidad/2025/5d/app/Multi-Campaign%20Management%20System/docs/guides/INSTRUCCIONES_DESPLIEGUE.md)**: Cómo subir cambios a Hostinger sin romper nada.
- **[Sincronización de Producción](file:///h:/Mi%20unidad/2025/5d/app/Multi-Campaign%20Management%20System/docs/guides/GUIA_SINCRONIZAR_PRODUCCION.md)**: Cómo bajar datos reales a local.

### 3. Historial y Control de Cambios (`docs/`)
- **[CHANGELOG.md](file:///h:/Mi%20unidad/2025/5d/app/Multi-Campaign%20Management%20System/docs/CHANGELOG.md)**: Evolución del sistema versión a versión.
- **[Estado del Proyecto](file:///h:/Mi%20unidad/2025/5d/app/Multi-Campaign%20Management%20System/docs/ESTADO_PROYECTO_FEBRERO_2026.md)**: Resumen de la versión actual (v1.3.2).

---

## 🚀 Pasos Críticos para Replicar el Sistema

1.  **Entorno Local**:
    - Instalar PHP 8.1+ y MySQL 8.
    - Clonar repositorio.
    - Configurar `config/config.php` apuntando a `localhost`.
    - Importar esquema desde el último backup en el panel de Hostinger.

2.  **Configuración Geográfica**:
    - El sistema depende de la tabla `territorios`. Asegurarse de correr los scripts de importación en `docs/core/DOCUMENTACION_SISTEMA_GEOGRAFICO.md` si la DB está vacía.

3.  **Conexión Remota**:
    - Usar la nueva Skill de AI para despliegues rápidos vía FTP/SSH.
    - **IMPORTANTE**: El puerto SSH en Hostinger es **65002**.

---

> [!TIP]
> Para cualquier modificación nueva, siempre consulta primero el `CHANGELOG.md` para entender el estado de las dependencias.
