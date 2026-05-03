import paramiko
import os

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
LOCAL_PATH = r"H:\Mi unidad\2025\5d\app\Multi-Campaign Management System"
REMOTE_PATH = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"


def main():
    with open(
        r"H:\Mi unidad/2025/5d/app/Multi-Campaign Management System\tmp_ssh_pass.txt",
        "r",
    ) as f:
        password = f.read().strip()

    print("Conectando...")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=password, timeout=30)

    sftp = client.open_sftp()

    # Crear carpeta mod_elecciones
    print("Creando carpeta mod_elecciones...")
    try:
        sftp.stat(REMOTE_PATH + "/mod_elecciones")
    except:
        sftp.mkdir(REMOTE_PATH + "/mod_elecciones")
        sftp.mkdir(REMOTE_PATH + "/mod_elecciones/public")
        sftp.mkdir(REMOTE_PATH + "/mod_elecciones/src")
        sftp.mkdir(REMOTE_PATH + "/mod_elecciones/src/Views")

    # Subir archivos de mod_elecciones
    files = [
        ("mod_elecciones/public/index.php", "mod_elecciones/public/index.php"),
        ("mod_elecciones/get_filters.php", "mod_elecciones/get_filters.php"),
        (
            "mod_elecciones/src/Views/dashboard.php",
            "mod_elecciones/src/Views/dashboard.php",
        ),
    ]

    for local_file, remote_file in files:
        full_local = os.path.join(LOCAL_PATH, local_file)
        full_remote = REMOTE_PATH + "/" + remote_file
        if os.path.exists(full_local):
            sftp.put(full_local, full_remote)
            print("  Subido: {}".format(local_file))

    sftp.close()
    print("\nOK - mod_elecciones subido!")

    # Ahora crear config.php para mod_elecciones
    print("\nCreando config para mod_elecciones...")

    config_content = """<?php
/**
 * Configuracion especial para mod_elecciones
 * Conecta a la base de datos original de Hostinger
 */
define('DB_HOST', 'auth-db690.hstgr.io');
define('DB_NAME', 'u156469157_aratio_v1');
define('DB_USER', 'u156469157_aratio_v1');
define('DB_PASS', '15zxCeBbvgsR');
define('DB_CHARSET', 'utf8mb4');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    }
    return $pdo;
}
"""

    sftp = client.open_sftp()
    with sftp.file(REMOTE_PATH + "/mod_elecciones/config.php", "w") as f:
        f.write(config_content.encode("utf-8"))
    sftp.close()
    print("OK - config.php creado")

    # Modificar archivos para usar config propia
    print("\nModificando archivos de mod_elecciones...")

    stdin, stdout, stderr = client.exec_command(
        "sed -i \"s|require_once __DIR__ . '/../../../config/config.php';|require_once __DIR__ . '/../config.php';|\" "
        + REMOTE_PATH
        + "/mod_elecciones/src/Views/dashboard.php"
    )

    stdin, stdout, stderr = client.exec_command(
        "sed -i \"s|require_once __DIR__ . '/../../config/config.php';|require_once __DIR__ . '/../config.php';|\" "
        + REMOTE_PATH
        + "/mod_elecciones/get_filters.php"
    )

    print("OK - Archivos modificados!")
    client.close()


if __name__ == "__main__":
    main()
