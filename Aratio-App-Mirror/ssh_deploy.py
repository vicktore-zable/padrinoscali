import paramiko
import os
import sys

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
REMOTE_PATH = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"
LOCAL_PATH = r"H:\Mi unidad\2025\5d\app\Multi-Campaign Management System"

ITEMS = [
    "root_config.php",
    "index.php",
    "login.php",
    "logout.php",
    ".htaccess",
    "api",
    "includes",
    "pages",
    "assets",
    "mod_colab",
    "mod_jac",
    "public",
    "public_html",
]

EXCLUDE = [
    ".git",
    ".gitignore",
    ".md",
    "README",
    "CHANGELOG",
    "tmp_",
    "deploy_",
    "migrate_",
    "cleanup_",
    "diagnostico",
    "*.sql",
    "*.log",
    "node_modules",
]


def should_exclude(filename):
    for ex in EXCLUDE:
        if ex in filename.lower() or filename.lower().endswith(".md"):
            return True
    return False


def main():
    with open(
        r"H:\Mi unidad\2025\5d\app\Multi-Campaign Management System\tmp_ssh_pass.txt",
        "r",
    ) as f:
        password = f.read().strip()

    print("Conectando al servidor...")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=password, timeout=30)

    sftp = client.open_sftp()

    # Crear directorio aratio
    public_html = "/home/u577647812/domains/edisongiraldo.com/public_html"
    try:
        sftp.stat(public_html)
    except:
        print("Error: public_html no existe")
        sys.exit(1)

    # Crear aratio dentro de public_html
    try:
        sftp.stat(REMOTE_PATH)
    except:
        sftp.mkdir(REMOTE_PATH)
        print("Directorio creado: {}".format(REMOTE_PATH))

    uploaded = 0
    skipped = 0

    for item in ITEMS:
        local_item = os.path.join(LOCAL_PATH, item)

        if not os.path.exists(local_item):
            print("SKIP (no existe): {}".format(item))
            skipped += 1
            continue

        if should_exclude(item):
            print("SKIP (excluido): {}".format(item))
            skipped += 1
            continue

        if os.path.isdir(local_item):
            print("Subiendo carpeta: {}...".format(item))
            for root, dirs, files in os.walk(local_item):
                rel_path = os.path.relpath(root, LOCAL_PATH)
                remote_dir = os.path.join(REMOTE_PATH, rel_path).replace("\\", "/")

                try:
                    sftp.stat(remote_dir)
                except:
                    sftp.mkdir(remote_dir)

                for f in files:
                    if should_exclude(f):
                        continue
                    local_file = os.path.join(root, f)
                    remote_file = os.path.join(remote_dir, f).replace("\\", "/")
                    try:
                        sftp.put(local_file, remote_file)
                        uploaded += 1
                    except Exception as e:
                        print("Error {}: {}".format(f, e))
            print("  Carpeta {} completada".format(item))
        else:
            remote_file = os.path.join(REMOTE_PATH, item).replace("\\", "/")
            try:
                sftp.put(local_item, remote_file)
                print("Subido: {}".format(item))
                uploaded += 1
            except Exception as e:
                print("Error {}: {}".format(item, e))

    sftp.close()
    client.close()

    print("")
    print("=" * 50)
    print("ARCHIVOS SUBIDOS: {}".format(uploaded))
    print("OMITIDOS: {}".format(skipped))
    print("DESTINO: {}".format(REMOTE_PATH))
    print("=" * 50)

    # Actualizar root_config.php con nuevas credenciales DB
    print("\nActualizando configuracion de base de datos...")

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=password, timeout=30)

    sftp = client.open_sftp()
    remote_config = os.path.join(REMOTE_PATH, "root_config.php").replace("\\", "/")

    with sftp.file(remote_config, "r") as f:
        config_content = f.read().decode("utf-8")

    # Reemplazar credenciales de DB
    # Buscar la seccion de DB y reemplazarla
    new_content = config_content

    # Reemplazar las definiciones de DB
    if "define('DB_HOST', 'auth-db690.hstgr.io')" in new_content:
        new_content = new_content.replace(
            "define('DB_HOST', 'auth-db690.hstgr.io');",
            "define('DB_HOST', 'localhost');",
        )
    if "define('DB_NAME', 'u156469157_aratio_v1')" in new_content:
        new_content = new_content.replace(
            "define('DB_NAME', 'u156469157_aratio_v1');",
            "define('DB_NAME', 'u577647812_aratio');",
        )
    if "define('DB_USER', 'u156469157_aratio_v1')" in new_content:
        new_content = new_content.replace(
            "define('DB_USER', 'u156469157_aratio_v1');",
            "define('DB_USER', 'u577647812_aratio');",
        )
    if "define('DB_PASS', '15zxCeBbvgsR')" in new_content:
        new_content = new_content.replace(
            "define('DB_PASS', '15zxCeBbvgsR');", "define('DB_PASS', 'v6xSHUWhjrxE');"
        )

    with sftp.file(remote_config, "w") as f:
        f.write(new_content.encode("utf-8"))

    sftp.close()
    client.close()

    print("OK - Configuracion de DB actualizada!")
    print("")
    print("========================================")
    print("DESPLIEGUE COMPLETADO!")
    print("========================================")
    print("")
    print("URL: https://edisongiraldo.com/aratio/")
    print("")


if __name__ == "__main__":
    main()
