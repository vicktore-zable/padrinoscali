import mysql.connector
import json

db_config = {
    "host": "auth-db690.hstgr.io",
    "user": "u156469157_aratio_v1",
    "password": "15zxCeBbvgsR",
    "database": "u156469157_aratio_v1"
}

def find_edison():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        
        # Search for Edison in candidatos
        cursor.execute("SELECT id, nombres, apellidos, nombre_completo FROM candidatos WHERE nombre_completo LIKE '%Edison%' OR nombres LIKE '%Edison%'")
        candidatos = cursor.fetchall()
        print("Candidatos matching Edison:")
        print(json.dumps(candidatos, indent=2))
        
        # Search for Edison in campanas
        cursor.execute("SELECT id, nombre, candidato_nombre FROM campanas WHERE nombre LIKE '%Edison%' OR candidato_nombre LIKE '%Edison%'")
        campanas = cursor.fetchall()
        print("\nCampanas matching Edison:")
        print(json.dumps(campanas, indent=2))
        
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    find_edison()
