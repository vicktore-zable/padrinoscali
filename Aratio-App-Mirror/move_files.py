import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"


def main():
    with open(
        r"H:\Mi unidad/2025/5d\app\Multi-Campaign Management System\tmp_ssh_pass.txt",
        "r",
    ) as f:
        password = f.read().strip()

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=password, timeout=30)

    public_html = "/home/u577647812/domains/edisongiraldo.com/public_html"

    print("Moviendo archivos de aratio/ a public_html/...")

    # Mover todos los archivos y carpetas de aratio a public_html
    items = [
        "index.php",
        "login.php",
        "logout.php",
        ".htaccess",
        "root_config.php",
        "api",
        "includes",
        "pages",
        "mod_colab",
        "mod_jac",
        "mod_elecciones",
        "public",
        "public_html",
        "php-errors.log",
    ]

    for item in items:
        # Mover (renombrar)
        stdin, stdout, stderr = client.exec_command(
            "mv {}/aratio/{} {}/{} 2>/dev/null || echo 'No movido: {}'".format(
                public_html, item, public_html, item, item
            )
        )
        result = stdout.read().decode("utf-8").strip()
        if result:
            print(result)

    # Verificar
    stdin, stdout, stderr = client.exec_command("ls -la {}".format(public_html))
    print("\nArchivos en public_html:")
    print(stdout.read().decode("utf-8"))

    client.close()
    print("\nListo! Ahora prueba: http://82.197.82.47/")


if __name__ == "__main__":
    main()
