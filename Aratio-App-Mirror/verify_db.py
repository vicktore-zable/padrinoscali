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

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=password, timeout=30)

    # Corregir APP_URL para incluir /aratio
    stdin, stdout, stderr = client.exec_command(
        "sed -i \"s|https://edisongiraldo.com'|https://edisongiraldo.com/aratio'|g\" /home/u577647812/domains/edisongiraldo.com/public_html/aratio/root_config.php"
    )

    # Probar conexion a MySQL
    print("Probando conexion a MySQL...")
    stdin, stdout, stderr = client.exec_command(
        "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e 'SELECT VERSION();'"
    )
    result = stdout.read().decode("utf-8")
    error = stderr.read().decode("utf-8")
    print("Resultado: {}".format(result))
    if error:
        print("Error: {}".format(error))

    # Contar tablas
    stdin, stdout, stderr = client.exec_command(
        "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e 'SHOW TABLES;'"
    )
    tables = stdout.read().decode("utf-8")
    print("\nTablas actuales:")
    print(tables)

    # Verificar usuarios
    stdin, stdout, stderr = client.exec_command(
        "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e 'SELECT id, nombre, email, rol FROM usuarios LIMIT 5;'"
    )
    users = stdout.read().decode("utf-8")
    print("\nUsuarios:")
    print(users)

    client.close()
    print("\nListo!")


if __name__ == "__main__":
    main()
