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

    # Leer el archivo, corregir las rutas, y escribir de vuelta
    sftp = client.open_sftp()
    remote_file = (
        "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/root_config.php"
    )

    with sftp.file(remote_file, "r") as f:
        content = f.read().decode("utf-8")

    # Reemplazar las rutas
    content = content.replace("__DIR__ . '/../includes/", "__DIR__ . '/includes/")
    content = content.replace("__DIR__ . '/../config/", "__DIR__ . '/config/")
    content = content.replace("__DIR__ . '/../uploads/", "__DIR__ . '/uploads/")
    content = content.replace("__DIR__ . '/../cache/", "__DIR__ . '/cache/")
    content = content.replace("dirname(__DIR__)", "__DIR__")

    # Escribir el archivo corregido
    with sftp.file(remote_file, "w") as f:
        f.write(content.encode("utf-8"))

    sftp.close()
    print("Archivo corregido!")

    # Probar de nuevo
    stdin, stdout, stderr = client.exec_command(
        "curl -s -H 'Host: edisongiraldo.com' http://127.0.0.1/aratio/ 2>&1 | head -30"
    )
    print("\nResultado:")
    print(stdout.read().decode("utf-8", errors="replace"))

    client.close()


if __name__ == "__main__":
    main()
