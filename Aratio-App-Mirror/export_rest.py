import pymysql

ORIGIN_HOST = "auth-db690.hstgr.io"
ORIGIN_USER = "u156469157_aratio_v1"
ORIGIN_PASS = "15zxCeBbvgsR"
ORIGIN_DB = "u156469157_aratio_v1"

# Tablas a migrar COMPLETAS
TABLES_FULL = [
    "acciones_comunitarias",
    "asistencia_eventos",
    "campanas",
    "candidatos",
    "compromisos",
    "donaciones",
    "elecciones",
    "eventos",
    "grupos_politicos",
    "historial_cambios_lider",
    "importaciones_colaboradores",
    "jac_plancha_miembros",
    "jac_planchas",
    "jac_registros",
    "puestos_votacion",
    "supervisores_telefonos",
    "usuarios_campanas",
]

# Tablas a migrar solo ESTRUCTURA (sin datos)
TABLES_STRUCTURE_ONLY = [
    "colaboradores",
    "curriculum",
    "historial_estados",
    "reportes_diaD",
    "sesiones",
]

# Vistas
VIEWS = [
    "v_colaboradores_completo",
    "v_colaboradores_red",
    "v_estadisticas_campana",
    "v_estadisticas_perfil",
    "v_estadisticas_territorio",
    "v_lideres_metricas",
]


def export_table_structure(conn, table):
    cursor = conn.cursor()
    cursor.execute("SHOW CREATE TABLE {}".format(table))
    create = cursor.fetchone()[1]
    return create


def export_table_data(conn, table):
    cursor = conn.cursor()
    cursor.execute("SELECT * FROM {}".format(table))
    rows = cursor.fetchall()
    cursor.execute("DESCRIBE {}".format(table))
    columns = [c[0] for c in cursor.fetchall()]
    return columns, rows


def main():
    print("=" * 60)
    print("MIGRANDO TABLAS RESTANTES")
    print("=" * 60)

    # Conectar a origen
    print("\n[1] Conectando a base de datos origen...")
    conn = pymysql.connect(
        host=ORIGIN_HOST,
        user=ORIGIN_USER,
        password=ORIGIN_PASS,
        database=ORIGIN_DB,
        charset="utf8mb4",
    )
    cursor = conn.cursor()
    print("OK!")

    all_sql = []

    # Migrar tablas completas
    print("\n[2] Exportando tablas completas ({} tablas)...".format(len(TABLES_FULL)))
    for table in TABLES_FULL:
        try:
            create = export_table_structure(conn, table)
            all_sql.append("DROP TABLE IF EXISTS {};".format(table))
            all_sql.append(create + ";")
            all_sql.append("")

            columns, rows = export_table_data(conn, table)
            if rows:
                cols_str = ", ".join(columns)
                for row in rows:
                    values = []
                    for val in row:
                        if val is None:
                            values.append("NULL")
                        elif isinstance(val, (int, float)):
                            values.append(str(val))
                        else:
                            val_str = str(val).replace("'", "''")
                            values.append("'{}'".format(val_str))
                    vals_str = ", ".join(values)
                    all_sql.append(
                        "INSERT INTO {} ({}) VALUES ({});".format(
                            table, cols_str, vals_str
                        )
                    )

            print("  OK: {} ({} registros)".format(table, len(rows)))
            all_sql.append("")
        except Exception as e:
            print("  ERROR en {}: {}".format(table, e))

    # Migrar tablas solo estructura
    print(
        "\n[3] Exportando tablas solo estructura ({} tablas)...".format(
            len(TABLES_STRUCTURE_ONLY)
        )
    )
    for table in TABLES_STRUCTURE_ONLY:
        try:
            create = export_table_structure(conn, table)
            all_sql.append("DROP TABLE IF EXISTS {};".format(table))
            all_sql.append(create + ";")
            all_sql.append("")

            print("  OK: {} (solo estructura)".format(table))
            all_sql.append("")
        except Exception as e:
            print("  ERROR en {}: {}".format(table, e))

    # Migrar vistas
    print("\n[4] Exportando vistas ({} vistas)...".format(len(VIEWS)))
    for view in VIEWS:
        try:
            cursor.execute("SHOW CREATE VIEW {}".format(view))
            create = cursor.fetchone()[1]
            all_sql.append("DROP TABLE IF EXISTS {};".format(view))
            all_sql.append("CREATE VIEW {} AS {}".format(view, create))
            all_sql.append("")
            print("  OK: {}".format(view))
            all_sql.append("")
        except Exception as e:
            print("  ERROR en {}: {}".format(view, e))

    conn.close()

    # Guardar SQL
    sql_file = "aratio_migration_rest.sql"
    with open(sql_file, "w", encoding="utf-8") as f:
        f.write("\n".join(all_sql))

    print("\n[5] Archivo guardado: {} ({} lineas)".format(sql_file, len(all_sql)))
    print("\nListo para importar!")


if __name__ == "__main__":
    main()
