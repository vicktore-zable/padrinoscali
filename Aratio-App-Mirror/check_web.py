import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"


def main():
    with open(
        r"H:\Mi unidad/2025/5d/app/Multi-Campaign Management System/tmp_ssh_pass.txt",
        "r",
    ) as f:
        password = f.read().strip()

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=password, timeout=30)

    # Ver todas las IPs
    stdin, stdout, stderr = client.exec_command("ip addr show | grep inet")
    print("IPs del servidor:")
    print(stdout.read().decode("utf-8"))

    # Ver configuración de Apache/httpd
    stdin, stdout, stderr = client.exec_command(
        "ls -la /etc/apache2/sites-enabled/ 2>/dev/null || ls -la /etc/httpd/conf.d/ 2>/dev/null || echo 'No encontrado'"
    )
    print("\nConfiguración Apache:")
    print(stdout.read().decode("utf-8"))

    # Probar directamente al IP
    stdin, stdout, stderr = client.exec_command(
        "curl -I -s http://127.0.0.1/ | head -3"
    )
    print("\nPrueba 127.0.0.1:")
    print(stdout.read().decode("utf-8"))

    # Probar con el nombre del dominio
    stdin, stdout, stderr = client.exec_command(
        "curl -H 'Host: edisongiraldo.com' -I -s http://127.0.0.1/aratio/ | head -5"
    )
    print("\nPrueba con Host header:")
    print(stdout.read().decode("utf-8"))

    client.close()


if __name__ == "__main__":
    main()
