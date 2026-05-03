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

    # Obtener IP del servidor
    stdin, stdout, stderr = client.exec_command("curl -s ifconfig.me")
    ip = stdout.read().decode("utf-8").strip()
    print("IP del servidor: {}".format(ip))

    # Verificar configuracion de dominios
    stdin, stdout, stderr = client.exec_command("cat /etc/hosts | grep edisongiraldo")
    print("\n/etc/hosts:")
    print(stdout.read().decode("utf-8"))

    # Verificar si hay archivos en public_html raiz
    stdin, stdout, stderr = client.exec_command(
        "ls -la /home/u577647812/domains/edisongiraldo.com/public_html/"
    )
    print("\npublic_html raiz:")
    print(stdout.read().decode("utf-8"))

    client.close()


if __name__ == "__main__":
    main()
