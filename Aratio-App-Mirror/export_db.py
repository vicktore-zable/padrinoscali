import pymysql

ORIGIN_HOST = "auth-db690.hstgr.io"
ORIGIN_USER = "u156469157_aratio_v1"
ORIGIN_PASS = "15zxCeBbvgsR"
ORIGIN_DB = "u156469157_aratio_v1"

TABLES = [
    "usuarios",
    "territorios",
    "territorios_valle",
    "campanas",
    "candidatos",
    "colaboradores",
    "curriculum",
    "historial_cambios_lider",
    "historial_estados",
]


def export_table(table):
    conn = pymysql.connect(
        host=ORIGIN_HOST,
        user=ORIGIN_USER,
        password=ORIGIN_PASS,
        database=ORIGIN_DB,
        charset="utf8mb4",
    )
    cursor = conn.cursor()

    print("  Obteniendo estructura de {}...".format(table))
    cursor.execute("SHOW CREATE TABLE {}".format(table))
    create = cursor.fetchone()[1]

    print("  Obteniendo datos de {}...".format(table))
    cursor.execute("SELECT * FROM {}".format(table))
    rows = cursor.fetchall()

    cursor.execute("DESCRIBE {}".format(table))
    columns = [c[0] for c in cursor.fetchall()]

    conn.close()

    return create, columns, rows


def main():
    print("EXPORTANDO TABLAS PRINCIPALES (9 tablas)...")
    print("")

    all_sql = []

    for table in TABLES:
        try:
            create, columns, rows = export_table(table)
            all_sql.append("DROP TABLE IF EXISTS {};".format(table))
            all_sql.append(create + ";")
            all_sql.append("")

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

    # Guardar
    sql_file = "aratio_tables_basic.sql"
    with open(sql_file, "w", encoding="utf-8") as f:
        f.write("\n".join(all_sql))

    print("")
    print("Archivo guardado: {} ({} lineas)".format(sql_file, len(all_sql)))


if __name__ == "__main__":
    main()
