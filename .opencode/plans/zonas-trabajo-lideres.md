# Plan: Zonas de Trabajo - Vista Unificada para Líderes

## Resumen de Cambios Requeridos

El usuario solicita que un líder (ej. Edison) pueda ver:
1. **Sus propias zonas de trabajo** 
2. **Las zonas de todos sus colaboradores (seguidores directos)**
3. **Sin duplicar datos** - si el mismo territorio/barrio está asignado a múltiples personas, se muestra una sola vez con todos los responsables
4. **Contadores separados** - propias vs seguidores (no sumados)
5. **Botón "Recargar"** visible siempre
6. **Mapa centrado en Cali** con zoom apropiado

---

## Archivos a Modificar

### 1. `Aratio-App-Mirror/api/zonas_trabajo.php` - Acción `con_seguidores`

**Cambios en la API:**
- Modificar query SQL para incluir geometría (`ST_AsGeoJSON`)
- Implementar lógica de **desduplicación** por `territorio_id + barrio`
- Agrupar responsables en array `responsables[]` por zona
- Mantener contadores separados: `propias` vs `seguidores` (basados en datos originales, no desduplicados)
- Retornar estructura unificada para mapa y tabla

**Estructura de respuesta esperada:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "territorio_id": 5,
      "departamento": "Valle del Cauca",
      "municipio": "Cali",
      "Tipo_territorio": "Comuna",
      "Territorio": "Comuna 20",
      "barrio": "Silóe",
      "geometry_json": "{...}",
      "responsables": [
        {"nombre": "Edison Giraldo", "documento": "123", "tipo": "propia", "impacto_estimado": 50, "descripcion": "Trabajo comunitario"},
        {"nombre": "María López", "documento": "456", "tipo": "seguidor", "impacto_estimado": 30, "descripcion": "Apoyo vecinal"}
      ],
      "tipo_display": "mixta" // propia | seguidor | mixta
    }
  ],
  "total": 15,
  "propias": 8,
  "seguidores": 12
}
```

### 2. `Aratio-App-Mirror/pages/colaborador_detalle.php` - Componente `colaboradorDetalle()`

**Cambios en el Frontend:**

#### Estado (líneas ~1360-1370)
```javascript
// Agregar propiedades para control de mapa
mapaZonasInicializado: false,
mapaZonas: null,
zonaRecargando: false,
ultimaCargaZonas: null,
```

#### Método `loadZonasTrabajo()` (líneas ~1398-1462)
- Usar endpoint `action=geo&con_seguidores=true` (ya implementado)
- Procesar respuesta con `responsables[]` y `tipo_display`
- Actualizar `zonasTrabajo` con datos desduplicados
- Actualizar `statsZonas` con `propias` y `seguidores` separados
- Inicializar/actualizar mapa Leaflet

#### Nuevo método `inicializarMapaZonas()`
```javascript
async inicializarMapaZonas() {
    if (this.zonasTrabajo.length === 0) return;
    
    const container = document.getElementById('mapaZonas');
    if (!container) return;
    
    // Destruir mapa anterior si existe
    if (this.mapaZonas) {
        this.mapaZonas.remove();
        this.mapaZonas = null;
    }
    
    // Crear mapa centrado en CALI
    this.mapaZonas = L.map('mapaZonas', { 
        zoomControl: true 
    }).setView([3.4516, -76.5320], 11); // Zoom 11 = nivel ciudad
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap',
        maxZoom: 18
    }).addTo(this.mapaZonas);
    
    // Cargar GeoJSON de todas las zonas
    const params = `action=geo&con_seguidores=1&colaborador_id=${this.colaboradorId}`;
    const geo = await fetch(`api/zonas_trabajo.php?${params}`).then(r => r.json());
    
    if (geo.features && geo.features.length > 0) {
        const layer = L.geoJSON(geo, {
            style: (feature) => {
                const tipo = feature.properties.tipo_display || 'propia';
                return {
                    color: tipo === 'propia' ? '#d946ef' : (tipo === 'seguidor' ? '#3b82f6' : '#8b5cf6'),
                    weight: 2,
                    fillOpacity: 0.15,
                    fillColor: tipo === 'propia' ? '#fdf4ff' : (tipo === 'seguidor' ? '#eff6ff' : '#f5f3ff')
                };
            },
            onEachFeature: (feature, layer) => {
                const props = feature.properties;
                const responsables = props.responsables || [];
                const listaResp = responsables.map(r => 
                    `${r.nombre} (${r.tipo === 'propia' ? 'Propia' : 'Seguidor'})`
                ).join('<br>');
                
                layer.bindPopup(`
                    <div class="p-2 min-w-[200px]">
                        <h4 class="font-bold text-sm mb-1">${props.barrio || 'Barrio'}</h4>
                        <p class="text-xs text-gray-600">${props.Territorio || ''}, ${props.municipio || ''}</p>
                        <p class="text-xs mt-1">Tipo: <span class="font-medium capitalize">${props.tipo_display || 'propia'}</span></p>
                        <div class="text-xs mt-2 border-t pt-2">
                            <strong>Responsables:</strong><br>${listaResp}
                        </div>
                    </div>
                `);
            }
        }).addTo(this.mapaZonas);
        
        // Ajustar vista a TODAS las zonas
        this.mapaZonas.fitBounds(layer.getBounds().pad(0.1));
    }
    
    this.mapaZonasInicializado = true;
}
```

#### Método `recargarZonas()`
```javascript
async recargarZonas() {
    this.zonaRecargando = true;
    this.zonasTrabajo = [];
    this.mapaZonasInicializado = false;
    this.loadZonasAttempts = 0;
    await this.loadZonasTrabajo();
    this.zonaRecargando = false;
    this.ultimaCargaZonas = new Date();
    this.notificar('Zonas recargadas correctamente', 'success');
}
```

#### Template - Pestaña "Zonas de Trabajo" (líneas ~706-780)
**Cambios en la UI:**
1. **Header con botones:**
   - "Agregar Zona" (existente)
   - **"Recargar" (NUEVO)** - visible siempre, icono refresh-cw
   - Timestamp última carga: "Actualizado: hace 2 min"

2. **Stats separados (NO sumados):**
   ```html
   <div class="mb-3 flex gap-3 text-sm flex-wrap">
       <span class="px-3 py-1 rounded-full bg-fuchsia-100 text-fuchsia-700 font-medium">
           <span x-text="statsZonas.propias"></span> propias
       </span>
       <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-medium">
           <span x-text="statsZonas.seguidores"></span> de seguidores
       </span>
       <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 font-medium">
           Total únicas: <span x-text="zonasTrabajo.length"></span>
       </span>
   </div>
   ```

3. **Tabla con columna "Responsable(s)":**
   - Mostrar todos los responsables de la zona (array `responsables`)
   - Badge por cada uno: Propia (fucsia) / Seguidor (azul)

4. **Contenedor del mapa (NUEVO):**
   ```html
   <div class="lg:col-span-2">
       <div id="mapaZonas" class="h-96 rounded-lg border border-gray-200" 
            x-show="zonasTrabajo.length > 0"></div>
       <div x-show="zonasTrabajo.length === 0" class="text-center py-8 text-gray-500">
           <i data-lucide="map-pin" class="w-12 h-12 mx-auto mb-2 text-gray-300"></i>
           <p>No hay zonas con geometría para mostrar en el mapa</p>
       </div>
   </div>
   ```

5. **Grid layout actualizado:**
   - Tabla a la izquierda (lg:w-1/2)
   - Mapa a la derecha (lg:w-1/2)
   - En móvil: tabla arriba, mapa abajo

---

## Flujo de Datos

```
Usuario (Líder) abre ficha colaborador
    ↓
Clic en tab "Zonas de Trabajo"
    ↓
loadZonasTrabajo() → API action=geo&con_seguidores=true&colaborador_id=X
    ↓
API retorna zonas desduplicadas + responsables[] + contadores separados
    ↓
Frontend:
  - zonasTrabajo = data (para tabla)
  - statsZonas = {propias, seguidores} (para badges)
  - inicializarMapaZonas() → carga GeoJSON → fitBounds()
    ↓
Usuario ve: tabla + mapa sincronizados
Usuario clic "Recargar" → recargarZonas() → repite ciclo
```

---

## Casos de Prueba

| Escenario | Resultado Esperado |
|-----------|-------------------|
| Líder sin zonas propias, 3 seguidores con 2 zonas cada una (1 compartida) | Tabla: 5 filas únicas. Badges: Propias=0, Seguidores=6. Mapa: 5 polígonos. |
| Líder con zona en Silóe, 2 seguidores también en Silóe | Tabla: 1 fila Silóe con 3 responsables. Badges: Propias=1, Seguidores=2. Mapa: 1 polígono Silóe. |
| Clic "Recargar" | Spinner en botón → recarga API → actualiza tabla y mapa → toast success |
| Cambio de tab y vuelta | No recarga automática (cache), pero botón "Recargar" disponible |
| Mapa sin geometría | Mensaje "No hay zonas con geometría" en lugar de mapa vacío |

---

## Notas Técnicas

1. **Desduplicación**: Clave = `territorio_id|barrio`. Prioridad: "propia" > "seguidor" para `tipo_display`
2. **Contadores**: Usan datos SIN desduplicar (cuentan asignaciones reales)
3. **Mapa**: Zoom inicial 11 (nivel ciudad Cali), `fitBounds()` ajusta a todas las zonas
4. **Performance**: GeoJSON se carga una vez; `fitBounds` usa padding 0.1
5. **Compatibilidad**: Mantiene API `action=con_seguidores` para tabla, `action=geo` para mapa

---

## Comandos de Verificación Post-Deploy

```bash
# 1. Verificar API
curl "https://padrinoscali.org/aratio/api/zonas_trabajo.php?action=con_seguidores&colaborador_id=1849" | jq .

# 2. Verificar geometría
curl "https://padrinoscali.org/aratio/api/zonas_trabajo.php?action=geo&con_seguidores=1&colaborador_id=1849" | jq '.features[0]'

# 3. Verificar en UI
# Abrir: https://padrinoscali.org/aratio/index.php?page=colaborador_detalle&id=1849
# Ir a tab "Zonas de Trabajo"
# Verificar: badges separados, tabla con responsables, mapa con polígonos, botón recargar
```