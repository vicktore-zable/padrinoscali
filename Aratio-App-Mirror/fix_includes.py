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

    # Modificar index.php para usar root_config.php
    print("Modificando index.php para usar root_config.php...")

    stdin, stdout, stderr = client.exec_command(
        "sed -i \"s|require_once __DIR__ . '/config/config.php';|require_once __DIR__ . '/root_config.php';|\" /home/u577647812/domains/edisongiraldo.com/public_html/aratio/index.php"
    )

    # Verificar el cambio
    stdin, stdout, stderr = client.exec_command(
        "head -5 /home/u577647812/domains/edisongiraldo.com/public_html/aratio/index.php"
    )
    print("index.php (inicio):")
    print(stdout.read().decode("utf-8", errors="replace"))

    # Lo mismo para login.php
    stdin, stdout, stderr = client.exec_command(
        "sed -i \"s|require_once __DIR__ . '/config/config.php';|require_once __DIR__ . '/root_config.php';|\" /home/u577647812/domains/edisongiraldo.com/public_html/aratio/login.php"
    )

    # Y logout.php
    stdin, stdout, stderr = client.exec_command(
        "sed -i \"s|require_once __DIR__ . '/config/config.php';|require_once __DIR__ . '/root_config.php';|\" /home/u577647812/domains/edisongiraldo.com/public_html/aratio/logout.php"
    )

    print("\nOK - Archivos actualizados!")
    client.close()


if __name__ == "__main__":
    main()
