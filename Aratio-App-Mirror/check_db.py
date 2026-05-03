import pymysql

HOST = "auth-db690.hstgr.io"
USER = "u156469157_aratio_v1"
PASS = "15zxCeBbvgsR"
DB = "u156469157_aratio_v1"

conn = pymysql.connect(host=HOST, user=USER, password=PASS, database=DB)
cursor = conn.cursor()

# Obtener todas las tablas y sus cuentas
cursor.execute("SHOW TABLES")
tables = cursor.fetchall()

print("TOTAL TABLAS: {}".format(len(tables)))
print("")
for t in tables:
    name = t[0]
    cursor.execute("SELECT COUNT(*) FROM {}".format(name))
    count = cursor.fetchone()[0]
    print("  {}: {}".format(name, count))

conn.close()
