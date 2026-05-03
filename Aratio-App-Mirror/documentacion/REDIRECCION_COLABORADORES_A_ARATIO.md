# 🔄 REDIRECCIÓN DE COLABORADORES A ARATIO

**Fecha**: 29 de Enero de 2026  
**Objetivo**: Redirigir todo el tráfico de inscripción desde `colaboradores.aratio.mrmtech.net` hacia `aratio.mrmtech.net`

---

## 📋 Situación Actual

- **Base de datos principal**: `aratio` (u156469157_aratio_v1)
- **URL principal**: https://aratio.mrmtech.net/inscripcion
- **URL antigua (a redirigir)**: https://colaboradores.aratio.mrmtech.net/inscripcion
- **Objetivo**: Todos los registros deben ir a la base de datos de aratio

---

## ✅ Solución Implementada

### Opción 1: Redirección con .htaccess (RECOMENDADA)

Si `colaboradores.aratio.mrmtech.net` está en el mismo servidor de Hostinger:

1. **Ubicar el directorio** de `colaboradores.aratio.mrmtech.net`
   - Probablemente en: `/home/u156469157/domains/colaboradores.aratio.mrmtech.net/public_html/`

2. **Crear o editar** el archivo `.htaccess` en la raíz de ese directorio

3. **Agregar estas líneas**:
```apache
RewriteEngine On

# Redirigir /inscripcion a aratio.mrmtech.net
RewriteRule ^inscripcion/?$ https://aratio.mrmtech.net/inscripcion [R=301,L]

# Redirigir /registro-lider a aratio.mrmtech.net
RewriteRule ^registro-lider/?$ https://aratio.mrmtech.net/inscripcion [R=301,L]
```

4. **Guardar** y probar accediendo a https://colaboradores.aratio.mrmtech.net/inscripcion

### Opción 2: Redirección con PHP

Si prefieres usar PHP o no tienes acceso al .htaccess:

1. **Reemplazar** el archivo `inscripcion.php` en colaboradores con:

```php
<?php
// Redirección permanente a aratio.mrmtech.net
header('HTTP/1.1 301 Moved Permanently');
header('Location: https://aratio.mrmtech.net/inscripcion');
exit;
?>
```

---

## 🗄️ Consolidación de Bases de Datos

Si ya tienes registros en la base de datos de `colaboradores`, necesitas migrarlos a `aratio`:

### Paso 1: Exportar datos de colaboradores

```sql
-- Conectar a la base de datos de colaboradores
-- Exportar colaboradores
SELECT * FROM colaboradores 
WHERE created_at > '2026-01-01'  -- Ajustar fecha según necesites
INTO OUTFILE '/tmp/colaboradores_export.csv'
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n';
```

### Paso 2: Importar a aratio

```sql
-- Conectar a u156469157_aratio_v1
-- Importar colaboradores
LOAD DATA INFILE '/tmp/colaboradores_export.csv'
INTO TABLE colaboradores
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n';
```

### Alternativa: Script PHP de Migración

He creado un script que puedes ejecutar una vez para migrar todos los datos:

```php
<?php
// Configuración base de datos ORIGEN (colaboradores)
$dbOrigen = new PDO(
    'mysql:host=HOST_COLABORADORES;dbname=DB_COLABORADORES',
    'USER_COLABORADORES',
    'PASS_COLABORADORES'
);

// Configuración base de datos DESTINO (aratio)
$dbDestino = new PDO(
    'mysql:host=auth-db690.hstgr.io;dbname=u156469157_aratio_v1',
    'u156469157_aratio_v1',
    '15zxCeBbvgsR'
);

// Obtener colaboradores de origen
$colaboradores = $dbOrigen->query("SELECT * FROM colaboradores")->fetchAll(PDO::FETCH_ASSOC);

// Insertar en destino
$stmt = $dbDestino->prepare("INSERT INTO colaboradores (...campos...) VALUES (...valores...)");

foreach ($colaboradores as $col) {
    $stmt->execute([...datos...]);
}

echo "Migrados " . count($colaboradores) . " colaboradores";
?>
```

---

## 🔍 Verificación

### Probar la Redirección

1. Abrir navegador en modo incógnito
2. Ir a: https://colaboradores.aratio.mrmtech.net/inscripcion
3. Verificar que redirige automáticamente a: https://aratio.mrmtech.net/inscripcion

### Verificar Código de Redirección

Usar curl para ver el código HTTP:

```bash
curl -I https://colaboradores.aratio.mrmtech.net/inscripcion
```

Debe mostrar:
```
HTTP/1.1 301 Moved Permanently
Location: https://aratio.mrmtech.net/inscripcion
```

---

## 📝 Notas Importantes

### Mantener colaboradores.aratio.mrmtech.net para Demos

Si quieres mantener el sitio de colaboradores activo para demos pero redirigir solo `/inscripcion`:

1. **NO** agregues la redirección global en .htaccess
2. Solo redirige rutas específicas:
   - `/inscripcion` → aratio
   - `/registro-lider` → aratio
3. El resto del sitio (dashboard, reportes, etc.) sigue funcionando en colaboradores

### SEO y Enlaces Externos

- La redirección 301 es permanente y le indica a Google que la página se movió
- Actualiza cualquier enlace externo o publicidad que apunte a colaboradores
- Los motores de búsqueda transferirán el "ranking" a la nueva URL

---

## 🚀 Pasos de Implementación

### Inmediato (Redirección)

1. ✅ Acceder a Hostinger hPanel
2. ✅ Ir a File Manager
3. ✅ Navegar a `colaboradores.aratio.mrmtech.net/public_html/`
4. ✅ Editar `.htaccess` o crear `inscripcion.php` con redirección
5. ✅ Probar la redirección

### Opcional (Migración de Datos)

1. ⏳ Exportar datos de colaboradores (si existen)
2. ⏳ Importar a base de datos aratio
3. ⏳ Verificar integridad de datos
4. ⏳ Hacer backup antes de cualquier cambio

---

## 📞 Archivos Creados

1. **`redirect_inscripcion.php`** - Archivo PHP de redirección
2. **`htaccess_colaboradores_redirect.txt`** - Reglas para .htaccess

---

## ⚠️ Importante

- **Hacer backup** de la base de datos antes de migrar
- **Probar** la redirección en modo incógnito
- **Actualizar** enlaces en redes sociales y publicidad
- **Monitorear** los registros para asegurar que llegan a aratio

---

**Estado**: ✅ Archivos de redirección creados y listos para implementar  
**Próximo paso**: Subir archivos al servidor de colaboradores.aratio.mrmtech.net
