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

    # Verificar si hay configuraciones de dominio
    print("=== Verificando configuracion ===")

    # Verificar si hay archivos de configuracion de apache
    stdin, stdout, stderr = client.exec_command(
        "ls /etc/apache2/sites-enabled/ 2>/dev/null || ls /etc/httpd/conf.d/ 2>/dev/null || echo 'No encontrado'"
    )
    print("Config Apache:", stdout.read().decode("utf-8"))

    # Verificar document root
    stdin, stdout, stderr = client.exec_command(
        "grep -r 'DocumentRoot' /etc/apache2/ 2>/dev/null | head -5"
    )
    print("\nDocumentRoot:", stdout.read().decode("utf-8", errors="replace"))

    # Intentar ver las cabeceras de respuesta
    stdin, stdout, stderr = client.exec_command(
        "curl -v http://127.0.0.1/ 2>&1 | head -20"
    )
    print("\nRespuesta detallada:")
    print(stdout.read().decode("utf-8", errors="replace"))

    client.close()


if __name__ == "__main__":
    main()
