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

    # Buscar logs de error
    stdin, stdout, stderr = client.exec_command(
        "find /home/u577647812 -name 'error_log' 2>/dev/null | head -10"
    )
    print("Archivos error_log:")
    print(stdout.read().decode("utf-8"))

    # Ver logs de Apache/error
    stdin, stdout, stderr = client.exec_command("ls -la /home/u577647812/.logs/")
    print("\nLogs:")
    print(stdout.read().decode("utf-8"))

    # Ver error_log más reciente
    stdin, stdout, stderr = client.exec_command(
        "tail -50 /home/u577647812/.logs/error.log 2>/dev/null || echo 'No encontrado'"
    )
    print("\nError log:")
    print(stdout.read().decode("utf-8", errors="replace"))

    client.close()


if __name__ == "__main__":
    main()
