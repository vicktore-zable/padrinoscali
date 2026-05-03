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

    public_html = "/home/u577647812/domains/edisongiraldo.com/public_html"

    # Renombrar default.php para que nointerfiera
    print("Renombrando default.php...")
    stdin, stdout, stderr = client.exec_command(
        "mv " + public_html + "/default.php " + public_html + "/default.php.bak"
    )

    # Eliminar carpeta aratio vacía
    stdin, stdout, stderr = client.exec_command(
        "rmdir "
        + public_html
        + "/aratio 2>/dev/null || echo 'aratio no vacía o no existe'"
    )

    # Eliminar cache
    stdin, stdout, stderr = client.exec_command(
        "rm -rf " + public_html + "/cache/* 2>/dev/null"
    )

    # Verificar archivos finales
    stdin, stdout, stderr = client.exec_command("ls -la " + public_html + "/")
    print("\nArchivos finales:")
    print(stdout.read().decode("utf-8"))

    # Probar
    print("\nProbando...")
    stdin, stdout, stderr = client.exec_command("curl -s http://127.0.0.1/ | head -5")
    print(stdout.read().decode("utf-8", errors="replace"))

    client.close()


if __name__ == "__main__":
    main()
