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

    # Verificar estructura
    stdin, stdout, stderr = client.exec_command(
        "ls -la /home/u577647812/domains/edisongiraldo.com/public_html/aratio/"
    )
    print("Estructura actual:")
    print(stdout.read().decode("utf-8"))

    # Verificar si existe config/
    stdin, stdout, stderr = client.exec_command(
        "ls -la /home/u577647812/domains/edisongiraldo.com/public_html/aratio/config/ 2>/dev/null || echo 'No existe config/'"
    )
    print("\nCarpeta config/:")
    print(stdout.read().decode("utf-8"))

    # Verificar si existe includes/
    stdin, stdout, stderr = client.exec_command(
        "ls -la /home/u577647812/domains/edisongiraldo.com/public_html/aratio/includes/"
    )
    print("\nCarpeta includes/:")
    print(stdout.read().decode("utf-8"))

    client.close()


if __name__ == "__main__":
    main()
