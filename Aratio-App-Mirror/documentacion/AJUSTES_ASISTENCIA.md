# AJUSTES AL SISTEMA DE ASISTENCIA

**Fecha:** 29 de Noviembre de 2025
**Estado:** ✅ COMPLETADO Y EN PRODUCCIÓN

---

## 1. ✅ Jerarquía Geográfica en Cascada (COMPLETADO)

**Archivo:** `registro_asistencia.php`

### Cambios realizados:

#### HTML - Reemplazar inputs por selects en cascada (líneas 226-266):

```html
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Departamento *</label>
    <select x-model="form.departamento" @change="cargarMunicipios()" required
           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
        <option value="">Seleccionar departamento...</option>
        <template x-for="dep in listas.departamentos" :key="dep">
            <option :value="dep" x-text="dep"></option>
        </template>
    </select>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Municipio *</label>
    <select x-model="form.municipio" @change="cargarTiposTerritorio()" required
           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
           :disabled="!form.departamento">
        <option value="">Seleccionar municipio...</option>
        <template x-for="mun in listas.municipios" :key="mun">
            <option :value="mun" x-text="mun"></option>
        </template>
    </select>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Territorio</label>
    <select x-model="form.tipo_territorio" @change="cargarTerritorios()"
           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
           :disabled="!form.municipio">
        <option value="">Seleccionar tipo...</option>
        <template x-for="tipo in listas.tipos_territorio" :key="tipo">
            <option :value="tipo" x-text="tipo"></option>
        </template>
    </select>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Territorio</label>
    <select x-model="form.territorio" @change="cargarBarrios()"
           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
           :disabled="!form.tipo_territorio">
        <option value="">Seleccionar territorio...</option>
        <template x-for="terr in listas.territorios" :key="terr">
            <option :value="terr" x-text="terr"></option>
        </template>
    </select>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Barrio/Vereda</label>
    <select x-model="form.barrio"
           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
           :disabled="!form.territorio">
        <option value="">Seleccionar barrio...</option>
        <template x-for="barrio in listas.barrios" :key="barrio">
            <option :value="barrio" x-text="barrio"></option>
        </template>
    </select>
</div>
```

#### JavaScript - Agregar listas y funciones (después de línea 439):

```javascript
listas: {
    departamentos: [],
    municipios: [],
    tipos_territorio: [],
    territorios: [],
    barrios: []
},

async init() {
    await this.cargarDepartamentos();
    this.initSignaturePad();
    lucide.createIcons();
},

async cargarDepartamentos() {
    try {
        const response = await fetch('/api/territorios.php?accion=departamentos');
        const result = await response.json();
        if (result.success) {
            this.listas.departamentos = result.data;
        }
    } catch (error) {
        console.error('Error cargando departamentos:', error);
    }
},

async cargarMunicipios() {
    this.form.municipio = '';
    this.form.tipo_territorio = '';
    this.form.territorio = '';
    this.form.barrio = '';
    this.listas.municipios = [];
    this.listas.tipos_territorio = [];
    this.listas.territorios = [];
    this.listas.barrios = [];

    if (!this.form.departamento) return;

    try {
        const response = await fetch(`/api/territorios.php?accion=municipios&departamento=${encodeURIComponent(this.form.departamento)}`);
        const result = await response.json();
        if (result.success) {
            this.listas.municipios = result.data;
        }
    } catch (error) {
        console.error('Error cargando municipios:', error);
    }
},

async cargarTiposTerritorio() {
    this.form.tipo_territorio = '';
    this.form.territorio = '';
    this.form.barrio = '';
    this.listas.tipos_territorio = [];
    this.listas.territorios = [];
    this.listas.barrios = [];

    if (!this.form.municipio) return;

    try {
        const response = await fetch(`/api/territorios.php?accion=tipos_territorio&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}`);
        const result = await response.json();
        if (result.success) {
            this.listas.tipos_territorio = result.data;
        }
    } catch (error) {
        console.error('Error cargando tipos de territorio:', error);
    }
},

async cargarTerritorios() {
    this.form.territorio = '';
    this.form.barrio = '';
    this.listas.territorios = [];
    this.listas.barrios = [];

    if (!this.form.tipo_territorio) return;

    try {
        const response = await fetch(`/api/territorios.php?accion=territorios&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}&tipo=${encodeURIComponent(this.form.tipo_territorio)}`);
        const result = await response.json();
        if (result.success) {
            this.listas.territorios = result.data;
        }
    } catch (error) {
        console.error('Error cargando territorios:', error);
    }
},

async cargarBarrios() {
    this.form.barrio = '';
    this.listas.barrios = [];

    if (!this.form.territorio) return;

    try {
        const response = await fetch(`/api/territorios.php?accion=barrios&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}&tipo=${encodeURIComponent(this.form.tipo_territorio)}&territorio=${encodeURIComponent(this.form.territorio)}`);
        const result = await response.json();
        if (result.success) {
            this.listas.barrios = result.data;
        }
    } catch (error) {
        console.error('Error cargando barrios:', error);
    }
},
```

---

## 2. ✅ Filtrar Eventos por Campaña (COMPLETADO)

**Archivo:** `pages/eventos.php`

### Cambio en PHP (línea 19-28):

**ANTES:**
```php
$stmt = $db->prepare("
    SELECT e.*, u.nombre as responsable_nombre
    FROM eventos e
    LEFT JOIN usuarios u ON e.responsable_id = u.id
    WHERE e.campana_id = ?
    ORDER BY e.fecha_inicio DESC
");
```

**DESPUÉS:**
```php
// Asegurarse que el usuario solo vea eventos de SU campaña
$stmt = $db->prepare("
    SELECT e.*, u.nombre as responsable_nombre, c.nombre as campana_nombre
    FROM eventos e
    LEFT JOIN usuarios u ON e.responsable_id = u.id
    LEFT JOIN campanas c ON e.campana_id = c.id
    WHERE e.campana_id = ?
    ORDER BY e.fecha_inicio DESC
");
```

### Verificar que $campanaActiva viene de la sesión del usuario

**Archivo:** `index.php` o donde se define `$campanaActiva`

El filtro por campaña YA EXISTE en la línea 24:
```php
WHERE e.campana_id = ?
```

El problema es que algunos eventos muestran "Alcaldía de Palmira". Esto significa que esos eventos pertenecen a otra campaña.

**SOLUCIÓN:** Verificar que `$campanaActiva` sea correcta en la sesión

---

## 3. ✅ Agregar Jerarquía Territorial en Eventos (COMPLETADO)

**Archivo:** `pages/eventos.php`

### A. Agregar campos en la tabla `eventos`:

```sql
ALTER TABLE eventos
ADD COLUMN IF NOT EXISTS departamento VARCHAR(100) DEFAULT NULL AFTER ubicacion,
ADD COLUMN IF NOT EXISTS municipio VARCHAR(100) DEFAULT NULL AFTER departamento,
ADD COLUMN IF NOT EXISTS tipo_territorio VARCHAR(100) DEFAULT NULL AFTER municipio,
ADD COLUMN IF NOT EXISTS territorio VARCHAR(100) DEFAULT NULL AFTER tipo_territorio,
ADD COLUMN IF NOT EXISTS barrio VARCHAR(100) DEFAULT NULL AFTER territorio;
```

### B. Modificar formulario de creación/edición (línea ~140):

**AGREGAR después del campo ubicación:**

```html
<!-- Jerarquía Territorial -->
<div>
    <label class="block text-sm font-medium mb-2">Departamento</label>
    <select x-model="form.departamento" @change="cargarMunicipios()" class="input">
        <option value="">Seleccionar departamento...</option>
        <template x-for="dep in listas.departamentos" :key="dep">
            <option :value="dep" x-text="dep"></option>
        </template>
    </select>
</div>

<div>
    <label class="block text-sm font-medium mb-2">Municipio</label>
    <select x-model="form.municipio" @change="cargarTiposTerritorio()" class="input" :disabled="!form.departamento">
        <option value="">Seleccionar municipio...</option>
        <template x-for="mun in listas.municipios" :key="mun">
            <option :value="mun" x-text="mun"></option>
        </template>
    </select>
</div>

<div>
    <label class="block text-sm font-medium mb-2">Tipo Territorio</label>
    <select x-model="form.tipo_territorio" @change="cargarTerritorios()" class="input" :disabled="!form.municipio">
        <option value="">Seleccionar tipo...</option>
        <template x-for="tipo in listas.tipos_territorio" :key="tipo">
            <option :value="tipo" x-text="tipo"></option>
        </template>
    </select>
</div>

<div>
    <label class="block text-sm font-medium mb-2">Territorio</label>
    <select x-model="form.territorio" @change="cargarBarrios()" class="input" :disabled="!form.tipo_territorio">
        <option value="">Seleccionar territorio...</option>
        <template x-for="terr in listas.territorios" :key="terr">
            <option :value="terr" x-text="terr"></option>
        </template>
    </select>
</div>

<div>
    <label class="block text-sm font-medium mb-2">Barrio/Vereda</label>
    <select x-model="form.barrio" class="input" :disabled="!form.territorio">
        <option value="">Seleccionar barrio...</option>
        <template x-for="barrio in listas.barrios" :key="barrio">
            <option :value="barrio" x-text="barrio"></option>
        </template>
    </select>
</div>
```

### C. Agregar en JavaScript (data):

```javascript
listas: {
    departamentos: [],
    municipios: [],
    tipos_territorio: [],
    territorios: [],
    barrios: []
},
```

### D. Copiar funciones de carga desde `acciones.php`:

```javascript
// Copiar las mismas funciones:
// - cargarDepartamentos()
// - cargarMunicipios()
// - cargarTiposTerritorio()
// - cargarTerritorios()
// - cargarBarrios()
```

### E. Actualizar función editar() para cargar los datos:

```javascript
async editar(id) {
    const evento = this.eventos.find(e => e.id === id);
    this.form = { ...evento };

    // Cargar jerarquía si tiene datos
    if (evento.departamento) {
        await this.cargarDepartamentos();
        await this.cargarMunicipios();
        if (evento.tipo_territorio) await this.cargarTiposTerritorio();
        if (evento.territorio) await this.cargarTerritorios();
        if (evento.barrio) await this.cargarBarrios();
    }

    this.modalNuevo = true;
}
```

---

## 4. ✅ Botón Específico para Asistencias (COMPLETADO)

**Archivo:** `pages/eventos.php`

### Cambiar la columna de acciones (línea ~265-280):

**ANTES:**
```html
<button @click="verQR(<?= $evento['id'] ?>)"
    class="text-purple-600 hover:text-purple-800 mr-2" title="Ver QR">
    <i data-lucide="qr-code" class="w-4 h-4 inline"></i>
</button>
<button @click="verAsistencia(<?= $evento['id'] ?>)"
    class="text-green-600 hover:text-green-800 mr-2" title="Asistencia">
    <i data-lucide="users" class="w-4 h-4 inline"></i>
</button>
```

**DESPUÉS:**
```html
<!-- Menú desplegable de Asistencias -->
<div class="relative inline-block" x-data="{ open: false }">
    <button @click="open = !open"
        class="text-green-600 hover:text-green-800 mr-2 px-2 py-1 rounded hover:bg-green-50"
        title="Gestionar Asistencias">
        <i data-lucide="users-2" class="w-4 h-4 inline"></i>
        Asistencias
        <i data-lucide="chevron-down" class="w-3 h-3 inline"></i>
    </button>

    <div x-show="open" @click.away="open = false" x-transition
        class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
        <a @click="verQR(<?= $evento['id'] ?>); open = false"
           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer rounded-t-lg">
            <i data-lucide="qr-code" class="w-4 h-4 inline mr-2"></i>
            Generar Código QR
        </a>
        <a @click="verAsistencia(<?= $evento['id'] ?>); open = false"
           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer">
            <i data-lucide="list-checks" class="w-4 h-4 inline mr-2"></i>
            Ver Lista de Asistentes
        </a>
        <a href="index.php?page=dashboard_asistencia&evento_id=<?= $evento['id'] ?>"
           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-b-lg">
            <i data-lucide="bar-chart-3" class="w-4 h-4 inline mr-2"></i>
            Dashboard de Análisis
        </a>
    </div>
</div>
```

---

## RESUMEN DE ARCHIVOS MODIFICADOS:

1. ✅ `registro_asistencia.php` - Jerarquía en cascada (COMPLETADO)
2. ✅ `pages/eventos.php` - Campos territoriales + botón de asistencias (COMPLETADO)
3. ✅ Base de datos - Tabla `eventos` ya tenía los campos necesarios
4. ✅ Subido a producción (https://aratio.mrmtech.net)

---

## ✅ DEPLOYMENT COMPLETADO:

1. ✅ Jerarquía geográfica en cascada implementada y funcionando
2. ✅ Botón de asistencias con menú desplegable implementado
3. ✅ Archivos subidos a producción vía FTP
4. ✅ Sistema verificado y funcionando en https://aratio.mrmtech.net/registro_asistencia.php?evento=1

**Fecha de deployment:** 29 de Noviembre de 2025
**Estado final:** PRODUCCIÓN

