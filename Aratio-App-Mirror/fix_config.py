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

    # Corregir root_config.php - cambiar path del log
    print("Corrigiendo root_config.php...")

    stdin, stdout, stderr = client.exec_command(
        "sed -i 's|aratio.mrmtech.net|edisongiraldo.com|g' /home/u577647812/domains/edisongiraldo.com/public_html/aratio/root_config.php"
    )

    stdin, stdout, stderr = client.exec_command(
        "grep -n 'error_log' /home/u577647812/domains/edisongiraldo.com/public_html/aratio/root_config.php"
    )
    print("Configuracion de log:")
    print(stdout.read().decode("utf-8"))

    # Tambien actualizar el APP_URL
    stdin, stdout, stderr = client.exec_command(
        "sed -i 's|aratio.mrmtech.net|edisongiraldo.com/aratio|g' /home/u577647812/domains/edisongiraldo.com/public_html/aratio/root_config.php"
    )

    stdin, stdout, stderr = client.exec_command(
        "grep -n 'APP_URL' /home/u577647812/domains/edisongiraldo.com/public_html/aratio/root_config.php"
    )
    print("\nAPP_URL:")
    print(stdout.read().decode("utf-8"))

    print("\nOK - Configuracion actualizada!")
    client.close()


if __name__ == "__main__":
    main()
