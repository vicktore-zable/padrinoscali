# ✅ INSTRUCCIONES PARA SUBIR A HOSTINGER

**Fecha**: 12 de Febrero de 2026, 11:00 AM
**Estado**: Archivos listos para subir

---

## 📦 ARCHIVOS MODIFICADOS LISTOS PARA SUBIR

Los siguientes archivos han sido modificados y están listos para subir a producción:

### 1. **PublicController.php**
- **Ruta local**: `mod_colab\src\Controllers\PublicController.php`
- **Ruta remota**: `/domains/edisongiraldo.com/public_html/aratio/mod_colab/src/Controllers/PublicController.php`
- **Cambios**: 
  - Lógica para crear curriculum de líderes
  - Procesamiento de datos de CV

### 2. **inscripcion_simpatizante.php**
- **Ruta local**: `mod_colab\src\Views\public\inscripcion_simpatizante.php`
- **Ruta remota**: `/domains/edisongiraldo.com/public_html/aratio/mod_colab/src/Views/public/inscripcion_simpatizante.php`
- **Cambios**:
  - Selector dinámico de puestos de votación
  - Método `loadPuestos()` en JavaScript

### 3. **registro_lider.php**
- **Ruta local**: `mod_colab\src\Views\public\registro_lider.php`
- **Ruta remota**: `/domains/edisongiraldo.com/public_html/aratio/mod_colab/src/Views/public/registro_lider.php`
- **Cambios**:
  - Selector dinámico de puestos de votación
  - Sección completa de curriculum (experiencia, formación, participación política)
  - Método `loadPuestos()` en JavaScript

### 4. **web.php** (Sin cambios, pero verificar)
- **Ruta local**: `mod_colab\routes\web.php`
- **Ruta remota**: `/domains/edisongiraldo.com/public_html/aratio/mod_colab/routes/web.php`
- **Estado**: Ya tiene las rutas necesarias

---

## 🚀 MÉTODO 1: SUBIR CON WINSCP (RECOMENDADO)

### Paso 1: Abrir WinSCP
1. Abrir WinSCP
2. Crear nueva sesión:
   - **Protocolo**: FTP
   - **Host**: 157.173.208.254
   - **Puerto**: 21
   - **Usuario**: u577647812
   - **Contraseña**: E=j$`01yHi^?XfpoM@|CD"5H4

### Paso 2: Conectar y Navegar
1. Conectar al servidor
2. En el panel derecho (remoto), navegar a:
   `/domains/edisongiraldo.com/public_html/aratio/mod_colab/`

### Paso 3: Subir Archivos
1. En el panel izquierdo (local), navegar a:
   `H:\Mi unidad\2025\5d\app\Multi-Campaign Management System\mod_colab\`

2. Subir los archivos uno por uno:
   - `src\Controllers\PublicController.php` → `src/Controllers/`
   - `src\Views\public\inscripcion_simpatizante.php` → `src/Views/public/`
   - `src\Views\public\registro_lider.php` → `src/Views/public/`

---

## 🚀 MÉTODO 2: USAR SCRIPT WINSCP

Si tienes WinSCP instalado con soporte de línea de comandos:

```powershell
cd "H:\Mi unidad\2025\5d\app\Multi-Campaign Management System"
winscp.com /script=upload-winscp.txt
```

---

## 🚀 MÉTODO 3: SUBIR VÍA PANEL DE HOSTINGER

### Paso 1: Acceder al File Manager
1. Ir a: https://hpanel.hostinger.com
2. Iniciar sesión
3. Ir a "File Manager"

### Paso 2: Navegar y Subir
1. Navegar a: `/domains/edisongiraldo.com/public_html/aratio/mod_colab/`
2. Subir cada archivo a su carpeta correspondiente

---

## ✅ VERIFICACIÓN POST-SUBIDA

Después de subir los archivos, verificar que todo funcione:

### 1. Probar Registro de Simpatizantes
```
URL: https://edisongiraldo.com/aratio/registro-simpatizante

Verificar:
✓ Se ve la interfaz bonita
✓ Se cargan los departamentos
✓ Al seleccionar municipio, se cargan los puestos de votación
✓ El selector de puestos es un dropdown (no texto libre)
```

### 2. Probar Registro de Líderes
```
URL: https://edisongiraldo.com/aratio/registro-lider

Verificar:
✓ Se ve la interfaz bonita
✓ Se cargan los departamentos
✓ Al seleccionar municipio, se cargan los puestos de votación
✓ El selector de puestos es un dropdown (no texto libre)
✓ Aparece la sección "Hoja de Vida (Opcional)" con fondo morado
✓ Tiene 3 subsecciones: Experiencia, Formación, Participación
```

### 3. Probar Registro Completo
```
1. Completar formulario de líder con todos los datos
2. Incluir datos de curriculum
3. Enviar formulario
4. Verificar que se cree el colaborador
5. Verificar que se cree el curriculum en la BD
```

---

## 🎨 CONFIRMACIÓN DE INTERFAZ

**SÍ, LA INTERFAZ BONITA SE CONSERVARÁ EN PRODUCCIÓN**

Los archivos que estás subiendo contienen:
- ✅ Tailwind CSS (clases de estilo)
- ✅ Alpine.js (interactividad)
- ✅ Diseño responsive
- ✅ Colores y estilos personalizados
- ✅ Animaciones y transiciones

La interfaz se verá **exactamente igual** que en local.

---

## 📊 RESUMEN DE CAMBIOS

| Componente | Cambio | Estado |
|------------|--------|--------|
| Selector Puestos (Simpatizante) | ✅ Agregado | Listo para subir |
| Selector Puestos (Líder) | ✅ Agregado | Listo para subir |
| Campos Curriculum (Líder) | ✅ Agregado | Listo para subir |
| Guardado Curriculum | ✅ Implementado | Listo para subir |
| Interfaz Bonita | ✅ Conservada | Se mantendrá en producción |

---

## 🔍 ARCHIVOS LOCALES

Todos los archivos están en:
```
H:\Mi unidad\2025\5d\app\Multi-Campaign Management System\mod_colab\
```

---

## ⚠️ IMPORTANTE

1. **Backup**: Los archivos actuales en producción se sobrescribirán
2. **Pruebas**: Después de subir, probar inmediatamente
3. **Rollback**: Si algo falla, tienes los archivos originales en el servidor

---

## 📞 SOPORTE

Si encuentras algún problema después de subir:

1. Verificar logs en: `/domains/edisongiraldo.com/public_html/aratio/mod_colab/storage/logs/`
2. Verificar permisos de archivos (deben ser 644)
3. Limpiar caché del navegador

---

**¿Listo para subir?** 🚀

Usa cualquiera de los 3 métodos descritos arriba.

**Recomendación**: Método 1 (WinSCP manual) es el más confiable.
