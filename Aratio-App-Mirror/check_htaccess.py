import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"


def main():
    with open(
        r"H:\Mi unidad/2025\5d\app\Multi-Campaign Management System\tmp_ssh_pass.txt",
        "r",
    ) as f:
        password = f.read().strip()

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=password, timeout=30)

    public_html = "/home/u577647812/domains/edisongiraldo.com/public_html"

    # Ver .htaccess
    print("=== .htaccess ===")
    stdin, stdout, stderr = client.exec_command("cat {}/.htaccess".format(public_html))
    print(stdout.read().decode("utf-8", errors="replace"))

    # Ver permisos
    print("\n=== Permisos ===")
    stdin, stdout, stderr = client.exec_command("ls -la {}/*.php".format(public_html))
    print(stdout.read().decode("utf-8"))

    # Ver si hay error en index.php
    print("\n=== Test index.php ===")
    stdin, stdout, stderr = client.exec_command(
        "php {}/index.php 2>&1 | head -10".format(public_html)
    )
    print(stdout.read().decode("utf-8", errors="replace"))

    client.close()


if __name__ == "__main__":
    main()
