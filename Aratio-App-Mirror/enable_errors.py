import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"


def main():
    with open(
        r"H:\Mi unidad/2025/5d/app/Multi-Campaign Management System\tmp_ssh_pass.txt",
        "r",
    ) as f:
        password = f.read().strip()

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=password, timeout=30)

    # Habilitar errores en root_config.php temporalmente
    print("Habilitando errores PHP...")

    stdin, stdout, stderr = client.exec_command(
        "sed -i \"s|ini_set('display_errors', 0);|ini_set('display_errors', 1);|\" /home/u577647812/domains/edisongiraldo.com/public_html/aratio/root_config.php"
    )

    # Verificar el cambio
    stdin, stdout, stderr = client.exec_command(
        "grep 'display_errors' /home/u577647812/domains/edisongiraldo.com/public_html/aratio/root_config.php"
    )
    print("display_errors:", stdout.read().decode("utf-8"))

    # Ahora probar de nuevo
    print("\nProbando acceso...")
    stdin, stdout, stderr = client.exec_command(
        "curl -s -H 'Host: edisongiraldo.com' http://127.0.0.1/aratio/ 2>&1 | head -30"
    )
    print("Resultado:")
    print(stdout.read().decode("utf-8", errors="replace"))

    client.close()


if __name__ == "__main__":
    main()
