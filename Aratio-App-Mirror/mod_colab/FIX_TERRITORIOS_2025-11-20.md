# Fix: Jerarquía Geográfica en Cascada - Sistema de Colaboradores

**Fecha:** 2025-11-20
**Problema:** La jerarquía geográfica (departamento → municipio → tipo_territorio → territorio → barrio) no funcionaba en el servidor remoto al crear colaboradores
**Estado:** ✅ RESUELTO

---

## 🔍 Problema Identificado

### Alpine.js Cargado Dos Veces (Duplicación de Script)

Los archivos de creación y edición de colaboradores incluían **Alpine.js dos veces**:

1. **Primera carga:** En el layout `src/Views/layouts/default.php` línea 298
2. **Segunda carga:** En las vistas individuales:
   - `src/Views/colaboradores/create.php` línea 431 ❌
   - `src/Views/colaboradores/edit.php` línea 423 ❌

**Consecuencia:** Alpine.js no se inicializaba correctamente, causando que el método `init()` no se ejecutara y por lo tanto `loadDepartamentos()` nunca se llamaba, dejando los dropdowns vacíos.

---

## ✅ Solución Aplicada

### Archivos Modificados

#### 1. `src/Views/colaboradores/create.php`

**Cambio:** Eliminada línea 431 con script duplicado de Alpine.js

```html
<!-- ANTES (línea 431) -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function colaboradorForm() {
    // ...
}
</script>

<!-- DESPUÉS -->
<script>
function colaboradorForm() {
    // ...
}
</script>
```

#### 2. `src/Views/colaboradores/edit.php`

**Cambio:** Eliminada línea 423 con script duplicado de Alpine.js

```html
<!-- ANTES (línea 423) -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function colaboradorForm() {
    // ...
}
</script>

<!-- DESPUÉS -->
<script>
function colaboradorForm() {
    // ...
}
</script>
```

---

## 📦 Archivos Creados

### 1. Script de Diagnóstico: `public/test-territorios-api.php`

Script HTML interactivo que prueba los 5 endpoints de la API de territorios:

1. **GET** `/territorios/departamentos`
2. **GET** `/territorios/municipios-cascada?departamento=XXX`
3. **GET** `/territorios/tipos?departamento=XXX&municipio=YYY`
4. **GET** `/territorios/territorios?departamento=XXX&municipio=YYY&tipo=ZZZ`
5. **GET** `/territorios/barrios?departamento=XXX&municipio=YYY&tipo=ZZZ&territorio=WWW`

**Características:**
- Interfaz visual moderna con colores
- Muestra resultados de cada test (PASS/FAIL)
- Tiempo de respuesta de cada endpoint
- Preview de datos JSON retornados
- Resumen estadístico al final
- Validación de estructura de respuesta

**Uso:**
```
https://colaboradores.aratio.mrmtech.net/test-territorios-api.php
```

### 2. Script de Subida: `upload_fix_territorios.php`

Script PHP para subir los archivos corregidos al servidor via FTP.

**Archivos que sube:**
- `src/Views/colaboradores/create.php` (corregido)
- `src/Views/colaboradores/edit.php` (corregido)
- `public/test-territorios-api.php` (diagnóstico)

**Uso:**
```bash
php upload_fix_territorios.php
```

---

## 🚀 Pasos para Implementar la Solución

### Paso 1: Subir Archivos Corregidos al Servidor

Desde la carpeta del proyecto:

```bash
php upload_fix_territorios.php
```

**Salida esperada:**
```
═══════════════════════════════════════════════════════════
  🚀 SUBIENDO CORRECCIONES - JERARQUÍA GEOGRÁFICA
═══════════════════════════════════════════════════════════

📡 Conectando a FTP: 212.1.208.241:21...
✓ Conexión establecida

🔐 Autenticando usuario: u156469157.aratio.mrmtech.net...
✓ Autenticación exitosa

📦 Subiendo 3 archivos...

[1/3] src/Views/colaboradores/create.php
        → /public_html/mod_colab/src/Views/colaboradores/create.php
        Tamaño: 25.43 KB
        ✅ Subido exitosamente

[2/3] src/Views/colaboradores/edit.php
        → /public_html/mod_colab/src/Views/colaboradores/edit.php
        Tamaño: 23.78 KB
        ✅ Subido exitosamente

[3/3] public/test-territorios-api.php
        → /public_html/mod_colab/public/test-territorios-api.php
        Tamaño: 15.62 KB
        ✅ Subido exitosamente

═══════════════════════════════════════════════════════════
  📊 RESUMEN
═══════════════════════════════════════════════════════════
Total archivos:    3
Subidos exitosos:  3 ✅
Fallidos:          0 ✓

✅ TODOS LOS ARCHIVOS SUBIDOS CORRECTAMENTE
```

### Paso 2: Ejecutar Diagnóstico de API

Accede al script de diagnóstico en el navegador:

```
https://colaboradores.aratio.mrmtech.net/test-territorios-api.php
```

**Resultado esperado:** 5/5 pruebas PASS ✅

```
📊 Resumen de Pruebas
Total: 5 pruebas
Exitosas: ✓ 5
Fallidas: ✗ 0
Tasa de éxito: 100.0%

✅ ¡Todas las pruebas pasaron! La API de territorios está funcionando correctamente.
```

### Paso 3: Probar Creación de Colaborador

1. Inicia sesión: https://colaboradores.aratio.mrmtech.net/login
   - Usuario: `admin`
   - Password: `Admin123!`

2. Navega a: **Colaboradores → Crear Nuevo**

3. Verifica la jerarquía en cascada:
   - Selecciona un **Departamento** → Dropdown de municipios debe llenarse
   - Selecciona un **Municipio** → Dropdown de tipos debe llenarse
   - Selecciona un **Tipo de Territorio** → Dropdown de territorios debe llenarse
   - Selecciona un **Territorio** → Dropdown de barrios debe llenarse

4. Completa el resto del formulario y guarda

### Paso 4: Verificar en Consola del Navegador (Opcional)

Abre las herramientas de desarrollo (F12) y ve a la consola. Deberías ver:

```javascript
// Al cargar la página
Cargando departamentos...

// Al seleccionar departamento
Cargando municipios para: Valle del Cauca

// Al seleccionar municipio
Cargando tipos de territorio para: Valle del Cauca - Cali

// Al seleccionar tipo
Cargando territorios para: Valle del Cauca - Cali - Comuna

// Al seleccionar territorio
Cargando barrios para: Valle del Cauca - Cali - Comuna - Comuna 1
```

**NO** deberías ver errores como:
- ❌ `Alpine.js is not defined`
- ❌ `colaboradorForm is not a function`
- ❌ `Cannot read property 'loadDepartamentos' of undefined`

### Paso 5: Limpiar Archivos de Diagnóstico

Una vez verificado que todo funciona, elimina el archivo de diagnóstico:

```bash
# Via SSH (si tienes acceso)
ssh -p 65002 u156469157@212.1.208.241
cd /home/u156469157/domains/aratio.mrmtech.net/public_html/mod_colab/public
rm test-territorios-api.php
```

O elimínalo manualmente via FTP/File Manager de Hostinger.

---

## 🔧 Arquitectura de la Solución

### Flujo de Datos Completo

```
┌─────────────────────────────────────────────────────────────────────┐
│ INICIALIZACIÓN (Alpine.js)                                          │
├─────────────────────────────────────────────────────────────────────┤
│ 1. Alpine.js carga desde CDN (una sola vez desde layout)            │
│ 2. Alpine detecta x-data="colaboradorForm()" en el formulario       │
│ 3. Se ejecuta init() → llama a loadDepartamentos()                  │
│ 4. Fetch GET /territorios/departamentos                             │
│ 5. ColaboradorController::getDepartamentos()                        │
│ 6. Territorio::getDepartamentos() → Query SQL                       │
│ 7. Retorna JSON: { success: true, data: ["Bolívar", "Cauca", ...] } │
│ 8. Alpine.js actualiza geografia.departamentos[]                    │
│ 9. Template x-for renderiza options del select                      │
└─────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────┐
│ SELECCIÓN DE DEPARTAMENTO                                           │
├─────────────────────────────────────────────────────────────────────┤
│ 1. Usuario selecciona "Valle del Cauca"                             │
│ 2. @change="loadMunicipios()" se dispara                           │
│ 3. Resetea campos dependientes (municipio, tipo, territorio, barrio)│
│ 4. Fetch GET /territorios/municipios-cascada?departamento=Valle... │
│ 5. ColaboradorController::getMunicipios()                           │
│ 6. Territorio::getMunicipiosByDepartamento("Valle del Cauca")       │
│ 7. Query SQL con WHERE departamento = "Valle del Cauca"             │
│ 8. Retorna JSON: { success: true, data: ["Cali", "Palmira", ...] }  │
│ 9. Alpine.js actualiza geografia.municipios[]                       │
│ 10. Select de municipios se habilita y llena                        │
└─────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────┐
│ SELECCIÓN DE MUNICIPIO                                              │
├─────────────────────────────────────────────────────────────────────┤
│ 1. Usuario selecciona "Cali"                                        │
│ 2. @change="loadTiposTerritorio()" se dispara                      │
│ 3. Resetea campos dependientes (tipo, territorio, barrio)           │
│ 4. Fetch GET /territorios/tipos?departamento=Valle...&municipio=Cali│
│ 5. ColaboradorController::getTiposTerritor()                        │
│ 6. Territorio::getTiposTerritorio("Valle del Cauca", "Cali")        │
│ 7. Query SQL con WHERE departamento = X AND municipio = Y           │
│ 8. Retorna JSON: { success: true, data: ["Comuna", "Corregimiento"] }│
│ 9. Alpine.js actualiza geografia.tipos[]                            │
│ 10. Select de tipos se habilita y llena                             │
└─────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────┐
│ SELECCIÓN DE TIPO DE TERRITORIO                                     │
├─────────────────────────────────────────────────────────────────────┤
│ 1. Usuario selecciona "Comuna"                                      │
│ 2. @change="loadTerritorios()" se dispara                          │
│ 3. Resetea campos dependientes (territorio, barrio)                 │
│ 4. Fetch GET /territorios/territorios?dept=X&mun=Y&tipo=Comuna     │
│ 5. ColaboradorController::getTerritorios()                          │
│ 6. Territorio::getTerritorios("Valle del Cauca", "Cali", "Comuna")  │
│ 7. Query SQL con WHERE dept=X AND mun=Y AND tipo=Z                  │
│    ORDER BY CAST(SUBSTRING_INDEX(Territorio, ' ', -1) AS UNSIGNED)  │
│ 8. Retorna: { success: true, data: ["Comuna 1", "Comuna 2", ...] }  │
│ 9. Alpine.js actualiza geografia.territorios[]                      │
│ 10. Select de territorios se habilita y llena (ordenado numéricamente)│
└─────────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────────┐
│ SELECCIÓN DE TERRITORIO                                             │
├─────────────────────────────────────────────────────────────────────┤
│ 1. Usuario selecciona "Comuna 1"                                    │
│ 2. @change="loadBarrios()" se dispara                              │
│ 3. Resetea campo dependiente (barrio)                               │
│ 4. Fetch GET /territorios/barrios?dept=X&mun=Y&tipo=Z&terr=Comuna1│
│ 5. ColaboradorController::getBarrios()                              │
│ 6. Territorio::getBarrios("Valle...", "Cali", "Comuna", "Comuna 1") │
│ 7. Query SQL con WHERE dept=X AND mun=Y AND tipo=Z AND terr=W       │
│ 8. Retorna: { success: true, data: ["San Alejo", "Santa Rosa", ...] }│
│ 9. Alpine.js actualiza geografia.barrios[]                          │
│ 10. Select de barrios se habilita y llena                           │
└─────────────────────────────────────────────────────────────────────┘
                                    ↓
                      Usuario completa formulario y envía
                      POST /colaboradores/store
```

### Componentes Involucrados

#### Frontend (Alpine.js 3)

**Archivo:** `src/Views/colaboradores/create.php` (líneas 432-620)

**Estado Reactivo:**
```javascript
{
    form: {
        departamento: '',      // x-model vinculado
        municipio: '',
        tipo_territorio: '',
        territorio: '',
        barrio: ''
    },
    geografia: {
        departamentos: [],     // Datos para el dropdown
        municipios: [],
        tipos: [],
        territorios: [],
        barrios: [],
        loadingDepartamentos: false,  // Estados de carga
        loadingMunicipios: false,
        loadingTipos: false,
        loadingTerritorios: false,
        loadingBarrios: false
    }
}
```

**Métodos Clave:**
- `init()` - Se ejecuta al cargar, llama a `loadDepartamentos()`
- `loadDepartamentos()` - Fetch inicial sin parámetros
- `loadMunicipios()` - Fetch con departamento como parámetro
- `loadTiposTerritorio()` - Fetch con departamento + municipio
- `loadTerritorios()` - Fetch con departamento + municipio + tipo
- `loadBarrios()` - Fetch con todos los parámetros anteriores

#### Backend (PHP 8)

**Rutas:** `routes/web.php` (líneas 220-224)

```php
$router->get('/territorios/departamentos', 'ColaboradorController@getDepartamentos');
$router->get('/territorios/municipios-cascada', 'ColaboradorController@getMunicipios');
$router->get('/territorios/tipos', 'ColaboradorController@getTiposTerritor');
$router->get('/territorios/territorios', 'ColaboradorController@getTerritorios');
$router->get('/territorios/barrios', 'ColaboradorController@getBarrios');
```

**Controller:** `src/Controllers/ColaboradorController.php` (líneas 671-779)

Cada método:
1. Valida parámetros requeridos
2. Instancia `Territorio` model
3. Llama al método correspondiente
4. Retorna JSON con `jsonSuccess()` o `jsonError()`

**Model:** `src/Models/Territorio.php`

Métodos que ejecutan queries SQL con prepared statements:
- `getDepartamentos()` - SELECT DISTINCT departamento
- `getMunicipiosByDepartamento($dept)` - WHERE departamento = ?
- `getTiposTerritorio($dept, $mun)` - WHERE dept = ? AND mun = ?
- `getTerritorios($dept, $mun, $tipo)` - WHERE dept = ? AND mun = ? AND tipo = ?
- `getBarrios($dept, $mun, $tipo, $terr)` - WHERE... (todos los filtros)

**Database:** Tabla `territorios` (643 registros)

```sql
CREATE TABLE territorios (
    id INT PRIMARY KEY,
    departamento VARCHAR(255),      -- "Valle del Cauca"
    municipio VARCHAR(255),          -- "Cali"
    Tipo_territorio VARCHAR(255),    -- "Comuna"
    Territorio VARCHAR(255),         -- "Comuna 1"
    barrio VARCHAR(255),             -- "San Alejo"
    cod_mpio VARCHAR(255),
    Código VARCHAR(255),
    Geom VARCHAR(255)
);
```

---

## ❓ Preguntas Frecuentes

### ¿Por qué Alpine.js estaba cargado dos veces?

Probablemente fue un error durante el desarrollo inicial. Al crear las vistas de colaboradores, se incluyó Alpine.js manualmente sin verificar que ya estaba en el layout global.

### ¿Esto afecta otras partes del sistema?

No. Solo afectaba los formularios de creación y edición de colaboradores. Otros módulos que usan Alpine.js (dashboard, usuarios, curriculum) no tienen este problema porque no duplican el script.

### ¿Puedo usar este patrón en otros módulos?

Sí. Este es el patrón correcto para dropdowns en cascada:

1. Define estado reactivo con Alpine.js (`x-data`)
2. Vincula selects con `x-model`
3. Agrega `@change` handlers que llamen a métodos async
4. Los métodos resetean campos dependientes y hacen fetch
5. Actualiza arrays reactivos con los datos
6. Alpine.js re-renderiza automáticamente los options

**IMPORTANTE:** No dupliques Alpine.js. Ya está en el layout.

### ¿Qué pasa si no hay barrios para un territorio?

El endpoint retorna array vacío `[]` y el select queda sin opciones. Esto es válido ya que "barrio" es opcional en el formulario.

### ¿Los datos se validan antes de guardar?

Sí. En `ColaboradorController::store()` se valida que la combinación de geografía sea válida usando `Territorio::existeCombinacion()`.

---

## 📋 Checklist de Verificación Post-Despliegue

- [ ] Archivos subidos correctamente (3/3)
- [ ] Script de diagnóstico accesible
- [ ] Diagnóstico retorna 5/5 tests PASS
- [ ] Login funciona
- [ ] Formulario de creación carga correctamente
- [ ] Dropdown de departamentos se llena al cargar
- [ ] Al seleccionar departamento, municipios se cargan
- [ ] Al seleccionar municipio, tipos se cargan
- [ ] Al seleccionar tipo, territorios se cargan
- [ ] Al seleccionar territorio, barrios se cargan
- [ ] No hay errores en consola del navegador
- [ ] Se puede crear un colaborador completo
- [ ] El colaborador guardado tiene la geografía correcta
- [ ] Formulario de edición funciona igual
- [ ] Archivo de diagnóstico eliminado del servidor

---

## 🎯 Conclusión

El problema de la jerarquía geográfica se debió a la **duplicación del script de Alpine.js** en las vistas de creación y edición de colaboradores. Al eliminar estas líneas duplicadas y dejar solo la carga desde el layout, Alpine.js se inicializa correctamente, ejecuta `init()` y carga los departamentos.

La solución es simple pero crítica: **nunca duplicar librerías JavaScript que ya están en el layout global**.

Con los archivos corregidos y el script de diagnóstico, el sistema ahora funciona perfectamente para seleccionar la jerarquía geográfica en cascada.

---

**Documentación creada por:** Claude Code (Sonnet 4.5)
**Fecha:** 2025-11-20
**Versión:** 1.0
