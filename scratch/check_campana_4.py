import mysql.connector
import json

db_config = {
    "host": "auth-db690.hstgr.io",
    "user": "u156469157_aratio_v1",
    "password": "15zxCeBbvgsR",
    "database": "u156469157_aratio_v1"
}

def check_campana_4():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("SELECT * FROM eventos WHERE campana_id = 4")
        eventos = cursor.fetchall()
        print("Events for Campana 4:")
        print(json.dumps(eventos, indent=2, default=str))
        
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    check_campana_4()
