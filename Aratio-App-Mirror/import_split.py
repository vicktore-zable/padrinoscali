import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"


def main():
    with open(
        r"H:\Mi unidad/2025/5d/app/Multi-Campaign Management System\tmp_ssh_pass.txt",
        "r",
    ) as f:
        password = f.read().strip()

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=password, timeout=30)

    # Leer archivo completo
    with open(
        r"H:\Mi unidad\2025\5d\app\Multi-Campaign Management System\aratio_migration_rest.sql",
        "r",
        encoding="utf-8",
    ) as f:
        sql_content = f.read()

    # Dividir por tablas
    tables = sql_content.split("DROP TABLE IF EXISTS")

    print("Procesando {} secciones...".format(len(tables)))

    for i, section in enumerate(tables):
        if not section.strip():
            continue

        # Obtener nombre de tabla
        table_name = section.split(";")[0].strip()
        print("Procesando: {}...".format(table_name))

        # Crear SQL temporal para esta tabla
        section_sql = "DROP TABLE IF EXISTS " + section

        # Escribir a archivo temporal
        stdin, stdout, stderr = client.exec_command(
            "echo '{}' > /tmp/temp_table.sql".format(
                section_sql.replace("'", "'\\''").replace("\n", "\\n")[:10000]
            )
        )

        # Ejecutar
        stdin, stdout, stderr = client.exec_command(
            "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio < /tmp/temp_table.sql 2>&1"
        )

        result = stdout.read().decode("utf-8", errors="replace")
        error = stderr.read().decode("utf-8", errors="replace")

        if error and "ERROR" in error:
            print("  Error: {}".format(error[:100]))
        else:
            print("  OK")

    # Ver resultado
    stdin, stdout, stderr = client.exec_command(
        "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e 'SHOW TABLES;'"
    )
    print("\nTablas finales:")
    print(stdout.read().decode("utf-8"))

    client.close()


if __name__ == "__main__":
    main()
