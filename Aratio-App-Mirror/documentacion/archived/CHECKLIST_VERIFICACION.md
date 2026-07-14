# 🔍 CHECKLIST DE VERIFICACIÓN - Dropdowns

## ✅ Archivos Subidos al Servidor

1. ✅ `api/territorios.php` - API funcionando (verificado con JSON válido)
2. ✅ `pages/campanas.php` - Con fallback de "Valle del Cauca"
3. ✅ `pages/eventos.php` - Con fallback de "Valle del Cauca"

## 🎯 Qué Verificar en el Navegador

### Paso 1: Abrir la Aplicación
1. Ir a: https://aratio.mrmtech.net/
2. Iniciar sesión
3. Ir a "Campañas"
4. Clic en "Nueva Campaña"

### Paso 2: Abrir Consola del Navegador
1. Presionar **F12**
2. Ir a pestaña **"Console"**
3. Buscar mensajes que digan:
   - "Cargando departamentos API..."
   - "API Response Text:"
   - Cualquier error en rojo

### Paso 3: Inspeccionar el Dropdown
1. En el modal "Nueva Campaña"
2. Buscar el campo "Departamento *"
3. Verificar si:
   - ❓ El dropdown está visible
   - ❓ Tiene la opción "Seleccionar departamento..."
   - ❓ Tiene opciones de departamentos
   - ❓ Está vacío

## 🔧 Posibles Problemas y Soluciones

### Problema 1: Dropdown Vacío
**Causa**: Los archivos no se actualizaron en el servidor
**Solución**: Verificar que los archivos se subieron correctamente

### Problema 2: Error en Consola
**Causa**: La API no está respondiendo correctamente
**Solución**: Verificar que https://aratio.mrmtech.net/api/territorios.php?accion=departamentos devuelve JSON

### Problema 3: Dropdown No Visible
**Causa**: Error de JavaScript o Alpine.js
**Solución**: Revisar errores en la consola

## 📸 Información Necesaria

Por favor, toma capturas de pantalla de:

1. **Modal "Nueva Campaña"** completo
2. **Consola del navegador** (pestaña Console)
3. **Pestaña Network** (si hay errores de red)

## 🆘 Comandos de Verificación Manual

Si tienes acceso al servidor por SSH o File Manager:

1. Verificar que existe: `/public_html/pages/campanas.php`
2. Verificar que existe: `/public_html/api/territorios.php`
3. Verificar fecha de modificación (debe ser hoy 2026-01-23)

## ✅ API Verificada

La API está funcionando correctamente:
```
URL: https://aratio.mrmtech.net/api/territorios.php?accion=departamentos
Response: {"success":true,"data":["Valle del Cauca"],"count":1}
```

## 📝 Próximos Pasos

1. Verificar en el navegador (capturas de pantalla)
2. Revisar consola del navegador
3. Compartir los errores que aparezcan
