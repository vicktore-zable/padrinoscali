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

    # Prueba directa
    stdin, stdout, stderr = client.exec_command(
        'mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e "SELECT COUNT(*) as cnt FROM usuarios;"'
    )
    print("usuarios:", stdout.read().decode("utf-8"))

    stdin, stdout, stderr = client.exec_command(
        'mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e "SELECT COUNT(*) as cnt FROM elecciones;"'
    )
    print("elecciones:", stdout.read().decode("utf-8"))

    stdin, stdout, stderr = client.exec_command(
        'mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e "SELECT COUNT(*) as cnt FROM campanas;"'
    )
    print("campanas:", stdout.read().decode("utf-8"))

    stdin, stdout, stderr = client.exec_command(
        'mysql -u u577647812_aratio -pv6xSHUWhjrxE u577577647812_aratio -e "SELECT * FROM usuarios LIMIT 2;" 2>&1'
    )
    print("error:", stderr.read().decode("utf-8"))

    client.close()


if __name__ == "__main__":
    main()
