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

    # Eliminar htaccess.bak y crear nuevo minimal
    print("Eliminando htaccess.bak...")
    stdin, stdout, stderr = client.exec_command(
        "rm -f " + public_html + "/.htaccess.bak"
    )

    # Crear htaccess minimal
    htaccess = """Options -Indexes
Options +FollowSymLinks
DirectoryIndex index.php index.html
"""

    sftp = client.open_sftp()
    with sftp.file(public_html + "/.htaccess", "w") as f:
        f.write(htaccess.encode("utf-8"))
    sftp.close()
    print("htaccess creado")

    # Cambiar permisos
    stdin, stdout, stderr = client.exec_command("chmod 755 " + public_html)
    stdin, stdout, stderr = client.exec_command("chmod 644 " + public_html + "/*.php")

    # Probar
    print("\nProbando...")
    stdin, stdout, stderr = client.exec_command("curl -s http://127.0.0.1/ | head -10")
    print(stdout.read().decode("utf-8", errors="replace"))

    client.close()
    print("\nListo!")


if __name__ == "__main__":
    main()
