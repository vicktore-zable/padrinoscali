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

    sftp = client.open_sftp()
    sftp.put(
        r"H:\Mi unidad\2025\5d\app\Multi-Campaign Management System\test.php",
        "/home/u577647812/domains/edisongiraldo.com/public_html/test.php",
    )
    sftp.close()
    print("test.php subido")

    # Probar
    stdin, stdout, stderr = client.exec_command(
        "curl -s http://157.173.208.254/test.php | head -5"
    )
    print("\nResultado:")
    print(stdout.read().decode("utf-8", errors="replace"))

    client.close()


if __name__ == "__main__":
    main()
