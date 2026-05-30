import mysql.connector
import json

db_config = {
    "host": "auth-db690.hstgr.io",
    "user": "u156469157_aratio_v1",
    "password": "15zxCeBbvgsR",
    "database": "u156469157_aratio_v1"
}

def check_candidatos():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("SELECT id, nombre, campana_id FROM candidatos")
        candidatos = cursor.fetchall()
        print("Candidatos:")
        print(json.dumps(candidatos, indent=2))
        
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    check_candidatos()
