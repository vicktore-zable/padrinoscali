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

    # Ver errores PHP
    print("=== ERRORES PHP ===")
    stdin, stdout, stderr = client.exec_command(
        "tail -50 /home/u577647812/domains/edisongiraldo.com/public_html/aratio/php-errors.log"
    )
    print(stdout.read().decode("utf-8", errors="replace"))

    # Probar acceso local
    print("\n=== PRUEBA LOCAL ===")
    stdin, stdout, stderr = client.exec_command(
        "curl -s -H 'Host: edisongiraldo.com' http://127.0.0.1/aratio/ 2>&1 | head -20"
    )
    print(stdout.read().decode("utf-8", errors="replace"))

    # Ver archivos en public_html
    print("\n=== ARCHIVOS ===")
    stdin, stdout, stderr = client.exec_command(
        "ls -la /home/u577647812/domains/edisongiraldo.com/public_html/aratio/"
    )
    print(stdout.read().decode("utf-8"))

    client.close()


if __name__ == "__main__":
    main()
