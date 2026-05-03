import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"


def main():
    with open(
        r"H:\Mi unidad/2025\5d\app/Multi-Campaign Management System/tmp_ssh_pass.txt",
        "r",
    ) as f:
        password = f.read().strip()

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=password, timeout=30)

    public_html = "/home/u577647812/domains/edisongiraldo.com/public_html"

    # Ver permisos de la carpeta
    print("=== Permisos public_html ===")
    stdin, stdout, stderr = client.exec_command(
        "ls -la /home/u577647812/domains/edisongiraldo.com/"
    )
    print(stdout.read().decode("utf-8"))

    # Ver si hay problema con el owner
    stdin, stdout, stderr = client.exec_command(
        "stat -c '%a %U:%G' {}".format(public_html)
    )
    print("\nOwner:", stdout.read().decode("utf-8"))

    # Intentar cambiar permisos
    print("\n=== Cambiando permisos ===")
    stdin, stdout, stderr = client.exec_command("chmod 755 {}".format(public_html))
    print("Permisos cambiados")

    # Probar de nuevo
    stdin, stdout, stderr = client.exec_command("curl -s http://127.0.0.1/ | head -10")
    print("\nResultado:")
    print(stdout.read().decode("utf-8", errors="replace"))

    # Probar con PHP directamente
    stdin, stdout, stderr = client.exec_command("php -r \"echo 'PHP OK'\"")
    print("\nPHP:", stdout.read().decode("utf-8"))

    client.close()


if __name__ == "__main__":
    main()
