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

    # Ver contenido de campanas
    stdin, stdout, stderr = client.exec_command(
        'mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -N -e "SELECT id, codigo, nombre FROM campanas;" 2>/dev/null'
    )
    print("campanas:")
    print(stdout.read().decode("utf-8"))

    # Ver contenido de candidatos
    stdin, stdout, stderr = client.exec_command(
        'mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -N -e "SELECT id, nombres FROM candidatos LIMIT 5;" 2>/dev/null'
    )
    print("candidatos:")
    print(stdout.read().decode("utf-8"))

    # Ver usuarios
    stdin, stdout, stderr = client.exec_command(
        'mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -N -e "SELECT id, nombre, email FROM usuarios LIMIT 3;" 2>/dev/null'
    )
    print("usuarios:")
    print(stdout.read().decode("utf-8"))

    client.close()


if __name__ == "__main__":
    main()
