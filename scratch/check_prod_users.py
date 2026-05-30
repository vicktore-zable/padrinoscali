import mysql.connector
import json

db_config = {
    "host": "157.173.208.254",
    "user": "u577647812_aratio",
    "password": "v6xSHUWhjrxE",
    "database": "u577647812_aratio"
}

def check_users():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("SELECT id, nombre, email, rol FROM usuarios")
        usuarios = cursor.fetchall()
        print("Users:")
        print(json.dumps(usuarios, indent=2))
        
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    check_users()
