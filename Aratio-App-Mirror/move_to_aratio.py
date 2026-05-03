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
    aratio = public_html + "/aratio"

    print("Moviendo archivos de public_html/ a public_html/aratio/...")

    # Mover archivos PHP
    items = [
        "index.php",
        "login.php",
        "logout.php",
        "root_config.php",
        ".htaccess",
        "test.php",
        "php-errors.log",
    ]
    for item in items:
        stdin, stdout, stderr = client.exec_command(
            "mv {}/{} {}/{} 2>/dev/null || echo 'No movido: {}'".format(
                public_html, item, aratio, item, item
            )
        )
        result = stdout.read().decode("utf-8").strip()
        if result and "No movido" not in result:
            print(result)

    # Mover carpetas
    dirs = [
        "api",
        "includes",
        "pages",
        "mod_colab",
        "mod_jac",
        "mod_elecciones",
        "public",
        "public_html",
    ]
    for d in dirs:
        stdin, stdout, stderr = client.exec_command(
            "mv {}/{} {}/{} 2>/dev/null || echo 'No movido: {}'".format(
                public_html, d, aratio, d, d
            )
        )
        result = stdout.read().decode("utf-8").strip()
        if result and "No movido" not in result:
            print(result)

    # Verificar
    stdin, stdout, stderr = client.exec_command("ls -la {}/".format(aratio))
    print("\nArchivos en aratio/:")
    print(stdout.read().decode("utf-8"))

    # Probar
    print("\nProbando...")
    stdin, stdout, stderr = client.exec_command(
        "curl -s -H 'Host: edisongiraldo.com' http://127.0.0.1/aratio/ | head -10"
    )
    print(stdout.read().decode("utf-8", errors="replace"))

    client.close()
    print("\nListo! Archivos movidos a /aratio/")


if __name__ == "__main__":
    main()
