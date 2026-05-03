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

    # Verificar si mod_elecciones está configurado para conectar a DB externa
    print("=== Verificando mod_elecciones ===")

    stdin, stdout, stderr = client.exec_command(
        "ls -la " + public_html + "/mod_elecciones/"
    )
    print(stdout.read().decode("utf-8"))

    # Verificar config de mod_elecciones
    stdin, stdout, stderr = client.exec_command(
        "cat "
        + public_html
        + "/mod_elecciones/config.php 2>/dev/null || echo 'No existe config.php'"
    )
    print("\nconfig.php:", stdout.read().decode("utf-8", errors="replace"))

    # Verificar que root_config.php no tenga referencias a la DB original
    print("\n=== Verificando root_config.php ===")
    stdin, stdout, stderr = client.exec_command(
        "grep -n 'auth-db690\\|156469157' " + public_html + "/root_config.php"
    )
    print(stdout.read().decode("utf-8", errors="replace"))

    client.close()


if __name__ == "__main__":
    main()
