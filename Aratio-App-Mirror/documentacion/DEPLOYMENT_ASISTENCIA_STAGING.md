# 📋 DEPLOYMENT: Sistema de Asistencia a Eventos - Staging

**Fecha:** 29 de Noviembre de 2025
**Versión:** 1.1.0 - Sistema de Asistencia Completo
**Estado:** ✅ LISTO PARA DEPLOYMENT

---

## 🎯 Resumen de Cambios

Se implementó un sistema completo de registro y análisis de asistencia a eventos con las siguientes características:

### ✅ Características Implementadas

1. **Formulario Público de Registro** (acceso vía QR)
   - Información personal completa con tipo de documento
   - Jerarquía territorial de 5 niveles (Departamento → Municipio → Tipo Territorio → Territorio → Barrio)
   - Datos demográficos (género, fecha de nacimiento, grupo etario automático)
   - Selección múltiple de áreas de interés (9 opciones)
   - Firma digital con canvas touch-compatible
   - Habeas data obligatorio y aceptación de comunicaciones opcional
   - Cierre automático 24 horas después de finalización del evento

2. **Dashboard de Análisis con Filtros Avanzados**
   - 8 filtros: evento, departamento, municipio, barrio, género, grupo etario, área de interés, habeas data
   - 4 KPIs en tiempo real
   - 4 gráficos interactivos (Chart.js): género, grupo etario, áreas de interés, municipios
   - Tabla de datos con búsqueda en tiempo real
   - Preparado para exportación a Excel

3. **API REST Completa**
   - Registro de asistencia (público, sin auth)
   - Consulta de asistentes con filtros
   - Dashboard con estadísticas y datos para gráficos
   - Validaciones de cierre de evento
   - Prevención de duplicados

---

## 📁 Archivos Nuevos/Modificados

### Nuevos Archivos

1. **`database/migrations/update_asistencia_eventos_v2.sql`**
   - Migración de base de datos con verificaciones inteligentes
   - Agrega campos: tipo_territorio, territorio, grupo_etareo, areas_interes, habeas_data, acepta_comunicaciones
   - Renombra: firma → firma_digital, observaciones → notas
   - Triggers automáticos para cálculo de grupo etario

2. **`pages/dashboard_asistencia.php`**
   - Dashboard completo con filtros avanzados
   - KPIs, gráficos y tabla de datos
   - Integración con API

3. **`api/dashboard_asistencia.php`**
   - API para el dashboard
   - Filtros dinámicos
   - Generación de datos para gráficos Chart.js

### Archivos Modificados

1. **`registro_asistencia.php`**
   - Formulario completo con todos los campos nuevos
   - Validación de cierre a 24 horas
   - Áreas de interés y habeas data
   - Firma digital funcional

2. **`api/asistencia_eventos.php`**
   - Actualizado para nuevos campos
   - Cambio de cierre: 4h → 24h
   - Soporte para JSON en areas_interes

3. **`pages/eventos.php`**
   - Modal de asistencia actualizado con nuevos campos
   - Tabla mejorada con grupo etario, habeas data, teléfono
   - Click para ver detalles

---

## 🗄️ Cambios en Base de Datos

### Tabla: `asistencia_eventos`

**Campos Nuevos:**
```sql
- tipo_territorio VARCHAR(100)      -- Tipo de división territorial
- territorio VARCHAR(100)            -- Nombre del territorio
- grupo_etareo VARCHAR(20)           -- Calculado automáticamente
- areas_interes JSON                 -- Array de áreas de interés
- habeas_data BOOLEAN NOT NULL       -- Aceptación obligatoria
- acepta_comunicaciones BOOLEAN      -- Opcional
```

**Campos Renombrados:**
```sql
- firma → firma_digital
- observaciones → notas
```

**Triggers Creados:**
```sql
- calcular_grupo_etareo_asistencia (INSERT)
- calcular_grupo_etareo_asistencia_update (UPDATE)
```

**Índices Agregados:**
```sql
- idx_grupo_etareo
- idx_tipo_territorio
```

---

## 🚀 Pasos para Deployment en Staging

### **Paso 1: Backup de Base de Datos** ⚠️ CRÍTICO

```bash
# Conectar a phpMyAdmin staging: https://hstgr.io
# Base de datos: u156469157_aratio_v1
# Exportar todo como backup:
# - Formato: SQL
# - Opciones: Estructura y datos
# - Guardar como: backup_aratio_pre_asistencia_YYYYMMDD.sql
```

### **Paso 2: Aplicar Migración de Base de Datos**

**Opción A: Via phpMyAdmin (Recomendado)**

1. Ir a phpMyAdmin en Hostinger
2. Seleccionar base de datos: `u156469157_aratio_v1`
3. Click en "SQL"
4. Copiar contenido completo de: `database/migrations/update_asistencia_eventos_v2.sql`
5. Pegar y ejecutar
6. Verificar mensaje: "Migración completada exitosamente"

**Opción B: Via SSH (si está disponible)**

```bash
# Subir archivo de migración
scp -P 65002 database/migrations/update_asistencia_eventos_v2.sql \
    u156469157@aratio.mrmtech.net:/home/u156469157/staging/

# Conectar por SSH
ssh -p 65002 u156469157@aratio.mrmtech.net

# Ejecutar migración
mysql -h auth-db690.hstgr.io -u u156469157_aratio_v1 \
      -p15zxCeBbvgsR u156469157_aratio_v1 \
      < /home/u156469157/staging/update_asistencia_eventos_v2.sql
```

### **Paso 3: Subir Archivos PHP Actualizados**

**Via FileZilla/FTP:**

```
Host: ftp.aratio.mrmtech.net
Username: u156469157
Password: [tu password FTP]
Port: 21
```

**Archivos a Subir:**

```
staging.aratio.mrmtech.net/
├── registro_asistencia.php                   # ACTUALIZADO
├── api/
│   ├── asistencia_eventos.php                # ACTUALIZADO
│   └── dashboard_asistencia.php              # NUEVO
└── pages/
    ├── eventos.php                            # ACTUALIZADO
    └── dashboard_asistencia.php              # NUEVO
```

**Comando PowerShell para preparar archivos:**

```powershell
# Desde el directorio raíz del proyecto
$origen = "H:\Mi unidad\2025\5d\app\Multi-Campaign Management System\src\php-export"
$destino = "H:\staging_upload"

# Crear directorio temporal
New-Item -ItemType Directory -Force -Path $destino

# Copiar archivos modificados
Copy-Item "$origen\registro_asistencia.php" -Destination $destino
Copy-Item "$origen\api\asistencia_eventos.php" -Destination "$destino\api\"
Copy-Item "$origen\api\dashboard_asistencia.php" -Destination "$destino\api\"
Copy-Item "$origen\pages\eventos.php" -Destination "$destino\pages\"
Copy-Item "$origen\pages\dashboard_asistencia.php" -Destination "$destino\pages\"

Write-Host "✅ Archivos preparados en: $destino"
```

### **Paso 4: Verificación Post-Deployment**

#### 4.1 Verificar Base de Datos

```sql
-- En phpMyAdmin, ejecutar:
DESCRIBE asistencia_eventos;

-- Verificar que existan los campos:
-- - tipo_territorio
-- - territorio
-- - grupo_etareo
-- - areas_interes
-- - firma_digital
-- - habeas_data
-- - acepta_comunicaciones
-- - notas
```

#### 4.2 Verificar Triggers

```sql
-- En phpMyAdmin, ejecutar:
SHOW TRIGGERS LIKE 'asistencia_eventos';

-- Deben aparecer 2 triggers:
-- - calcular_grupo_etareo_asistencia (INSERT)
-- - calcular_grupo_etareo_asistencia_update (UPDATE)
```

#### 4.3 Testing Funcional

**✅ Test 1: Crear Evento de Prueba**

1. Login en staging: `https://staging.aratio.mrmtech.net`
2. Ir a módulo "Eventos"
3. Crear evento de prueba:
   - Nombre: "Evento Test Asistencia"
   - Fecha inicio: Hoy
   - Fecha fin: Mañana
   - Ubicación: Bogotá

**✅ Test 2: Generar y Probar QR**

1. Click en ícono QR del evento de prueba
2. Copiar URL generada
3. Abrir en navegador incógnito
4. Verificar que cargue el formulario público

**✅ Test 3: Registro de Asistencia**

1. En el formulario público, llenar todos los campos:
   - Nombre completo
   - Tipo documento: CC
   - Documento: 123456789
   - Fecha nacimiento: 01/01/1990 (debe calcular grupo etario: 26-35)
   - Género: Masculino
   - Departamento: Cundinamarca
   - Municipio: Bogotá
   - Tipo territorio: Localidad
   - Territorio: Localidad 5
   - Barrio: Ejemplo
   - Áreas de interés: Marcar 2-3 opciones
   - Firma: Dibujar firma
   - Habeas data: ✅ Marcar (obligatorio)
   - Comunicaciones: ✅ Marcar (opcional)

2. Click "Registrar Asistencia"
3. Verificar mensaje de éxito

**✅ Test 4: Visualizar Asistentes**

1. Volver al módulo Eventos (autenticado)
2. Click en ícono de "Asistencia" (👥) del evento
3. Verificar que aparezca el registro con todos los campos:
   - Nombre completo
   - Documento con tipo
   - Género
   - Grupo etario (debe ser "26-35")
   - Ubicación (Bogotá - Ejemplo)
   - Teléfono
   - Habeas data (✅ verde)
   - Fecha y método de registro

**✅ Test 5: Dashboard de Asistencia**

1. Agregar enlace temporal al menú de navegación (index.php) o acceder directamente:
   ```
   https://staging.aratio.mrmtech.net/index.php?page=dashboard_asistencia
   ```

2. Verificar:
   - KPIs se cargan correctamente
   - Gráficos se renderizan (Chart.js)
   - Filtros funcionan
   - Búsqueda en tiempo real funciona

**✅ Test 6: Validación de Cierre**

1. Modificar la fecha de fin del evento a hace 25 horas
2. Intentar acceder al formulario público vía QR
3. Verificar mensaje: "Registro cerrado (24 horas después de finalización)"

---

## 🔍 Troubleshooting

### Problema 1: Error al aplicar migración

**Síntoma:** ERROR 1060 (42S21): Duplicate column name

**Solución:** La migración v2 ya maneja esto automáticamente. Si persiste:
```sql
-- Ejecutar este query para ver qué columnas existen:
DESCRIBE asistencia_eventos;

-- Luego modificar la migración para omitir las columnas existentes
```

### Problema 2: Triggers no se crean

**Síntoma:** Grupo etario aparece NULL

**Solución:**
```sql
-- Eliminar triggers antiguos si existen
DROP TRIGGER IF EXISTS calcular_grupo_etareo_asistencia;
DROP TRIGGER IF EXISTS calcular_grupo_etareo_asistencia_update;

-- Volver a ejecutar la parte de triggers de la migración
```

### Problema 3: Formulario público no carga

**Síntoma:** Error 500 o página en blanco

**Solución:**
```bash
# Verificar logs de error en Hostinger
# Panel → Files → Error Logs

# Verificar permisos del archivo
# registro_asistencia.php debe tener permisos 644
```

### Problema 4: API no responde

**Síntoma:** Error en consola del navegador

**Solución:**
```bash
# Verificar que los archivos API estén en la carpeta correcta:
# /api/asistencia_eventos.php
# /api/dashboard_asistencia.php

# Verificar que tengan permisos 644
```

### Problema 5: Gráficos no se muestran

**Síntoma:** Espacios vacíos en el dashboard

**Solución:**
```javascript
// Verificar en consola del navegador que Chart.js esté cargado:
console.log(typeof Chart);
// Debe devolver: "function"

// Si no está cargado, verificar que index.php tenga:
// <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
```

---

## 📊 Estructura de Datos de Ejemplo

### Registro de Asistencia - Payload JSON

```json
{
  "evento_id": 1,
  "nombre": "Juan Pérez García",
  "tipo_documento": "CC",
  "documento": "1234567890",
  "telefono": "3001234567",
  "email": "juan@ejemplo.com",
  "fecha_nacimiento": "1990-05-15",
  "genero": "masculino",
  "departamento": "Cundinamarca",
  "municipio": "Bogotá",
  "tipo_territorio": "Localidad",
  "territorio": "Localidad 5 - Usme",
  "barrio": "La Aurora",
  "areas_interes": ["Educación", "Salud", "Seguridad"],
  "firma": "data:image/png;base64,iVBORw0KGgoAAAANS...",
  "observaciones": "Excelente evento, muy informativo",
  "habeas_data": true,
  "acepta_comunicaciones": true,
  "metodo_registro": "qr"
}
```

### Respuesta API Dashboard

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Juan Pérez García",
      "documento": "1234567890",
      "tipo_documento": "CC",
      "genero": "masculino",
      "grupo_etareo": "26-35",
      "municipio": "Bogotá",
      "barrio": "La Aurora",
      "areas_interes": ["Educación", "Salud", "Seguridad"],
      "habeas_data": 1,
      "acepta_comunicaciones": 1,
      "fecha_registro": "2025-11-29 10:30:00",
      "metodo_registro": "qr"
    }
  ],
  "stats": {
    "total": 125,
    "masculino": 65,
    "femenino": 58,
    "otro": 2,
    "habeas_data": 125,
    "acepta_comunicaciones": 98
  },
  "charts": {
    "genero": { ... },
    "grupoEtareo": { ... },
    "areasInteres": { ... },
    "municipios": { ... }
  }
}
```

---

## 📝 Checklist de Deployment

### Pre-Deployment
- [x] Código revisado y funcional en local
- [x] Migración de BD probada en local
- [x] Documentación completa
- [ ] Backup de BD de staging creado
- [ ] Archivos preparados para upload

### Deployment
- [ ] Migración de BD aplicada en staging
- [ ] Archivos PHP subidos vía FTP
- [ ] Permisos de archivos verificados (644)

### Post-Deployment
- [ ] Test 1: Crear evento ✅
- [ ] Test 2: Generar QR ✅
- [ ] Test 3: Registro público ✅
- [ ] Test 4: Visualizar asistentes ✅
- [ ] Test 5: Dashboard con filtros ✅
- [ ] Test 6: Validación de cierre ✅

### Validación Final
- [ ] No hay errores en logs de PHP
- [ ] No hay errores en consola del navegador
- [ ] Todos los gráficos se renderizan
- [ ] Filtros funcionan correctamente
- [ ] Formulario público accesible vía QR

---

## 🎯 Próximos Pasos (Post-Deployment)

1. **Agregar Dashboard al Menú Principal**
   - Modificar `index.php` para incluir enlace a `dashboard_asistencia`
   - Agregar ícono en sidebar

2. **Testing con Usuarios Reales**
   - Crear evento real
   - Generar QR e imprimir
   - Registrar asistentes reales
   - Analizar datos

3. **Implementar Exportación Excel** (opcional)
   - Instalar PHPSpreadsheet
   - Crear endpoint de exportación
   - Botones de descarga funcionales

4. **Deployment a Producción**
   - Repetir proceso en dominio principal
   - Configurar backups automáticos
   - Monitoreo de rendimiento

---

## 📞 Soporte

**Documentación:**
- Este archivo: `DEPLOYMENT_ASISTENCIA_STAGING.md`
- Migración: `database/migrations/update_asistencia_eventos_v2.sql`

**Logs en Staging:**
- Error logs: Panel Hostinger → Files → Error Logs
- PHP errors: Verificar en código con `error_log()`

**Testing Local:**
- Base de datos: `u156469157_aratio_v1`
- Comando MySQL: `"F:/xampp2/mysql/bin/mysql.exe" -u root u156469157_aratio_v1`

---

**Última actualización:** 29 de Noviembre de 2025
**Estado:** ✅ Listo para deployment a staging
**Versión del sistema:** 1.1.0
