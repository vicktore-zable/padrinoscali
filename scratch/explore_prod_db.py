import mysql.connector
import json

db_config = {
    "host": "157.173.208.254",
    "user": "u577647812_aratio",
    "password": "v6xSHUWhjrxE",
    "database": "u577647812_aratio"
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
        
        # Get last events
        cursor.execute("SELECT id, nombre, campana_id FROM eventos ORDER BY id DESC LIMIT 5")
        eventos = cursor.fetchall()
        print("\nLast Events:")
        print(json.dumps(eventos, indent=2, default=str))
        
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    explore()
