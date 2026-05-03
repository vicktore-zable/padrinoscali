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

    # Renombrar .htaccess a .htaccess.bak
    stdin, stdout, stderr = client.exec_command(
        "mv {}/.htaccess {}/.htaccess.bak".format(public_html, public_html)
    )
    print(" htaccess renombrado")

    # Probar sin .htaccess
    stdin, stdout, stderr = client.exec_command("curl -s http://127.0.0.1/ | head -10")
    print("\nSin .htaccess:")
    print(stdout.read().decode("utf-8", errors="restore"))

    # Si funciona, crear .htaccess nuevo minimal
    stdin, stdout, stderr = client.exec_command("curl -s http://127.0.0.1/ | head -5")
    if "Forbidden" not in stdout.read().decode("utf-8"):
        print("\nFunciona sin .htaccess! Creando nuevo...")

        minimal_htaccess = """Options -Indexes
Options +FollowSymLinks
DirectoryIndex index.php
"""
        sftp = client.open_sftp()
        with sftp.file(public_html + "/.htaccess", "w") as f:
            f.write(minimal_htaccess.encode("utf-8"))
        sftp.close()
        print("Nuevo .htaccess creado")

    client.close()


if __name__ == "__main__":
    main()
