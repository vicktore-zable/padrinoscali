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

    # Tablas a migrar
    tables = [
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

    # Tablas solo estructura
    structure_only = [
        "colaboradores",
        "curriculum",
        "historial_estados",
        "reportes_diaD",
        "sesiones",
    ]

    # Leer el SQL
    with open(
        r"H:\Mi unidad/2025\5d\app\Multi-Campaign Management System\aratio_migration_rest.sql",
        "r",
        encoding="utf-8",
    ) as f:
        content = f.read()

    sftp = client.open_sftp()

    # Procesar cada tabla
    for table in tables + structure_only:
        print("Procesando {}...".format(table))

        # Extraer CREATE TABLE para esta tabla
        marker = "DROP TABLE IF EXISTS {};".format(table)
        idx = content.find(marker)

        if idx == -1:
            print("  No encontrada")
            continue

        # Encontrar el final (siguiente DROP TABLE o fin de archivo)
        next_marker = "DROP TABLE IF EXISTS"
        next_idx = content.find(next_marker, idx + len(marker))

        if next_idx == -1:
            table_sql = content[idx:]
        else:
            table_sql = content[idx:next_idx]

        # Escribir a archivo temporal
        with sftp.file("/home/u577647812/create_{}.sql".format(table), "w") as f:
            f.write(table_sql.encode("utf-8"))

        # Ejecutar
        stdin, stdout, stderr = client.exec_command(
            "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio < /home/u577647812/create_{}.sql".format(
                table
            )
        )
        error = stderr.read().decode("utf-8", errors="replace")

        if error and "ERROR" in error:
            print("  Error: {}".format(error[:100]))
        else:
            print("  OK - Estructura creada")

    sftp.close()

    # Verificar tablas
    stdin, stdout, stderr = client.exec_command(
        "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e 'SHOW TABLES;'"
    )
    print("\nTablas creadas:")
    print(stdout.read().decode("utf-8"))

    # Contar registros
    print("\nConteo:")
    for table in ["usuarios", "campanas", "elecciones"]:
        stdin, stdout, stderr = client.exec_command(
            "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e 'SELECT COUNT(*) FROM {};'".format(
                table
            )
        )
        count = stdout.read().decode("utf-8").strip().split("\n")[-1]
        print("  {}: {}".format(table, count))

    client.close()
    print("\nMIGRACION PARCIAL COMPLETADA!")
    print("Falta importar datos de las tablas completas.")


if __name__ == "__main__":
    main()
