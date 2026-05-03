import pymysql

# Nueva base de datos
DB_HOST = "localhost"
DB_USER = "u577647812_aratio"
DB_PASS = "v6xSHUWhjrxE"
DB_NAME = "u577647812_aratio"

print("Conectando a la base de datos del nuevo servidor...")
print("Host: {}".format(DB_HOST))
print("User: {}".format(DB_USER))
print("DB: {}".format(DB_NAME))

try:
    conn = pymysql.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASS,
        database=DB_NAME,
        charset="utf8mb4",
    )
    print("OK - Conexion exitosa!")

    cursor = conn.cursor()

    # Verificar tablas existentes
    cursor.execute("SHOW TABLES")
    tables = cursor.fetchall()

    print("\nTablas existentes: {}".format(len(tables)))
    for t in tables:
        print("  - {}".format(t[0]))

    conn.close()
    print("\nBase de datos lista para usar!")

except Exception as e:
    print("Error al conectar: {}".format(e))
