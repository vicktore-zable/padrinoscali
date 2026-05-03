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

    # Crear config para mod_elecciones - usa la DB original
    print("Creando config para mod_elecciones...")

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

    # Escribir archivo
    sftp = client.open_sftp()
    remote_file = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/mod_elecciones/config.php"

    with sftp.file(remote_file, "w") as f:
        f.write(config_content.encode("utf-8"))

    sftp.close()
    print("OK - config.php creado en mod_elecciones/")

    # Ahora modificar los archivos de mod_elecciones para usar esta config
    print("\nModificando mod_elecciones para usar config propia...")

    # Modificar dashboard.php
    stdin, stdout, stderr = client.exec_command(
        "sed -i \"s|require_once __DIR__ . '/../../../config/config.php';|require_once __DIR__ . '/../config.php';|\" /home/u577647812/domains/edisongiraldo.com/public_html/aratio/mod_elecciones/src/Views/dashboard.php"
    )

    # Modificar get_filters.php
    stdin, stdout, stderr = client.exec_command(
        "sed -i \"s|require_once __DIR__ . '/../../config/config.php';|require_once __DIR__ . '/../config.php';|\" /home/u577647812/domains/edisongiraldo.com/public_html/aratio/mod_elecciones/get_filters.php"
    )

    print("OK - Archivos modificados")

    client.close()


if __name__ == "__main__":
    main()
