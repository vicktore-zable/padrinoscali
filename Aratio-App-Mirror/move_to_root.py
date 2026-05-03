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

    print("Moviendo archivos de aratio/ a public_html/ (raiz)...")

    # Mover archivos uno por uno
    files = [
        "index.php",
        "login.php",
        "logout.php",
        "root_config.php",
        "test.php",
        "php-errors.log",
    ]
    for f in files:
        stdin, stdout, stderr = client.exec_command(
            "mv " + public_html + "/aratio/" + f + " " + public_html + "/"
        )
        print("Movido: " + f)

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
            "mv " + public_html + "/aratio/" + d + " " + public_html + "/"
        )
        print("Movido: " + d)

    # Verificar
    stdin, stdout, stderr = client.exec_command("ls -la " + public_html + "/")
    print("\nArchivos en public_html/:")
    print(stdout.read().decode("utf-8"))

    client.close()
    print("\nListo! Prueba: http://157.173.208.254/")


if __name__ == "__main__":
    main()
