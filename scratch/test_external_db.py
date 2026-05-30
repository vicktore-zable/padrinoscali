import mysql.connector

DB_NAME = 'u577647812_aratio'
DB_USER = 'u577647812_aratio'
DB_PASS = 'v6xSHUWhjrxE'

hosts = ['srv1540.hstgr.io', 'auth-db690.hstgr.io', '157.173.208.254']

for host in hosts:
    print(f"Probando conexión externa a MySQL host: {host}...")
    try:
        conn = mysql.connector.connect(
            host=host,
            user=DB_USER,
            password=DB_PASS,
            database=DB_NAME,
            connection_timeout=5
        )
        print(f" -> EXITO conectando a {host}!")
        cursor = conn.cursor()
        cursor.execute("SELECT COUNT(*) FROM usuarios")
        count = cursor.fetchone()[0]
        print(f" -> Usuarios: {count}")
        conn.close()
    except Exception as e:
        print(f" -> ERROR: {e}")
