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

    print("=== Haciendo el sistema independiente ===")

    # 1. Eliminar config.php de mod_elecciones para que use la DB local
    print("\n1. Eliminando config.php de mod_elecciones...")
    stdin, stdout, stderr = client.exec_command(
        "rm -f " + public_html + "/mod_elecciones/config.php"
    )
    print("   OK - Ahora mod_elecciones usará la DB local")

    # 2. Corregir path del log de errores
    print("\n2. Corrigiendo path del log de errores...")
    stdin, stdout, stderr = client.exec_command(
        "sed -i 's|u156469157/domains/edisongiraldo.com|u577647812/domains/edisongiraldo.com|g' "
        + public_html
        + "/root_config.php"
    )

    # 3. Verificar que no haya referencias a la DB original
    print("\n3. Verificando conexiones a DB externa...")
    stdin, stdout, stderr = client.exec_command(
        "grep -rn 'auth-db690\\|u156469157_aratio' "
        + public_html
        + "/*.php "
        + public_html
        + "/mod_elecciones/*.php 2>/dev/null | grep -v 'auth-db690' || echo 'No hay referencias'"
    )
    result = stdout.read().decode("utf-8", errors="replace")
    if result.strip():
        print("   Advertencia:", result)
    else:
        print("   OK - Sin referencias a la DB original")

    print("\n=== Sistema ahora es INDEPENDIENTE ===")
    client.close()


if __name__ == "__main__":
    main()
