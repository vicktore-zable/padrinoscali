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

    # Probar acceso local
    print("=== Pruebas locales ===")

    stdin, stdout, stderr = client.exec_command(
        "curl -s http://127.0.0.1/aratio/ 2>&1 | head -10"
    )
    print("Sin host header:")
    print(stdout.read().decode("utf-8", errors="replace"))

    stdin, stdout, stderr = client.exec_command(
        "curl -s -H 'Host: edisongiraldo.com' http://127.0.0.1/aratio/ 2>&1 | head -10"
    )
    print("\nCon host header:")
    print(stdout.read().decode("utf-8", errors="replace"))

    # Verificar archivos
    print("\n=== Archivos en aratio ===")
    stdin, stdout, stderr = client.exec_command(
        "ls -la /home/u577647812/domains/edisongiraldo.com/public_html/aratio/*.php"
    )
    print(stdout.read().decode("utf-8"))

    # Ver contenido de default.php
    print("\n=== default.php ===")
    stdin, stdout, stderr = client.exec_command(
        "head -5 /home/u577647812/domains/edisongiraldo.com/public_html/default.php"
    )
    print(stdout.read().decode("utf-8", errors="replace"))

    client.close()


if __name__ == "__main__":
    main()
