import paramiko

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

    # Ver todas las tablas y sus cuentas
    stdin, stdout, stderr = client.exec_command(
        "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e 'SHOW TABLES;'"
    )
    tables = stdout.read().decode("utf-8").strip().split("\n")

    print("Todas las tablas ({}):".format(len(tables) - 1))
    for t in tables[1:]:  # Skip header
        stdin, stdout, stderr = client.exec_command(
            "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e 'SELECT COUNT(*) FROM {};' 2>/dev/null".format(
                t
            )
        )
        count = (
            stdout.read().decode("utf-8").strip().split("\n")[-1]
            if stdout.read()
            else "?"
        )
        print("  {}: {}".format(t, count))

    client.close()


if __name__ == "__main__":
    main()
