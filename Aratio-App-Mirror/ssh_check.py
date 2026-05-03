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

    # Ver archivos en aratio
    stdin, stdout, stderr = client.exec_command(
        "ls -la /home/u577647812/domains/edisongiraldo.com/public_html/aratio/"
    )
    print("Archivos en /aratio/:")
    print(stdout.read().decode("utf-8"))

    # Ver root_config.php
    stdin, stdout, stderr = client.exec_command(
        "head -40 /home/u577647812/domains/edisongiraldo.com/public_html/aratio/root_config.php"
    )
    print("\nroot_config.php (primeras 40 lineas):")
    print(stdout.read().decode("utf-8"))

    # Verificar el error log
    stdin, stdout, stderr = client.exec_command(
        "tail -50 /home/u577647812/domains/edisongiraldo.com/public_html/aratio/php-errors.log 2>/dev/null || echo 'No hay log'"
    )
    print("\nErrores PHP:")
    print(stdout.read().decode("utf-8"))

    client.close()


if __name__ == "__main__":
    main()
