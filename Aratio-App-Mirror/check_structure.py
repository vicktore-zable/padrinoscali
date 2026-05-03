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

    # Ver estructura
    stdin, stdout, stderr = client.exec_command(
        "ls -la /home/u577647812/domains/edisongiraldo.com/public_html/aratio/"
    )
    print("Archivos en aratio/:")
    print(stdout.read().decode("utf-8"))

    # Ver si mod_elecciones esta en el origen
    stdin, stdout, stderr = client.exec_command(
        "ls -la /home/u156469157/domains/aratio.mrmtech.net/public_html/mod_elecciones/ 2>/dev/null | head -10"
    )
    print("\nmod_elecciones en servidor original:")
    print(stdout.read().decode("utf-8"))

    client.close()


if __name__ == "__main__":
    main()
