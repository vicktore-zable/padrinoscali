import paramiko
import sys

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

    # Crear archivo de log
    stdin, stdout, stderr = client.exec_command(
        "touch /home/u577647812/domains/edisongiraldo.com/public_html/aratio/php-errors.log && chmod 644 /home/u577647812/domains/edisongiraldo.com/public_html/aratio/php-errors.log"
    )

    # Verificar que index.php existe y tiene contenido
    stdin, stdout, stderr = client.exec_command(
        "head -20 /home/u577647812/domains/edisongiraldo.com/public_html/aratio/index.php"
    )
    print("index.php (inicio):")
    print(stdout.read().decode("utf-8", errors="replace"))

    # Verificar que root_config se incluye
    stdin, stdout, stderr = client.exec_command(
        "grep -n 'root_config' /home/u577647812/domains/edisongiraldo.com/public_html/aratio/index.php"
    )
    print("\nReferencia a root_config:")
    print(stdout.read().decode("utf-8"))

    # Verificar el .htaccess
    stdin, stdout, stderr = client.exec_command(
        "cat /home/u577647812/domains/edisongiraldo.com/public_html/aratio/.htaccess"
    )
    print("\n.htaccess:")
    print(stdout.read().decode("utf-8", errors="replace"))

    client.close()
    print("\nVerificacion completada")


if __name__ == "__main__":
    main()
