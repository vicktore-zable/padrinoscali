# Deployment: Selectores Geográficos de 5 Niveles

**Fecha**: 26 de Noviembre de 2025
**Versión**: 1.1.0
**Cambios**: Sistema de selectores geográficos en cascada implementado

---

## 📋 Resumen de Cambios

Se ha implementado un sistema completo de **selectores geográficos en cascada de 5 niveles** para Colombia en los módulos de Acciones Comunitarias, Compromisos y Eventos.

### Jerarquía Territorial
```
Nivel 1: Departamento (Ej: Valle del Cauca)
  └─ Nivel 2: Municipio (Ej: Cali)
      └─ Nivel 3: Tipo_territorio (Ej: Urbano, Comuna)
          └─ Nivel 4: Territorio (Ej: Comuna 1)
              └─ Nivel 5: Barrio (Ej: Terrón Colorado)
```

---

## 🗄️ Cambios en Base de Datos

### Nueva Tabla: `territorios`

```sql
CREATE TABLE `territorios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `departamento` varchar(255) DEFAULT NULL,
  `municipio` varchar(255) DEFAULT NULL,
  `Tipo_territorio` varchar(255) DEFAULT NULL,
  `cod_mpio` varchar(255) DEFAULT NULL,
  `Código` varchar(255) DEFAULT NULL,
  `Territorio` varchar(255) DEFAULT NULL,
  `barrio` varchar(255) DEFAULT NULL,
  `Geom` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Datos**: 643 registros (Valle del Cauca: Cali, Palmira, Yumbo)

### Columnas Agregadas a Tabla `eventos`

```sql
ALTER TABLE eventos
ADD COLUMN tipo_territorio VARCHAR(100) NULL AFTER municipio,
ADD COLUMN territorio VARCHAR(100) NULL AFTER tipo_territorio,
ADD COLUMN barrio VARCHAR(100) NULL AFTER territorio;
```

**Nota**: Las tablas `acciones_comunitarias` y `compromisos` ya tenían estos campos.

---

## 📁 Archivos Nuevos

### API REST
- **`/api/territorios.php`** - API para consultar jerarquía territorial
  - Endpoints: departamentos, municipios, tipos_territorio, territorios, barrios, completo

### Datos
- **`/database/territorios_schema.sql`** - Schema de tabla territorios
- **`/database/territorios_data.sql`** - 643 registros de datos

---

## 📝 Archivos Modificados

### Frontend (pages/)
1. **`pages/acciones.php`**
   - Agregados selectores de 5 niveles
   - Método `init()` con `x-init`
   - Método `abrirModalNuevo()`
   - Métodos de cascada completos

2. **`pages/compromisos.php`**
   - Agregados selectores de 5 niveles
   - Misma funcionalidad que acciones
   - Integrado en "5 preguntas" (¿DÓNDE?)

3. **`pages/eventos.php`**
   - Actualizado de 2 a 5 niveles
   - Agregado método `editar(id)`
   - Actualizado `cerrarModal()`
   - Métodos de cascada completos

### Backend (api/)
4. **`api/eventos.php`**
   - INSERT: Agregados 3 campos (tipo_territorio, territorio, barrio)
   - UPDATE: Agregados 3 campos

---

## 🚀 Plan de Despliegue en Hostinger

### Prerrequisitos

**Credenciales de Hostinger**:
- Host: `auth-db690.hstgr.io`
- Usuario: `u156469157_aratio_v1`
- Password: `15zxCeBbvgsR`
- Base de datos: `u156469157_aratio_v1`
- Dominio: `https://aratio.mrmtech.net`

**Acceso FTP**:
- Host: `ftp.aratio.mrmtech.net`
- Usuario: (según panel de Hostinger)
- Directorio: `/public_html/`

---

## 📋 Checklist de Despliegue

### Paso 1: Preparación Local ✅

- [x] Código funcional en desarrollo (XAMPP)
- [x] Base de datos con 643 registros de territorios
- [x] Todos los archivos sincronizados
- [x] Sin errores de sintaxis PHP
- [x] Documentación completa

### Paso 2: Backup de Producción

```bash
# Conectar a base de datos de producción
mysql -h auth-db690.hstgr.io -u u156469157_aratio_v1 -p u156469157_aratio_v1

# Exportar backup
mysqldump -h auth-db690.hstgr.io -u u156469157_aratio_v1 -p u156469157_aratio_v1 > backup_pre_selectores_$(date +%Y%m%d).sql
```

### Paso 3: Migraciones de Base de Datos

**Archivo**: `database/migration_selectores_20251126.sql`

```sql
-- 1. Crear tabla territorios
CREATE TABLE IF NOT EXISTS `territorios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `departamento` varchar(255) DEFAULT NULL,
  `municipio` varchar(255) DEFAULT NULL,
  `Tipo_territorio` varchar(255) DEFAULT NULL,
  `cod_mpio` varchar(255) DEFAULT NULL,
  `Código` varchar(255) DEFAULT NULL,
  `Territorio` varchar(255) DEFAULT NULL,
  `barrio` varchar(255) DEFAULT NULL,
  `Geom` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Agregar columnas a eventos (solo si no existen)
ALTER TABLE eventos
ADD COLUMN IF NOT EXISTS tipo_territorio VARCHAR(100) NULL AFTER municipio,
ADD COLUMN IF NOT EXISTS territorio VARCHAR(100) NULL AFTER tipo_territorio,
ADD COLUMN IF NOT EXISTS barrio VARCHAR(100) NULL AFTER territorio;

-- 3. Importar datos de territorios
-- (Se hará por separado usando phpMyAdmin o archivo SQL)
```

### Paso 4: Subir Archivos vía FTP

**Archivos a subir**:

```
/api/territorios.php                    → /public_html/api/
/pages/acciones.php                     → /public_html/pages/
/pages/compromisos.php                  → /public_html/pages/
/pages/eventos.php                      → /public_html/pages/
/database/territorios_schema.sql        → temporal (para phpMyAdmin)
/database/territorios_data.sql          → temporal (para phpMyAdmin)
```

### Paso 5: Ejecutar Migraciones en Producción

**Opción A: phpMyAdmin** (Recomendado)
1. Acceder a phpMyAdmin en Hostinger
2. Seleccionar base de datos `u156469157_aratio_v1`
3. Ir a pestaña "Importar"
4. Importar `territorios_schema.sql`
5. Importar `territorios_data.sql`
6. Ejecutar ALTER TABLE para eventos (si es necesario)

**Opción B: Terminal SSH** (Si está disponible)
```bash
mysql -h auth-db690.hstgr.io -u u156469157_aratio_v1 -p u156469157_aratio_v1 < territorios_schema.sql
mysql -h auth-db690.hstgr.io -u u156469157_aratio_v1 -p u156469157_aratio_v1 < territorios_data.sql
```

### Paso 6: Verificación Post-Despliegue

**URLs de Prueba**:
- ✅ API: `https://aratio.mrmtech.net/api/territorios.php?accion=departamentos`
- ✅ Acciones: `https://aratio.mrmtech.net/pages/acciones.php`
- ✅ Compromisos: `https://aratio.mrmtech.net/pages/compromisos.php`
- ✅ Eventos: `https://aratio.mrmtech.net/pages/eventos.php`

**Verificar**:
1. Login funciona
2. Selector de departamentos carga "Valle del Cauca"
3. Cascada funciona (departamento → municipio → tipo → territorio → barrio)
4. Crear evento/acción/compromiso con datos geográficos
5. Editar evento carga selectores correctamente
6. Planillas de asistencia funcionan

---

## 🔧 Troubleshooting

### Problema: "Table territorios doesn't exist"
**Solución**: Importar `territorios_schema.sql` y `territorios_data.sql`

### Problema: Selectores vacíos
**Solución**:
1. Verificar que API `/api/territorios.php` es accesible
2. Revisar consola del navegador (F12) para errores JavaScript
3. Verificar que Alpine.js está cargado

### Problema: Error al editar eventos
**Solución**: Verificar que columnas `tipo_territorio`, `territorio`, `barrio` existen en tabla `eventos`

### Problema: CORS o permisos
**Solución**: Verificar que archivo `.htaccess` permite acceso a `/api/`

---

## 📊 Estadísticas del Cambio

| Métrica | Valor |
|---------|-------|
| Archivos nuevos | 3 |
| Archivos modificados | 4 |
| Líneas de código agregadas | ~800 |
| Endpoints API nuevos | 6 |
| Registros en BD | 643 |
| Niveles geográficos | 5 |
| Módulos afectados | 3 (Acciones, Compromisos, Eventos) |

---

## 🎯 Beneficios Implementados

✅ **Precisión Geográfica**: 5 niveles de detalle territorial
✅ **Cascada Automática**: Selectores se llenan automáticamente
✅ **Validación**: Campos obligatorios y opcionales bien definidos
✅ **Edición Completa**: Pre-carga de datos al editar
✅ **API REST**: Endpoints reutilizables
✅ **Escalable**: Fácil agregar más departamentos

---

## 📅 Próximos Pasos

### Inmediato
- [ ] Hacer backup de producción
- [ ] Subir archivos a Hostinger
- [ ] Ejecutar migraciones
- [ ] Verificar funcionamiento

### Corto Plazo
- [ ] Agregar más departamentos de Colombia
- [ ] Implementar caché en frontend
- [ ] Agregar indicador de "Cargando..."

### Mediano Plazo
- [ ] Mapa interactivo que se actualice con selección
- [ ] Exportar/Importar datos territoriales
- [ ] Cobertura nacional completa

---

## 📞 Contacto y Soporte

**Sistema**: Aratio - Multi-Campaign Management System
**Versión**: 1.1.0
**Estado**: ✅ Listo para deployment
**Documentación**: Ver `SELECTORES_GEOGRAFICOS_IMPLEMENTADOS.md`

---

**Actualizado**: 26 de Noviembre de 2025
**Responsable**: Claude Code
**Estado**: ✅ **LISTO PARA PRODUCCIÓN**
