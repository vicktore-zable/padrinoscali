import pymysql

HOST = "auth-db690.hstgr.io"
USER = "u156469157_aratio_v1"
PASS = "15zxCeBbvgsR"
DB = "u156469157_aratio_v1"

conn = pymysql.connect(
    host=HOST, user=USER, password=PASS, database=DB, charset="utf8mb4"
)
cursor = conn.cursor()

# Tablas con datos a migrar
TABLES_WITH_DATA = [
    "acciones_comunitarias",
    "asistencia_eventos",
    "campanas",
    "candidatos",
    "eventos",
    "historial_cambios_lider",
    "jac_plancha_miembros",
    "jac_planchas",
    "jac_registros",
    "usuarios_campanas",
]


def escape_val(val):
    if val is None:
        return "NULL"
    if isinstance(val, (int, float)):
        return str(val)
    return "'{}'".format(str(val).replace("'", "''"))


all_sql = []

for table in TABLES_WITH_DATA:
    print("Exportando {}...".format(table))

    # Get structure
    cursor.execute("SHOW CREATE TABLE {}".format(table))
    create = cursor.fetchone()[1]
    all_sql.append("DROP TABLE IF EXISTS {};".format(table))
    all_sql.append(create + ";")
    all_sql.append("")

    # Get data
    cursor.execute("SELECT * FROM {}".format(table))
    rows = cursor.fetchall()

    if rows:
        cursor.execute("DESCRIBE {}".format(table))
        cols = [c[0] for c in cursor.fetchall()]
        cols_str = ", ".join(cols)

        for row in rows:
            vals = ", ".join([escape_val(v) for v in row])
            all_sql.append(
                "INSERT INTO {} ({}) VALUES ({});".format(table, cols_str, vals)
            )

    print("  {} registros".format(len(rows)))
    all_sql.append("")

conn.close()

# Save
with open("aratio_data_only.sql", "w", encoding="utf-8") as f:
    f.write("\n".join(all_sql))

print("\nArchivo guardado: aratio_data_only.sql ({} lineas)".format(len(all_sql)))
