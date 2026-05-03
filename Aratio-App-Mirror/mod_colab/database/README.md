# Base de Datos - Sistema de Gestión de Colaboradores

## 📋 Descripción

Base de datos MySQL 8 para el Sistema de Gestión Integral de Colaboradores con soporte para relaciones jerárquicas, auditoría completa y cálculos automáticos.

## 🗄️ Archivos

- **schema.sql** - Estructura completa de la base de datos (tablas, vistas, procedimientos)
- **triggers.sql** - Triggers para auditoría y validaciones automáticas
- **seeds.sql** - Datos de prueba con 100+ colaboradores en estructura jerárquica

## 🚀 Instalación

### Opción 1: Instalación Completa

```bash
# Importar todo en orden
mysql -u root -p < schema.sql
mysql -u root -p aratio < triggers.sql
mysql -u root -p aratio < seeds.sql
```

### Opción 2: Paso a Paso

```bash
# 1. Crear base de datos y tablas
mysql -u root -p < schema.sql

# 2. Agregar triggers
mysql -u root -p aratio < triggers.sql

# 3. Cargar datos de prueba (opcional)
mysql -u root -p aratio < seeds.sql
```

## 📊 Estructura de Tablas

### Tablas Principales

1. **colaboradores** - Información de colaboradores con relación jerárquica
   - Campos calculados: `estado`, `grupo_etareo`
   - Relación recursiva: `lider_directo` → `documento`

2. **curriculum** - Currículums con campos JSON
   - experiencia_laboral
   - formacion_academica
   - participacion_politica

3. **usuarios** - Autenticación y control de acceso
   - Tipos: admin, lider, consulta
   - Soporte 2FA

4. **sesiones** - Gestión de sesiones activas

5. **historial_cambios_lider** - Auditoría de cambios de líder

6. **importaciones_excel** - Log de importaciones

7. **logs_auditoria** - Auditoría completa del sistema

### Vistas Disponibles

- **v_colaboradores_completo** - Colaboradores con información completa y seguidores
- **v_estadisticas_perfil** - Estadísticas agregadas por perfil
- **v_estadisticas_territorio** - Estadísticas por territorio
- **v_lideres_metricas** - Líderes con métricas de su red

### Procedimientos Almacenados

```sql
-- Obtener red jerárquica completa de un líder
CALL sp_obtener_red_jerarquica('1000000001');

-- Cambiar líder de un colaborador
CALL sp_cambiar_lider('1000000050', '1000000010', 'Reorganización', 1);

-- Limpiar sesiones expiradas
CALL sp_limpiar_sesiones_expiradas();

-- Obtener estadísticas generales
CALL sp_estadisticas_generales();
```

## 🔐 Triggers Implementados

### Auditoría Automática

- `trg_colaboradores_after_insert` - Log de nuevos colaboradores
- `trg_colaboradores_after_update` - Log de cambios
- `trg_colaboradores_after_delete` - Log de eliminaciones
- `trg_usuarios_after_insert` - Log de nuevos usuarios
- `trg_usuarios_after_update` - Log de cambios en usuarios
- `trg_curriculum_after_insert/update` - Log de curriculum

### Validaciones

- `trg_colaboradores_before_insert_validar` - Validar datos antes de insertar
- `trg_colaboradores_before_update_validar` - Validar cambios y registrar cambios de líder
- `trg_usuarios_before_insert_validar` - Validar email y contraseña
- `trg_usuarios_before_update_validar` - Validar cambios en usuarios

### Validaciones Implementadas

✅ Un colaborador no puede ser su propio líder
✅ Formato de email válido
✅ Fecha de nacimiento no futura
✅ Edad mínima 13 años
✅ Documento solo números para CC y TI
✅ Usuario mínimo 4 caracteres
✅ Contraseña debe estar hasheada (bcrypt)
✅ Registro automático de cambios de líder

## 📈 Campos Calculados Automáticos

### estado (GENERATED COLUMN)

Calculado automáticamente basado en:
```sql
CASE
    WHEN dato_potencial = 0 THEN 'Nuevo'
    WHEN dato_historico = 0 THEN 'Desvinculado'
    WHEN dato_historico > dato_potencial THEN 'Creció'
    WHEN dato_historico < dato_potencial THEN 'Decrece'
    ELSE 'Igual'
END
```

### grupo_etareo (GENERATED COLUMN)

Calculado automáticamente basado en edad:
- Infancia (0-12 años)
- Adolescencia (13-17 años)
- Juventud (18-28 años)
- Adultez Joven (29-40 años)
- Adultez Media (41-60 años)
- Adultez Mayor (61+ años)

## 👥 Datos de Prueba (Seeds)

Al ejecutar `seeds.sql` se crean:

- **106 colaboradores** en estructura jerárquica de 3 niveles
- **7 usuarios** (1 admin, 5 líderes, 1 consulta)
- **2 currículums** de ejemplo
- Contraseña de prueba para todos: `Admin123!`

### Estructura Jerárquica

```
Nivel 0: 1 Líder Principal (documento: 1000000001)
    │
    ├─ Nivel 1: 5 Líderes Regionales
    │   │
    │   ├─ Nivel 2: 15 Líderes de Zona (3 por regional)
    │   │   │
    │   │   └─ Nivel 3: 85+ Colaboradores Base
```

### Usuarios de Prueba

| Usuario   | Email                  | Tipo     | Documento   | Contraseña |
|-----------|------------------------|----------|-------------|------------|
| admin     | admin@aratio.com       | admin    | -           | Admin123!  |
| mgarcia   | mgarcia@aratio.com     | lider    | 1000000002  | Admin123!  |
| jhernandez| jhernandez@aratio.com  | lider    | 1000000003  | Admin123!  |
| amartinez | amartinez@aratio.com   | lider    | 1000000004  | Admin123!  |
| rsanchez  | rsanchez@aratio.com    | lider    | 1000000005  | Admin123!  |
| lgomez    | lgomez@aratio.com      | lider    | 1000000006  | Admin123!  |
| consulta  | consulta@aratio.com    | consulta | -           | Admin123!  |

## 🔍 Consultas Útiles

### Ver jerarquía completa

```sql
SELECT
    c.documento,
    CONCAT(c.nombres, ' ', c.apellidos) AS nombre,
    c.lider_directo,
    CONCAT(l.nombres, ' ', l.apellidos) AS nombre_lider,
    COUNT(s.id) AS total_seguidores
FROM colaboradores c
LEFT JOIN colaboradores l ON c.lider_directo = l.documento
LEFT JOIN colaboradores s ON s.lider_directo = c.documento
GROUP BY c.id
ORDER BY c.lider_directo, c.documento;
```

### Top 10 líderes por seguidores

```sql
SELECT * FROM v_lideres_metricas LIMIT 10;
```

### Estadísticas por perfil

```sql
SELECT * FROM v_estadisticas_perfil;
```

### Colaboradores nuevos (últimos 30 días)

```sql
SELECT
    documento,
    CONCAT(nombres, ' ', apellidos) AS nombre,
    perfil,
    territorio,
    created_at
FROM colaboradores
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
ORDER BY created_at DESC;
```

### Auditoría de un colaborador

```sql
SELECT
    accion,
    datos_anteriores,
    datos_nuevos,
    created_at,
    ip_address
FROM logs_auditoria
WHERE tabla_afectada = 'colaboradores'
    AND registro_id = 1
ORDER BY created_at DESC;
```

## ⚙️ Eventos Programados

El sistema incluye un evento que se ejecuta cada hora:

```sql
-- Limpia sesiones expiradas automáticamente
CREATE EVENT evt_limpiar_sesiones
ON SCHEDULE EVERY 1 HOUR
DO CALL sp_limpiar_sesiones_expiradas();
```

Para verificar eventos activos:
```sql
SHOW EVENTS FROM aratio;
```

## 🔧 Mantenimiento

### Backup

```bash
# Backup completo
mysqldump -u root -p aratio > backup_aratio_$(date +%Y%m%d).sql

# Backup solo estructura
mysqldump -u root -p --no-data aratio > backup_estructura.sql

# Backup solo datos
mysqldump -u root -p --no-create-info aratio > backup_datos.sql
```

### Restauración

```bash
mysql -u root -p aratio < backup_aratio_20250112.sql
```

### Optimizar Tablas

```sql
OPTIMIZE TABLE colaboradores, usuarios, logs_auditoria;
```

### Ver Tamaño de Tablas

```sql
SELECT
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS "Size (MB)"
FROM information_schema.TABLES
WHERE table_schema = 'aratio'
ORDER BY (data_length + index_length) DESC;
```

## 📝 Notas Importantes

### Para Auditoría

Los triggers de auditoría requieren establecer variables de sesión antes de cada operación:

```php
// En PHP, antes de hacer operaciones:
$pdo->exec("SET @current_user_id = " . $userId);
$pdo->exec("SET @current_ip = '" . $_SERVER['REMOTE_ADDR'] . "'");
$pdo->exec("SET @current_user_agent = '" . $_SERVER['HTTP_USER_AGENT'] . "'");
```

Esto se implementará automáticamente en la clase `Database.php`.

### Índices

El schema incluye índices optimizados para:
- Búsquedas por documento
- Consultas jerárquicas (lider_directo)
- Filtros por perfil, territorio, estado
- Consultas de auditoría por fecha
- Búsquedas de sesiones

### Performance

Para bases de datos con más de 10,000 colaboradores, considerar:
- Particionamiento de `logs_auditoria` por fecha
- Archivado de logs antiguos
- Caché de consultas frecuentes
- Índices adicionales según patrones de uso

## 🐛 Troubleshooting

### Error: "Generated columns cannot have a default value"

Esto es normal, los campos `estado` y `grupo_etareo` se calculan automáticamente.

### Error: "Trigger already exists"

Ejecutar los `DROP TRIGGER IF EXISTS` en `triggers.sql` primero.

### Event scheduler no funciona

```sql
-- Verificar si está habilitado
SHOW VARIABLES LIKE 'event_scheduler';

-- Habilitar
SET GLOBAL event_scheduler = ON;

-- Agregar a my.cnf para permanencia
[mysqld]
event_scheduler=ON
```

### Sesiones no se limpian

Verificar que el event scheduler esté activo y que el procedimiento funcione:
```sql
CALL sp_limpiar_sesiones_expiradas();
SELECT COUNT(*) FROM sesiones WHERE expira_en < NOW();
```

## 📚 Referencias

- [MySQL 8.0 Reference Manual](https://dev.mysql.com/doc/refman/8.0/en/)
- [Generated Columns](https://dev.mysql.com/doc/refman/8.0/en/create-table-generated-columns.html)
- [Triggers](https://dev.mysql.com/doc/refman/8.0/en/triggers.html)
- [Stored Procedures](https://dev.mysql.com/doc/refman/8.0/en/stored-programs.html)
