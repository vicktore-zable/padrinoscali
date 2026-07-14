# 📥 GUÍA: SINCRONIZAR ARCHIVOS DE PRODUCCIÓN

## Problema
El servidor local está usando archivos antiguos. Necesitas los archivos actuales de producción.

---

## ✅ SOLUCIÓN RECOMENDADA: Usar WinSCP (Más Fácil)

### Paso 1: Descargar WinSCP
https://winscp.net/eng/download.php

### Paso 2: Conectar a Hostinger
1. Abrir WinSCP
2. Configurar conexión:
   - **Protocolo**: FTP
   - **Host**: 212.1.208.241
   - **Puerto**: 21
   - **Usuario**: u156469157.aratio.mrmtech.net
   - **Contraseña**: sthLX6bJPoGh

### Paso 3: Descargar Archivos
1. En el panel derecho (servidor), navega a `/public_html`
2. Selecciona toda la carpeta
3. Arrastra al panel izquierdo (local) a la carpeta:
   ```
   H:\Mi unidad\2025\5d\app\Multi-Campaign Management System\mod_colab_produccion
   ```

### Paso 4: Usar Archivos de Producción
```batch
cd "H:\Mi unidad\2025\5d\app\Multi-Campaign Management System\mod_colab_produccion"
copy .env.local .env
cd public
php -S localhost:8000
```

---

## 🔄 ALTERNATIVA: Sincronización Selectiva

Si solo quieres actualizar archivos específicos:

### Archivos Clave a Descargar

1. **Controladores**:
   - `src/Controllers/PublicController.php`
   - `src/Controllers/ColaboradorController.php`

2. **Vistas**:
   - `src/Views/public/inscripcion_simpatizante.php`
   - `src/Views/public/registro_lider.php`
   - `src/Views/public/inscripcion.php`

3. **Rutas**:
   - `routes/web.php`

4. **Modelos**:
   - `src/Models/Territorio.php`
   - `src/Models/Colaborador.php`

5. **Configuración**:
   - `config/config.php`
   - `.env` (renombrar a `.env.produccion`)

---

## 🚀 OPCIÓN RÁPIDA: Usar Archivos Actuales + Cambios

Si prefieres mantener los archivos locales y solo aplicar los cambios que hice:

### Los cambios que hice son:

1. **PublicController.php** - Líneas 51-56 y 186-335
2. **routes/web.php** - Líneas 57-60
3. **registro_lider.php** - Archivo nuevo

### Puedes:
1. Descargar solo estos 3 archivos de producción
2. Aplicar manualmente los cambios que documenté
3. O usar los archivos modificados que ya están en `mod_colab/`

---

## 🔍 VERIFICAR QUÉ ARCHIVOS TIENES

Ejecuta esto para ver la fecha de tus archivos locales:

```powershell
cd "H:\Mi unidad\2025\5d\app\Multi-Campaign Management System\mod_colab"

# Ver fecha de archivos clave
Get-ChildItem -Path "src\Controllers\PublicController.php" | Select-Object Name, LastWriteTime
Get-ChildItem -Path "routes\web.php" | Select-Object Name, LastWriteTime
Get-ChildItem -Path "src\Views\public\*.php" | Select-Object Name, LastWriteTime
```

---

## 📊 COMPARACIÓN: Local vs Producción

Para saber si tus archivos locales están actualizados:

### Opción 1: Descargar un archivo de producción y comparar

```powershell
# Descargar PublicController.php de producción
curl -u "u156469157.aratio.mrmtech.net:sthLX6bJPoGh" `
  ftp://212.1.208.241/src/Controllers/PublicController.php `
  -o PublicController_produccion.php

# Comparar con el local
fc PublicController_produccion.php "mod_colab\src\Controllers\PublicController.php"
```

---

## ⚡ SOLUCIÓN INMEDIATA

Si quieres probar AHORA con los archivos que ya tienes:

1. **Verifica que los cambios estén aplicados**:
   ```powershell
   cd "H:\Mi unidad\2025\5d\app\Multi-Campaign Management System\mod_colab"
   
   # Buscar el método showRegistroLider
   Select-String -Path "src\Controllers\PublicController.php" -Pattern "showRegistroLider"
   ```

2. **Si encuentra el método**, tus archivos YA tienen los cambios y puedes usar el servidor local

3. **Si NO lo encuentra**, necesitas:
   - Descargar archivos de producción, O
   - Aplicar los cambios manualmente

---

## 🎯 RECOMENDACIÓN

**Opción A** (Más Segura): 
1. Descargar TODO de producción con WinSCP
2. Crear carpeta `mod_colab_produccion`
3. Aplicar los cambios que hice sobre esos archivos

**Opción B** (Más Rápida):
1. Verificar si los archivos locales ya tienen los cambios
2. Si los tienen, usar directamente
3. Si no, descargar solo los 3 archivos modificados

---

## 🆘 NECESITAS AYUDA?

Dime:
1. ¿Prefieres descargar TODO de producción?
2. ¿O verificar si los archivos locales ya están actualizados?
3. ¿O aplicar solo los cambios específicos?

Y te guío paso a paso.
