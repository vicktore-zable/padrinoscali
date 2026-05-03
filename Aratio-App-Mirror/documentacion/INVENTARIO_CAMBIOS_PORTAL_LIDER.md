# Inventario de Archivos Modificados/Creados - Portal de Líderes

**Fecha:** 2026-02-17  
**Propósito:** Corregir errores del Portal de Líderes para funcionamiento en producción

---

## 📋 Lista de Archivos a Subir a Producción

### 1. Archivos NUEVOS (Crear)

| # | Archivo Local | Ruta en Producción | Descripción |
|---|---------------|-------------------|-------------|
| 1 | `mod_lider/src/Models/Evento.php` | `domains/aratio.mrmtech.net/public_html/mod_lider/src/Models/Evento.php` | Modelo para gestión de eventos (faltante) |

### 2. Archivos MODIFICADOS (Actualizar)

| # | Archivo Local | Ruta en Producción | Descripción |
|---|---------------|-------------------|-------------|
| 2 | `mod_lider/src/Models/Colaborador.php` | `domains/aratio.mrmtech.net/public_html/mod_lider/src/Models/Colaborador.php` | Agregados métodos `getDownlineDocumentos()` y `getNeighborhoodBreakdown()` |

---

## 📝 Detalles de Cambios

### Archivo 1: `mod_lider/src/Models/Evento.php` (NUEVO)

**Motivo:** El controlador `LeaderPortalController.php` intentaba instanciar `App\Models\Evento` pero la clase no existía.

**Métodos implementados:**
- `getAll($page, $perPage, $filters)` - Obtener eventos con paginación
- `count($filters)` - Contar eventos
- `getById($id)` - Obtener evento por ID
- `getUpcoming($limit, $campanaId)` - Obtener próximos eventos
- `getByCampana($campanaId)` - Obtener eventos por campaña

### Archivo 2: `mod_lider/src/Models/Colaborador.php` (MODIFICADO)

**Motivo:** El controlador `LeaderPortalController.php` llamaba a métodos que no existían.

**Métodos agregados:**
- `getDownlineDocumentos($documentoLider)` - Obtiene recursivamente todos los documentos de la red descendiente
- `getNeighborhoodBreakdown($documentos)` - Obtiene desglose por barrio/territorio
- `getDownlineRecursive()` - Método privado auxiliar para recursión

---

## 🚀 Instrucciones de Subida

### Opción A: Script PowerShell (Automático)

```powershell
powershell -ExecutionPolicy Bypass -File "subir_portal_lider_auto.ps1"
```

### Opción B: FTP Manual

Conectar a:
- **Servidor:** `ftp://212.1.208.241`
- **Usuario:** `u156469157.aratio.mrmtech.net`
- **Contraseña:** `sthLX6bJPoGh`

Subir archivos:
1. `mod_lider/src/Models/Evento.php` → `domains/aratio.mrmtech.net/public_html/mod_lider/src/Models/Evento.php`
2. `mod_lider/src/Models/Colaborador.php` → `domains/aratio.mrmtech.net/public_html/mod_lider/src/Models/Colaborador.php`

### Opción C: Usar WinSCP o FileZilla

Importar configuración desde el script o conectar manualmente con las credenciales arriba.

---

## ✅ Verificación Post-Despliegue

1. Acceder a: `https://aratio.mrmtech.net/index.php?page=portal_login`
2. Iniciar sesión con:
   - **Usuario:** `lider`
   - **Contraseña:** `123456`
3. Verificar que el dashboard carga sin errores

---

## 🔐 Credenciales de Prueba

| Usuario | Contraseña | Tipo |
|---------|------------|------|
| `lider` | `123456` | Usuario de prueba |
| `1130665763` | `3178386580` | Colaborador real (doc/tel) |

---

## ⚠️ Notas Importantes

1. Asegurarse de que el directorio `mod_lider/src/Models/` exista en producción
2. Los warnings de constantes ya definidas (`APP_NAME`, etc.) son cosméticos y no afectan funcionalidad
3. Verificar permisos de escritura en el directorio de logs

---

**Generado automáticamente por Kilo Code**
