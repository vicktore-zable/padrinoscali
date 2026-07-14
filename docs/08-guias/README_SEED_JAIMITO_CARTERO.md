# 📊 GENERACIÓN DE DATOS DE PRUEBA - JAIMITO EL CARTERO

**Fecha**: 29 de Enero de 2026  
**Campaña**: Jaimito el Cartero  
**Total de usuarios**: 100  
**Ubicación**: Cali, Valle del Cauca

---

## 🎯 Estructura Jerárquica

### Distribución de Usuarios

| Nivel | Tipo | Cantidad | Descripción |
|-------|------|----------|-------------|
| 5 | Líder Principal | 1 | Coordinador general de la campaña |
| 4 | Líderes Zonales | 7 | Coordinadores de comuna (1 por cada 2 comunas) |
| 3 | Líderes de Barrio | 14 | Coordinadores de barrio (2 por zona) |
| 2 | Simpatizantes Activos | ~40 | Colaboradores con participación media |
| 1 | Simpatizantes Básicos | ~38 | Colaboradores con participación básica |
| **TOTAL** | | **100** | |

---

## 📍 Distribución Geográfica

### Comunas de Cali Cubiertas

| Comuna | Barrios | Líderes Zonales | Líderes de Barrio | Simpatizantes |
|--------|---------|-----------------|-------------------|---------------|
| Comuna 1 | El Calvario | 0 | 0 | 0 |
| Comuna 2 | Obrero, La Merced | 1 | 2 | ~11 |
| Comuna 3 | San Nicolás, El Peñón | 1 | 2 | ~11 |
| Comuna 4 | Floralia, Jorge Isaacs | 1 | 2 | ~11 |
| Comuna 5 | Chiminangos, Mariano Ramos | 1 | 2 | ~11 |
| Comuna 6 | Caldas, Eucarístico | 1 | 2 | ~11 |
| Comuna 7 | Alfonso López, Comuneros | 1 | 2 | ~11 |
| Comuna 8 | Villa del Lago, Mojica | 1 | 2 | ~12 |

---

## 👥 Características de los Usuarios

### Datos Generados

- **Cédulas**: Números de 5 dígitos (10001 - 10100)
- **Género**: Distribuido aleatoriamente (aproximadamente 50/50)
- **Fechas de nacimiento**: Entre 1975 y 1998 (edades 26-49 años)
- **Emails**: Formato `nombre.apellido@demo.com`
- **Teléfonos**: Formato 300XXXXXXX (números consecutivos)
- **WhatsApp**: Mismo que teléfono
- **Estado**: Todos activos

### Métricas de Participación

- **Potencial de movilización**: 55-95 puntos
  - Líderes principales: 80-95
  - Líderes zonales: 75-90
  - Líderes de barrio: 70-80
  - Simpatizantes: 55-70

- **Histórico de participación**: 50-90 puntos
  - Similar distribución a potencial de movilización

---

## 📁 Archivos del Script

### Archivos SQL Generados

1. **`seed_jaimito_cartero_parte1.sql`**
   - Líder principal
   - 7 líderes zonales
   - 14 líderes de barrio
   - Total: 22 usuarios

2. **`seed_jaimito_cartero_parte2.sql`**
   - Primeros 30 simpatizantes
   - Distribuidos en comunas 2, 3, 4 y 5

3. **`seed_jaimito_cartero_parte3.sql`**
   - Últimos 48 simpatizantes
   - Distribuidos en comunas 6, 7 y 8
   - Consultas de verificación

---

## 🚀 Instrucciones de Ejecución

### Opción 1: Ejecutar desde phpMyAdmin (Recomendado)

1. **Acceder a phpMyAdmin**
   ```
   URL: https://hpanel.hostinger.com
   Usuario: (credenciales de Hostinger)
   Base de datos: u156469157_aratio_v1
   ```

2. **Ejecutar scripts en orden**
   - Ir a pestaña "SQL"
   - Copiar y pegar contenido de `seed_jaimito_cartero_parte1.sql`
   - Clic en "Continuar"
   - Repetir con parte 2 y parte 3

3. **Verificar resultados**
   - Las consultas de verificación al final de parte 3 mostrarán el resumen

### Opción 2: Ejecutar desde línea de comandos

```bash
# Conectar vía SSH
ssh -p 65002 u156469157@212.1.208.241

# Ejecutar scripts
mysql -h auth-db690.hstgr.io -u u156469157_aratio_v1 -p15zxCeBbvgsR u156469157_aratio_v1 < seed_jaimito_cartero_parte1.sql
mysql -h auth-db690.hstgr.io -u u156469157_aratio_v1 -p15zxCeBbvgsR u156469157_aratio_v1 < seed_jaimito_cartero_parte2.sql
mysql -h auth-db690.hstgr.io -u u156469157_aratio_v1 -p15zxCeBbvgsR u156469157_aratio_v1 < seed_jaimito_cartero_parte3.sql
```

### Opción 3: Subir archivos al servidor y ejecutar

1. **Subir archivos vía FTP**
   ```
   Host: 212.1.208.241
   Usuario: u156469157.aratio.mrmtech.net
   Password: sthLX6bJPoGh
   Directorio: /public_html/temp/
   ```

2. **Ejecutar desde PHP**
   - Crear un archivo `ejecutar_seed.php` en el servidor
   - Ver ejemplo en la sección siguiente

---

## 📝 Script PHP para Ejecutar (Opcional)

Crear archivo `ejecutar_seed_jaimito.php`:

```php
<?php
// Configuración de base de datos
$host = 'auth-db690.hstgr.io';
$dbname = 'u156469157_aratio_v1';
$username = 'u156469157_aratio_v1';
$password = '15zxCeBbvgsR';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Ejecutando Scripts de Seed</h1>";
    
    // Ejecutar parte 1
    echo "<p>Ejecutando parte 1...</p>";
    $sql1 = file_get_contents('seed_jaimito_cartero_parte1.sql');
    $pdo->exec($sql1);
    echo "<p style='color:green'>✅ Parte 1 completada</p>";
    
    // Ejecutar parte 2
    echo "<p>Ejecutando parte 2...</p>";
    $sql2 = file_get_contents('seed_jaimito_cartero_parte2.sql');
    $pdo->exec($sql2);
    echo "<p style='color:green'>✅ Parte 2 completada</p>";
    
    // Ejecutar parte 3
    echo "<p>Ejecutando parte 3...</p>";
    $sql3 = file_get_contents('seed_jaimito_cartero_parte3.sql');
    $pdo->exec($sql3);
    echo "<p style='color:green'>✅ Parte 3 completada</p>";
    
    // Verificar
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM colaboradores WHERE campana_id = (SELECT id FROM campanas WHERE nombre LIKE '%Jaimito%' LIMIT 1)");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2 style='color:green'>✅ Proceso Completado</h2>";
    echo "<p><strong>Total de usuarios creados: {$result['total']}</strong></p>";
    
} catch(PDOException $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
```

---

## ✅ Verificación Post-Ejecución

### Consultas de Verificación

```sql
-- Ver total de usuarios
SELECT COUNT(*) as total 
FROM colaboradores 
WHERE campana_id = (SELECT id FROM campanas WHERE nombre LIKE '%Jaimito%' LIMIT 1);

-- Ver distribución por nivel
SELECT 
    nivel_liderazgo,
    tipo_colaborador,
    COUNT(*) as cantidad
FROM colaboradores 
WHERE campana_id = (SELECT id FROM campanas WHERE nombre LIKE '%Jaimito%' LIMIT 1)
GROUP BY nivel_liderazgo, tipo_colaborador
ORDER BY nivel_liderazgo DESC;

-- Ver distribución por comuna
SELECT 
    territorio,
    COUNT(*) as cantidad
FROM colaboradores 
WHERE campana_id = (SELECT id FROM campanas WHERE nombre LIKE '%Jaimito%' LIMIT 1)
GROUP BY territorio
ORDER BY territorio;

-- Ver distribución por género
SELECT 
    genero,
    COUNT(*) as cantidad
FROM colaboradores 
WHERE campana_id = (SELECT id FROM colaboradores WHERE numero_documento = '10022'), 'activo', 60, 56, NOW(), NOW());

-- =====================================================
-- VERIFICACIÓN Y RESUMEN
-- =====================================================

-- Contar usuarios creados
SELECT 
    'Total de colaboradores creados' as descripcion,
    COUNT(*) as cantidad
FROM colaboradores 
WHERE campana_id = @campana_id;

-- Resumen por nivel de liderazgo
SELECT 
    nivel_liderazgo,
    tipo_colaborador,
    COUNT(*) as cantidad
FROM colaboradores 
WHERE campana_id = @campana_id
GROUP BY nivel_liderazgo, tipo_colaborador
ORDER BY nivel_liderazgo DESC;

-- Resumen por comuna
SELECT 
    territorio as comuna,
    COUNT(*) as cantidad_colaboradores
FROM colaboradores 
WHERE campana_id = @campana_id
GROUP BY territorio
ORDER BY territorio;

-- Resumen por género
SELECT 
    genero,
    COUNT(*) as cantidad
FROM colaboradores 
WHERE campana_id = @campana_id
GROUP BY genero;
```

---

## 🎯 Resultado Esperado

Después de ejecutar los 3 scripts, deberías tener:

- ✅ 100 usuarios de prueba en la campaña "Jaimito el Cartero"
- ✅ Estructura jerárquica de 5 niveles
- ✅ Distribución geográfica en 8 comunas de Cali
- ✅ Datos realistas (nombres, emails, teléfonos, etc.)
- ✅ Métricas de participación variadas
- ✅ Todos los usuarios en estado activo

---

## 📞 Notas Importantes

1. **Campaña Demo**: Estos datos son para demostración y pruebas
2. **Emails**: Todos usan el dominio `@demo.com` (no son reales)
3. **Teléfonos**: Números consecutivos desde 3023456789
4. **Cédulas**: Números de 5 dígitos (10001-10100)
5. **Jerarquía**: Cada usuario tiene su `lider_id` correctamente asignado

---

**Creado por**: Sistema Aratio  
**Fecha**: 2026-01-29  
**Versión**: 1.0
