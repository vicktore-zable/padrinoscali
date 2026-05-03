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

    # Corregir las rutas de includes - cambiar ../ por ./
    print("Corrigiendo rutas de includes...")

    stdin, stdout, stderr = client.exec_command(
        "sed -i 's|__DIR__ . '/../includes/|__DIR__ . '/includes/|g' /home/u577647812/domains/edisongiraldo.com/public_html/aratio/root_config.php"
    )

    # También corregir paths de config
    stdin, stdout, stderr = client.exec_command(
        "sed -i 's|__DIR__ . '/../config/|__DIR__ . '/config/|g' /home/u577647812/domains/edisongiraldo.com/public_html/aratio/root_config.php"
    )

    stdin, stdout, stderr = client.exec_command(
        "sed -i 's|__DIR__ . '/../uploads/|__DIR__ . '/uploads/|g' /home/u577647812/domains/edisongiraldo.com/public_html/aratio/root_config.php"
    )

    stdin, stdout, stderr = client.exec_command(
        "sed -i 's|__DIR__ . '/../cache/|__DIR__ . '/cache/|g' /home/u577647812/domains/edisongiraldo.com/public_html/aratio/root_config.php"
    )

    # Verificar los cambios
    stdin, stdout, stderr = client.exec_command(
        "grep -n '__DIR__.*includes' /home/u577647812/domains/edisongiraldo.com/public_html/aratio/root_config.php | head -5"
    )
    print("Rutas de includes corregidas:")
    print(stdout.read().decode("utf-8"))

    # Probar de nuevo
    stdin, stdout, stderr = client.exec_command(
        "curl -s -H 'Host: edisongiraldo.com' http://127.0.0.1/aratio/ 2>&1 | head -30"
    )
    print("\nResultado:")
    print(stdout.read().decode("utf-8", errors="replace"))

    client.close()


if __name__ == "__main__":
    main()
