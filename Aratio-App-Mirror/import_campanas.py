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

    print("Subiendo campanas y candidatos...")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=password, timeout=30)

    sftp = client.open_sftp()
    sftp.put(
        r"H:\Mi unidad\2025\5d\app\Multi-Campaign Management System\aratio_campanas_candidatos.sql",
        "/home/u577647812/aratio_campanas_candidatos.sql",
    )
    sftp.close()

    stdin, stdout, stderr = client.exec_command(
        "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio < /home/u577647812/aratio_campanas_candidatos.sql 2>&1"
    )
    error = stderr.read().decode("utf-8", errors="replace")

    if error and "ERROR" in error:
        print("Error:", error[:200])
    else:
        print("OK!")

    # Verificar
    for table in ["campanas", "candidatos"]:
        stdin, stdout, stderr = client.exec_command(
            "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e 'SELECT COUNT(*) FROM {};'".format(
                table
            )
        )
        count = stdout.read().decode("utf-8").strip().split("\n")[-1]
        print("  {}: {}".format(table, count))

    client.close()
    print("\nCompletado!")


if __name__ == "__main__":
    main()
