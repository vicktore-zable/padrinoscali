# Documentación de Correcciones: Registro de Líderes y Cascadas

Fecha: 15 de Febrero de 2026
Objetivo: Estabilizar el formulario de Registro de Líderes, solucionar errores de CSP y asegurar la carga de listas en cascada (Líderes, Puestos de Votación).

## 1. Resumen del Problema
Los formularios de registro público (`/registro-lider`) presentaban múltiples fallos:
1.  **Bloqueo de Scripts (CSP):** Los iconos `lucide` no cargaban por restricciones de seguridad.
2.  **Conflictos de Inclusión:** Errores "Cannot redeclare function" al cargar configuraciones múltiples veces.
3.  **Fallo en Cascadas:**
    *   La lista de "Líder a quien reporta" no cargaba (Error 401/Auth).
    *   La lista de "Puestos de Votación" no cargaba tras seleccionar municipio.
4.  **UI Reactiva:** Alpine.js no actualizaba el DOM correctamente al recibir nuevos datos.

---

## 2. Cambios Realizados

### A. Configuración y Seguridad (Backend)

#### `mod_colab/config/config.php`
*   **Protección de Funciones:** Se envolvieron todas las funciones globales (`env`, `base_path`, etc.) en bloques `if (!function_exists(...))` para prevenir errores fatales si el archivo se carga más de una vez.
*   **CSP (Content Security Policy):** Se añadió `https://unpkg.com` a la directiva `script-src` para permitir la carga de la librería de iconos Lucide.

#### `index.php` (Raíz)
*   **Orden de Carga:** Se movió la lógica de enrutamiento local (`/registro-lider`, etc.) **antes** de la inclusión de la configuración global. Esto asegura un entorno limpio para los módulos públicos.

### B. APIs (Backend)

#### `api/colaboradores.php`
*   **Acceso Público:** Se modificó la validación de autenticación para **excluir** la acción `lideres`. Ahora `handleGetLideres` es accesible públicamente sin sesión iniciada.
*   **Inclusión de Candidato:** Se actualizó `handleGetLideres` para buscar e incluir automáticamente al **Candidato** de la campaña al principio de la lista, asegurando que siempre haya una opción por defecto.
*   **Búsqueda Flexible:** Se amplió el filtro SQL para aceptar perfiles `Lider`, `Líder` (con tilde) o `Candidato`.

#### `api/territorios.php`
*   **Carga Segura:** Se simplificó la carga de `config.php` confiando en las nuevas protecciones `function_exists`.
*   **Filtro Puestos de Votación:** Se **relajó** la consulta de puestos de votación para filtrar **solo por Municipio**.
    *   *Razón:* Discrepancias menores en el nombre del Departamento (tildes, mayúsculas) entre el frontend y la base de datos causaban que la consulta retornara vacío. Dado que el Municipio suele ser único en el contexto (o suficientemente específico), esto garantiza resultados.

### C. Frontend (`registro_lider.php`)

*   **Reactividad Alpine.js:**
    *   Se forzó la actualización de arrays usando sintaxis de propagación: `this.puestos = [...result.data]`.
    *   Se corrigió el bucle `x-for` para usar una variable iteradora simple (`p in puestos`) y evitar conflictos de nombres.
    *   Se asignó una `:key="p.id"` única para optimizar el renderizado.
*   **Manejo de Carga (Puestos):**
    *   Se separó la carga de puestos en su propia función asíncrona robusta `loadPuestos()`.
    *   Se eliminó la dependencia del campo `departamento` en la petición API para coincidir con el backend relajado.
*   **Feedback Visual:**
    *   Se añadieron mensajes de estado ("Cargando...", "No se encontraron puestos") para que el usuario sepa qué está ocurriendo.

---

## 3. Guía para Prevenir Regresiones (Instrucciones)

Para mantener la estabilidad de las cascadas y los registros, siga estas reglas estrictas al modificar el código:

### 1. Mantener Protecciones en Configuración
**NUNCA** elimine los cheques `if (!function_exists('nombre_funcion'))` en `config.php`. Estos son vitales porque la arquitectura actual puede cargar la configuración desde múltiples puntos de entrada (`index.php` raíz, `api/`, `mod_colab/`).

### 2. Acceso a APIs Públicas
Si crea un nuevo endpoint que debe ser accedido desde un formulario de registro público (sin login), **debe explícitamente excluirlo** de `requireAuth()` en el archivo API correspondiente.
*Ejemplo:*
```php
if ($action !== 'lideres' && $action !== 'nueva_accion_publica') {
    requireAuth();
}
```

### 3. Manejo de Cascadas en Frontend (Alpine.js)
Al implementar selectores dependientes (Cascadas):
*   **Limpieza:** Limpie siempre el array dependiente antes de cargar (`this.hijos = []`).
*   **Keys Únicas:** Use siempre direcitva `:key` en bucles `x-for`.
*   **Sincronización:** Si una carga depende de otra, asegúrese de que los datos padres (ej. ID del municipio) estén disponibles y sean válidos antes de llamar a la API.
*   **Depuración:** Mantenga `console.log` o mensajes de error visibles en la UI (`x-show="error"`) para diagnosticar rápidamente fallos de datos vacíos.

### 4. Consultas Geográficas (SQL)
Evite filtros excesivamente estrictos en campos de texto libre como "Departamento" o "Municipio" si no está seguro de la normalización de datos. Prefiera filtrar por IDs si es posible, o use `LIKE` para mayor flexibilidad.
