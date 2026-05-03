#!/bin/bash

# Script para sincronizar base de datos local con remota
# Exporta toda la BD local e importa a remota

set -e

MYSQL_LOCAL="F:/xampp2/mysql/bin/mysql.exe"
MYSQLDUMP_LOCAL="F:/xampp2/mysql/bin/mysqldump.exe"

DB_REMOTE_HOST="auth-db690.hstgr.io"
DB_REMOTE_USER="u156469157_aratio"
DB_REMOTE_PASS="15zxCeBbvgsR"
DB_REMOTE_NAME="u156469157_aratio"

DB_LOCAL_HOST="localhost"
DB_LOCAL_USER="root"
DB_LOCAL_PASS=""
DB_LOCAL_NAME="aratio"

EXPORT_FILE="database/full_export_$(date +%Y%m%d_%H%M%S).sql"

echo "========================================="
echo " SINCRONIZAR BASE DE DATOS"
echo " Local → Remota (Hostinger)"
echo "========================================="
echo ""

echo "1. Exportando base de datos local completa..."
"$MYSQLDUMP_LOCAL" -h "$DB_LOCAL_HOST" -u "$DB_LOCAL_USER" \
  --routines --triggers --events \
  --ignore-table="$DB_LOCAL_NAME.colaboradores_backup_utf8_20251020" \
  "$DB_LOCAL_NAME" > "$EXPORT_FILE"

echo "✓ Exportado a: $EXPORT_FILE"
echo "  Tamaño: $(du -h "$EXPORT_FILE" | cut -f1)"

echo ""
echo "2. Limpiando base de datos remota..."
"$MYSQL_LOCAL" -h "$DB_REMOTE_HOST" -u "$DB_REMOTE_USER" -p"$DB_REMOTE_PASS" \
  "$DB_REMOTE_NAME" << 'EOSQL'
SET FOREIGN_KEY_CHECKS = 0;

-- Eliminar procedures
DROP PROCEDURE IF EXISTS sp_obtener_red_jerarquica;
DROP PROCEDURE IF EXISTS sp_cambiar_lider;
DROP PROCEDURE IF EXISTS sp_estadisticas_generales;
DROP PROCEDURE IF EXISTS sp_limpiar_sesiones_expiradas;
DROP PROCEDURE IF EXISTS sp_limpiar_tokens_expirados;

-- Eliminar vistas
DROP VIEW IF EXISTS v_colaboradores_completo;
DROP VIEW IF EXISTS v_estadisticas_perfil;
DROP VIEW IF EXISTS v_estadisticas_territorio;
DROP VIEW IF EXISTS v_lideres_metricas;

-- Eliminar tablas
DROP TABLE IF EXISTS logs_auditoria;
DROP TABLE IF EXISTS importaciones_excel;
DROP TABLE IF EXISTS historial_cambios_lider;
DROP TABLE IF EXISTS sesiones;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS curriculum;
DROP TABLE IF EXISTS territorios;
DROP TABLE IF EXISTS colaboradores;

SET FOREIGN_KEY_CHECKS = 1;
EOSQL

echo "✓ Base de datos remota limpiada"

echo ""
echo "3. Importando a base de datos remota..."
"$MYSQL_LOCAL" -h "$DB_REMOTE_HOST" -u "$DB_REMOTE_USER" -p"$DB_REMOTE_PASS" \
  "$DB_REMOTE_NAME" < "$EXPORT_FILE"

echo "✓ Importación completada"

echo ""
echo "4. Verificando tablas importadas..."
"$MYSQL_LOCAL" -h "$DB_REMOTE_HOST" -u "$DB_REMOTE_USER" -p"$DB_REMOTE_PASS" \
  "$DB_REMOTE_NAME" -e "SHOW TABLES;"

echo ""
echo "5. Verificando conteos..."
"$MYSQL_LOCAL" -h "$DB_REMOTE_HOST" -u "$DB_REMOTE_USER" -p"$DB_REMOTE_PASS" \
  "$DB_REMOTE_NAME" << 'EOSQL'
SELECT 'colaboradores' as tabla, COUNT(*) as registros FROM colaboradores
UNION ALL
SELECT 'usuarios', COUNT(*) FROM usuarios
UNION ALL
SELECT 'territorios', COUNT(*) FROM territorios
UNION ALL
SELECT 'curriculum', COUNT(*) FROM curriculum;
EOSQL

echo ""
echo "========================================="
echo " SINCRONIZACIÓN COMPLETADA"
echo "========================================="
echo ""
echo "Ahora prueba el sitio:"
echo "https://colaboradores.aratio.mrmtech.net/"
echo ""
