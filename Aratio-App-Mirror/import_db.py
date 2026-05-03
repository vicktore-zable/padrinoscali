import paramiko
import sys

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"


def main():
    with open(
        r"H:\Mi unidad\2025\5d\app\Multi-Campaign Management System\tmp_ssh_pass.txt",
        "r",
    ) as f:
        password = f.read().strip()

    print("Subiendo archivo SQL...")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=password, timeout=30)

    sftp = client.open_sftp()
    sftp.put("aratio_tables_basic.sql", "/home/u577647812/aratio_tables_basic.sql")
    sftp.close()
    print("OK - Archivo subido")

    print("Importando datos...")
    stdin, stdout, stderr = client.exec_command(
        "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio < /home/u577647812/aratio_tables_basic.sql"
    )

    result = stdout.read().decode("utf-8", errors="replace")
    error = stderr.read().decode("utf-8", errors="replace")

    if error:
        print("Mensajes: {}".format(error))

    print("Verificando tablas...")
    stdin, stdout, stderr = client.exec_command(
        "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e 'SHOW TABLES;'"
    )
    tables = stdout.read().decode("utf-8")
    print(tables)

    # Contar registros en tablas principales
    print("Conteo de registros:")
    for table in ["usuarios", "colaboradores", "territorios"]:
        stdin, stdout, stderr = client.exec_command(
            "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e 'SELECT COUNT(*) FROM {};'".format(
                table
            )
        )
        count = stdout.read().decode("utf-8").strip().split("\n")[-1]
        print("  {}: {}".format(table, count))

    client.close()
    print("")
    print("MIGRACION COMPLETADA!")
    print("Prueba en: https://edisongiraldo.com/aratio/")


if __name__ == "__main__":
    main()
