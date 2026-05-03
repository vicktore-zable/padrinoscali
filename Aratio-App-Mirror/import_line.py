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

    # Leer el archivo SQL y ejecutar línea por línea
    print("Ejecutando SQL línea por línea...")

    with open("aratio_migration_rest.sql", "r", encoding="utf-8") as f:
        lines = f.readlines()

    errors = []
    current_stmt = ""
    line_num = 0

    for i, line in enumerate(lines):
        line_num = i + 1
        current_stmt += line

        # Si la línea termina en ; ejecutar
        if line.strip().endswith(";") and not line.strip().startswith("INSERT"):
            # Es un statement de creación
            stdin, stdout, stderr = client.exec_command(
                'mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e "{}" 2>&1'.format(
                    current_stmt.replace('"', '\\"')
                )
            )
            error = stderr.read().decode("utf-8", errors="replace")
            if error and "ERROR" in error:
                errors.append("Linea {}: {}".format(line_num, error[:200]))
            current_stmt = ""
        elif line.strip().startswith("INSERT INTO"):
            # Acumular INSERTs hasta encontrar ;
            pass
        elif "VALUES" in current_stmt and ";" in line:
            # Ejecutar INSERT
            stdin, stdout, stderr = client.exec_command(
                'mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e "{}" 2>&1'.format(
                    current_stmt.replace('"', '\\"').replace("\n", " ")
                )
            )
            error = stderr.read().decode("utf-8", errors="replace")
            if error and "ERROR" in error:
                errors.append("Linea {} INSERT: {}".format(line_num, error[:200]))
            current_stmt = ""

    if errors:
        print("\nErrores encontrados (primeros 5):")
        for e in errors[:5]:
            print("  {}".format(e))
    else:
        print("OK - Todo ejecuto sin errores!")

    # Verificar tablas
    stdin, stdout, stderr = client.exec_command(
        "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e 'SHOW TABLES;'"
    )
    print("\nTablas actuales:")
    print(stdout.read().decode("utf-8"))

    client.close()


if __name__ == "__main__":
    main()
