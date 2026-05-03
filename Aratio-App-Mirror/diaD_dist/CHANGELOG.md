# Changelog - Módulo Día D (Standalone)

Todas las fechas en formato AAAA-MM-DD.

## [1.4.0] - 2026-03-08
### Añadido
- **Ordenamiento en Matriz**: Se añadió un botón para ordenar los puestos de la Matriz Territorial por cantidad de votos de mayor a menor.
- **Generación de Datos de Prueba**: Se implementó y ejecutó un script seeder para validar el funcionamiento con 10 registros distribuidos en varios municipios.
- **Optimización de Gráficos**: Mejora en el renderizado de Chart.js para evitar colisiones de IDs y asegurar la actualización de datos.

## [1.3.0] - 2026-03-08
### Añadido
- Nuevo diseño de Dashboard a ancho completo (100% de la pantalla).
- Reorganización de tarjetas de métricas: Votos, Puestos, Mesas y Líderes Únicos.
- Diagrama de barras con el ranking de los 10 líderes con más votos.
- Diagrama de barras con la votación por Comuna (Yumbo).
- Lógica de mapa proporcional: los círculos crecen según la cantidad de votos reportados.
- Botón de exportación a Excel (XLSX) en el detalle por mesa.
- Eliminación de la sección de alertas críticas del Dashboard.

## [1.2.0] - 2026-03-08
### Añadido
- **Búsqueda Contextual**: Se mejoró la búsqueda del puesto de votación para ser contextual dentro del municipio seleccionado, activándose al escribir 3 caracteres.
- **Acceso Público**: Se eliminó la restricción de acceso al dashboard para que sea visible públicamente.
- **Identidad Visual**: Se actualizó el título de los reportes y dashboard para reflejar el nombre de la campaña (Camilo Hurtado).
- **Control de Versiones**: Se integró un footer con control de versión digital y enlace directo al changelog.

## [1.1.1] - 2026-03-04
### Añadido
- **Eliminación de WhatsApp**: Se eliminó el redireccionamiento automático a WhatsApp tras guardar el reporte, dejando únicamente el registro en la base de datos por petición del usuario.
- **Botón de Acción**: Se actualizó el texto del botón a "Guardar Reporte".

## [1.1.0] - 2026-03-04
### Añadido
- **Automatización de Totales**: El campo "Total Acumulado" ahora se calcula automáticamente en el servidor sumando los reportes previos de la mesa.
- **Simplificación de Captura**: Se eliminó el input manual de total acumulado para reducir errores humanos.
- **Integración WhatsApp**: Se actualizó el mensaje de WhatsApp para incluir el total calculado devuelto por la API.

## [1.0.0] - 2026-03-03
### Añadido
- **Módulo de Captura**: Interfaz optimizada para móviles para reporte de votos en tiempo real.
- **Dashboard Territorial**: Visualización con mapa Leaflet, heatmap y matriz de mesas.
- **Cálculo Incremental**: Lógica de suma de votos nuevos para garantizar integridad de totales.
- **Sistema de Semáforo**: Alertas visuales basadas en cumplimiento de metas por puesto/mesa.
- **Filtros Avanzados**: Filtrado por municipio y detalle exhaustivo por mesa.
- **Geolocalización**: Auto-ajuste de mapa (`fitBounds`) y marcadores dinámicos con pulsación.
- **Personalización (Theming)**: Archivo `config/theme.php` para cambiar colores y marca en un solo lugar.
- **Empaquetado Standalone**: Estructura de archivos desacoplada para fácil despliegue en otros hostings.

### Seguridad
- Sanitización de entradas en todos los endpoints.
- Optimización de conexiones persistentes con `DatabaseManager`.
