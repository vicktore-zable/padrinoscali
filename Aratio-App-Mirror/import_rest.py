import paramiko
import sys

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"


def main():
    with open(
        r"H:\Mi unidad/2025/5d/app/Multi-Campaign Management System\tmp_ssh_pass.txt",
        "r",
    ) as f:
        password = f.read().strip()

    print("Subiendo archivo SQL...")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=password, timeout=30)

    sftp = client.open_sftp()
    sftp.put("aratio_migration_rest.sql", "/home/u577647812/aratio_migration_rest.sql")
    sftp.close()
    print("OK - Archivo subido")

    print("\nImportando datos...")
    stdin, stdout, stderr = client.exec_command(
        "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio < /home/u577647812/aratio_migration_rest.sql 2>&1"
    )

    result = stdout.read().decode("utf-8", errors="replace")
    error = stderr.read().decode("utf-8", errors="replace")

    if error and "ERROR" in error:
        print("Errores: {}".format(error[:500]))
    else:
        print("OK - Importacion completada!")

    # Verificar tablas
    print("\nVerificando tablas...")
    stdin, stdout, stderr = client.exec_command(
        "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e 'SHOW TABLES;'"
    )
    tables = stdout.read().decode("utf-8")
    print(tables)

    # Contar registros en tablas importantes
    print("\nConteo de registros:")
    for table in ["usuarios", "colaboradores", "campanas", "elecciones", "territorios"]:
        stdin, stdout, stderr = client.exec_command(
            "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e 'SELECT COUNT(*) FROM {};' 2>/dev/null".format(
                table
            )
        )
        count = stdout.read().decode("utf-8").strip().split("\n")[-1]
        print("  {}: {}".format(table, count))

    client.close()
    print("\nMIGRACION COMPLETADA!")
    print("Prueba en: http://82.197.82.47/aratio/")


if __name__ == "__main__":
    main()
