import mysql.connector

db_config = {
    "host": "157.173.208.254",
    "user": "u577647812_aratio",
    "password": "v6xSHUWhjrxE",
    "database": "u577647812_aratio"
}

def update_events():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        
        query = """
        UPDATE eventos 
        SET departamento = 'Valle del Cauca', municipio = 'Cali'
        WHERE id IN (1, 2)
        """
        
        cursor.execute(query)
        conn.commit()
        print(f"Updated {cursor.rowcount} events with geographic data.")
        
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    update_events()
