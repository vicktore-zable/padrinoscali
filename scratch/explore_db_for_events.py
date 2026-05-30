import mysql.connector
import json

db_config = {
    "host": "auth-db690.hstgr.io",
    "user": "u156469157_aratio_v1",
    "password": "15zxCeBbvgsR",
    "database": "u156469157_aratio_v1"
}

def explore():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        
        # Get active campaigns
        cursor.execute("SELECT id, nombre, estado FROM campanas WHERE estado != 'eliminada'")
        campanas = cursor.fetchall()
        print("Active Campaigns:")
        print(json.dumps(campanas, indent=2))
        
        # Get users
        cursor.execute("SELECT id, nombre, email, rol FROM usuarios LIMIT 10")
        usuarios = cursor.fetchall()
        print("\nUsers:")
        print(json.dumps(usuarios, indent=2))
        
        # Get last events to see structure
        cursor.execute("SELECT * FROM eventos ORDER BY id DESC LIMIT 2")
        eventos = cursor.fetchall()
        print("\nLast Events:")
        print(json.dumps(eventos, indent=2, default=str))
        
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    explore()
