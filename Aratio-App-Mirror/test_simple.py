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

    sftp = client.open_sftp()

    # Subir archivo SQL
    print("Subiendo archivo SQL...")
    sftp.put(
        r"H:\Mi unidad\2025\5d\app\Multi-Campaign Management System\aratio_migration_rest.sql",
        "/home/u577647812/aratio_migration_rest.sql",
    )
    sftp.close()
    print("OK - Archivo subido!")

    # Intentar importar - probar primero con una tabla simple
    print("\nProbando con tabla simple (acciones_comunitarias)...")

    # Crear SQL simple de prueba
    test_sql = """
DROP TABLE IF EXISTS acciones_comunitarias;
CREATE TABLE `acciones_comunitarias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campana_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` enum('puerta-puerta','brigada-salud','jornada-social') NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_accion` datetime NOT NULL,
  `departamento` varchar(100) NOT NULL,
  `municipio` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
"""

    # Escribir y ejecutar
    sftp = client.open_sftp()
    with sftp.file("/home/u577647812/test_simple.sql", "w") as f:
        f.write(test_sql.encode("utf-8"))
    sftp.close()

    stdin, stdout, stderr = client.exec_command(
        "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio < /home/u577647812/test_simple.sql 2>&1"
    )
    result = stdout.read().decode("utf-8", errors="replace")
    error = stderr.read().decode("utf-8", errors="replace")

    print("Resultado:", result[:200] if result else "OK")
    if error:
        print("Error:", error[:200])

    # Verificar
    stdin, stdout, stderr = client.exec_command(
        "mysql -u u577647812_aratio -pv6xSHUWhjrxE u577647812_aratio -e 'SHOW TABLES;'"
    )
    print("\nTablas:", stdout.read().decode("utf-8"))

    client.close()


if __name__ == "__main__":
    main()
