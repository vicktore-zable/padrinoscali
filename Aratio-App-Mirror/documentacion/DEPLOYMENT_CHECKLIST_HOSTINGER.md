# ✅ Checklist de Deployment a Hostinger

**Proyecto**: Aratio - Selectores Geográficos 5 Niveles
**Fecha**: 26 de Noviembre de 2025
**Versión**: 1.1.0

---

## 📋 Información de Acceso

### Base de Datos Producción
```
Host: auth-db690.hstgr.io
Usuario: u156469157_aratio_v1
Password: 15zxCeBbvgsR
Base de datos: u156469157_aratio_v1
```

### FTP Hostinger
```
Host: ftp.aratio.mrmtech.net (o usar IP del panel)
Usuario: (ver panel de Hostinger)
Password: (ver panel de Hostinger)
Puerto: 21 (FTP) o 22 (SFTP)
Directorio web: /public_html/
```

### URLs
```
Producción: https://aratio.mrmtech.net
phpMyAdmin: https://hpanel.hostinger.com (panel → Base de datos → phpMyAdmin)
```

---

## 🔒 PASO 1: BACKUP (CRÍTICO)

### 1.1 Backup de Base de Datos

**Opción A: phpMyAdmin** (Recomendado)
- [ ] 1. Acceder a https://hpanel.hostinger.com
- [ ] 2. Ir a "Base de datos" → phpMyAdmin
- [ ] 3. Seleccionar base de datos `u156469157_aratio_v1`
- [ ] 4. Click en "Exportar"
- [ ] 5. Método: Rápido
- [ ] 6. Formato: SQL
- [ ] 7. Descargar archivo: `backup_aratio_20251126_preDeployment.sql`
- [ ] 8. Guardar en: `H:/Mi unidad/2025/5d/app/backups/`

**Opción B: Terminal Local** (Si tienes mysqldump)
```bash
mysqldump -h auth-db690.hstgr.io -u u156469157_aratio_v1 -p u156469157_aratio_v1 > backup_aratio_20251126.sql
```

### 1.2 Backup de Archivos (Opcional pero recomendado)

- [ ] Descargar carpeta `/public_html/` completa vía FTP
- [ ] Guardar en: `H:/Mi unidad/2025/5d/app/backups/public_html_20251126/`

---

## 📤 PASO 2: SUBIR ARCHIVOS VÍA FTP

### 2.1 Conectar al FTP

**Usar cliente FTP**: FileZilla, WinSCP, o Cyberduck

```
Host: ftp.aratio.mrmtech.net
Usuario: [del panel Hostinger]
Password: [del panel Hostinger]
Puerto: 21
```

### 2.2 Archivos a Subir

**Archivos OBLIGATORIOS**:

| Archivo Local | Destino Remoto | Status |
|---------------|----------------|--------|
| `src/php-export/api/territorios.php` | `/public_html/api/territorios.php` | [ ] |
| `src/php-export/pages/acciones.php` | `/public_html/pages/acciones.php` | [ ] |
| `src/php-export/pages/compromisos.php` | `/public_html/pages/compromisos.php` | [ ] |
| `src/php-export/pages/eventos.php` | `/public_html/pages/eventos.php` | [ ] |
| `src/php-export/api/eventos.php` | `/public_html/api/eventos.php` | [ ] |

**Archivos para BD** (temporal, para phpMyAdmin):

| Archivo Local | Destino | Status |
|---------------|---------|--------|
| `src/php-export/database/migration_selectores_20251126.sql` | Subir a `/public_html/temp/` | [ ] |
| `colaboradores/database/territorios_schema.sql` | Subir a `/public_html/temp/` | [ ] |
| `colaboradores/database/territorios_data.sql` | Subir a `/public_html/temp/` | [ ] |

**Rutas completas de archivos**:
```
H:/Mi unidad/2025/5d/app/Multi-Campaign Management System/src/php-export/api/territorios.php
H:/Mi unidad/2025/5d/app/Multi-Campaign Management System/src/php-export/api/eventos.php
H:/Mi unidad/2025/5d/app/Multi-Campaign Management System/src/php-export/pages/acciones.php
H:/Mi unidad/2025/5d/app/Multi-Campaign Management System/src/php-export/pages/compromisos.php
H:/Mi unidad/2025/5d/app/Multi-Campaign Management System/src/php-export/pages/eventos.php
H:/Mi unidad/2025/5d/app/colaboradores/database/territorios_schema.sql
H:/Mi unidad/2025/5d/app/colaboradores/database/territorios_data.sql
H:/Mi unidad/2025/5d/app/Multi-Campaign Management System/src/php-export/database/migration_selectores_20251126.sql
```

### 2.3 Verificar Subida
- [ ] Acceder a: `https://aratio.mrmtech.net/api/territorios.php`
- [ ] Debe mostrar error de conexión o array vacío (normal, falta BD)

---

## 🗄️ PASO 3: MIGRAR BASE DE DATOS

### 3.1 Acceder a phpMyAdmin

- [ ] 1. Ir a: https://hpanel.hostinger.com
- [ ] 2. Login con credenciales de Hostinger
- [ ] 3. Ir a "Base de datos" → phpMyAdmin
- [ ] 4. Seleccionar base de datos: `u156469157_aratio_v1`

### 3.2 Crear Tabla Territorios

- [ ] 1. Click en pestaña "SQL"
- [ ] 2. Copiar contenido de: `territorios_schema.sql`
- [ ] 3. Pegar en el área de texto
- [ ] 4. Click en "Continuar"
- [ ] 5. Verificar mensaje: "1 tabla creada"

**O importar archivo**:
- [ ] 1. Click en pestaña "Importar"
- [ ] 2. "Elegir archivo" → `territorios_schema.sql`
- [ ] 3. Click en "Continuar"

### 3.3 Importar Datos de Territorios (643 registros)

- [ ] 1. Click en pestaña "Importar"
- [ ] 2. "Elegir archivo" → `territorios_data.sql`
- [ ] 3. **IMPORTANTE**: Verificar que charset sea `utf8mb4`
- [ ] 4. Click en "Continuar"
- [ ] 5. Esperar (puede tardar 1-2 minutos)
- [ ] 6. Verificar mensaje: "643 filas insertadas"

### 3.4 Agregar Columnas a Tabla Eventos

**Opción A: Ejecutar Migration Script**
- [ ] 1. Click en pestaña "SQL"
- [ ] 2. Copiar contenido de: `migration_selectores_20251126.sql`
- [ ] 3. Pegar y ejecutar
- [ ] 4. Verificar que se agregaron 3 columnas

**Opción B: Manual**
- [ ] 1. Click en tabla `eventos`
- [ ] 2. Click en pestaña "Estructura"
- [ ] 3. Click en "Agregar" columna
- [ ] 4. Agregar:
  - Nombre: `tipo_territorio`, Tipo: `VARCHAR(100)`, NULL: Sí, Después de: `municipio`
  - Nombre: `territorio`, Tipo: `VARCHAR(100)`, NULL: Sí, Después de: `tipo_territorio`
  - Nombre: `barrio`, Tipo: `VARCHAR(100)`, NULL: Sí, Después de: `territorio`

### 3.5 Verificar Migraciones

- [ ] 1. Click en tabla `territorios`
- [ ] 2. Click en "Examinar"
- [ ] 3. Verificar que hay 643 registros
- [ ] 4. Verificar datos (debe aparecer "Valle del Cauca", "Cali", etc.)

- [ ] 5. Click en tabla `eventos`
- [ ] 6. Click en "Estructura"
- [ ] 7. Verificar columnas: `tipo_territorio`, `territorio`, `barrio`

---

## ✅ PASO 4: VERIFICACIÓN POST-DEPLOYMENT

### 4.1 Probar API REST

- [ ] 1. Abrir navegador
- [ ] 2. Ir a: `https://aratio.mrmtech.net/api/territorios.php?accion=departamentos`
- [ ] 3. Debe mostrar: `{"success":true,"data":["Valle del Cauca"],"count":1}`

**Otras pruebas de API**:
- [ ] `?accion=municipios&departamento=Valle+del+Cauca` → Debe retornar: Cali, Palmira, Yumbo
- [ ] `?accion=tipos_territorio&departamento=Valle+del+Cauca&municipio=Cali` → Debe retornar tipos

### 4.2 Probar Frontend

**Login**:
- [ ] 1. Ir a: `https://aratio.mrmtech.net`
- [ ] 2. Login: `admin@aratio.mrmtech.net` / `Admin123!`
- [ ] 3. Debe acceder al dashboard

**Acciones Comunitarias**:
- [ ] 1. Ir a: Acciones Comunitarias
- [ ] 2. Click en "Nueva Acción"
- [ ] 3. Verificar que selector "Departamento" muestra "Valle del Cauca"
- [ ] 4. Seleccionar departamento
- [ ] 5. Verificar que selector "Municipio" se habilita y muestra opciones
- [ ] 6. Continuar cascada hasta "Barrio"
- [ ] 7. Cancelar (no guardar aún)

**Compromisos**:
- [ ] 1. Ir a: Compromisos
- [ ] 2. Click en "Nuevo Compromiso"
- [ ] 3. Verificar selectores de 5 niveles (igual que acciones)

**Eventos**:
- [ ] 1. Ir a: Eventos
- [ ] 2. Click en "Nuevo Evento"
- [ ] 3. Verificar selectores de 5 niveles
- [ ] 4. Si hay eventos existentes, click en "Editar" (ícono amarillo)
- [ ] 5. Verificar que modal abre con datos pre-cargados

### 4.3 Prueba Completa de Creación

**Crear Evento de Prueba**:
- [ ] 1. Ir a: Eventos → "Nuevo Evento"
- [ ] 2. Llenar datos:
  - Nombre: "Prueba Selectores"
  - Tipo: "Reunión comunitaria"
  - Fecha inicio: [hoy]
  - Departamento: "Valle del Cauca"
  - Municipio: "Cali"
  - Tipo territorio: "Urbano"
  - Territorio: "Comuna 1"
  - Barrio: [seleccionar uno]
- [ ] 3. Guardar
- [ ] 4. Verificar que se guarda correctamente
- [ ] 5. Editar el evento
- [ ] 6. Verificar que todos los selectores cargan correctamente

### 4.4 Verificar en Base de Datos

- [ ] 1. Ir a phpMyAdmin
- [ ] 2. Ejecutar query:
```sql
SELECT id, nombre, departamento, municipio, tipo_territorio, territorio, barrio
FROM eventos
WHERE nombre = 'Prueba Selectores';
```
- [ ] 3. Verificar que todos los campos geográficos se guardaron correctamente

---

## 🧹 PASO 5: LIMPIEZA

### 5.1 Eliminar Archivos Temporales

- [ ] 1. Conectar vía FTP
- [ ] 2. Eliminar carpeta `/public_html/temp/` (si se creó)
- [ ] 3. O eliminar archivos SQL subidos temporalmente

### 5.2 Eliminar Evento de Prueba (Opcional)

- [ ] 1. En el sistema, ir a Eventos
- [ ] 2. Eliminar evento "Prueba Selectores"

---

## 🚨 PLAN DE ROLLBACK (Si algo falla)

### Si hay errores críticos:

1. **Restaurar Base de Datos**:
   - [ ] Ir a phpMyAdmin
   - [ ] Click en "Importar"
   - [ ] Seleccionar backup: `backup_aratio_20251126_preDeployment.sql`
   - [ ] Ejecutar

2. **Restaurar Archivos**:
   - [ ] Conectar vía FTP
   - [ ] Sobrescribir archivos con versiones del backup
   - [ ] O eliminar archivos nuevos (`api/territorios.php`)

3. **Verificar Funcionamiento**:
   - [ ] Probar login
   - [ ] Probar crear evento/acción (sin selectores, campos de texto)

---

## 📊 Resumen Final

### Checklist General

- [ ] **Backup completo realizado**
- [ ] **Archivos subidos vía FTP** (5 archivos)
- [ ] **Tabla territorios creada** (643 registros)
- [ ] **Columnas agregadas a eventos** (3 columnas)
- [ ] **API funcionando** (retorna datos)
- [ ] **Frontend funcionando** (selectores cargan)
- [ ] **Prueba completa exitosa** (crear/editar)
- [ ] **Limpieza realizada**

### Tiempo Estimado
- **Backup**: 10 minutos
- **Subir archivos FTP**: 15 minutos
- **Migraciones BD**: 20 minutos
- **Verificaciones**: 20 minutos
- **Total**: ~1 hora

---

## 📞 Contacto en Caso de Problemas

**Errores comunes y soluciones rápidas**:

1. **"Table territorios doesn't exist"**
   → Importar `territorios_schema.sql`

2. **Selectores vacíos**
   → Verificar API: `https://aratio.mrmtech.net/api/territorios.php?accion=departamentos`
   → Si no funciona, revisar que archivo fue subido correctamente

3. **Error al guardar eventos**
   → Verificar que columnas `tipo_territorio`, `territorio`, `barrio` existen en tabla `eventos`

4. **Caracteres raros (ñ, tildes)**
   → Verificar que charset de tablas sea `utf8mb4_unicode_ci`

---

**Estado**: ⏳ PENDIENTE DE EJECUCIÓN
**Última actualización**: 26 de Noviembre de 2025

---

## ✅ DEPLOYMENT COMPLETADO

Una vez completados TODOS los checkboxes arriba, marca aquí:

- [ ] **DEPLOYMENT EXITOSO Y VERIFICADO**
- [ ] **Sistema funcionando en producción**
- [ ] **Backup guardado de forma segura**
- [ ] **Documentación actualizada**

**Fecha de deployment**: _______________
**Responsable**: _______________
**Notas**: _______________________________________________
