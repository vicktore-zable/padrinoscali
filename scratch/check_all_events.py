# -*- coding: utf-8 -*-
import mysql.connector

def check_events():
    config = {
        'host': 'auth-db690.hstgr.io',
        'database': 'u156469157_aratio_v1',
        'user': 'u156469157_aratio_v1',
        'password': '15zxCeBbvgsR'
    }
    
    try:
        conn = mysql.connector.connect(**config)
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("SELECT id, nombre, campana_id FROM eventos")
        events = cursor.fetchall()
        for e in events:
            print(f"ID: {e['id']}, Name: {e['nombre']}, Campaign: {e['campana_id']}")
            
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    check_events()
