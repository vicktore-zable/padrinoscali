import pymysql
import paramiko
import sys

# Credenciales origen (Hostinger original)
ORIGIN_HOST = "auth-db690.hstgr.io"
ORIGIN_USER = "u156469157_aratio_v1"
ORIGIN_PASS = "15zxCeBbvgsR"
ORIGIN_DB = "u156469157_aratio_v1"

# Credenciales destino (nuevo servidor via SSH)
DEST_HOST = "localhost"
DEST_USER = "u577647812_aratio"
DEST_PASS = "v6xSHUWhjrxE"
DEST_DB = "u577647812_aratio"


def main():
    print("=" * 60)
    print("EXPORTANDO BASE DE DATOS DE ARATIO")
    print("=" * 60)
    print("")
    print("Origen: {}@{}".format(ORIGIN_USER, ORIGIN_HOST))
    print("Destino: {}@{}".format(DEST_USER, DEST_HOST))
    print("")

    # Conectar a origen
    print("[1/4] Conectando a base de datos origen...")
    try:
        conn_origin = pymysql.connect(
            host=ORIGIN_HOST,
            user=ORIGIN_USER,
            password=ORIGIN_PASS,
            database=ORIGIN_DB,
            charset="utf8mb4",
        )
        print("OK - Conexion exitosa!")
    except Exception as e:
        print("ERROR: {}".format(e))
        return

    cursor = conn_origin.cursor()

    # Obtener tablas
    cursor.execute("SHOW TABLES")
    tables = [t[0] for t in cursor.fetchall()]
    print("Tablas encontradas: {}".format(len(tables)))
    print("")

    # Obtener estructura y datos de cada tabla
    print("[2/4] Exportando estructura y datos...")
    all_sql = []

    for table in tables:
        print("  Exportando: {}...".format(table))

        # CREATE TABLE
        cursor.execute("SHOW CREATE TABLE {}".format(table))
        create_stmt = cursor.fetchone()[1]
        all_sql.append("DROP TABLE IF EXISTS {};".format(table))
        all_sql.append(create_stmt + ";")
        all_sql.append("")

        # Datos
        cursor.execute("SELECT * FROM {}".format(table))
        rows = cursor.fetchall()

        if rows:
            # Obtener columnas
            cursor.execute("DESCRIBE {}".format(table))
            columns = [c[0] for c in cursor.fetchall()]
            cols_str = ", ".join(columns)

            for row in rows:
                # Escapar valores
                values = []
                for val in row:
                    if val is None:
                        values.append("NULL")
                    elif isinstance(val, (int, float)):
                        values.append(str(val))
                    else:
                        # Escapar comillas simples
                        val_str = str(val).replace("'", "''")
                        values.append("'{}'".format(val_str))

                vals_str = ", ".join(values)
                all_sql.append(
                    "INSERT INTO {} ({}) VALUES ({});".format(table, cols_str, vals_str)
                )

        all_sql.append("")

    conn_origin.close()
    print("OK - Exportacion completa!")

    # Guardar SQL a archivo temporal
    sql_file = "aratio_migration.sql"
    with open(sql_file, "w", encoding="utf-8") as f:
        f.write("\n".join(all_sql))

    print("[3/4] Archivo SQL guardado: {} ({} lineas)".format(sql_file, len(all_sql)))

    # Ahora conectar al servidor destino via SSH y ejecutar SQL
    print("[4/4] Importando en el nuevo servidor...")

    with open(
        r"H:\Mi unidad\2025\5d\app\Multi-Campaign Management System\tmp_ssh_pass.txt",
        "r",
    ) as f:
        ssh_pass = f.read().strip()

    # Subir archivo SQL
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(
        "157.173.208.254",
        port=65002,
        username="u577647812",
        password=ssh_pass,
        timeout=30,
    )

    sftp = client.open_sftp()
    sftp.put(sql_file, "/home/u577647812/aratio_migration.sql")
    sftp.close()
    print("  Archivo SQL subido al servidor")

    # Importar
    stdin, stdout, stderr = client.exec_command(
        "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio < /home/u577647812/aratio_migration.sql"
    )

    result = stdout.read().decode("utf-8")
    error = stderr.read().decode("utf-8")

    if error and "ERROR" in error:
        print("ERROR en importacion:")
        print(error)
    else:
        print("OK - Importacion completada!")

    # Verificar tablas importadas
    stdin, stdout, stderr = client.exec_command(
        "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e 'SHOW TABLES;'"
    )
    tables_imported = stdout.read().decode("utf-8")
    print("")
    print("Tablas en el nuevo servidor:")
    print(tables_imported)

    client.close()

    print("")
    print("=" * 60)
    print("MIGRACION COMPLETADA!")
    print("=" * 60)
    print("")
    print("Ahora puedes probar en: https://edisongiraldo.com/aratio/")
    print("")


if __name__ == "__main__":
    main()
